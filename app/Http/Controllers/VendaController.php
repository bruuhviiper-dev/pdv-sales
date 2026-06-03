<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use Illuminate\Http\Request;

class VendaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venda::with(['cliente', 'user', 'itens'])->latest();

        if ($request->filled('busca')) {
            $query->where('numero_venda', 'like', "%{$request->busca}%");
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('forma_pagamento')) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }

        $vendas = $query->paginate(20)->withQueryString();

        $totais = Venda::where('status', 'concluida')
            ->when($request->filled('data_inicio'), fn($q) => $q->whereDate('created_at', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'), fn($q) => $q->whereDate('created_at', '<=', $request->data_fim))
            ->selectRaw('SUM(total) as total_vendas, COUNT(*) as quantidade')
            ->first();

        return view('vendas.index', compact('vendas', 'totais'));
    }

    public function show(Venda $venda)
    {
        $venda->load(['cliente', 'user', 'itens.produto']);
        return view('vendas.show', compact('venda'));
    }

    public function cancelar(Venda $venda)
    {
        if ($venda->status !== 'concluida') {
            return redirect()->back()->with('error', 'Esta venda não pode ser cancelada.');
        }

        \DB::transaction(function () use ($venda) {
            foreach ($venda->itens as $item) {
                $produto = $item->produto;
                if ($produto && $produto->controla_estoque) {
                    $estoqueAnterior = $produto->estoque_atual;
                    $produto->estoque_atual += $item->quantidade;
                    $produto->save();

                    \App\Models\EstoqueMovimentacao::create([
                        'produto_id' => $produto->id,
                        'user_id' => auth()->id(),
                        'venda_id' => $venda->id,
                        'tipo' => 'devolucao',
                        'quantidade' => $item->quantidade,
                        'estoque_anterior' => $estoqueAnterior,
                        'estoque_atual' => $produto->estoque_atual,
                        'motivo' => "Cancelamento da venda {$venda->numero_venda}",
                    ]);
                }
            }
            $venda->update(['status' => 'cancelada']);
        });

        return redirect()->route('vendas.index')->with('success', 'Venda cancelada e estoque restaurado.');
    }
}
