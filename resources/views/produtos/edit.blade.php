@extends('layouts.app')
@section('title', 'Editar Produto')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('produtos.index') }}">Produtos</a></li>
    <li class="breadcrumb-item active">{{ $produto->nome }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-pencil me-2 text-primary"></i>Editar Produto</h5>
    <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Voltar</a>
</div>

<form method="POST" action="{{ route('produtos.update', $produto) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">Dados do Produto</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome', $produto->nome) }}" class="form-control @error('nome') is-invalid @enderror" required>
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código de Barras</label>
                            <input type="text" name="codigo_barras" value="{{ old('codigo_barras', $produto->codigo_barras) }}" class="form-control @error('codigo_barras') is-invalid @enderror">
                            @error('codigo_barras')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $produto->sku) }}" class="form-control @error('sku') is-invalid @enderror">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Sem categoria</option>
                                @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('categoria_id', $produto->categoria_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unidade <span class="text-danger">*</span></label>
                            <select name="unidade" class="form-select" required>
                                @foreach(['UN', 'KG', 'G', 'L', 'ML', 'CX', 'PC', 'M', 'M2'] as $u)
                                <option value="{{ $u }}" {{ old('unidade', $produto->unidade) === $u ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="2">{{ old('descricao', $produto->descricao) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Preços e Estoque</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Preço de Custo (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="preco_custo" value="{{ old('preco_custo', $produto->preco_custo) }}" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço de Venda (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="preco_venda" value="{{ old('preco_venda', $produto->preco_venda) }}" class="form-control" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Margem atual</label>
                            <input type="text" class="form-control" value="{{ $produto->margem_lucro }}%" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estoque Mínimo</label>
                            <input type="number" name="estoque_minimo" value="{{ old('estoque_minimo', $produto->estoque_minimo) }}" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estoque Atual</label>
                            <input type="text" class="form-control" value="{{ $produto->estoque_atual }} {{ $produto->unidade }}" readonly>
                            <div class="form-text">Para ajustar o estoque, use o módulo Estoque.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="controla_estoque" value="1" {{ old('controla_estoque', $produto->controla_estoque) ? 'checked' : '' }}>
                                <label class="form-check-label">Controlar estoque</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ old('ativo', $produto->ativo) ? 'checked' : '' }}>
                                <label class="form-check-label">Produto ativo</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Foto do Produto</div>
                <div class="card-body text-center">
                    @if($produto->foto)
                    <img src="{{ Storage::url($produto->foto) }}" class="img-fluid rounded mb-3" style="max-height:150px">
                    @else
                    <div class="border rounded p-4 mb-3 bg-light">
                        <i class="ti ti-photo text-muted" style="font-size:3rem"></i>
                    </div>
                    @endif
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <div class="text-muted small mt-1">JPG, PNG, máx. 2MB</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-check-lg me-1"></i>Salvar Alterações
        </button>
        <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection
