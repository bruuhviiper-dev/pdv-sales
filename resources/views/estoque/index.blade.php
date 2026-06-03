@extends('layouts.app')
@section('title', 'Estoque')
@section('breadcrumb')<li class="breadcrumb-item active">Estoque</li>@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-archive me-2 text-primary"></i>Controle de Estoque
        @if($totalCritico > 0)
        <span class="badge bg-danger ms-2">{{ $totalCritico }} em estoque crítico</span>
        @endif
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('estoque.historico') }}" class="btn btn-outline-secondary">
            <i class="ti ti-history me-1"></i>Histórico
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMovimentar">
            <i class="ti ti-arrow-left-right me-1"></i>Registrar Movimentação
        </button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-8"><input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar produto..."></div>
            <div class="col-md-3">
                <select name="alerta" class="form-select">
                    <option value="">Todos os produtos</option>
                    <option value="critico" {{ request('alerta') === 'critico' ? 'selected' : '' }}>Estoque Crítico</option>
                </select>
            </div>
            <div class="col-md-1"><button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-search"></i></button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Produto</th><th>Categoria</th><th class="text-center">Estoque Atual</th><th class="text-center">Estoque Mínimo</th><th class="text-end">Val. Estoque</th><th class="text-center">Status</th></tr>
            </thead>
            <tbody>
                @forelse($produtos as $produto)
                <tr class="{{ $produto->isEstoqueCritico() ? 'table-warning' : '' }}">
                    <td>
                        <div class="fw-semibold">{{ $produto->nome }}</div>
                        @if($produto->codigo_barras)<div class="text-muted small font-monospace">{{ $produto->codigo_barras }}</div>@endif
                    </td>
                    <td>{{ $produto->categoria?->nome ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge fs-6 {{ $produto->estoque_atual == 0 ? 'bg-danger' : ($produto->isEstoqueCritico() ? 'bg-warning text-dark' : 'bg-success') }}">
                            {{ $produto->estoque_atual }} {{ $produto->unidade }}
                        </span>
                    </td>
                    <td class="text-center">{{ $produto->estoque_minimo }} {{ $produto->unidade }}</td>
                    <td class="text-end small">{{ moeda($produto->estoque_atual * $produto->preco_custo) }}</td>
                    <td class="text-center">
                        @if($produto->estoque_atual == 0)
                            <span class="badge bg-danger">Zerado</span>
                        @elseif($produto->isEstoqueCritico())
                            <span class="badge bg-warning text-dark">Crítico</span>
                        @else
                            <span class="badge bg-success">OK</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum produto encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $produtos->links() }}</div>
</div>

{{-- Modal Movimentação --}}
<div class="modal fade" id="modalMovimentar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('estoque.movimentar') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-arrow-left-right me-2"></i>Movimentação de Estoque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Produto <span class="text-danger">*</span></label>
                        <select name="produto_id" class="form-select" required>
                            <option value="">Selecione o produto</option>
                            @foreach($produtos as $p)
                            <option value="{{ $p->id }}">{{ $p->nome }} (Atual: {{ $p->estoque_atual }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select" required>
                            <option value="entrada">Entrada (compra/recebimento)</option>
                            <option value="saida">Saída (perda/consumo)</option>
                            <option value="ajuste">Ajuste de inventário</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantidade <span class="text-danger">*</span></label>
                        <input type="number" name="quantidade" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Custo Unitário (R$)</label>
                        <input type="number" name="custo_unitario" class="form-control" min="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <input type="text" name="motivo" class="form-control" placeholder="Ex: Compra de fornecedor, Inventário...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
