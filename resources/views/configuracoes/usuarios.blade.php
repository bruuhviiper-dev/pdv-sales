@extends('layouts.app')
@section('title', 'Usuários')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('configuracoes.index') }}">Configurações</a></li>
    <li class="breadcrumb-item active">Usuários</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-primary"></i>Usuários do Sistema</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
        <i class="bi bi-plus-lg me-1"></i>Novo Usuário
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Cadastrado em</th><th class="text-center">Ações</th></tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                <tr>
                    <td class="fw-semibold">{{ $usuario->name }} {{ $usuario->id === auth()->id() ? '<span class="badge bg-info">Você</span>' : '' }}</td>
                    <td class="text-muted">{{ $usuario->email }}</td>
                    <td>
                        @foreach($usuario->roles as $role)
                        <span class="badge {{ ['admin' => 'bg-danger', 'operador' => 'bg-primary', 'estoquista' => 'bg-success'][$role->name] ?? 'bg-secondary' }}">
                            {{ ucfirst($role->name) }}
                        </span>
                        @endforeach
                    </td>
                    <td class="small text-muted">{{ $usuario->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        @if($usuario->id !== auth()->id())
                        <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" onsubmit="return confirm('Excluir usuário?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalNovoUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Novo Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3"><label class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3"><label class="form-label">Senha <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="mb-3"><label class="form-label">Confirmar Senha</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">
                                {{ ['admin' => 'Administrador', 'operador' => 'Operador de Caixa', 'estoquista' => 'Estoquista'][$role->name] ?? ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
