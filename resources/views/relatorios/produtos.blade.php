@extends('layouts.app')
@section('title', 'Produtos Mais Vendidos')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('relatorios.index') }}">Relatórios</a></li>
    <li class="breadcrumb-item active">Produtos</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-box me-2 text-primary"></i>Produtos Mais Vendidos</h5>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="ti ti-printer me-1"></i>Imprimir</button>
</div>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><input type="date" name="data_inicio" value="{{ $dataInicio }}" class="form-control"></div>
            <div class="col-md-4"><input type="date" name="data_fim" value="{{ $dataFim }}" class="form-control"></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary w-100">Filtrar</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Produto</th><th>Categoria</th><th class="text-center">Qtd Vendida</th><th class="text-end">Receita</th><th class="text-end">Custo</th><th class="text-end">Lucro Bruto</th><th class="text-end">Margem</th></tr>
            </thead>
            <tbody>
                @forelse($produtos as $i => $p)
                <tr>
                    <td><span class="badge bg-primary rounded-pill">{{ $i+1 }}</span></td>
                    <td class="fw-semibold">{{ $p->nome }}</td>
                    <td class="text-muted small">{{ $p->categoria ?? '—' }}</td>
                    <td class="text-center fw-bold">{{ $p->total_vendido }}</td>
                    <td class="text-end">R$ {{ number_format($p->total_receita, 2, ',', '.') }}</td>
                    <td class="text-end text-muted small">R$ {{ number_format($p->total_custo, 2, ',', '.') }}</td>
                    <td class="text-end text-success fw-semibold">R$ {{ number_format($p->lucro_bruto, 2, ',', '.') }}</td>
                    <td class="text-end">
                        @php $margem = $p->total_receita > 0 ? ($p->lucro_bruto / $p->total_receita) * 100 : 0 @endphp
                        <span class="{{ $margem >= 30 ? 'text-success' : ($margem >= 10 ? 'text-warning' : 'text-danger') }}">{{ number_format($margem, 1) }}%</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted">Nenhuma venda no período</td></tr>
                @endforelse
            </tbody>
            @if($produtos->isNotEmpty())
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4">TOTAL</td>
                    <td class="text-end">R$ {{ number_format($produtos->sum('total_receita'), 2, ',', '.') }}</td>
                    <td class="text-end">R$ {{ number_format($produtos->sum('total_custo'), 2, ',', '.') }}</td>
                    <td class="text-end text-success">R$ {{ number_format($produtos->sum('lucro_bruto'), 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
