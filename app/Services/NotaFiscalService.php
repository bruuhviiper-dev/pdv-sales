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
     * Emite a NFC-e para a venda. Retorna ['ok' => bool, 'mensagem' => string].
     */
    public static function emitir(Venda $venda): array
    {
        if (!self::configurado()) {
            return ['ok' => false, 'mensagem' => 'Token da NFC-e não configurado. Acesse Configurações > NFC-e.'];
        }

        $token = Configuracao::get('nfce_token');
        $ref   = 'venda-' . $venda->id;

        $payload = [
            'cnpj_emitente'        => preg_replace('/\D/', '', Configuracao::get('empresa_cnpj', '')),
            'natureza_operacao'    => 'Venda ao consumidor',
            'presenca_comprador'   => '1', // operação presencial
            'modalidade_frete'     => '9',
            'local_destino'        => '1',
            'itens'                => $venda->itens->values()->map(function ($item, $i) {
                return [
                    'numero_item'                 => $i + 1,
                    'codigo_ncm'                  => '00000000',
                    'quantidade_comercial'        => $item->quantidade,
                    'quantidade_tributavel'       => $item->quantidade,
                    'cfop'                        => '5102',
                    'valor_unitario_comercial'    => number_format($item->preco_unitario, 2, '.', ''),
                    'valor_unitario_tributavel'   => number_format($item->preco_unitario, 2, '.', ''),
                    'valor_bruto'                 => number_format($item->subtotal, 2, '.', ''),
                    'descricao'                   => $item->produto_nome,
                    'icms_origem'                 => '0',
                    'icms_situacao_tributaria'    => '102',
                    'unidade_comercial'           => 'UN',
                    'unidade_tributavel'          => 'UN',
                ];
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
