@extends('layouts.app')
@section('title', 'Vendas')
@section('breadcrumb')<li class="breadcrumb-item active">Vendas</li>@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-receipt-2 me-2 text-primary"></i>Histórico de Vendas</h5>
    <a href="{{ route('pdv.index') }}" class="btn btn-primary"><i class="ti ti-shopping-cart me-1"></i>Nova Venda (PDV)</a>
</div>

@if($totais->total_vendas)
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body text-center">
                <div class="text-success fw-bold fs-4">{{ moeda($totais->total_vendas) }}</div>
                <div class="text-muted small">Total do Período</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body text-center">
                <div class="text-primary fw-bold fs-4">{{ $totais->quantidade }}</div>
                <div class="text-muted small">Vendas Realizadas</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body text-center">
                <div class="text-info fw-bold fs-4">R$ {{ $totais->quantidade > 0 ? number_format($totais->total_vendas / $totais->quantidade, 2, ',', '.') : '0,00' }}</div>
                <div class="text-muted small">Ticket Médio</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Nº da venda..."></div>
            <div class="col-md-2"><input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="form-control"></div>
            <div class="col-md-2"><input type="date" name="data_fim" value="{{ request('data_fim') }}" class="form-control"></div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    <option value="concluida" {{ request('status') === 'concluida' ? 'selected' : '' }}>Concluídas</option>
                    <option value="cancelada" {{ request('status') === 'cancelada' ? 'selected' : '' }}>Canceladas</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="forma_pagamento" class="form-select">
                    <option value="">Forma de Pgto</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="pix">PIX</option>
                    <option value="cartao_debito">Débito</option>
                    <option value="cartao_credito">Crédito</option>
                    <option value="fiado">Fiado</option>
                </select>
            </div>
            <div class="col-md-1"><button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-search"></i></button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nº Venda</th><th>Data</th><th>Cliente</th><th>Operador</th><th>Forma Pgto</th><th class="text-center">Itens</th><th class="text-end">Total</th><th class="text-center">Status</th><th class="text-center">Ações</th></tr>
            </thead>
            <tbody>
                @forelse($vendas as $venda)
                <tr>
                    <td class="font-monospace fw-semibold small">{{ $venda->numero_venda }}</td>
                    <td class="small">{{ $venda->created_at->format('d/m/Y H:i') }}</td>
                    <td class="small">{{ $venda->cliente?->nome ?? 'Consumidor Final' }}</td>
                    <td class="small">{{ $venda->user->name }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $venda->formaPagamentoLabel() }}</span>
                    </td>
                    <td class="text-center">{{ $venda->itens->count() }}</td>
                    <td class="text-end fw-bold">{{ moeda($venda->total) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $venda->status === 'concluida' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($venda->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('vendas.show', $venda) }}" class="btn btn-outline-primary"><i class="ti ti-eye"></i></a>
                            @if($venda->status === 'concluida')
                            <form method="POST" action="{{ route('vendas.cancelar', $venda) }}" onsubmit="return confirm('Cancelar esta venda e restaurar o estoque?')">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-danger"><i class="ti ti-x-circle"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-5 text-muted">Nenhuma venda encontrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $vendas->links() }}</div>
</div>
@endsection
