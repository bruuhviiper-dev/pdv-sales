<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::with('categoria')->orderBy('nome');

        if ($request->filled('busca')) {
            $query->where(function ($q) use ($request) {
                $q->where('nome', 'like', "%{$request->busca}%")
                  ->orWhere('codigo_barras', 'like', "%{$request->busca}%")
                  ->orWhere('sku', 'like', "%{$request->busca}%");
            });
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }
        if ($request->filled('estoque')) {
            if ($request->estoque === 'critico') {
                $query->whereColumn('estoque_atual', '<=', 'estoque_minimo')->where('controla_estoque', true);
            } elseif ($request->estoque === 'zerado') {
                $query->where('estoque_atual', 0);
            }
        }

        $produtos = $query->paginate(20)->withQueryString();
        $categorias = Categoria::where('ativo', true)->orderBy('nome')->get();

        return view('produtos.index', compact('produtos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::where('ativo', true)->orderBy('nome')->get();
        return view('produtos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo_barras' => 'nullable|unique:produtos,codigo_barras',
            'sku' => 'nullable|unique:produtos,sku',
            'descricao' => 'nullable',
            'preco_custo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0.01',
            'estoque_atual' => 'required|integer|min:0',
            'estoque_minimo' => 'required|integer|min:0',
            'unidade' => 'required|max:10',
            'controla_estoque' => 'boolean',
            'ativo' => 'boolean',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $dados['foto'] = $request->file('foto')->store('produtos', 'public');
        }

        $dados['margem_lucro'] = $dados['preco_custo'] > 0
            ? round((($dados['preco_venda'] - $dados['preco_custo']) / $dados['preco_custo']) * 100, 2)
            : 0;

        $dados['controla_estoque'] = $request->boolean('controla_estoque', true);
        $dados['ativo'] = $request->boolean('ativo', true);

        Produto::create($dados);

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function show(Produto $produto)
    {
        $produto->load('categoria', 'movimentacoes.user');
        $movimentacoes = $produto->movimentacoes()->with('user')->latest()->paginate(15);
        return view('produtos.show', compact('produto', 'movimentacoes'));
    }

    public function edit(Produto $produto)
    {
        $categorias = Categoria::where('ativo', true)->orderBy('nome')->get();
        return view('produtos.edit', compact('produto', 'categorias'));
    }

    public function update(Request $request, Produto $produto)
    {
        $dados = $request->validate([
            'nome' => 'required|max:255',
            'categoria_id' => 'nullable|exists:categorias,id',
            'codigo_barras' => 'nullable|unique:produtos,codigo_barras,' . $produto->id,
            'sku' => 'nullable|unique:produtos,sku,' . $produto->id,
            'descricao' => 'nullable',
            'preco_custo' => 'required|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0.01',
            'estoque_minimo' => 'required|integer|min:0',
            'unidade' => 'required|max:10',
            'controla_estoque' => 'boolean',
            'ativo' => 'boolean',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($produto->foto) Storage::disk('public')->delete($produto->foto);
            $dados['foto'] = $request->file('foto')->store('produtos', 'public');
        }

        $dados['margem_lucro'] = $dados['preco_custo'] > 0
            ? round((($dados['preco_venda'] - $dados['preco_custo']) / $dados['preco_custo']) * 100, 2)
            : 0;

        $dados['controla_estoque'] = $request->boolean('controla_estoque', true);
        $dados['ativo'] = $request->boolean('ativo', true);

        $produto->update($dados);

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        return redirect()->route('produtos.index')->with('success', 'Produto excluído com sucesso!');
    }

    public function buscarApi(Request $request)
    {
        $termo = $request->get('q', '');
        $produtos = Produto::where('ativo', true)
            ->where('estoque_atual', '>', 0)
            ->where(function ($q) use ($termo) {
                $q->where('nome', 'like', "%{$termo}%")
                  ->orWhere('codigo_barras', $termo)
                  ->orWhere('sku', 'like', "%{$termo}%");
            })
            ->limit(10)
            ->get(['id', 'nome', 'preco_venda', 'estoque_atual', 'codigo_barras', 'unidade']);

        return response()->json($produtos);
    }
}
