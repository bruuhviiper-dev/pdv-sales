@extends('layouts.app')
@section('title', $cliente->nome)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active">{{ $cliente->nome }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>{{ $cliente->nome }}</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Dados do Cliente</div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $cliente->ativo ? 'bg-success' : 'bg-secondary' }}">{{ $cliente->ativo ? 'Ativo' : 'Inativo' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">CPF/CNPJ</span><span>{{ $cliente->cpf_cnpj ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Telefone</span><span>{{ $cliente->telefone ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Email</span><span class="small">{{ $cliente->email ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Cidade</span><span>{{ $cliente->cidade ? $cliente->cidade.'/'.$cliente->estado : '—' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Limite Fiado</span><span>R$ {{ number_format($cliente->limite_fiado, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Saldo Fiado</span>
                    <span class="{{ $cliente->saldo_fiado > 0 ? 'text-danger fw-bold' : 'text-success' }}">R$ {{ number_format($cliente->saldo_fiado, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Histórico de Compras</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Data</th><th>Nº Venda</th><th>Itens</th><th>Forma Pgto</th><th class="text-end">Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($vendas as $venda)
                        <tr>
                            <td><small>{{ $venda->created_at->format('d/m/Y') }}</small></td>
                            <td><a href="{{ route('vendas.show', $venda) }}">{{ $venda->numero_venda }}</a></td>
                            <td>{{ $venda->itens->count() }}</td>
                            <td>{{ $venda->formaPagamentoLabel() }}</td>
                            <td class="text-end fw-semibold">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                            <td><span class="badge {{ $venda->status === 'concluida' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($venda->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Nenhuma compra registrada</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $vendas->links() }}</div>
        </div>
    </div>
</div>
@endsection
