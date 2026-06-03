<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Venda;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CaixaController extends Controller
{
    public function index()
    {
        $caixaAberto = Caixa::where('status', 'aberto')->latest()->first();
        $historico = Caixa::with('user')->latest()->paginate(10);
        return view('caixa.index', compact('caixaAberto', 'historico'));
    }

    public function abrir(Request $request)
    {
        if (Caixa::where('status', 'aberto')->exists()) {
            return redirect()->route('caixa.index')->with('error', 'Já existe um caixa aberto.');
        }

        $request->validate(['saldo_abertura' => 'required|numeric|min:0']);

        Caixa::create([
            'user_id' => auth()->id(),
            'saldo_abertura' => $request->saldo_abertura,
            'aberto_em' => now(),
            'status' => 'aberto',
        ]);

        return redirect()->route('caixa.index')->with('success', 'Caixa aberto com sucesso!');
    }

    public function fechar(Request $request, Caixa $caixa)
    {
        if ($caixa->status !== 'aberto') {
            return redirect()->back()->with('error', 'Este caixa já foi fechado.');
        }

        $vendas = Venda::where('created_at', '>=', $caixa->aberto_em)
            ->where('status', 'concluida')
            ->get();

        $totais = [
            'total_vendas' => $vendas->sum('total'),
            'total_dinheiro' => $vendas->where('forma_pagamento', 'dinheiro')->sum('total'),
            'total_pix' => $vendas->where('forma_pagamento', 'pix')->sum('total'),
            'total_cartao' => $vendas->whereIn('forma_pagamento', ['cartao_debito', 'cartao_credito'])->sum('total'),
            'total_fiado' => $vendas->where('forma_pagamento', 'fiado')->sum('total'),
        ];

        $caixa->update(array_merge($totais, [
            'saldo_fechamento' => $caixa->saldo_abertura + $totais['total_dinheiro'],
            'fechado_em' => now(),
            'status' => 'fechado',
            'observacoes' => $request->observacoes,
        ]));

        return redirect()->route('caixa.index')->with('success', 'Caixa fechado com sucesso!');
    }

    public function relatorio(Caixa $caixa)
    {
        $vendas = Venda::with(['itens', 'cliente'])
            ->where('created_at', '>=', $caixa->aberto_em)
            ->when($caixa->fechado_em, fn($q) => $q->where('created_at', '<=', $caixa->fechado_em))
            ->where('status', 'concluida')
            ->get();

        return view('caixa.relatorio', compact('caixa', 'vendas'));
    }
}
