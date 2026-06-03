@extends('layouts.app')
@section('title', 'Editar Cliente')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active">{{ $cliente->nome }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Editar Cliente</h5>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
</div>
<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('clientes.update', $cliente) }}">
            @csrf @method('PUT')
            <div class="card mb-3">
                <div class="card-header">Dados Pessoais</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome', $cliente->nome) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CPF / CNPJ</label>
                            <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone', $cliente->telefone) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Endereço</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">CEP</label>
                            <input type="text" name="cep" value="{{ old('cep', $cliente->cep) }}" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="endereco" value="{{ old('endereco', $cliente->endereco) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="bairro" value="{{ old('bairro', $cliente->bairro) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" value="{{ old('cidade', $cliente->cidade) }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input type="text" name="estado" value="{{ old('estado', $cliente->estado) }}" class="form-control" maxlength="2">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Configurações</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Limite de Fiado (R$)</label>
                            <input type="number" name="limite_fiado" value="{{ old('limite_fiado', $cliente->limite_fiado) }}" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ old('ativo', $cliente->ativo) ? 'checked' : '' }}>
                                <label class="form-check-label">Cliente ativo</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar Alterações</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
