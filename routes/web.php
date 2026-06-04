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

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {

    // Dashboard — todos os usuários autenticados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil próprio — todos
    Route::get('/minha-conta', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/minha-conta', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/minha-conta', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // PDV — Admin e Operador
    Route::middleware('role:admin|operador')->group(function () {
        Route::get('/pdv', fn() => view('pdv.index'))->name('pdv.index');
        Route::get('/api/produtos/buscar', [ProdutoController::class, 'buscarApi'])->name('produtos.buscar');

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/criar', [ClienteController::class, 'create'])->name('clientes.create');
        Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');

        Route::get('/caixa', [CaixaController::class, 'index'])->name('caixa.index');
        Route::post('/caixa/abrir', [CaixaController::class, 'abrir'])->name('caixa.abrir');
        Route::patch('/caixa/{caixa}/fechar', [CaixaController::class, 'fechar'])->name('caixa.fechar');
        Route::get('/caixa/{caixa}/relatorio', [CaixaController::class, 'relatorio'])->name('caixa.relatorio');

        Route::get('/vendas', [VendaController::class, 'index'])->name('vendas.index');
        Route::get('/vendas/{venda}', [VendaController::class, 'show'])->name('vendas.show');
        Route::get('/vendas/{venda}/recibo', [VendaController::class, 'recibo'])->name('vendas.recibo');
    });

    // Produtos e Estoque — Admin e Estoquista
    Route::middleware('role:admin|estoquista')->group(function () {
        Route::resource('produtos', ProdutoController::class);
        Route::resource('categorias', CategoriaController::class)->except(['show']);

        Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index');
        Route::post('/estoque/movimentar', [EstoqueController::class, 'movimentar'])->name('estoque.movimentar');
        Route::get('/estoque/historico', [EstoqueController::class, 'historico'])->name('estoque.historico');

        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/relatorios/vendas', [RelatorioController::class, 'vendas'])->name('relatorios.vendas');
        Route::get('/relatorios/produtos', [RelatorioController::class, 'produtos'])->name('relatorios.produtos');
        Route::get('/relatorios/clientes', [RelatorioController::class, 'clientes'])->name('relatorios.clientes');
        Route::get('/relatorios/estoque', [RelatorioController::class, 'estoque'])->name('relatorios.estoque');
    });

    // Admin exclusivo
    Route::middleware('role:admin')->group(function () {
        Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
        Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

        Route::patch('/vendas/{venda}/cancelar', [VendaController::class, 'cancelar'])->name('vendas.cancelar');
        Route::post('/vendas/{venda}/nfce', [VendaController::class, 'emitirNfce'])->name('vendas.nfce');

        Route::get('/financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');
        Route::get('/financeiro/criar', [FinanceiroController::class, 'create'])->name('financeiro.create');
        Route::post('/financeiro', [FinanceiroController::class, 'store'])->name('financeiro.store');
        Route::patch('/financeiro/{conta}/pagar', [FinanceiroController::class, 'pagar'])->name('financeiro.pagar');
        Route::delete('/financeiro/{conta}', [FinanceiroController::class, 'destroy'])->name('financeiro.destroy');

        Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
        Route::post('/configuracoes', [ConfiguracaoController::class, 'salvar'])->name('configuracoes.salvar');
        Route::post('/configuracoes/testar-nfce', [ConfiguracaoController::class, 'testarNfce'])->name('configuracoes.testar-nfce');
        Route::delete('/configuracoes/logo', [ConfiguracaoController::class, 'removerLogo'])->name('configuracoes.remover-logo');
        Route::get('/usuarios', [ConfiguracaoController::class, 'usuarios'])->name('usuarios.index');
        Route::post('/usuarios', [ConfiguracaoController::class, 'criarUsuario'])->name('usuarios.store');
        Route::delete('/usuarios/{user}', [ConfiguracaoController::class, 'excluirUsuario'])->name('usuarios.destroy');
    });
});

require __DIR__.'/auth.php';
