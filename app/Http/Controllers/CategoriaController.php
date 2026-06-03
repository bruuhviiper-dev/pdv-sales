<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('produtos')->orderBy('nome')->paginate(20);
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|max:255|unique:categorias,nome',
            'descricao' => 'nullable',
            'cor' => 'required|max:7',
        ]);
        $dados['ativo'] = true;
        Categoria::create($dados);
        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $dados = $request->validate([
            'nome' => 'required|max:255|unique:categorias,nome,' . $categoria->id,
            'descricao' => 'nullable',
            'cor' => 'required|max:7',
            'ativo' => 'boolean',
        ]);
        $dados['ativo'] = $request->boolean('ativo', true);
        $categoria->update($dados);
        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->produtos()->count() > 0) {
            return redirect()->route('categorias.index')->with('error', 'Não é possível excluir uma categoria com produtos vinculados.');
        }
        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
