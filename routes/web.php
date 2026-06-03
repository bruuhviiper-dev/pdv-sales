<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ConfiguracaoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // PDV
    Route::get('/pdv', function () {
        return view('pdv.index');
    })->name('pdv.index');

    // Produtos
    Route::resource('produtos', ProdutoController::class);
    Route::get('/api/produtos/buscar', [ProdutoController::class, 'buscarApi'])->name('produtos.buscar');

    // Categorias
    Route::resource('categorias', CategoriaController::class)->except(['show']);

    // Clientes
    Route::resource('clientes', ClienteController::class);

    // Estoque
    Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index');
    Route::post('/estoque/movimentar', [EstoqueController::class, 'movimentar'])->name('estoque.movimentar');
    Route::get('/estoque/historico', [EstoqueController::class, 'historico'])->name('estoque.historico');

    // Vendas
    Route::get('/vendas', [VendaController::class, 'index'])->name('vendas.index');
    Route::get('/vendas/{venda}', [VendaController::class, 'show'])->name('vendas.show');
    Route::patch('/vendas/{venda}/cancelar', [VendaController::class, 'cancelar'])->name('vendas.cancelar');

    // Caixa
    Route::get('/caixa', [CaixaController::class, 'index'])->name('caixa.index');
    Route::post('/caixa/abrir', [CaixaController::class, 'abrir'])->name('caixa.abrir');
    Route::patch('/caixa/{caixa}/fechar', [CaixaController::class, 'fechar'])->name('caixa.fechar');
    Route::get('/caixa/{caixa}/relatorio', [CaixaController::class, 'relatorio'])->name('caixa.relatorio');

    // Financeiro
    Route::get('/financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');
    Route::get('/financeiro/criar', [FinanceiroController::class, 'create'])->name('financeiro.create');
    Route::post('/financeiro', [FinanceiroController::class, 'store'])->name('financeiro.store');
    Route::patch('/financeiro/{conta}/pagar', [FinanceiroController::class, 'pagar'])->name('financeiro.pagar');
    Route::delete('/financeiro/{conta}', [FinanceiroController::class, 'destroy'])->name('financeiro.destroy');

    // Relatórios
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/relatorios/vendas', [RelatorioController::class, 'vendas'])->name('relatorios.vendas');
    Route::get('/relatorios/produtos', [RelatorioController::class, 'produtos'])->name('relatorios.produtos');
    Route::get('/relatorios/clientes', [RelatorioController::class, 'clientes'])->name('relatorios.clientes');
    Route::get('/relatorios/estoque', [RelatorioController::class, 'estoque'])->name('relatorios.estoque');

    // Configurações e usuários (admin only)
    Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
    Route::post('/configuracoes', [ConfiguracaoController::class, 'salvar'])->name('configuracoes.salvar');
    Route::get('/usuarios', [ConfiguracaoController::class, 'usuarios'])->name('usuarios.index');
    Route::post('/usuarios', [ConfiguracaoController::class, 'criarUsuario'])->name('usuarios.store');
    Route::delete('/usuarios/{user}', [ConfiguracaoController::class, 'excluirUsuario'])->name('usuarios.destroy');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
