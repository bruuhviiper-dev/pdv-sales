@extends('layouts.app')
@section('title', 'Editar Categoria')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}">Categorias</a></li>
    <li class="breadcrumb-item active">{{ $categoria->nome }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header fw-bold"><i class="ti ti-pencil me-2"></i>Editar Categoria</div>
            <div class="card-body">
                <form method="POST" action="{{ route('categorias.update', $categoria) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" value="{{ old('nome', $categoria->nome) }}" class="form-control @error('nome') is-invalid @enderror" required>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" value="{{ old('descricao', $categoria->descricao) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cor</label>
                        <input type="color" name="cor" value="{{ old('cor', $categoria->cor) }}" class="form-control form-control-color">
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ old('ativo', $categoria->ativo) ? 'checked' : '' }}>
                            <label class="form-check-label">Categoria ativa</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-check-lg me-1"></i>Salvar</button>
                        <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
