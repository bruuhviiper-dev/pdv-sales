<?php

namespace App\Services;

use App\Models\Configuracao;
use App\Models\Venda;
use Illuminate\Support\Facades\Http;

/**
 * Emissão de NFC-e via API (Focus NFe).
 *
 * Requer:
 *  - Conta no provedor (focusnfe.com.br) com token de API
 *  - Certificado digital A1 cadastrado no painel do provedor
 *
 * Configurável em: Configurações > NFC-e.
 */
class NotaFiscalService
{
    public static function configurado(): bool
    {
        return !empty(Configuracao::get('nfce_token'));
    }

    public static function ambiente(): string
    {
        return Configuracao::get('nfce_ambiente', 'homologacao');
    }

    protected static function baseUrl(): string
    {
        return self::ambiente() === 'producao'
            ? 'https://api.focusnfe.com.br'
            : 'https://homologacao.focusnfe.com.br';
    }

    /**
     * Testa a conexão/autenticação com o provedor no ambiente atual.
     * Retorna ['ok' => bool, 'mensagem' => string].
     */
    public static function testarConexao(): array
    {
        if (!self::configurado()) {
            return ['ok' => false, 'mensagem' => 'Informe o token da API antes de testar.'];
        }

        try {
            $resp = Http::withBasicAuth(Configuracao::get('nfce_token'), '')
                ->timeout(20)
                ->acceptJson()
                ->get(self::baseUrl() . '/v2/empresas');

            $amb = self::ambiente() === 'producao' ? 'Produção' : 'Homologação';

            if ($resp->successful()) {
                return ['ok' => true, 'mensagem' => "Conexão OK com o provedor ({$amb}). Token válido."];
            }
            if ($resp->status() === 403 || $resp->status() === 401) {
                return ['ok' => false, 'mensagem' => "Token inválido para o ambiente de {$amb} (HTTP {$resp->status()}). Confira o token e o ambiente."];
            }
            return ['ok' => false, 'mensagem' => "Provedor respondeu HTTP {$resp->status()} em {$amb}."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensagem' => 'Falha de conexão: ' . $e->getMessage()];
        }
    }

    /** Itens pendentes para emitir NFC-e em produção (checklist de prontidão). */
    public static function pendencias(): array
    {
        $p = [];
        if (empty(Configuracao::get('empresa_cnpj')))  $p[] = 'Informe o CNPJ da empresa (Dados da Empresa).';
        if (empty(Configuracao::get('empresa_cidade'))) $p[] = 'Informe a cidade da empresa.';
        if (empty(Configuracao::get('empresa_estado'))) $p[] = 'Informe o estado (UF) da empresa.';
        if (!self::configurado())                       $p[] = 'Informe o token da API do provedor.';
        if (empty(\App\Models\Produto::query()->whereNotNull('ncm')->where('ncm', '!=', '')->exists()))
            $p[] = 'Cadastre o NCM nos produtos (ou um NCM padrão) — exigido pela SEFAZ.';
        return $p;
    }

    /**
     * Emite a NFC-e para a venda. Retorna ['ok' => bool, 'mensagem' => string].
     */
    public static function emitir(Venda $venda): array
    {
        if (!self::configurado()) {
            return ['ok' => false, 'mensagem' => 'Token da NFC-e não configurado. Acesse Configurações > NFC-e.'];
        }

        $token = Configuracao::get('nfce_token');
        $ref   = 'venda-' . $venda->id;

        // Padrões fiscais (usados quando o produto não tem o dado preenchido)
        $defCfop   = Configuracao::get('fiscal_cfop', '5102');
        $defSit    = Configuracao::get('fiscal_situacao', '102');
        $defOrigem = Configuracao::get('fiscal_origem', '0');
        $defNcm    = preg_replace('/\D/', '', (string) Configuracao::get('fiscal_ncm', ''));

        $venda->loadMissing('itens.produto');

        $payload = [
            'cnpj_emitente'        => preg_replace('/\D/', '', Configuracao::get('empresa_cnpj', '')),
            'natureza_operacao'    => 'Venda ao consumidor',
            'presenca_comprador'   => '1', // operação presencial
            'modalidade_frete'     => '9',
            'local_destino'        => '1',
            'itens'                => $venda->itens->values()->map(function ($item, $i) use ($defCfop, $defSit, $defOrigem, $defNcm) {
                $p   = $item->produto;
                $ncm = $p && $p->ncm ? preg_replace('/\D/', '', $p->ncm) : $defNcm;
                $unid = $p->unidade ?? 'UN';
                $linha = [
                    'numero_item'                 => $i + 1,
                    'codigo_produto'              => $p->sku ?? ($p->id ?? ($i + 1)),
                    'descricao'                   => $item->produto_nome,
                    'codigo_ncm'                  => $ncm ?: '00000000',
                    'cfop'                        => $p->cfop ?? $defCfop,
                    'quantidade_comercial'        => $item->quantidade,
                    'quantidade_tributavel'       => $item->quantidade,
                    'valor_unitario_comercial'    => number_format($item->preco_unitario, 2, '.', ''),
                    'valor_unitario_tributavel'   => number_format($item->preco_unitario, 2, '.', ''),
                    'valor_bruto'                 => number_format($item->subtotal, 2, '.', ''),
                    'icms_origem'                 => $p->origem ?? $defOrigem,
                    'icms_situacao_tributaria'    => $p->situacao_tributaria ?? $defSit,
                    'unidade_comercial'           => $unid,
                    'unidade_tributavel'          => $unid,
                ];
                if ($p && $p->cest) {
                    $linha['cest'] = preg_replace('/\D/', '', $p->cest);
                }
                return $linha;
            })->toArray(),
            'formas_pagamento' => [[
                'forma_pagamento' => self::mapForma($venda->forma_pagamento),
                'valor_pagamento' => number_format($venda->total, 2, '.', ''),
            ]],
        ];

        try {
            $resp = Http::withBasicAuth($token, '')
                ->timeout(30)
                ->post(self::baseUrl() . "/v2/nfce?ref={$ref}", $payload);

            $data = $resp->json() ?? [];
            $status = $data['status'] ?? 'erro';

            if (in_array($status, ['autorizado', 'processando_autorizacao'])) {
                $venda->update([
                    'nfce_status'   => $status === 'autorizado' ? 'autorizada' : 'processando',
                    'nfce_chave'    => $data['chave_nfe'] ?? null,
                    'nfce_url'      => $data['caminho_danfe'] ?? ($data['url'] ?? null),
                    'nfce_mensagem' => $data['mensagem'] ?? 'Enviada para autorização.',
                ]);
                return ['ok' => true, 'mensagem' => 'NFC-e enviada com sucesso!'];
            }

            $msg = $data['mensagem'] ?? ($data['erros'][0]['mensagem'] ?? 'Falha na emissão.');
            $venda->update(['nfce_status' => 'erro', 'nfce_mensagem' => $msg]);
            return ['ok' => false, 'mensagem' => "NFC-e: {$msg}"];

        } catch (\Throwable $e) {
            $venda->update(['nfce_status' => 'erro', 'nfce_mensagem' => $e->getMessage()]);
            return ['ok' => false, 'mensagem' => 'Erro ao conectar ao provedor: ' . $e->getMessage()];
        }
    }

    protected static function mapForma(string $forma): string
    {
        return match ($forma) {
            'dinheiro'       => '01',
            'cartao_credito' => '03',
            'cartao_debito'  => '04',
            'pix'            => '17',
            'fiado'          => '15',
            default          => '99',
        };
    }
}
