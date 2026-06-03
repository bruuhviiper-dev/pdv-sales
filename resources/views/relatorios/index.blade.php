@extends('layouts.app')
@section('title', 'Relatórios')
@section('breadcrumb')<li class="breadcrumb-item active">Relatórios</li>@endsection
@section('content')
<h5 class="fw-bold mb-4"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Central de Relatórios</h5>
<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('relatorios.vendas') }}" class="card text-decoration-none text-dark h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt text-primary" style="font-size:2.5rem"></i>
                <h6 class="mt-3 fw-bold">Relatório de Vendas</h6>
                <p class="text-muted small mb-0">Vendas por período, totais e formas de pagamento</p>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('relatorios.produtos') }}" class="card text-decoration-none text-dark h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-box-seam text-success" style="font-size:2.5rem"></i>
                <h6 class="mt-3 fw-bold">Produtos Mais Vendidos</h6>
                <p class="text-muted small mb-0">Ranking de produtos, receita e lucro bruto</p>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('relatorios.clientes') }}" class="card text-decoration-none text-dark h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-people text-warning" style="font-size:2.5rem"></i>
                <h6 class="mt-3 fw-bold">Ranking de Clientes</h6>
                <p class="text-muted small mb-0">Melhores clientes por valor comprado</p>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('relatorios.estoque') }}" class="card text-decoration-none text-dark h-100">
            <div class="card-body text-center py-5">
                <i class="bi bi-archive text-info" style="font-size:2.5rem"></i>
                <h6 class="mt-3 fw-bold">Inventário de Estoque</h6>
                <p class="text-muted small mb-0">Valor do estoque atual e produtos críticos</p>
            </div>
        </a>
    </div>
</div>
@endsection
