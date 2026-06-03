@extends('layouts.app')
@section('title', 'Novo Produto')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('produtos.index') }}">Produtos</a></li>
    <li class="breadcrumb-item active">Novo Produto</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-plus-square me-2 text-primary"></i>Novo Produto</h5>
    <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Voltar</a>
</div>

<form method="POST" action="{{ route('produtos.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">Dados do Produto</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" value="{{ old('nome') }}" class="form-control @error('nome') is-invalid @enderror" required autofocus>
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código de Barras</label>
                            <input type="text" name="codigo_barras" value="{{ old('codigo_barras') }}" class="form-control @error('codigo_barras') is-invalid @enderror" placeholder="EAN-13, QR Code...">
                            @error('codigo_barras')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku') }}" class="form-control @error('sku') is-invalid @enderror">
                            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Sem categoria</option>
                                @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unidade <span class="text-danger">*</span></label>
                            <select name="unidade" class="form-select" required>
                                @foreach(['UN', 'KG', 'G', 'L', 'ML', 'CX', 'PC', 'M', 'M2'] as $u)
                                <option value="{{ $u }}" {{ old('unidade', 'UN') === $u ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="2">{{ old('descricao') }}</textarea>
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
                            <input type="number" name="preco_custo" value="{{ old('preco_custo', '0') }}" class="form-control @error('preco_custo') is-invalid @enderror" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Preço de Venda (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="preco_venda" value="{{ old('preco_venda') }}" class="form-control @error('preco_venda') is-invalid @enderror" min="0.01" step="0.01" required>
                            @error('preco_venda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Margem (%)</label>
                            <input type="text" id="margem_display" class="form-control" readonly placeholder="Calculada automaticamente">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estoque Atual <span class="text-danger">*</span></label>
                            <input type="number" name="estoque_atual" value="{{ old('estoque_atual', '0') }}" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estoque Mínimo (alerta) <span class="text-danger">*</span></label>
                            <input type="number" name="estoque_minimo" value="{{ old('estoque_minimo', '5') }}" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="controla_estoque" value="1" {{ old('controla_estoque', true) ? 'checked' : '' }}>
                                <label class="form-check-label">Controlar estoque</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ old('ativo', true) ? 'checked' : '' }}>
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
                    <div class="border rounded p-4 mb-3 bg-light">
                        <i class="ti ti-photo text-muted" style="font-size:3rem"></i>
                        <div class="text-muted small mt-2">Nenhuma foto</div>
                    </div>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <div class="text-muted small mt-1">JPG, PNG, máx. 2MB</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-check-lg me-1"></i>Salvar Produto
        </button>
        <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
const custo = document.querySelector('[name=preco_custo]');
const venda = document.querySelector('[name=preco_venda]');
const margem = document.getElementById('margem_display');

function calcMargem() {
    const c = parseFloat(custo.value) || 0;
    const v = parseFloat(venda.value) || 0;
    margem.value = c > 0 ? ((v - c) / c * 100).toFixed(2) + '%' : '—';
}
custo.addEventListener('input', calcMargem);
venda.addEventListener('input', calcMargem);
</script>
@endpush
