<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.index');
    }

    public function vendas(Request $request)
    {
        $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', Carbon::now()->format('Y-m-d'));

        $vendas = Venda::with(['cliente', 'user'])
            ->whereDate('created_at', '>=', $dataInicio)
            ->whereDate('created_at', '<=', $dataFim)
            ->where('status', 'concluida')
            ->latest()
            ->get();

        $porFormaPagamento = $vendas->groupBy('forma_pagamento')->map(fn($g) => [
            'quantidade' => $g->count(),
            'total' => $g->sum('total'),
        ]);

        $porDia = $vendas->groupBy(fn($v) => $v->created_at->format('d/m'))->map(fn($g) => [
            'quantidade' => $g->count(),
            'total' => $g->sum('total'),
        ]);

        return view('relatorios.vendas', compact('vendas', 'porFormaPagamento', 'porDia', 'dataInicio', 'dataFim'));
    }

    public function produtos(Request $request)
    {
        $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', Carbon::now()->format('Y-m-d'));

        $produtos = DB::table('venda_itens')
            ->join('vendas', 'venda_itens.venda_id', '=', 'vendas.id')
            ->join('produtos', 'venda_itens.produto_id', '=', 'produtos.id')
            ->leftJoin('categorias', 'produtos.categoria_id', '=', 'categorias.id')
            ->whereDate('vendas.created_at', '>=', $dataInicio)
            ->whereDate('vendas.created_at', '<=', $dataFim)
            ->where('vendas.status', 'concluida')
            ->selectRaw('
                produtos.nome, categorias.nome as categoria,
                SUM(venda_itens.quantidade) as total_vendido,
                SUM(venda_itens.subtotal) as total_receita,
                AVG(venda_itens.preco_unitario) as preco_medio,
                SUM(venda_itens.quantidade * venda_itens.preco_custo) as total_custo,
                SUM(venda_itens.subtotal) - SUM(venda_itens.quantidade * venda_itens.preco_custo) as lucro_bruto
            ')
            ->groupBy('produtos.id', 'produtos.nome', 'categorias.nome')
            ->orderByDesc('total_vendido')
            ->get();

        return view('relatorios.produtos', compact('produtos', 'dataInicio', 'dataFim'));
    }

    public function clientes(Request $request)
    {
        $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', Carbon::now()->format('Y-m-d'));

        $clientes = DB::table('clientes')
            ->leftJoin('vendas', function ($join) use ($dataInicio, $dataFim) {
                $join->on('clientes.id', '=', 'vendas.cliente_id')
                    ->whereDate('vendas.created_at', '>=', $dataInicio)
                    ->whereDate('vendas.created_at', '<=', $dataFim)
                    ->where('vendas.status', 'concluida');
            })
            ->selectRaw('clientes.nome, clientes.telefone, COUNT(vendas.id) as total_compras, COALESCE(SUM(vendas.total), 0) as total_gasto')
            ->groupBy('clientes.id', 'clientes.nome', 'clientes.telefone')
            ->orderByDesc('total_gasto')
            ->get();

        return view('relatorios.clientes', compact('clientes', 'dataInicio', 'dataFim'));
    }

    public function estoque()
    {
        $produtos = Produto::with('categoria')
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(fn($p) => [
                'produto' => $p,
                'valor_estoque' => $p->estoque_atual * $p->preco_custo,
                'valor_venda' => $p->estoque_atual * $p->preco_venda,
                'critico' => $p->isEstoqueCritico(),
            ]);

        return view('relatorios.estoque', compact('produtos'));
    }
}
