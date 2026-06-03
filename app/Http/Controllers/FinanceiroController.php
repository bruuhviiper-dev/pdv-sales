<?php

namespace App\Http\Controllers;

use App\Models\Conta;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $query = Conta::latest();

        if ($request->filled('tipo')) $query->where('tipo', $request->tipo);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('vencimento_inicio')) $query->where('vencimento', '>=', $request->vencimento_inicio);
        if ($request->filled('vencimento_fim')) $query->where('vencimento', '<=', $request->vencimento_fim);

        $contas = $query->paginate(20)->withQueryString();

        $totalPagar = Conta::where('tipo', 'pagar')->where('status', 'pendente')->sum('valor');
        $totalReceber = Conta::where('tipo', 'receber')->where('status', 'pendente')->sum('valor');
        $vencidas = Conta::where('status', 'pendente')->where('vencimento', '<', today())->count();

        return view('financeiro.index', compact('contas', 'totalPagar', 'totalReceber', 'vencidas'));
    }

    public function create()
    {
        return view('financeiro.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'tipo' => 'required|in:pagar,receber',
            'descricao' => 'required|max:255',
            'fornecedor_cliente' => 'nullable|max:255',
            'valor' => 'required|numeric|min:0.01',
            'vencimento' => 'required|date',
            'categoria' => 'nullable|max:100',
            'observacoes' => 'nullable',
        ]);

        $dados['user_id'] = auth()->id();
        $dados['status'] = 'pendente';

        Conta::create($dados);
        return redirect()->route('financeiro.index')->with('success', 'Conta cadastrada com sucesso!');
    }

    public function pagar(Request $request, Conta $conta)
    {
        $request->validate(['valor_pago' => 'required|numeric|min:0.01']);

        $conta->update([
            'valor_pago' => $request->valor_pago,
            'pago_em' => today(),
            'status' => 'pago',
        ]);

        return redirect()->route('financeiro.index')->with('success', 'Conta marcada como paga!');
    }

    public function destroy(Conta $conta)
    {
        $conta->delete();
        return redirect()->route('financeiro.index')->with('success', 'Conta excluída com sucesso!');
    }
}
