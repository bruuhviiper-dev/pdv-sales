@extends('layouts.app')
@section('title', 'Relatório do Caixa')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('caixa.index') }}">Caixa</a></li>
    <li class="breadcrumb-item active">Relatório</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-file-text me-2 text-primary"></i>Relatório do Caixa</h5>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="ti ti-printer me-1"></i>Imprimir</button>
        <a href="{{ route('caixa.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Voltar</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Resumo do Caixa</div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Abertura</span><span>{{ $caixa->aberto_em->format('d/m/Y H:i') }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Fechamento</span><span>{{ $caixa->fechado_em ? $caixa->fechado_em->format('d/m/Y H:i') : 'Aberto' }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Operador</span><span>{{ $caixa->user->name }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Saldo Abertura</span><span>{{ moeda($caixa->saldo_abertura) }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Total de Vendas</span><span class="fw-bold text-success">{{ moeda($caixa->total_vendas) }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Dinheiro</span><span>{{ moeda($caixa->total_dinheiro) }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">PIX</span><span>{{ moeda($caixa->total_pix) }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Cartão</span><span>{{ moeda($caixa->total_cartao) }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Fiado</span><span>{{ moeda($caixa->total_fiado) }}</span></div>
                @if($caixa->saldo_fechamento)
                <div class="d-flex justify-content-between py-2 fw-bold fs-5"><span>Saldo Final</span><span class="text-primary">{{ moeda($caixa->saldo_fechamento) }}</span></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Vendas por Forma de Pagamento</div>
            <div class="card-body"><canvas id="graficoPagamentos" height="200"></canvas></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Vendas do Caixa ({{ $vendas->count() }})</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Hora</th><th>Nº</th><th>Cliente</th><th>Itens</th><th>Forma Pgto</th><th class="text-end">Total</th></tr>
            </thead>
            <tbody>
                @foreach($vendas as $venda)
                <tr>
                    <td class="small">{{ $venda->created_at->format('H:i') }}</td>
                    <td><a href="{{ route('vendas.show', $venda) }}" class="font-monospace small">{{ $venda->numero_venda }}</a></td>
                    <td class="small">{{ $venda->cliente?->nome ?? 'Cons. Final' }}</td>
                    <td class="text-center">{{ $venda->itens->count() }}</td>
                    <td><span class="badge bg-secondary small">{{ $venda->formaPagamentoLabel() }}</span></td>
                    <td class="text-end fw-semibold">{{ moeda($venda->total) }}</td>
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
new Chart(document.getElementById('graficoPagamentos'), {
    type: 'doughnut',
    data: {
        labels: ['Dinheiro', 'PIX', 'Cartão', 'Fiado'],
        datasets: [{
            data: [{{ $caixa->total_dinheiro }}, {{ $caixa->total_pix }}, {{ $caixa->total_cartao }}, {{ $caixa->total_fiado }}],
            backgroundColor: ['#198754', '#0dcaf0', '#6f42c1', '#ffc107'],
        }]
    },
    options: { responsive: true }
});
</script>
@endpush
