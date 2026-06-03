@extends('layouts.app')
@section('title', 'Produtos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Produtos</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-box me-2 text-primary"></i>Produtos</h5>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i>Novo Produto
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar por nome, código...">
            </div>
            <div class="col-md-3">
                <select name="categoria_id" class="form-select">
                    <option value="">Todas as categorias</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="estoque" class="form-select">
                    <option value="">Todos</option>
                    <option value="critico" {{ request('estoque') === 'critico' ? 'selected' : '' }}>Estoque Crítico</option>
                    <option value="zerado" {{ request('estoque') === 'zerado' ? 'selected' : '' }}>Zerado</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Cód. Barras</th>
                    <th class="text-end">Custo</th>
                    <th class="text-end">Venda</th>
                    <th class="text-end">Margem</th>
                    <th class="text-center">Estoque</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produtos as $produto)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $produto->nome }}</div>
                        @if($produto->sku)<div class="text-muted small">SKU: {{ $produto->sku }}</div>@endif
                    </td>
                    <td>
                        @if($produto->categoria)
                        <span class="badge" style="background-color:{{ $produto->categoria->cor }}">{{ $produto->categoria->nome }}</span>
                        @else<span class="text-muted">—</span>@endif
                    </td>
                    <td><span class="font-monospace small">{{ $produto->codigo_barras ?? '—' }}</span></td>
                    <td class="text-end">R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}</td>
                    <td class="text-end fw-semibold">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                    <td class="text-end {{ $produto->margem_lucro >= 30 ? 'text-success' : ($produto->margem_lucro >= 10 ? 'text-warning' : 'text-danger') }}">
                        {{ $produto->margem_lucro }}%
                    </td>
                    <td class="text-center">
                        @if($produto->controla_estoque)
                            <span class="badge {{ $produto->isEstoqueCritico() ? 'bg-danger' : 'bg-success' }}">
                                {{ $produto->estoque_atual }} {{ $produto->unidade }}
                            </span>
                        @else<span class="badge bg-secondary">Não controla</span>@endif
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $produto->ativo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $produto->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('produtos.show', $produto) }}" class="btn btn-outline-primary" title="Detalhes"><i class="ti ti-eye"></i></a>
                            <a href="{{ route('produtos.edit', $produto) }}" class="btn btn-outline-secondary" title="Editar"><i class="ti ti-pencil"></i></a>
                            <form method="POST" action="{{ route('produtos.destroy', $produto) }}" onsubmit="return confirm('Excluir produto?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Excluir"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-5 text-muted">Nenhum produto encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $produtos->links() }}</div>
</div>
@endsection
