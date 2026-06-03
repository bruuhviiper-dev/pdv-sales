@extends('layouts.app')
@section('title', 'Financeiro')
@section('breadcrumb')<li class="breadcrumb-item active">Contas</li>@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-wallet me-2 text-primary"></i>Contas a Pagar / Receber</h5>
    <a href="{{ route('financeiro.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Nova Conta</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-danger bg-opacity-10">
            <div class="card-body text-center">
                <div class="text-danger fw-bold fs-4">{{ moeda($totalPagar) }}</div>
                <div class="text-muted small">A Pagar (pendente)</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body text-center">
                <div class="text-success fw-bold fs-4">{{ moeda($totalReceber) }}</div>
                <div class="text-muted small">A Receber (pendente)</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 {{ $vencidas > 0 ? 'bg-warning bg-opacity-10' : 'bg-secondary bg-opacity-10' }}">
            <div class="card-body text-center">
                <div class="{{ $vencidas > 0 ? 'text-warning' : 'text-secondary' }} fw-bold fs-4">{{ $vencidas }}</div>
                <div class="text-muted small">Contas Vencidas</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="tipo" class="form-select">
                    <option value="">Todos os tipos</option>
                    <option value="pagar" {{ request('tipo') === 'pagar' ? 'selected' : '' }}>A Pagar</option>
                    <option value="receber" {{ request('tipo') === 'receber' ? 'selected' : '' }}>A Receber</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="pago" {{ request('status') === 'pago' ? 'selected' : '' }}>Pago</option>
                    <option value="vencido" {{ request('status') === 'vencido' ? 'selected' : '' }}>Vencido</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="vencimento_inicio" value="{{ request('vencimento_inicio') }}" class="form-control" placeholder="Venc. início"></div>
            <div class="col-md-2"><input type="date" name="vencimento_fim" value="{{ request('vencimento_fim') }}" class="form-control" placeholder="Venc. fim"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-search"></i></button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Descrição</th><th>Fornecedor/Cliente</th><th>Tipo</th><th>Vencimento</th><th class="text-end">Valor</th><th>Status</th><th class="text-center">Ações</th></tr>
            </thead>
            <tbody>
                @forelse($contas as $conta)
                <tr class="{{ $conta->isVencida() ? 'table-warning' : '' }}">
                    <td class="fw-semibold">{{ $conta->descricao }}</td>
                    <td class="small text-muted">{{ $conta->fornecedor_cliente ?? '—' }}</td>
                    <td><span class="badge {{ $conta->tipo === 'pagar' ? 'bg-danger' : 'bg-success' }}">{{ $conta->tipo === 'pagar' ? 'A Pagar' : 'A Receber' }}</span></td>
                    <td class="small {{ $conta->isVencida() ? 'text-danger fw-bold' : '' }}">{{ \Carbon\Carbon::parse($conta->vencimento)->format('d/m/Y') }}</td>
                    <td class="text-end fw-bold">{{ moeda($conta->valor) }}</td>
                    <td><span class="badge {{ ['pendente' => 'bg-warning text-dark', 'pago' => 'bg-success', 'vencido' => 'bg-danger', 'cancelado' => 'bg-secondary'][$conta->status] }}">{{ ucfirst($conta->status) }}</span></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            @if($conta->status === 'pendente')
                            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalPagar{{ $conta->id }}" title="Registrar Pagamento">
                                <i class="ti ti-check-circle"></i>
                            </button>
                            @endif
                            <form method="POST" action="{{ route('financeiro.destroy', $conta) }}" onsubmit="return confirm('Excluir conta?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @if($conta->status === 'pendente')
                <div class="modal fade" id="modalPagar{{ $conta->id }}" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('financeiro.pagar', $conta) }}">
                                @csrf @method('PATCH')
                                <div class="modal-header"><h6 class="modal-title">Registrar Pagamento</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="mb-2"><label class="form-label small">Valor Pago (R$)</label>
                                        <input type="number" name="valor_pago" value="{{ $conta->valor }}" class="form-control" min="0.01" step="0.01" required>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="submit" class="btn btn-success btn-sm w-100">Confirmar Pagamento</button></div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">Nenhuma conta encontrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $contas->links() }}</div>
</div>
@endsection
