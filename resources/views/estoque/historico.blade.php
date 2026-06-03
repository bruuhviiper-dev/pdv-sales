@extends('layouts.app')
@section('title', 'Histórico de Estoque')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('estoque.index') }}">Estoque</a></li>
    <li class="breadcrumb-item active">Histórico</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Histórico de Movimentações</h5>
    <a href="{{ route('estoque.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-6">
                <select name="produto_id" class="form-select">
                    <option value="">Todos os produtos</option>
                    @foreach($produtos as $p)
                    <option value="{{ $p->id }}" {{ request('produto_id') == $p->id ? 'selected' : '' }}>{{ $p->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="tipo" class="form-select">
                    <option value="">Todos os tipos</option>
                    <option value="entrada" {{ request('tipo') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ request('tipo') === 'saida' ? 'selected' : '' }}>Saída</option>
                    <option value="ajuste" {{ request('tipo') === 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                    <option value="devolucao" {{ request('tipo') === 'devolucao' ? 'selected' : '' }}>Devolução</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i></button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Data/Hora</th><th>Produto</th><th>Tipo</th><th class="text-center">Qtd</th><th>Antes → Depois</th><th>Usuário</th><th>Motivo</th></tr>
            </thead>
            <tbody>
                @forelse($movimentacoes as $mov)
                <tr>
                    <td class="small">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                    <td class="fw-semibold small">{{ $mov->produto->nome }}</td>
                    <td>
                        @php $cores = ['entrada' => 'bg-success', 'saida' => 'bg-danger', 'ajuste' => 'bg-info', 'devolucao' => 'bg-warning text-dark'] @endphp
                        <span class="badge {{ $cores[$mov->tipo] ?? 'bg-secondary' }}">{{ ucfirst($mov->tipo) }}</span>
                    </td>
                    <td class="text-center fw-bold">{{ $mov->quantidade }}</td>
                    <td class="small">{{ $mov->estoque_anterior }} → <strong>{{ $mov->estoque_atual }}</strong></td>
                    <td class="small">{{ $mov->user->name }}</td>
                    <td class="small text-muted">{{ $mov->motivo ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Nenhuma movimentação encontrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $movimentacoes->links() }}</div>
</div>
@endsection
