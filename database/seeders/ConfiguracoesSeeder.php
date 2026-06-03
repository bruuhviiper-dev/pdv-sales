<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracao;

class ConfiguracoesSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['chave' => 'empresa_nome', 'valor' => 'Meu Mercadinho', 'grupo' => 'empresa'],
            ['chave' => 'empresa_cnpj', 'valor' => '12.345.678/0001-90', 'grupo' => 'empresa'],
            ['chave' => 'empresa_telefone', 'valor' => '(11) 99999-0000', 'grupo' => 'empresa'],
            ['chave' => 'empresa_endereco', 'valor' => 'Rua das Flores, 123', 'grupo' => 'empresa'],
            ['chave' => 'empresa_cidade', 'valor' => 'São Paulo', 'grupo' => 'empresa'],
            ['chave' => 'empresa_estado', 'valor' => 'SP', 'grupo' => 'empresa'],
        ];

        foreach ($configs as $config) {
            Configuracao::firstOrCreate(['chave' => $config['chave']], $config);
        }
    }
}
