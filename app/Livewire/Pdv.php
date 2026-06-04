<?php

namespace App\Livewire;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\EstoqueMovimentacao;
use App\Models\Caixa;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

/**
 * Componente Livewire do PDV (Frente de Caixa).
 *
 * É o coração do sistema: controla a busca de produtos, o carrinho, as formas
 * de pagamento, o cálculo de totais/troco e a gravação da venda (com baixa de
 * estoque e, opcionalmente, emissão de NFC-e).
 *
 * Como o estado vive no servidor (Livewire), cada ação do operador dispara uma
 * requisição. Por isso métodos como adicionarProduto/finalizarVenda recalculam
 * tudo a partir das propriedades públicas abaixo.
 *
 * Regras de negócio principais:
 *  - Só vende produto ativo e com estoque (quando ele controla estoque).
 *  - O total nunca fica negativo (desconto limitado ao subtotal).
 *  - Em dinheiro, o valor pago precisa cobrir o total (senão bloqueia a venda).
 *  - A gravação roda dentro de uma transação para não deixar venda "pela metade".
 */
class Pdv extends Component
{
    /** Texto digitado/bipado na busca de produtos. */
    public string $busca = '';

    /** Categoria selecionada na grade (null = todas). */
    public ?int $categoriaId = null;

    /** Itens do carrinho. Chave = "p_{id}", valor = dados do item (nome, preço, quantidade, subtotal...). */
    public array $carrinho = [];

    /** Cliente vinculado à venda (null = Consumidor Final). */
    public ?int $clienteId = null;

    /** Forma de pagamento atual: dinheiro | pix | cartao_debito | cartao_credito | fiado. */
    public string $formaPagamento = 'dinheiro';

    /** Desconto em valor (R$) aplicado sobre o subtotal. Anulável: o campo pode ser limpo no input. */
    public ?float $desconto = 0;

    /** Valor recebido em dinheiro (usado para calcular o troco). Anulável pelo mesmo motivo. */
    public ?float $valorPago = 0;

    /** Número de parcelas (só relevante no cartão de crédito). */
    public int $parcelas = 1;

    /** Observações livres da venda. */
    public string $observacoes = '';

    /** Indica que a última venda foi concluída (controla a exibição do modal de sucesso). */
    public bool $vendaConcluida = false;

    /** Resumo da última venda concluída (para o modal e o recibo). */
    public ?array $ultimaVenda = null;

    /** Sugestões de produtos da busca por texto (lista suspensa). */
    public array $produtosFiltrados = [];

    /**
     * Disparado automaticamente pelo Livewire sempre que a propriedade $busca muda.
     * Monta as sugestões e, se o operador bipou um código de barras exato,
     * já adiciona o produto direto ao carrinho (fluxo de leitor de código).
     */
    public function updatedBusca(): void
    {
        // Só busca a partir de 2 caracteres para não consultar o banco à toa.
        if (strlen($this->busca) < 2) {
            $this->produtosFiltrados = [];
            return;
        }

        $resultados = Produto::where('ativo', true)
            ->where('estoque_atual', '>', 0)
            ->where(function ($q) {
                // Casa por nome (parcial), código de barras (exato) ou SKU (parcial).
                $q->where('nome', 'like', "%{$this->busca}%")
                  ->orWhere('codigo_barras', $this->busca)
                  ->orWhere('sku', 'like', "%{$this->busca}%");
            })
            ->limit(12)
            ->get(['id', 'nome', 'preco_venda', 'estoque_atual', 'codigo_barras', 'unidade'])
            ->toArray();

        // Leitor de código de barras: 1 resultado cujo código bate exatamente => adiciona direto.
        if (count($resultados) === 1 && $resultados[0]['codigo_barras'] === $this->busca) {
            $this->adicionarProduto($resultados[0]['id']);
            $this->produtosFiltrados = [];
            return;
        }

        $this->produtosFiltrados = $resultados;
    }

    /**
     * Seleciona uma categoria na grade de toque (ou null para "Todos").
     * Limpa a busca para a grade voltar a mostrar a categoria escolhida.
     */
    public function selecionarCategoria(?int $id): void
    {
        $this->categoriaId = $id;
        $this->busca = '';
        $this->produtosFiltrados = [];
    }

    /**
     * Categorias exibidas como "abas" na grade.
     * Traz apenas categorias ativas que tenham ao menos um produto vendável
     * (ativo e com estoque), para não mostrar aba vazia.
     */
    #[Computed]
    public function categorias()
    {
        return Categoria::where('ativo', true)
            ->whereHas('produtos', fn ($q) => $q->where('ativo', true)->where('estoque_atual', '>', 0))
            ->orderBy('nome')
            ->get(['id', 'nome', 'cor']);
    }

