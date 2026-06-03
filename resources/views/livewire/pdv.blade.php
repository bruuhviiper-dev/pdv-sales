@extends('layouts.app')

@section('title', 'PDV — Frente de Caixa')

@section('content')
@if(!$caixaAberto)
<div class="alert alert-warning d-flex align-items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
    <div>
        <strong>Caixa fechado!</strong> Abra o caixa antes de realizar vendas.
        <a href="{{ route('caixa.index') }}" class="btn btn-sm btn-warning ms-3">Abrir Caixa</a>
    </div>
</div>
@endif

@if($vendaConcluida && $ultimaVenda)
<div class="alert alert-success d-flex align-items-center justify-content-between">
    <div>
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Venda {{ $ultimaVenda['numero'] }} concluída!</strong>
        Total: <strong>R$ {{ number_format($ultimaVenda['total'], 2, ',', '.') }}</strong>
        @if($ultimaVenda['troco'] > 0)
        — Troco: <strong>R$ {{ number_format($ultimaVenda['troco'], 2, ',', '.') }}</strong>
        @endif
    </div>
    <button wire:click="novaVenda" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Nova Venda
    </button>
</div>
@endif

<div class="row g-3">
    {{-- Busca e Carrinho --}}
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Buscar Produto (nome, código de barras ou SKU)
                </label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="busca"
                    class="form-control form-control-lg"
                    placeholder="Digite o nome ou código..."
                    autofocus
                    {{ !$caixaAberto ? 'disabled' : '' }}
                >
                @if(count($produtosFiltrados))
                <div class="list-group mt-2 shadow-sm">
                    @foreach($produtosFiltrados as $p)
                    <button
                        wire:click="adicionarProduto({{ $p['id'] }})"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <div class="fw-semibold">{{ $p['nome'] }}</div>
                            <div class="text-muted small">
                                {{ $p['codigo_barras'] ? 'Cód: '.$p['codigo_barras'].' — ' : '' }}
                                Estoque: {{ $p['estoque_atual'] }} {{ $p['unidade'] }}
                            </div>
                        </div>
                        <span class="badge bg-primary fs-6">R$ {{ number_format($p['preco_venda'], 2, ',', '.') }}</span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Carrinho --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart me-2"></i>Carrinho ({{ count($carrinho) }} itens)</span>
                @if(count($carrinho))
                <button wire:click="limparVenda" class="btn btn-sm btn-outline-danger" wire:confirm="Limpar o carrinho?">
                    <i class="bi bi-trash"></i>
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($carrinho as $key => $item)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $item['nome'] }}</div>
                        <div class="text-muted" style="font-size:.75rem">R$ {{ number_format($item['preco'], 2, ',', '.') }} / {{ $item['unidade'] }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] - 1 }})" class="btn btn-sm btn-outline-secondary px-2 py-0">−</button>
                        <span class="badge bg-secondary" style="min-width:32px">{{ $item['quantidade'] }}</span>
                        <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] + 1 }})" class="btn btn-sm btn-outline-secondary px-2 py-0">+</button>
                    </div>
                    <div class="text-end" style="min-width:90px">
                        <div class="fw-bold text-primary">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</div>
                    </div>
                    <button wire:click="removerItem('{{ $key }}')" class="btn btn-sm btn-outline-danger p-1">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart fs-1 d-block mb-2"></i>
                    Carrinho vazio — busque um produto acima
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Painel de Pagamento --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-credit-card me-2"></i>Pagamento</div>
            <div class="card-body">
                {{-- Cliente --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Cliente (opcional)</label>
                    <select wire:model.live="clienteId" class="form-select">
                        <option value="">— Consumidor Final —</option>
                        @foreach($clientes as $cli)
                        <option value="{{ $cli->id }}">{{ $cli->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Totais --}}
                <div class="bg-light rounded p-3 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span>R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted">Desconto</span>
                        <div class="input-group input-group-sm" style="width:120px">
                            <span class="input-group-text">R$</span>
                            <input type="number" wire:model.live="desconto" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>TOTAL</span>
                        <span class="text-primary">R$ {{ number_format($total, 2, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Forma de Pagamento --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Forma de Pagamento</label>
                    <div class="row g-2">
                        @foreach(['dinheiro' => ['Dinheiro', 'bi-cash'], 'pix' => ['PIX', 'bi-qr-code'], 'cartao_debito' => ['Débito', 'bi-credit-card'], 'cartao_credito' => ['Crédito', 'bi-credit-card-2-front'], 'fiado' => ['Fiado', 'bi-person-check']] as $val => $info)
                        <div class="col-6">
                            <div
                                wire:click="$set('formaPagamento', '{{ $val }}')"
                                class="border rounded p-2 text-center cursor-pointer {{ $formaPagamento === $val ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                                style="cursor:pointer"
                            >
                                <i class="bi {{ $info[1] }} d-block fs-5"></i>
                                <small>{{ $info[0] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($formaPagamento === 'dinheiro')
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Valor Recebido</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" wire:model.live="valorPago" class="form-control form-control-lg" min="0" step="0.01">
                    </div>
                    @if($troco > 0)
                    <div class="alert alert-info mt-2 py-2 mb-0">
                        <strong>Troco: R$ {{ number_format($troco, 2, ',', '.') }}</strong>
                    </div>
                    @endif
                </div>
                @endif

                @if($formaPagamento === 'cartao_credito')
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Parcelas</label>
                    <select wire:model="parcelas" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">{{ $i }}x de R$ {{ number_format($total / $i, 2, ',', '.') }}</option>
                        @endfor
                    </select>
                </div>
                @endif

                <button
                    wire:click="finalizarVenda"
                    wire:loading.attr="disabled"
                    class="btn btn-success btn-lg w-100"
                    {{ (empty($carrinho) || !$caixaAberto) ? 'disabled' : '' }}
                >
                    <span wire:loading.remove><i class="bi bi-check-circle me-2"></i>Finalizar Venda</span>
                    <span wire:loading><i class="bi bi-hourglass-split me-2"></i>Processando...</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('alerta', ({ mensagem }) => alert(mensagem));
});
</script>
@endpush
@endsection
