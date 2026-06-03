@extends('layouts.app')
@section('title', 'Minha Conta')
@section('breadcrumb')<li class="breadcrumb-item active">Minha Conta</li>@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-user-cog me-2 text-primary"></i>Minha Conta</h5>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="ti ti-user me-2 text-primary"></i>Dados Pessoais</div>
            <div class="card-body">
                @if(session('status') === 'profile-updated')
                <div class="alert alert-success py-2 mb-3"><i class="ti ti-check me-2"></i>Perfil atualizado!</div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('patch')
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="form-control @error('name') is-invalid @enderror" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Salvar
                        </button>
                        <span class="text-muted small">
                            Perfil: <strong>{{ auth()->user()->getRoleNames()->map(fn($r) => ucfirst($r))->implode(', ') }}</strong>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="ti ti-lock me-2 text-primary"></i>Alterar Senha</div>
            <div class="card-body">
                @if(session('status') === 'password-updated')
                <div class="alert alert-success py-2 mb-3"><i class="ti ti-check me-2"></i>Senha alterada!</div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('put')
                    <div class="mb-3">
                        <label class="form-label">Senha atual</label>
                        <input type="password" name="current_password"
                            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                            autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nova senha</label>
                        <input type="password" name="password"
                            class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                            autocomplete="new-password">
                        @error('password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="ti ti-lock me-1"></i>Alterar Senha
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
