@extends('layouts.app')
@section('title', 'Relatório de Vendas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('relatorios.index') }}">Relatórios</a></li>
    <li class="breadcrumb-item active">Vendas</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-primary"></i>Relatório de Vendas</h5>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Imprimir</button>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><label class="form-label small">Data Início</label><input type="date" name="data_inicio" value="{{ $dataInicio }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label small">Data Fim</label><input type="date" name="data_fim" value="{{ $dataFim }}" class="form-control"></div>
            <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Filtrar</button></div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card text-center border-0 bg-success bg-opacity-10"><div class="card-body"><div class="fw-bold fs-5 text-success">R$ {{ number_format($vendas->sum('total'), 2, ',', '.') }}</div><div class="small text-muted">Total Vendido</div></div></div></div>
    <div class="col-md-3"><div class="card text-center border-0 bg-primary bg-opacity-10"><div class="card-body"><div class="fw-bold fs-5 text-primary">{{ $vendas->count() }}</div><div class="small text-muted">Total de Vendas</div></div></div></div>
    <div class="col-md-3"><div class="card text-center border-0 bg-info bg-opacity-10"><div class="card-body"><div class="fw-bold fs-5 text-info">R$ {{ $vendas->count() > 0 ? number_format($vendas->sum('total') / $vendas->count(), 2, ',', '.') : '0,00' }}</div><div class="small text-muted">Ticket Médio</div></div></div></div>
    <div class="col-md-3"><div class="card text-center border-0 bg-warning bg-opacity-10"><div class="card-body"><div class="fw-bold fs-5 text-warning">R$ {{ number_format($vendas->sum('desconto'), 2, ',', '.') }}</div><div class="small text-muted">Total Descontos</div></div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Por Forma de Pagamento</div>
            <div class="card-body p-0">
                @foreach($porFormaPagamento as $forma => $dados)
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <span>{{ ['dinheiro' => 'Dinheiro', 'pix' => 'PIX', 'cartao_debito' => 'Débito', 'cartao_credito' => 'Crédito', 'fiado' => 'Fiado'][$forma] ?? ucfirst($forma) }}</span>
                    <div class="text-end">
                        <div class="fw-bold">R$ {{ number_format($dados['total'], 2, ',', '.') }}</div>
                        <div class="text-muted small">{{ $dados['quantidade'] }} vendas</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header">Vendas por Dia</div><div class="card-body"><canvas id="grafico"></canvas></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Lista de Vendas</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Data</th><th>Nº</th><th>Cliente</th><th>Operador</th><th>Forma Pgto</th><th class="text-end">Total</th></tr>
            </thead>
            <tbody>
                @foreach($vendas as $venda)
                <tr>
                    <td class="small">{{ $venda->created_at->format('d/m/Y H:i') }}</td>
                    <td class="font-monospace small">{{ $venda->numero_venda }}</td>
                    <td class="small">{{ $venda->cliente?->nome ?? 'Consumidor Final' }}</td>
                    <td class="small">{{ $venda->user->name }}</td>
                    <td><span class="badge bg-secondary small">{{ $venda->formaPagamentoLabel() }}</span></td>
                    <td class="text-end fw-semibold">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('grafico'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_keys($porDia->toArray())) !!},
        datasets: [{ label: 'Total (R$)', data: {!! json_encode($porDia->pluck('total')) !!}, borderColor: '#2563eb', tension: 0.4, fill: true, backgroundColor: 'rgba(37,99,235,.1)' }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
@endpush
