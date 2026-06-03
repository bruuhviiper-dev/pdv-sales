<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            ['nome' => 'Maria Silva', 'cpf_cnpj' => '123.456.789-01', 'telefone' => '(11) 98765-4321', 'cidade' => 'São Paulo', 'estado' => 'SP', 'limite_fiado' => 200.00],
            ['nome' => 'João Santos', 'cpf_cnpj' => '234.567.890-12', 'telefone' => '(11) 97654-3210', 'cidade' => 'São Paulo', 'estado' => 'SP', 'limite_fiado' => 150.00],
            ['nome' => 'Ana Oliveira', 'cpf_cnpj' => '345.678.901-23', 'telefone' => '(11) 96543-2109', 'email' => 'ana@email.com', 'cidade' => 'Guarulhos', 'estado' => 'SP', 'limite_fiado' => 100.00],
            ['nome' => 'Carlos Pereira', 'cpf_cnpj' => '456.789.012-34', 'telefone' => '(11) 95432-1098', 'cidade' => 'São Paulo', 'estado' => 'SP', 'limite_fiado' => 300.00],
            ['nome' => 'Fernanda Costa', 'cpf_cnpj' => '567.890.123-45', 'telefone' => '(11) 94321-0987', 'email' => 'fernanda@email.com', 'cidade' => 'Santo André', 'estado' => 'SP', 'limite_fiado' => 0.00],
            ['nome' => 'Roberto Almeida', 'cpf_cnpj' => '678.901.234-56', 'telefone' => '(11) 93210-9876', 'cidade' => 'São Bernardo', 'estado' => 'SP', 'limite_fiado' => 250.00],
            ['nome' => 'Marcia Ferreira', 'cpf_cnpj' => '789.012.345-67', 'telefone' => '(11) 92109-8765', 'cidade' => 'São Paulo', 'estado' => 'SP', 'limite_fiado' => 100.00],
            ['nome' => 'Paulo Rodrigues', 'cpf_cnpj' => '890.123.456-78', 'telefone' => '(11) 91098-7654', 'cidade' => 'Osasco', 'estado' => 'SP', 'limite_fiado' => 500.00],
            ['nome' => 'Lucia Martins', 'cpf_cnpj' => '901.234.567-89', 'telefone' => '(11) 90987-6543', 'email' => 'lucia@email.com', 'cidade' => 'São Paulo', 'estado' => 'SP', 'limite_fiado' => 200.00],
            ['nome' => 'Antonio Lima', 'cpf_cnpj' => '012.345.678-90', 'telefone' => '(11) 99876-5432', 'cidade' => 'Diadema', 'estado' => 'SP', 'limite_fiado' => 150.00],
        ];

        foreach ($clientes as $cliente) {
            Cliente::firstOrCreate(
                ['cpf_cnpj' => $cliente['cpf_cnpj']],
                array_merge($cliente, ['ativo' => true, 'saldo_fiado' => 0])
            );
        }
    }
}
