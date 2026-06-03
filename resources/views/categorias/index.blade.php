@extends('layouts.app')
@section('title', 'Categorias')
@section('breadcrumb')<li class="breadcrumb-item active">Categorias</li>@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Categorias</h5>
    <a href="{{ route('categorias.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nova Categoria</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nome</th><th>Descrição</th><th>Cor</th><th class="text-center">Produtos</th><th class="text-center">Status</th><th class="text-center">Ações</th></tr>
            </thead>
            <tbody>
                @forelse($categorias as $cat)
                <tr>
                    <td><span class="badge me-2" style="background:{{ $cat->cor }}">&nbsp;</span>{{ $cat->nome }}</td>
                    <td class="text-muted small">{{ $cat->descricao ?? '—' }}</td>
                    <td><span class="badge" style="background:{{ $cat->cor }}">{{ $cat->cor }}</span></td>
                    <td class="text-center">{{ $cat->produtos_count }}</td>
                    <td class="text-center"><span class="badge {{ $cat->ativo ? 'bg-success' : 'bg-secondary' }}">{{ $cat->ativo ? 'Ativa' : 'Inativa' }}</span></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('categorias.edit', $cat) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('categorias.destroy', $cat) }}" onsubmit="return confirm('Excluir categoria?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">Nenhuma categoria cadastrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $categorias->links() }}</div>
</div>
@endsection
