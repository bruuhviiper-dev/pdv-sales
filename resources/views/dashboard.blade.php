@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="ti ti-currency-dollar"></i></div>
                <div>
                    <div class="text-muted small">Receita Hoje</div>
                    <div class="fw-bold fs-5">{{ moeda($receitaHoje) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="ti ti-shopping-cart-check"></i></div>
                <div>
                    <div class="text-muted small">Vendas Hoje</div>
                    <div class="fw-bold fs-5">{{ $qtdVendasHoje }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-info bg-opacity-10 text-info"><i class="ti ti-trending-up"></i></div>
                <div>
                    <div class="text-muted small">Receita do Mês</div>
                    <div class="fw-bold fs-5">{{ moeda($receitaMes) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="ti ti-users"></i></div>
                <div>
                    <div class="text-muted small">Total Clientes</div>
                    <div class="fw-bold fs-5">{{ $totalClientes }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header py-3">
                <i class="ti ti-chart-bar me-2 text-primary"></i>Vendas — Últimos 30 dias
            </div>
            <div class="card-body"><canvas id="graficoVendas" height="100"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header py-3"><i class="ti ti-trophy me-2 text-warning"></i>Top 5 Produtos (30d)</div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($produtosMaisVendidos as $i => $prod)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill">{{ $i+1 }}</span>
                            <div>
                                <div class="fw-semibold small">{{ $prod->nome }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $prod->total_vendido }} un.</div>
                            </div>
                        </div>
                        <span class="text-success fw-semibold small">{{ moeda($prod->total_receita) }}</span>
                    </div>
                    @empty
                    <div class="list-group-item text-muted text-center py-4">Nenhuma venda no período</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span><i class="ti ti-alert-triangle me-2 text-danger"></i>Estoque Crítico</span>
                <a href="{{ route('estoque.index', ['alerta' => 'critico']) }}" class="btn btn-sm btn-outline-danger">Ver todos</a>
            </div>
            <div class="card-body p-0">
                @forelse($produtosCriticos as $produto)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold small">{{ $produto->nome }}</div>
                        <div class="text-muted" style="font-size:.75rem">Mínimo: {{ $produto->estoque_minimo }} {{ $produto->unidade }}</div>
                    </div>
                    <span class="badge {{ $produto->estoque_atual == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                        {{ $produto->estoque_atual }} {{ $produto->unidade }}
                    </span>
                </div>
                @empty
                <div class="text-success text-center py-4">
                    <i class="ti ti-check-circle fs-4"></i>
                    <div class="mt-1 small">Estoque OK!</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span><i class="ti ti-calendar-x me-2 text-warning"></i>Contas a Vencer (7 dias)</span>
                <a href="{{ route('financeiro.index') }}" class="btn btn-sm btn-outline-warning">Ver todas</a>
            </div>
            <div class="card-body p-0">
                @forelse($contasVencer as $conta)
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold small">{{ $conta->descricao }}</div>
                        <div class="text-muted" style="font-size:.75rem">Vence: {{ \Carbon\Carbon::parse($conta->vencimento)->format('d/m/Y') }}</div>
                    </div>
                    <span class="text-danger fw-bold small">{{ moeda($conta->valor) }}</span>
                </div>
                @empty
                <div class="text-success text-center py-4">
                    <i class="ti ti-check-circle fs-4"></i>
                    <div class="mt-1 small">Nenhuma conta urgente!</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels = {!! json_encode($vendasUltimos30->pluck('data')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!};
const totais = {!! json_encode($vendasUltimos30->pluck('total')) !!};
new Chart(document.getElementById('graficoVendas'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{ label: 'Receita (R$)', data: totais, backgroundColor: 'rgba(37,99,235,0.7)', borderRadius: 4 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') } } }
    }
});
</script>
@endpush
