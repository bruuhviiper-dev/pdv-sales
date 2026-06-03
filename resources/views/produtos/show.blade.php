@extends('layouts.app')
@section('title', $produto->nome)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('produtos.index') }}">Produtos</a></li>
    <li class="breadcrumb-item active">{{ $produto->nome }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-box me-2 text-primary"></i>{{ $produto->nome }}</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('produtos.edit', $produto) }}" class="btn btn-outline-primary"><i class="ti ti-pencil me-1"></i>Editar</a>
        <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                @if($produto->foto)
                <img src="{{ Storage::url($produto->foto) }}" class="img-fluid rounded" style="max-height:200px">
                @else
                <div class="py-5 text-muted"><i class="ti ti-photo fs-1"></i><div class="mt-2">Sem foto</div></div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $produto->ativo ? 'bg-success' : 'bg-secondary' }}">{{ $produto->ativo ? 'Ativo' : 'Inativo' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Categoria</span>
                    <span>{{ $produto->categoria?->nome ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Código Barras</span>
                    <span class="font-monospace">{{ $produto->codigo_barras ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Preço Custo</span>
                    <span>R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Preço Venda</span>
                    <strong class="text-primary">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Margem</span>
                    <span class="{{ $produto->margem_lucro >= 30 ? 'text-success' : 'text-warning' }}">{{ $produto->margem_lucro }}%</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Estoque</span>
                    <span class="badge {{ $produto->isEstoqueCritico() ? 'bg-danger' : 'bg-success' }} fs-6">
                        {{ $produto->estoque_atual }} {{ $produto->unidade }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Histórico de Movimentações de Estoque</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Quantidade</th>
                            <th>Antes → Depois</th>
                            <th>Usuário</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimentacoes as $mov)
                        <tr>
                            <td><small>{{ $mov->created_at->format('d/m/Y H:i') }}</small></td>
                            <td>
                                <span class="badge {{ $mov->tipo === 'entrada' ? 'bg-success' : ($mov->tipo === 'saida' ? 'bg-danger' : 'bg-secondary') }}">
                                    {{ ucfirst($mov->tipo) }}
                                </span>
                            </td>
                            <td><strong>{{ $mov->quantidade }}</strong></td>
                            <td class="small">{{ $mov->estoque_anterior }} → {{ $mov->estoque_atual }}</td>
                            <td class="small">{{ $mov->user->name }}</td>
                            <td class="small text-muted">{{ $mov->motivo ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Nenhuma movimentação registrada</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $movimentacoes->links() }}</div>
        </div>
    </div>
</div>
@endsection
