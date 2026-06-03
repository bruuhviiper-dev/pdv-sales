@extends('layouts.app')
@section('title', 'Venda '.$venda->numero_venda)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vendas.index') }}">Vendas</a></li>
    <li class="breadcrumb-item active">{{ $venda->numero_venda }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-receipt-2 me-2 text-primary"></i>Venda {{ $venda->numero_venda }}</h5>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="ti ti-printer me-1"></i>Imprimir</button>
        <a href="{{ route('vendas.index') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Voltar</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Informações da Venda</div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $venda->status === 'concluida' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($venda->status) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Data/Hora</span><span>{{ $venda->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Cliente</span><span>{{ $venda->cliente?->nome ?? 'Consumidor Final' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Operador</span><span>{{ $venda->user->name }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Forma Pgto</span><span>{{ $venda->formaPagamentoLabel() }}</span>
                </div>
                @if($venda->parcelas > 1)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Parcelas</span><span>{{ $venda->parcelas }}x</span>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Valor Pago</span><span>{{ moeda($venda->valor_pago) }}</span>
                </div>
                @if($venda->troco > 0)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Troco</span><span class="text-success fw-bold">{{ moeda($venda->troco) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Subtotal</span><span>{{ moeda($venda->subtotal) }}</span>
                </div>
                @if($venda->desconto > 0)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Desconto</span><span class="text-danger">- {{ moeda($venda->desconto) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 fw-bold fs-5">
                    <span>TOTAL</span><span class="text-primary">{{ moeda($venda->total) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Itens da Venda</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Produto</th><th class="text-center">Qtd</th><th class="text-end">Preço Unit.</th><th class="text-end">Subtotal</th><th class="text-end">Lucro</th></tr>
                    </thead>
                    <tbody>
                        @foreach($venda->itens as $item)
                        <tr>
                            <td>{{ $item->produto_nome }}</td>
                            <td class="text-center">{{ $item->quantidade }}</td>
                            <td class="text-end">{{ moeda($item->preco_unitario) }}</td>
                            <td class="text-end fw-semibold">{{ moeda($item->subtotal) }}</td>
                            <td class="text-end text-success small">
                                {{ moeda($item->subtotal - ($item->quantidade * $item->preco_custo)) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">Total</td>
                            <td class="text-end text-primary">{{ moeda($venda->total) }}</td>
                            <td class="text-end text-success">
                                {{ moeda($venda->itens->sum(fn($i) => $i->subtotal - ($i->quantidade * $i->preco_custo))) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
