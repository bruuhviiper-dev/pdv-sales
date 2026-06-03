<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracao;

class ConfiguracoesSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['chave' => 'empresa_nome', 'valor' => 'Meu Comércio', 'grupo' => 'empresa'],
            ['chave' => 'empresa_cnpj', 'valor' => '', 'grupo' => 'empresa'],
            ['chave' => 'empresa_telefone', 'valor' => '', 'grupo' => 'empresa'],
            ['chave' => 'empresa_email', 'valor' => '', 'grupo' => 'empresa'],
            ['chave' => 'empresa_endereco', 'valor' => '', 'grupo' => 'empresa'],
            ['chave' => 'empresa_cidade', 'valor' => '', 'grupo' => 'empresa'],
            ['chave' => 'empresa_estado', 'valor' => '', 'grupo' => 'empresa'],
            ['chave' => 'empresa_cep', 'valor' => '', 'grupo' => 'empresa'],
            ['chave' => 'moeda_simbolo', 'valor' => 'R$', 'grupo' => 'sistema'],
            ['chave' => 'recibo_rodape', 'valor' => 'Obrigado pela preferência!', 'grupo' => 'sistema'],
        ];

        foreach ($configs as $config) {
            Configuracao::firstOrCreate(['chave' => $config['chave']], $config);
        }
    }
}
