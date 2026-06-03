@extends('layouts.app')
@section('title', 'Inventário de Estoque')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('relatorios.index') }}">Relatórios</a></li>
    <li class="breadcrumb-item active">Estoque</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-archive me-2 text-primary"></i>Inventário de Estoque</h5>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="ti ti-printer me-1"></i>Imprimir</button>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-primary bg-opacity-10"><div class="card-body text-center">
            <div class="fw-bold fs-4 text-primary">R$ {{ number_format(collect($produtos)->sum('valor_estoque'), 2, ',', '.') }}</div>
            <div class="small text-muted">Valor a Custo (total estoque)</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-success bg-opacity-10"><div class="card-body text-center">
            <div class="fw-bold fs-4 text-success">R$ {{ number_format(collect($produtos)->sum('valor_venda'), 2, ',', '.') }}</div>
            <div class="small text-muted">Valor a Venda (potencial)</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-danger bg-opacity-10"><div class="card-body text-center">
            <div class="fw-bold fs-4 text-danger">{{ collect($produtos)->filter(fn($p) => $p['critico'])->count() }}</div>
            <div class="small text-muted">Produtos em Estoque Crítico</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Produto</th><th>Categoria</th><th class="text-center">Qtd</th><th class="text-center">Mínimo</th><th class="text-end">Custo Unit.</th><th class="text-end">Val. Estoque</th><th class="text-end">Val. Venda</th><th class="text-center">Status</th></tr>
            </thead>
            <tbody>
                @foreach($produtos as $item)
                <tr class="{{ $item['critico'] ? 'table-warning' : '' }}">
                    <td class="fw-semibold small">{{ $item['produto']->nome }}</td>
                    <td class="small text-muted">{{ $item['produto']->categoria?->nome ?? '—' }}</td>
                    <td class="text-center">{{ $item['produto']->estoque_atual }} {{ $item['produto']->unidade }}</td>
                    <td class="text-center text-muted">{{ $item['produto']->estoque_minimo }}</td>
                    <td class="text-end small">R$ {{ number_format($item['produto']->preco_custo, 2, ',', '.') }}</td>
                    <td class="text-end fw-semibold">R$ {{ number_format($item['valor_estoque'], 2, ',', '.') }}</td>
                    <td class="text-end text-success">R$ {{ number_format($item['valor_venda'], 2, ',', '.') }}</td>
                    <td class="text-center">
                        @if($item['produto']->estoque_atual == 0)<span class="badge bg-danger">Zerado</span>
                        @elseif($item['critico'])<span class="badge bg-warning text-dark">Crítico</span>
                        @else<span class="badge bg-success">OK</span>@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