    /**
     * Produtos mostrados na grade de toque.
     * Prioridade: se há busca (>=2 letras) filtra por ela; senão filtra pela
     * categoria selecionada; senão lista os primeiros 40 produtos vendáveis.
     */
    #[Computed]
    public function grade()
    {
        $query = Produto::where('ativo', true)->where('estoque_atual', '>', 0);

        if (strlen($this->busca) >= 2) {
            $query->where(function ($q) {
                $q->where('nome', 'like', "%{$this->busca}%")
                  ->orWhere('codigo_barras', $this->busca)
                  ->orWhere('sku', 'like', "%{$this->busca}%");
            });
        } elseif ($this->categoriaId) {
            $query->where('categoria_id', $this->categoriaId);
        }

        return $query->orderBy('nome')
            ->limit(40)
            ->get(['id', 'nome', 'preco_venda', 'estoque_atual', 'codigo_barras', 'unidade', 'foto']);
    }

    /**
     * Adiciona um produto ao carrinho (ou soma +1 se já estiver lá).
     * Respeita o estoque disponível quando o produto controla estoque.
     */
    public function adicionarProduto(int $produtoId): void
    {
        $produto = Produto::find($produtoId);
        if (!$produto || !$produto->ativo) return; // produto inexistente/inativo: ignora

        $key = 'p_' . $produtoId; // chave única do item no carrinho

        if (isset($this->carrinho[$key])) {
            // Já existe: incrementa a quantidade, validando o estoque.
            $novaQtd = $this->carrinho[$key]['quantidade'] + 1;
            if ($produto->controla_estoque && $novaQtd > $produto->estoque_atual) {
                $this->dispatch('alerta', mensagem: 'Estoque insuficiente para esse produto.');
                return;
            }
            $this->carrinho[$key]['quantidade'] = $novaQtd;
            $this->carrinho[$key]['subtotal'] = round($novaQtd * $this->carrinho[$key]['preco'], 2);
        } else {
            // Primeiro item deste produto: guarda um "snapshot" dos dados no carrinho.
            $this->carrinho[$key] = [
                'produto_id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => $produto->preco_venda,
                'preco_custo' => $produto->preco_custo, // guardado para apurar lucro depois
                'quantidade' => 1,
                'subtotal' => $produto->preco_venda,
                'estoque_max' => $produto->estoque_atual,
                'unidade' => $produto->unidade,
            ];
        }

        // Limpa a busca para o operador já digitar/bipar o próximo item.
        $this->busca = '';
        $this->produtosFiltrados = [];
    }

    /** Remove um item do carrinho pela sua chave. */
    public function removerItem(string $key): void
    {
        unset($this->carrinho[$key]);
    }

    /**
     * Define a quantidade exata de um item (botões +/− chamam este método).
     * Quantidade zero ou negativa remove o item.
     */
    public function atualizarQuantidade(string $key, int $quantidade): void
    {
        if ($quantidade <= 0) {
            $this->removerItem($key);
            return;
        }
        if (isset($this->carrinho[$key])) {
            $this->carrinho[$key]['quantidade'] = $quantidade;
            $this->carrinho[$key]['subtotal'] = round($quantidade * $this->carrinho[$key]['preco'], 2);
        }
    }

    /** Soma dos subtotais dos itens (antes do desconto). */
    #[Computed]
    public function subtotal(): float
    {
        return round(array_sum(array_column($this->carrinho, 'subtotal')), 2);
    }

    /** Total a pagar = subtotal − desconto, nunca menor que zero. */
    #[Computed]
    public function total(): float
    {
        return round(max(0, $this->subtotal - (float) $this->desconto), 2);
    }

    /** Troco = valor recebido − total (apenas em dinheiro; nunca negativo). */
    #[Computed]
    public function troco(): float
    {
        return $this->formaPagamento === 'dinheiro'
            ? max(0, round((float) $this->valorPago - $this->total, 2))
            : 0;
    }

    /** Verdadeiro se a loja tem uma chave PIX cadastrada (libera o QR Code). */
    #[Computed]
    public function pixConfigurado(): bool
    {
        return !empty(\App\Models\Configuracao::get('pix_chave'));
    }

    /**
     * Gera o payload "PIX Copia e Cola" para o total atual da venda.
     * Retorna null se não houver chave PIX ou se o total for zero.
     */
    #[Computed]
    public function pixPayload(): ?string
    {
        if (!$this->pixConfigurado || $this->total <= 0) {
            return null;
        }
        return \App\Services\Pix::payload(
            \App\Models\Configuracao::get('pix_chave'),
            // Beneficiário/cidade caem para o nome/cidade da empresa se não configurados.
            \App\Models\Configuracao::get('pix_beneficiario', \App\Models\Configuracao::get('empresa_nome', 'RECEBEDOR')),
            \App\Models\Configuracao::get('pix_cidade', \App\Models\Configuracao::get('empresa_cidade', 'CIDADE')),
            $this->total
        );
    }

