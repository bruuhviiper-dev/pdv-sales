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

class Pdv extends Component
{
    public string $busca = '';
    public ?int $categoriaId = null;
    public array $carrinho = [];
    public ?int $clienteId = null;
    public string $formaPagamento = 'dinheiro';
    public ?float $desconto = 0;
    public ?float $valorPago = 0;
    public int $parcelas = 1;
    public string $observacoes = '';
    public bool $vendaConcluida = false;
    public ?array $ultimaVenda = null;
    public array $produtosFiltrados = [];

    public function updatedBusca(): void
    {
        if (strlen($this->busca) < 2) {
            $this->produtosFiltrados = [];
            return;
        }

        $resultados = Produto::where('ativo', true)
            ->where('estoque_atual', '>', 0)
            ->where(function ($q) {
                $q->where('nome', 'like', "%{$this->busca}%")
                  ->orWhere('codigo_barras', $this->busca)
                  ->orWhere('sku', 'like', "%{$this->busca}%");
            })
            ->limit(12)
            ->get(['id', 'nome', 'preco_venda', 'estoque_atual', 'codigo_barras', 'unidade'])
            ->toArray();

        // Bipou um código de barras exato? adiciona direto ao carrinho.
        if (count($resultados) === 1 && $resultados[0]['codigo_barras'] === $this->busca) {
            $this->adicionarProduto($resultados[0]['id']);
            $this->produtosFiltrados = [];
            return;
        }

        $this->produtosFiltrados = $resultados;
    }

    public function selecionarCategoria(?int $id): void
    {
        $this->categoriaId = $id;
        $this->busca = '';
        $this->produtosFiltrados = [];
    }

    /** Categorias que possuem produtos vendáveis (ativos e com estoque). */
    #[Computed]
    public function categorias()
    {
        return Categoria::where('ativo', true)
            ->whereHas('produtos', fn ($q) => $q->where('ativo', true)->where('estoque_atual', '>', 0))
            ->orderBy('nome')
            ->get(['id', 'nome', 'cor']);
    }

    /** Produtos exibidos na grade de toque (resultado da busca ou da categoria selecionada). */
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

    public function adicionarProduto(int $produtoId): void
    {
        $produto = Produto::find($produtoId);
        if (!$produto || !$produto->ativo) return;

        $key = 'p_' . $produtoId;

        if (isset($this->carrinho[$key])) {
            $novaQtd = $this->carrinho[$key]['quantidade'] + 1;
            if ($produto->controla_estoque && $novaQtd > $produto->estoque_atual) {
                $this->dispatch('alerta', mensagem: 'Estoque insuficiente para esse produto.');
                return;
            }
            $this->carrinho[$key]['quantidade'] = $novaQtd;
            $this->carrinho[$key]['subtotal'] = round($novaQtd * $this->carrinho[$key]['preco'], 2);
        } else {
            $this->carrinho[$key] = [
                'produto_id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => $produto->preco_venda,
                'preco_custo' => $produto->preco_custo,
                'quantidade' => 1,
                'subtotal' => $produto->preco_venda,
                'estoque_max' => $produto->estoque_atual,
                'unidade' => $produto->unidade,
            ];
        }

        $this->busca = '';
        $this->produtosFiltrados = [];
    }

    public function removerItem(string $key): void
    {
        unset($this->carrinho[$key]);
    }

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

    #[Computed]
    public function subtotal(): float
    {
        return round(array_sum(array_column($this->carrinho, 'subtotal')), 2);
    }

    #[Computed]
    public function total(): float
    {
        return round(max(0, $this->subtotal - (float) $this->desconto), 2);
    }

    #[Computed]
    public function troco(): float
    {
        return $this->formaPagamento === 'dinheiro'
            ? max(0, round((float) $this->valorPago - $this->total, 2))
            : 0;
    }

    #[Computed]
    public function pixConfigurado(): bool
    {
        return !empty(\App\Models\Configuracao::get('pix_chave'));
    }

    #[Computed]
    public function pixPayload(): ?string
    {
        if (!$this->pixConfigurado || $this->total <= 0) {
            return null;
        }
        return \App\Services\Pix::payload(
            \App\Models\Configuracao::get('pix_chave'),
            \App\Models\Configuracao::get('pix_beneficiario', \App\Models\Configuracao::get('empresa_nome', 'RECEBEDOR')),
            \App\Models\Configuracao::get('pix_cidade', \App\Models\Configuracao::get('empresa_cidade', 'CIDADE')),
            $this->total
        );
    }

    public function finalizarVenda(): void
    {
        if (empty($this->carrinho)) {
            $this->dispatch('alerta', mensagem: 'Adicione produtos ao carrinho.');
            return;
        }

        if ($this->formaPagamento === 'dinheiro' && (float) $this->valorPago < $this->total) {
            $this->dispatch('alerta', mensagem: 'Valor pago é insuficiente.');
            return;
        }

        DB::transaction(function () {
            $venda = Venda::create([
                'numero_venda' => Venda::gerarNumero(),
                'cliente_id' => $this->clienteId,
                'user_id' => auth()->id(),
                'subtotal' => $this->subtotal,
                'desconto' => (float) $this->desconto,
                'total' => $this->total,
                'forma_pagamento' => $this->formaPagamento,
                'valor_pago' => $this->formaPagamento === 'dinheiro' ? (float) $this->valorPago : $this->total,
                'troco' => $this->troco,
                'parcelas' => $this->parcelas,
                'status' => 'concluida',
                'observacoes' => $this->observacoes,
            ]);

            foreach ($this->carrinho as $item) {
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

                $produto = Produto::lockForUpdate()->find($item['produto_id']);
                if ($produto && $produto->controla_estoque) {
                    $estoqueAnterior = $produto->estoque_atual;
                    $produto->estoque_atual -= $item['quantidade'];
                    $produto->save();

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

        // Emissão automática de NFC-e (fora da transação — chamada de rede)
        if (\App\Services\NotaFiscalService::configurado() && \App\Models\Configuracao::get('nfce_auto') === '1') {
            $vendaModel = Venda::with('itens.produto')->find($this->ultimaVenda['id']);
            $r = \App\Services\NotaFiscalService::emitir($vendaModel);
            $vendaModel->refresh();
            $this->ultimaVenda['nfce_status'] = $vendaModel->nfce_status;
            $this->ultimaVenda['nfce_url']    = $vendaModel->nfce_url;
            $this->ultimaVenda['nfce_msg']    = $r['mensagem'];
        }

        $this->limparVenda();
        $this->vendaConcluida = true;
        $this->dispatch('venda-finalizada');
    }

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

    public function novaVenda(): void
    {
        $this->vendaConcluida = false;
        $this->ultimaVenda = null;
    }

    public function render()
    {
        $clientes = Cliente::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'saldo_fiado', 'limite_fiado']);
        $caixaAberto = Caixa::where('status', 'aberto')->exists();
        return view('livewire.pdv', compact('clientes', 'caixaAberto'));
    }
}
