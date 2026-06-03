<div>
    @if(!$caixaAberto)
    <div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
            <strong>Caixa fechado!</strong> Abra o caixa antes de realizar vendas.
            <a href="{{ route('caixa.index') }}" class="btn btn-sm btn-warning ms-3">
                <i class="bi bi-unlock me-1"></i>Abrir Caixa
            </a>
        </div>
    </div>
    @endif

    @if($vendaConcluida && $ultimaVenda)
    <div class="alert alert-success alert-dismissible d-flex align-items-center justify-content-between mb-4">
        <div>
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <strong>Venda {{ $ultimaVenda['numero'] }} concluída!</strong>
            &nbsp; Total: <strong>R$ {{ number_format($ultimaVenda['total'], 2, ',', '.') }}</strong>
            @if($ultimaVenda['troco'] > 0)
            &nbsp;— Troco: <strong class="text-success">R$ {{ number_format($ultimaVenda['troco'], 2, ',', '.') }}</strong>
            @endif
            &nbsp;| Pgto: {{ $ultimaVenda['forma_pagamento'] }}
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
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-search me-1 text-primary"></i>Buscar Produto (nome ou código de barras)
                    </label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="busca"
                        class="form-control form-control-lg"
                        placeholder="Digite o nome ou escaneie o código..."
                        {{ !$caixaAberto ? 'disabled' : '' }}
                        autofocus
                    >
                    @if(count($produtosFiltrados))
                    <div class="list-group mt-2 shadow">
                        @foreach($produtosFiltrados as $p)
                        <button
                            wire:click="adicionarProduto({{ $p['id'] }})"
                            type="button"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2"
                        >
                            <div>
                                <div class="fw-semibold">{{ $p['nome'] }}</div>
                                <div class="text-muted small">
                                    @if($p['codigo_barras']) Cód: {{ $p['codigo_barras'] }} — @endif
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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart me-2 text-primary"></i>Carrinho — {{ count($carrinho) }} iten(s)</span>
                    @if(count($carrinho))
                    <button wire:click="limparVenda" type="button" class="btn btn-sm btn-outline-danger"
                        wire:confirm="Limpar o carrinho?">
                        <i class="bi bi-trash me-1"></i>Limpar
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @forelse($carrinho as $key => $item)
                    <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $item['nome'] }}</div>
                            <div class="text-muted" style="font-size:.75rem">
                                R$ {{ number_format($item['preco'], 2, ',', '.') }} / {{ $item['unidade'] }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] - 1 }})"
                                type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 lh-1">−</button>
                            <span class="badge bg-secondary px-2" style="min-width:32px;font-size:.85rem">{{ $item['quantidade'] }}</span>
                            <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] + 1 }})"
                                type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 lh-1">+</button>
                        </div>
                        <div class="text-end" style="min-width:90px">
                            <div class="fw-bold text-primary">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</div>
                        </div>
                        <button wire:click="removerItem('{{ $key }}')" type="button"
                            class="btn btn-sm btn-outline-danger px-2 py-1">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-cart3 d-block mb-2" style="font-size:2.5rem;opacity:.3"></i>
                        <div class="small">Carrinho vazio — busque um produto acima</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Painel de Pagamento --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-credit-card me-2 text-primary"></i>Pagamento
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cliente (opcional)</label>
                        <select wire:model.live="clienteId" class="form-select">
                            <option value="">— Consumidor Final —</option>
                            @foreach($clientes as $cli)
                            <option value="{{ $cli->id }}">{{ $cli->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">Subtotal</span>
                            <span>R$ {{ number_format($this->subtotal, 2, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Desconto (R$)</span>
                            <div class="input-group input-group-sm" style="width:120px">
                                <span class="input-group-text">R$</span>
                                <input type="number" wire:model.live="desconto" class="form-control" min="0" step="0.01" max="{{ $this->subtotal }}">
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>TOTAL</span>
                            <span class="text-primary">R$ {{ number_format($this->total, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Forma de Pagamento</label>
                        <div class="row g-2">
                            @foreach([
                                'dinheiro'       => ['Dinheiro',  'bi-cash'],
                                'pix'            => ['PIX',       'bi-qr-code'],
                                'cartao_debito'  => ['Débito',    'bi-credit-card'],
                                'cartao_credito' => ['Crédito',   'bi-credit-card-2-front'],
                                'fiado'          => ['Fiado',     'bi-person-check'],
                            ] as $val => [$label, $icon])
                            <div class="col-4">
                                <div
                                    wire:click="$set('formaPagamento', '{{ $val }}')"
                                    class="border rounded p-2 text-center small {{ $formaPagamento === $val ? 'border-primary bg-primary bg-opacity-10 fw-semibold' : 'bg-white' }}"
                                    style="cursor:pointer;transition:.15s"
                                >
                                    <i class="bi {{ $icon }} d-block mb-1" style="font-size:1.2rem;{{ $formaPagamento === $val ? 'color:#2563eb' : '' }}"></i>
                                    {{ $label }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($formaPagamento === 'dinheiro')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Valor Recebido</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">R$</span>
                            <input type="number" wire:model.live="valorPago" class="form-control form-control-lg" min="0" step="0.01">
                        </div>
                        @if($this->troco > 0)
                        <div class="alert alert-info py-2 mt-2 mb-0 fw-bold text-center">
                            <i class="bi bi-cash-coin me-2"></i>Troco: R$ {{ number_format($this->troco, 2, ',', '.') }}
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($formaPagamento === 'cartao_credito')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Parcelas</label>
                        <select wire:model="parcelas" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">
                                {{ $i }}x de R$ {{ $this->total > 0 ? number_format($this->total / $i, 2, ',', '.') : '0,00' }}
                                {{ $i === 1 ? '(à vista)' : '' }}
                            </option>
                            @endfor
                        </select>
                    </div>
                    @endif

                    <button
                        wire:click="finalizarVenda"
                        wire:loading.attr="disabled"
                        type="button"
                        class="btn btn-success btn-lg w-100 mt-1"
                        {{ (empty($carrinho) || !$caixaAberto) ? 'disabled' : '' }}
                    >
                        <span wire:loading.remove wire:target="finalizarVenda">
                            <i class="bi bi-check-circle me-2"></i>Finalizar Venda
                        </span>
                        <span wire:loading wire:target="finalizarVenda">
                            <span class="spinner-border spinner-border-sm me-2"></span>Processando...
                        </span>
                    </button>

                    @if(!empty($carrinho))
                    <div class="text-center mt-2 text-muted small">
                        {{ count($carrinho) }} produto(s) &nbsp;|&nbsp;
                        Total: R$ {{ number_format($this->total, 2, ',', '.') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('alerta', ({ mensagem }) => {
        alert(mensagem);
    });
});
</script>
@endpush
