<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $configs = Configuracao::all()->keyBy('chave');
        return view('configuracoes.index', compact('configs'));
    }

    public function salvar(Request $request)
    {
        $campos = ['empresa_nome', 'empresa_cnpj', 'empresa_telefone', 'empresa_endereco', 'empresa_cidade', 'empresa_estado'];

        foreach ($campos as $campo) {
            if ($request->has($campo)) {
                Configuracao::set($campo, $request->get($campo));
            }
        }

        if ($request->hasFile('empresa_logo')) {
            $request->validate(['empresa_logo' => 'image|max:1024']);
            $logo = $request->file('empresa_logo')->store('configuracoes', 'public');
            Configuracao::set('empresa_logo', $logo);
        }

        return redirect()->route('configuracoes.index')->with('success', 'Configurações salvas com sucesso!');
    }

    public function usuarios()
    {
        $usuarios = User::with('roles')->orderBy('name')->get();
        $roles = Role::all();
        return view('configuracoes.usuarios', compact('usuarios', 'roles'));
    }

    public function criarUsuario(Request $request)
    {
        $dados = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'password' => Hash::make($dados['password']),
        ]);

        $user->assignRole($dados['role']);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function excluirUsuario(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }
        $user->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
