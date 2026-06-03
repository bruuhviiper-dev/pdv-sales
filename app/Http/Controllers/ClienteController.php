<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::orderBy('nome');

        if ($request->filled('busca')) {
            $query->where(function ($q) use ($request) {
                $q->where('nome', 'like', "%{$request->busca}%")
                  ->orWhere('cpf_cnpj', 'like', "%{$request->busca}%")
                  ->orWhere('telefone', 'like', "%{$request->busca}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $clientes = $query->paginate(20)->withQueryString();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|max:255',
            'cpf_cnpj' => 'nullable|unique:clientes,cpf_cnpj',
            'telefone' => 'nullable|max:20',
            'email' => 'nullable|email',
            'cep' => 'nullable|max:10',
            'endereco' => 'nullable|max:255',
            'bairro' => 'nullable|max:100',
            'cidade' => 'nullable|max:100',
            'estado' => 'nullable|max:2',
            'limite_fiado' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable',
        ]);

        $dados['ativo'] = true;
        $dados['saldo_fiado'] = 0;
        $dados['limite_fiado'] = $dados['limite_fiado'] ?? 0;

        Cliente::create($dados);
        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $vendas = $cliente->vendas()->with('itens')->latest()->paginate(10);
        return view('clientes.show', compact('cliente', 'vendas'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $dados = $request->validate([
            'nome' => 'required|max:255',
            'cpf_cnpj' => 'nullable|unique:clientes,cpf_cnpj,' . $cliente->id,
            'telefone' => 'nullable|max:20',
            'email' => 'nullable|email',
            'cep' => 'nullable|max:10',
            'endereco' => 'nullable|max:255',
            'bairro' => 'nullable|max:100',
            'cidade' => 'nullable|max:100',
            'estado' => 'nullable|max:2',
            'limite_fiado' => 'nullable|numeric|min:0',
            'observacoes' => 'nullable',
            'ativo' => 'boolean',
        ]);

        $dados['ativo'] = $request->boolean('ativo', true);
        $cliente->update($dados);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!');
    }
}
