@extends('layouts.app')
@section('title', 'Nova Categoria')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}">Categorias</a></li>
    <li class="breadcrumb-item active">Nova Categoria</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header fw-bold"><i class="ti ti-plus-square me-2"></i>Nova Categoria</div>
            <div class="card-body">
                <form method="POST" action="{{ route('categorias.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" value="{{ old('nome') }}" class="form-control @error('nome') is-invalid @enderror" required autofocus>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" value="{{ old('descricao') }}" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Cor</label>
                        <input type="color" name="cor" value="{{ old('cor', '#2563eb') }}" class="form-control form-control-color">
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
