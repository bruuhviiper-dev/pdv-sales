@extends('layouts.app')
@section('title', 'Nova Conta')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('financeiro.index') }}">Contas</a></li>
    <li class="breadcrumb-item active">Nova Conta</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header fw-bold"><i class="ti ti-plus-square me-2"></i>Nova Conta</div>
            <div class="card-body">
                <form method="POST" action="{{ route('financeiro.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select" required>
                            <option value="pagar" {{ old('tipo') === 'pagar' ? 'selected' : '' }}>A Pagar</option>
                            <option value="receber" {{ old('tipo') === 'receber' ? 'selected' : '' }}>A Receber</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição <span class="text-danger">*</span></label>
                        <input type="text" name="descricao" value="{{ old('descricao') }}" class="form-control @error('descricao') is-invalid @enderror" required>
                        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fornecedor / Cliente</label>
                        <input type="text" name="fornecedor_cliente" value="{{ old('fornecedor_cliente') }}" class="form-control">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Valor (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="valor" value="{{ old('valor') }}" class="form-control" min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vencimento <span class="text-danger">*</span></label>
                            <input type="date" name="vencimento" value="{{ old('vencimento') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <input type="text" name="categoria" value="{{ old('categoria') }}" class="form-control" placeholder="Ex: Aluguel, Fornecedor, Salário...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2">{{ old('observacoes') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-check-lg me-1"></i>Salvar</button>
                        <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
