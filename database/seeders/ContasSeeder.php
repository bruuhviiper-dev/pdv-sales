<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conta;
use App\Models\User;
use Carbon\Carbon;

class ContasSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();

        $contas = [
            // A pagar
            ['tipo' => 'pagar', 'descricao' => 'Aluguel do Mês', 'fornecedor_cliente' => 'Imobiliária Central', 'valor' => 1800.00, 'vencimento' => Carbon::today()->addDays(5), 'categoria' => 'Aluguel', 'status' => 'pendente'],
            ['tipo' => 'pagar', 'descricao' => 'Conta de Energia', 'fornecedor_cliente' => 'Copel', 'valor' => 320.00, 'vencimento' => Carbon::today()->addDays(3), 'categoria' => 'Utilidades', 'status' => 'pendente'],
            ['tipo' => 'pagar', 'descricao' => 'Internet e Telefone', 'fornecedor_cliente' => 'Vivo Fibra', 'valor' => 149.90, 'vencimento' => Carbon::today()->addDays(10), 'categoria' => 'Utilidades', 'status' => 'pendente'],
            ['tipo' => 'pagar', 'descricao' => 'Fornecedor - Distribuidora ABC', 'fornecedor_cliente' => 'Distribuidora ABC Ltda', 'valor' => 2500.00, 'vencimento' => Carbon::today()->addDays(2), 'categoria' => 'Fornecedor', 'status' => 'pendente'],
            ['tipo' => 'pagar', 'descricao' => 'Fornecedor - Bebidas Sul', 'fornecedor_cliente' => 'Bebidas Sul Distribuidora', 'valor' => 850.00, 'vencimento' => Carbon::today()->subDays(2), 'categoria' => 'Fornecedor', 'status' => 'pendente'],
            ['tipo' => 'pagar', 'descricao' => 'Salário Funcionária', 'fornecedor_cliente' => 'Maria das Graças', 'valor' => 1400.00, 'vencimento' => Carbon::today()->addDays(15), 'categoria' => 'Folha', 'status' => 'pendente'],
            ['tipo' => 'pagar', 'descricao' => 'Aluguel - Mês Anterior', 'fornecedor_cliente' => 'Imobiliária Central', 'valor' => 1800.00, 'vencimento' => Carbon::today()->subDays(10), 'categoria' => 'Aluguel', 'status' => 'pago', 'pago_em' => Carbon::today()->subDays(9), 'valor_pago' => 1800.00],
            // A receber
            ['tipo' => 'receber', 'descricao' => 'Fiado - João Santos', 'fornecedor_cliente' => 'João Santos', 'valor' => 85.50, 'vencimento' => Carbon::today()->addDays(7), 'categoria' => 'Fiado', 'status' => 'pendente'],
            ['tipo' => 'receber', 'descricao' => 'Fiado - Maria Silva', 'fornecedor_cliente' => 'Maria Silva', 'valor' => 120.00, 'vencimento' => Carbon::today()->addDays(14), 'categoria' => 'Fiado', 'status' => 'pendente'],
            ['tipo' => 'receber', 'descricao' => 'Venda a prazo - Carlos Pereira', 'fornecedor_cliente' => 'Carlos Pereira', 'valor' => 350.00, 'vencimento' => Carbon::today()->addDays(30), 'categoria' => 'Venda', 'status' => 'pendente'],
        ];

        foreach ($contas as $conta) {
            Conta::create(array_merge($conta, ['user_id' => $admin->id]));
        }
    }
}
