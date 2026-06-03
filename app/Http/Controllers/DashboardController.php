<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Venda;
use App\Models\Cliente;
use App\Models\Conta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();

        $vendasHoje = Venda::whereDate('created_at', $hoje)->where('status', 'concluida');
        $receitaHoje = $vendasHoje->sum('total');
        $qtdVendasHoje = $vendasHoje->count();

        $receitaMes = Venda::whereMonth('created_at', $hoje->month)
            ->whereYear('created_at', $hoje->year)
            ->where('status', 'concluida')
            ->sum('total');

        $produtosCriticos = Produto::whereColumn('estoque_atual', '<=', 'estoque_minimo')
            ->where('controla_estoque', true)
            ->where('ativo', true)
            ->with('categoria')
            ->orderBy('estoque_atual')
            ->limit(8)
            ->get();

        $vendasUltimos30 = Venda::selectRaw('DATE(created_at) as data, SUM(total) as total, COUNT(*) as quantidade')
            ->where('created_at', '>=', Carbon::now()->subDays(29))
            ->where('status', 'concluida')
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        $produtosMaisVendidos = DB::table('venda_itens')
            ->join('vendas', 'venda_itens.venda_id', '=', 'vendas.id')
            ->join('produtos', 'venda_itens.produto_id', '=', 'produtos.id')
            ->where('vendas.created_at', '>=', Carbon::now()->subDays(30))
            ->where('vendas.status', 'concluida')
            ->selectRaw('produtos.nome, SUM(venda_itens.quantidade) as total_vendido, SUM(venda_itens.subtotal) as total_receita')
            ->groupBy('produtos.id', 'produtos.nome')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        $contasVencer = Conta::where('status', 'pendente')
            ->where('tipo', 'pagar')
            ->where('vencimento', '<=', Carbon::now()->addDays(7))
            ->orderBy('vencimento')
            ->limit(5)
            ->get();

        $totalClientes = Cliente::where('ativo', true)->count();

        return view('dashboard', compact(
            'receitaHoje', 'qtdVendasHoje', 'receitaMes',
            'produtosCriticos', 'vendasUltimos30', 'produtosMaisVendidos',
            'contasVencer', 'totalClientes'
        ));
    }
}
