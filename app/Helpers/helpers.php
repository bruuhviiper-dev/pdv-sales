<?php

use App\Models\Configuracao;

if (! function_exists('moeda')) {
    /**
     * Formata um valor monetário usando o símbolo configurado pelo estabelecimento.
     * Ex: moeda(1234.5) => "R$ 1.234,50"
     */
    function moeda($valor, bool $comSimbolo = true): string
    {
        $simbolo = Configuracao::get('moeda_simbolo', 'R$');
        $numero  = number_format((float) $valor, 2, ',', '.');
        return $comSimbolo ? trim($simbolo . ' ' . $numero) : $numero;
    }
}

if (! function_exists('simbolo_moeda')) {
    function simbolo_moeda(): string
    {
        return Configuracao::get('moeda_simbolo', 'R$');
    }
}
