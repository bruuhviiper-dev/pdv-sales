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
        $nfcePendencias = \App\Services\NotaFiscalService::pendencias();
        return view('configuracoes.index', compact('configs', 'nfcePendencias'));
    }

    public function testarNfce()
    {
        $r = \App\Services\NotaFiscalService::testarConexao();
        return back()->with($r['ok'] ? 'success' : 'error', $r['mensagem']);
    }

    public function salvar(Request $request)
    {
        $campos = [
            'empresa_nome', 'empresa_cnpj', 'empresa_ie', 'empresa_telefone', 'empresa_email',
            'empresa_endereco', 'empresa_cidade', 'empresa_estado', 'empresa_cep',
            'moeda_simbolo', 'recibo_rodape',
            'pix_chave', 'pix_beneficiario', 'pix_cidade',
            'nfce_provider', 'nfce_token', 'nfce_ambiente', 'nfce_serie', 'nfce_csc', 'nfce_csc_id',
            'nfce_regime', 'fiscal_ncm', 'fiscal_cfop', 'fiscal_situacao', 'fiscal_origem',
        ];

        foreach ($campos as $campo) {
            if ($request->has($campo)) {
                Configuracao::set($campo, $request->get($campo));
            }
        }

        // Emissão automática de NFC-e ao finalizar a venda (checkbox)
        Configuracao::set('nfce_auto', $request->boolean('nfce_auto') ? '1' : '0');

        if ($request->hasFile('empresa_logo')) {
            $request->validate(['empresa_logo' => 'image|max:1024'], [
                'empresa_logo.image' => 'O arquivo deve ser uma imagem (JPG, PNG).',
                'empresa_logo.max'   => 'A logo deve ter no máximo 1MB.',
            ]);
            // remove logo antiga
            $antiga = Configuracao::get('empresa_logo');
            if ($antiga && Storage::disk('public')->exists($antiga)) {
                Storage::disk('public')->delete($antiga);
            }
            $logo = $request->file('empresa_logo')->store('configuracoes', 'public');
            Configuracao::set('empresa_logo', $logo);
        }

        return redirect()->route('configuracoes.index')->with('success', 'Configurações salvas com sucesso!');
    }

    public function removerLogo()
    {
        $logo = Configuracao::get('empresa_logo');
        if ($logo && Storage::disk('public')->exists($logo)) {
            Storage::disk('public')->delete($logo);
        }
        Configuracao::set('empresa_logo', null);

        return redirect()->route('configuracoes.index')->with('success', 'Logo removida com sucesso!');
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
