@extends('layouts.app')
@section('title', 'Caixa')
@section('breadcrumb')<li class="breadcrumb-item active">Caixa</li>@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-coin me-2 text-primary"></i>Controle de Caixa</h5>
</div>

@if($caixaAberto)
<div class="card border-success mb-4">
    <div class="card-header bg-success bg-opacity-10 text-success">
        <i class="ti ti-circle-filled me-2"></i>Caixa Aberto — {{ $caixaAberto->aberto_em->format('d/m/Y \à\s H:i') }}
        por {{ $caixaAberto->user->name }}
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 text-center">
                <div class="text-muted small">Saldo Abertura</div>
                <div class="fw-bold fs-5">{{ moeda($caixaAberto->saldo_abertura) }}</div>
            </div>
            <div class="col-md-3 text-center">
                <div class="text-muted small">Tempo Aberto</div>
                <div class="fw-bold fs-5">{{ $caixaAberto->aberto_em->diffForHumans() }}</div>
            </div>
            <div class="col-md-6 text-end">
                <form method="POST" action="{{ route('caixa.fechar', $caixaAberto) }}" onsubmit="return confirm('Confirmar fechamento do caixa?')">
                    @csrf @method('PATCH')
                    <div class="mb-2">
                        <label class="form-label small">Observações do fechamento</label>
                        <textarea name="observacoes" class="form-control form-control-sm" rows="2" placeholder="Opcional..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-lock me-1"></i>Fechar Caixa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<div class="card border-warning mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h6 class="text-warning"><i class="ti ti-alert-triangle me-2"></i>Caixa Fechado</h6>
                <p class="text-muted mb-0">Abra o caixa para começar a registrar vendas.</p>
            </div>
            <div class="col-md-6">
                <form method="POST" action="{{ route('caixa.abrir') }}" class="d-flex gap-2 align-items-end">
                    @csrf
                    <div class="flex-grow-1">
                        <label class="form-label small">Saldo inicial (troco em caixa)</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ simbolo_moeda() }}</span>
                            <input type="number" name="saldo_abertura" class="form-control" value="0" min="0" step="0.01" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-lock-open me-1"></i>Abrir Caixa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">Histórico de Caixas</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Abertura</th><th>Fechamento</th><th>Operador</th><th class="text-end">Saldo Abertura</th><th class="text-end">Total Vendas</th><th class="text-end">Dinheiro</th><th class="text-end">PIX</th><th class="text-end">Cartão</th><th class="text-center">Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($historico as $caixa)
                <tr>
                    <td class="small">{{ $caixa->aberto_em->format('d/m/Y H:i') }}</td>
                    <td class="small">{{ $caixa->fechado_em ? $caixa->fechado_em->format('d/m/Y H:i') : '—' }}</td>
                    <td class="small">{{ $caixa->user->name }}</td>
                    <td class="text-end small">{{ moeda($caixa->saldo_abertura) }}</td>
                    <td class="text-end fw-semibold">{{ moeda($caixa->total_vendas) }}</td>
                    <td class="text-end small">{{ moeda($caixa->total_dinheiro) }}</td>
                    <td class="text-end small">{{ moeda($caixa->total_pix) }}</td>
                    <td class="text-end small">{{ moeda($caixa->total_cartao) }}</td>
                    <td class="text-center"><span class="badge {{ $caixa->status === 'aberto' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($caixa->status) }}</span></td>
                    <td><a href="{{ route('caixa.relatorio', $caixa) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-file-text"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-5 text-muted">Nenhum caixa registrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $historico->links() }}</div>
</div>
@endsection