    /**
     * Conclui a venda: valida, grava em transação (venda + itens + baixa de
     * estoque) e, se configurado, emite a NFC-e automaticamente.
     */
    public function finalizarVenda(): void
    {
        // Validações de negócio antes de gravar.
        if (empty($this->carrinho)) {
            $this->dispatch('alerta', mensagem: 'Adicione produtos ao carrinho.');
            return;
        }
        if ($this->formaPagamento === 'dinheiro' && (float) $this->valorPago < $this->total) {
            $this->dispatch('alerta', mensagem: 'Valor pago é insuficiente.');
            return;
        }

        // Transação: ou grava tudo (venda + itens + estoque), ou nada.
        DB::transaction(function () {
            $venda = Venda::create([
                'numero_venda' => Venda::gerarNumero(), // número sequencial amigável (ex.: VD000124)
                'cliente_id' => $this->clienteId,
                'user_id' => auth()->id(),              // operador que fez a venda
                'subtotal' => $this->subtotal,
                'desconto' => (float) $this->desconto,
                'total' => $this->total,
                'forma_pagamento' => $this->formaPagamento,
                // Em dinheiro registra o valor recebido; nos demais, o próprio total.
                'valor_pago' => $this->formaPagamento === 'dinheiro' ? (float) $this->valorPago : $this->total,
                'troco' => $this->troco,
                'parcelas' => $this->parcelas,
                'status' => 'concluida',
                'observacoes' => $this->observacoes,
            ]);

            foreach ($this->carrinho as $item) {
                // Grava o item com "preço/nome congelados" no momento da venda
                // (para o histórico não mudar se o produto for editado depois).
                VendaItem::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $item['produto_id'],
                    'produto_nome' => $item['nome'],
                    'preco_unitario' => $item['preco'],
                    'preco_custo' => $item['preco_custo'],
                    'quantidade' => $item['quantidade'],
                    'desconto' => 0,
                    'subtotal' => $item['subtotal'],
                ]);

                // Baixa de estoque com lockForUpdate para evitar venda concorrente do mesmo item.
                $produto = Produto::lockForUpdate()->find($item['produto_id']);
                if ($produto && $produto->controla_estoque) {
                    $estoqueAnterior = $produto->estoque_atual;
                    $produto->estoque_atual -= $item['quantidade'];
                    $produto->save();

                    // Registra a movimentação para auditoria/histórico de estoque.
                    EstoqueMovimentacao::create([
                        'produto_id' => $produto->id,
                        'user_id' => auth()->id(),
                        'venda_id' => $venda->id,
                        'tipo' => 'saida',
                        'quantidade' => $item['quantidade'],
                        'estoque_anterior' => $estoqueAnterior,
                        'estoque_atual' => $produto->estoque_atual,
                        'motivo' => "Venda {$venda->numero_venda}",
                    ]);
                }
            }

            // Resumo usado pelo modal de "venda concluída" e pelo recibo.
            $this->ultimaVenda = [
                'id' => $venda->id,
                'numero' => $venda->numero_venda,
                'total' => $venda->total,
                'forma_pagamento' => $venda->formaPagamentoLabel(),
                'troco' => $venda->troco,
                'itens' => count($this->carrinho),
                'nfce_status' => null,
                'nfce_url' => null,
            ];
        });

        // Emissão automática de NFC-e (FORA da transação, pois é chamada de rede
        // ao provedor e não deve travar/reverter o banco se a internet falhar).
        if (\App\Services\NotaFiscalService::configurado() && \App\Models\Configuracao::get('nfce_auto') === '1') {
            $vendaModel = Venda::with('itens.produto')->find($this->ultimaVenda['id']);
            $r = \App\Services\NotaFiscalService::emitir($vendaModel);
            $vendaModel->refresh(); // recarrega os campos nfce_* atualizados pelo serviço
            $this->ultimaVenda['nfce_status'] = $vendaModel->nfce_status;
            $this->ultimaVenda['nfce_url']    = $vendaModel->nfce_url;
            $this->ultimaVenda['nfce_msg']    = $r['mensagem'];
        }

        // Zera o carrinho e sinaliza sucesso (a view abre o modal via este evento).
        $this->limparVenda();
        $this->vendaConcluida = true;
        $this->dispatch('venda-finalizada');
    }

    /** Esvazia o carrinho e volta as opções de pagamento ao padrão. */
    public function limparVenda(): void
    {
        $this->carrinho = [];
        $this->clienteId = null;
        $this->formaPagamento = 'dinheiro';
        $this->desconto = 0;
        $this->valorPago = 0;
        $this->parcelas = 1;
        $this->observacoes = '';
        $this->busca = '';
        $this->produtosFiltrados = [];
    }

    /** Fecha o modal de sucesso e prepara a tela para a próxima venda. */
    public function novaVenda(): void
    {
        $this->vendaConcluida = false;
        $this->ultimaVenda = null;
    }

    /**
     * Renderiza o componente.
     * Carrega a lista de clientes (para vincular à venda) e verifica se há
     * caixa aberto — sem caixa aberto o PDV bloqueia a finalização.
     */
    public function render()
    {
        $clientes = Cliente::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'saldo_fiado', 'limite_fiado']);
        $caixaAberto = Caixa::where('status', 'aberto')->exists();
        return view('livewire.pdv', compact('clientes', 'caixaAberto'));
    }
}
