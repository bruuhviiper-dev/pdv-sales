@extends('layouts.app')
@section('title', 'Configurações')
@section('breadcrumb')<li class="breadcrumb-item active">Configurações</li>@endsection
@section('content')
<h5 class="fw-bold mb-4"><i class="bi bi-gear me-2 text-primary"></i>Configurações da Empresa</h5>
<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('configuracoes.salvar') }}" enctype="multipart/form-data">
            @csrf
            <div class="card mb-3">
                <div class="card-header">Dados da Empresa</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome da Empresa</label>
                            <input type="text" name="empresa_nome" value="{{ $configs->get('empresa_nome')?->valor }}" class="form-control" placeholder="Minha Loja">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNPJ / CPF</label>
                            <input type="text" name="empresa_cnpj" value="{{ $configs->get('empresa_cnpj')?->valor }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="empresa_telefone" value="{{ $configs->get('empresa_telefone')?->valor }}" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="empresa_endereco" value="{{ $configs->get('empresa_endereco')?->valor }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="empresa_cidade" value="{{ $configs->get('empresa_cidade')?->valor }}" class="form-control">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">UF</label>
                            <input type="text" name="empresa_estado" value="{{ $configs->get('empresa_estado')?->valor }}" class="form-control" maxlength="2">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Logo da Empresa</label>
                            @if($configs->get('empresa_logo'))
                            <div class="mb-2"><img src="{{ Storage::url($configs->get('empresa_logo')->valor) }}" class="img-thumbnail" style="max-height:80px"></div>
                            @endif
                            <input type="file" name="empresa_logo" class="form-control" accept="image/*">
                            <div class="form-text">PNG, JPG, máx. 1MB</div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i>Salvar Configurações
            </button>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Links Rápidos</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('usuarios.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Gerenciar Usuários
                </a>
                <a href="{{ route('estoque.historico') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Histórico de Estoque
                </a>
                <a href="{{ route('relatorios.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>Relatórios
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
