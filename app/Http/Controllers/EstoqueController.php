<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\EstoqueMovimentacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstoqueController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::with('categoria')->where('ativo', true);

        if ($request->filled('busca')) {
            $query->where('nome', 'like', "%{$request->busca}%");
        }
        if ($request->filled('alerta') && $request->alerta === 'critico') {
            $query->whereColumn('estoque_atual', '<=', 'estoque_minimo')->where('controla_estoque', true);
        }

        $produtos = $query->orderBy('nome')->paginate(20)->withQueryString();
        $totalCritico = Produto::whereColumn('estoque_atual', '<=', 'estoque_minimo')
            ->where('controla_estoque', true)->where('ativo', true)->count();

        return view('estoque.index', compact('produtos', 'totalCritico'));
    }

    public function movimentar(Request $request)
    {
        $dados = $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'tipo' => 'required|in:entrada,saida,ajuste',
            'quantidade' => 'required|integer|min:1',
            'custo_unitario' => 'nullable|numeric|min:0',
            'motivo' => 'nullable|max:255',
        ]);

        DB::transaction(function () use ($dados) {
            $produto = Produto::lockForUpdate()->findOrFail($dados['produto_id']);

            if ($dados['tipo'] === 'saida' && $produto->estoque_atual < $dados['quantidade']) {
                throw new \Exception('Estoque insuficiente.');
            }

            $estoqueAnterior = $produto->estoque_atual;

            if ($dados['tipo'] === 'entrada') {
                $produto->estoque_atual += $dados['quantidade'];
            } elseif ($dados['tipo'] === 'saida') {
                $produto->estoque_atual -= $dados['quantidade'];
            } else {
                $produto->estoque_atual = $dados['quantidade'];
            }
            $produto->save();

            EstoqueMovimentacao::create([
                'produto_id' => $produto->id,
                'user_id' => auth()->id(),
                'tipo' => $dados['tipo'],
                'quantidade' => $dados['quantidade'],
                'estoque_anterior' => $estoqueAnterior,
                'estoque_atual' => $produto->estoque_atual,
                'custo_unitario' => $dados['custo_unitario'] ?? null,
                'motivo' => $dados['motivo'] ?? null,
            ]);
        });

        return redirect()->route('estoque.index')->with('success', 'Movimentação registrada com sucesso!');
    }

    public function historico(Request $request)
    {
        $query = EstoqueMovimentacao::with(['produto', 'user'])->latest();

        if ($request->filled('produto_id')) {
            $query->where('produto_id', $request->produto_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $movimentacoes = $query->paginate(25)->withQueryString();
        $produtos = Produto::orderBy('nome')->get(['id', 'nome']);

        return view('estoque.historico', compact('movimentacoes', 'produtos'));
    }
}
