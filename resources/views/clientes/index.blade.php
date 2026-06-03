@extends('layouts.app')
@section('title', 'Clientes')
@section('breadcrumb')<li class="breadcrumb-item active">Clientes</li>@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-users me-2 text-primary"></i>Clientes</h5>
    <a href="{{ route('clientes.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Novo Cliente</a>
</div>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-8"><input type="text" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar por nome, CPF/CNPJ, telefone..."></div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
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
                <tr><th>Nome</th><th>CPF/CNPJ</th><th>Telefone</th><th>Cidade</th><th class="text-end">Fiado</th><th class="text-center">Status</th><th class="text-center">Ações</th></tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td class="fw-semibold">{{ $cliente->nome }}</td>
                    <td class="text-muted small">{{ $cliente->cpf_cnpj ?? '—' }}</td>
                    <td class="small">{{ $cliente->telefone ?? '—' }}</td>
                    <td class="small">{{ $cliente->cidade ? $cliente->cidade.'/'.$cliente->estado : '—' }}</td>
                    <td class="text-end {{ $cliente->saldo_fiado > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                        R$ {{ number_format($cliente->saldo_fiado, 2, ',', '.') }}
                    </td>
                    <td class="text-center"><span class="badge {{ $cliente->ativo ? 'bg-success' : 'bg-secondary' }}">{{ $cliente->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline-primary"><i class="ti ti-eye"></i></a>
                            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-secondary"><i class="ti ti-pencil"></i></a>
                            <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" onsubmit="return confirm('Excluir cliente?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">Nenhum cliente encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $clientes->links() }}</div>
</div>
@endsection
