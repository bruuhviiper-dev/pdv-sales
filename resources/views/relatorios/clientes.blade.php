@extends('layouts.app')
@section('title', 'Ranking de Clientes')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('relatorios.index') }}">Relatórios</a></li>
    <li class="breadcrumb-item active">Clientes</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-users me-2 text-primary"></i>Ranking de Clientes</h5>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="ti ti-printer me-1"></i>Imprimir</button>
</div>
<div class="card mb-3"><div class="card-body"><form method="GET" class="row g-2">
    <div class="col-md-4"><input type="date" name="data_inicio" value="{{ $dataInicio }}" class="form-control"></div>
    <div class="col-md-4"><input type="date" name="data_fim" value="{{ $dataFim }}" class="form-control"></div>
    <div class="col-md-4"><button type="submit" class="btn btn-primary w-100">Filtrar</button></div>
</form></div></div>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Cliente</th><th>Telefone</th><th class="text-center">Nº Compras</th><th class="text-end">Total Gasto</th><th class="text-end">Ticket Médio</th></tr>
            </thead>
            <tbody>
                @forelse($clientes as $i => $c)
                <tr>
                    <td><span class="badge {{ $i < 3 ? ['bg-warning text-dark', 'bg-secondary', 'bg-danger'][$i] : 'bg-light text-dark' }} rounded-pill">{{ $i+1 }}</span></td>
                    <td class="fw-semibold">{{ $c->nome }}</td>
                    <td class="small text-muted">{{ $c->telefone ?? '—' }}</td>
                    <td class="text-center">{{ $c->total_compras }}</td>
                    <td class="text-end fw-bold text-success">R$ {{ number_format($c->total_gasto, 2, ',', '.') }}</td>
                    <td class="text-end small">R$ {{ $c->total_compras > 0 ? number_format($c->total_gasto / $c->total_compras, 2, ',', '.') : '0,00' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum cliente no período</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
