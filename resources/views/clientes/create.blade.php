@extends('layouts.app')
@section('title', 'Novo Cliente')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active">Novo Cliente</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-user-plus me-2 text-primary"></i>Novo Cliente</h5>
    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Voltar</a>
</div>
<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('clientes.store') }}">
            @csrf
            <div class="card mb-3">
                <div class="card-header">Dados Pessoais</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome') }}" class="form-control @error('nome') is-invalid @enderror" required autofocus>
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CPF / CNPJ</label>
                            <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" class="form-control @error('cpf_cnpj') is-invalid @enderror">
                            @error('cpf_cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone / WhatsApp</label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" class="form-control" placeholder="(11) 99999-9999">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control">
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
                            <input type="text" name="cep" value="{{ old('cep') }}" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="endereco" value="{{ old('endereco') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bairro</label>
                            <input type="text" name="bairro" value="{{ old('bairro') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" value="{{ old('cidade') }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input type="text" name="estado" value="{{ old('estado') }}" class="form-control" maxlength="2">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Crédito / Fiado</div>
                <div class="card-body">
                    <div class="col-md-4">
                        <label class="form-label">Limite de Fiado (R$)</label>
                        <input type="number" name="limite_fiado" value="{{ old('limite_fiado', '0') }}" class="form-control" min="0" step="0.01">
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check-lg me-1"></i>Salvar Cliente</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
