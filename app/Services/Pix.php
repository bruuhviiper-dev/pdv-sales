<?php

namespace App\Services;

/**
 * Gerador de BR Code PIX (payload "Copia e Cola" / QR Code estático).
 * Segue o padrão EMV®/MPM do Banco Central. Funciona 100% offline.
 */
class Pix
{
    /**
     * Monta o payload PIX (string Copia e Cola).
     *
     * @param string $chave        Chave PIX (CPF/CNPJ/email/telefone/aleatória)
     * @param string $beneficiario Nome do recebedor (max 25)
     * @param string $cidade       Cidade do recebedor (max 15)
     * @param float|null $valor     Valor da transação (null = aberto)
     * @param string $txid         Identificador (padrão "***")
     */
    public static function payload(string $chave, string $beneficiario, string $cidade, ?float $valor = null, string $txid = '***'): string
    {
        $beneficiario = self::sanitizar($beneficiario, 25);
        $cidade       = self::sanitizar($cidade, 15);

        // Conta do recebedor (ID 26)
        $mai  = self::campo('00', 'br.gov.bcb.pix') . self::campo('01', $chave);
        $conta = self::campo('26', $mai);

        $payload  = self::campo('00', '01');                      // Payload Format Indicator
        $payload .= $conta;                                       // Merchant Account Info (PIX)
        $payload .= self::campo('52', '0000');                    // Merchant Category Code
        $payload .= self::campo('53', '986');                     // Moeda BRL
        if ($valor !== null && $valor > 0) {
            $payload .= self::campo('54', number_format($valor, 2, '.', ''));
        }
        $payload .= self::campo('58', 'BR');                      // País
        $payload .= self::campo('59', $beneficiario ?: 'RECEBEDOR');
        $payload .= self::campo('60', $cidade ?: 'CIDADE');
        $payload .= self::campo('62', self::campo('05', $txid));  // Additional Data (txid)
        $payload .= '6304';                                       // CRC16 (id+len) antes do cálculo

        return $payload . self::crc16($payload);
    }

    private static function campo(string $id, string $valor): string
    {
        $len = str_pad((string) strlen($valor), 2, '0', STR_PAD_LEFT);
        return $id . $len . $valor;
    }

    private static function sanitizar(string $texto, int $max): string
    {
        $texto = preg_replace('/[^A-Za-z0-9 ]/', '', self::semAcento($texto));
        return strtoupper(trim(substr($texto, 0, $max)));
    }

    private static function semAcento(string $texto): string
    {
        $de = ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','Á','À','Ã','Â','É','Ê','Í','Ó','Õ','Ô','Ú','Ç'];
        $para = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','A','A','A','A','E','E','I','O','O','O','U','C'];
        return str_replace($de, $para, $texto);
    }

    /** CRC16-CCITT (polinômio 0x1021, inicial 0xFFFF) */
    private static function crc16(string $payload): string
    {
        $polinomio = 0x1021;
        $resultado = 0xFFFF;
        for ($i = 0; $i < strlen($payload); $i++) {
            $resultado ^= (ord($payload[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                $resultado = ($resultado & 0x8000)
                    ? (($resultado << 1) ^ $polinomio)
                    : ($resultado << 1);
                $resultado &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
    }
}
