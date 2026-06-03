<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\User;
use App\Models\EstoqueMovimentacao;
use App\Models\Caixa;
use Carbon\Carbon;

class VendasSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $produtos = Produto::where('ativo', true)->get();
        $clientes = Cliente::all();

        $formasPagamento = ['dinheiro', 'pix', 'cartao_debito', 'cartao_credito', 'dinheiro', 'pix'];

        // Caixa demo
        $caixa = Caixa::create([
            'user_id' => $admin->id,
            'saldo_abertura' => 200.00,
            'aberto_em' => Carbon::now()->subDays(30),
            'fechado_em' => Carbon::now()->subDays(1),
            'status' => 'fechado',
            'total_vendas' => 0,
            'total_dinheiro' => 0,
            'total_pix' => 0,
            'total_cartao' => 0,
            'total_fiado' => 0,
        ]);

        $vendaNum = 1;

        for ($dia = 30; $dia >= 1; $dia--) {
            $qtdVendas = rand(2, 6);
            $data = Carbon::now()->subDays($dia)->setHour(rand(8, 20))->setMinute(rand(0, 59));

            for ($v = 0; $v < $qtdVendas; $v++) {
                $cliente = rand(0, 3) > 0 ? null : $clientes->random();
                $forma = $formasPagamento[array_rand($formasPagamento)];
                $qtdItens = rand(1, 5);
                $produtosSelecionados = $produtos->random(min($qtdItens, $produtos->count()));

                $subtotal = 0;
                $itens = [];

                foreach ($produtosSelecionados as $produto) {
                    $quantidade = rand(1, 3);
                    $itemSubtotal = round($produto->preco_venda * $quantidade, 2);
                    $subtotal += $itemSubtotal;
                    $itens[] = [
                        'produto_id' => $produto->id,
                        'produto_nome' => $produto->nome,
                        'preco_unitario' => $produto->preco_venda,
                        'preco_custo' => $produto->preco_custo,
                        'quantidade' => $quantidade,
                        'desconto' => 0,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                $desconto = rand(0, 10) > 8 ? round(rand(1, 10), 2) : 0;
                $total = max(0, round($subtotal - $desconto, 2));

                $venda = Venda::create([
                    'numero_venda' => 'VD' . str_pad($vendaNum++, 6, '0', STR_PAD_LEFT),
                    'cliente_id' => $cliente?->id,
                    'user_id' => $admin->id,
                    'subtotal' => $subtotal,
                    'desconto' => $desconto,
                    'total' => $total,
                    'forma_pagamento' => $forma,
                    'valor_pago' => $total,
                    'troco' => 0,
                    'parcelas' => 1,
                    'status' => 'concluida',
                    'created_at' => $data,
                    'updated_at' => $data,
                ]);

                foreach ($itens as $item) {
                    VendaItem::create(array_merge($item, ['venda_id' => $venda->id]));
                }
            }
        }
    }
}
