<div>
    @if(!$caixaAberto)
    <div class="alert alert-warning d-flex align-items-center gap-3 mb-3">
        <i class="ti ti-alert-triangle-filled fs-4"></i>
        <div>
            <strong>Caixa fechado!</strong> Abra o caixa antes de realizar vendas.
            <a href="{{ route('caixa.index') }}" class="btn btn-sm btn-warning ms-3">
                <i class="ti ti-lock-open me-1"></i>Abrir Caixa
            </a>
        </div>
    </div>
    @endif

    @if($vendaConcluida && $ultimaVenda)
    <div class="alert alert-success alert-dismissible d-flex align-items-center justify-content-between mb-3">
        <div>
            <i class="ti ti-circle-check-filled me-2 fs-5"></i>
            <strong>Venda {{ $ultimaVenda['numero'] }} concluída!</strong>
            &nbsp; Total: <strong>{{ moeda($ultimaVenda['total']) }}</strong>
            @if($ultimaVenda['troco'] > 0)
            &nbsp;— Troco: <strong class="text-success">{{ moeda($ultimaVenda['troco']) }}</strong>
            @endif
            &nbsp;| Pgto: {{ $ultimaVenda['forma_pagamento'] }}
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendas.recibo', $ultimaVenda['id']) }}" target="_blank" class="btn btn-outline-success btn-sm">
                <i class="ti ti-receipt me-1"></i>Recibo / WhatsApp
            </a>
            <button wire:click="novaVenda" class="btn btn-success btn-sm">
                <i class="ti ti-circle-plus me-1"></i>Nova Venda
            </button>
        </div>
    </div>
    @endif

    <div class="row g-3">
        {{-- ===== Busca e Carrinho ===== --}}
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold mb-2">
                        <i class="ti ti-search me-1 text-primary"></i>Buscar Produto
                    </label>
                    <div class="pdv-busca">
                        <i class="ti ti-barcode busca-icon"></i>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="busca"
                            class="form-control"
                            placeholder="Digite o nome ou escaneie o código de barras..."
                            {{ !$caixaAberto ? 'disabled' : '' }}
                            autofocus
                        >
                    </div>
                    @if(count($produtosFiltrados))
                    <div class="list-group mt-2 shadow-sm pdv-sugestoes">
                        @foreach($produtosFiltrados as $p)
                        <button
                            wire:key="sug-{{ $p['id'] }}"
                            wire:click="adicionarProduto({{ $p['id'] }})"
                            type="button"
                            class="list-group-item list-group-item-action pdv-sugestao d-flex justify-content-between align-items-center py-2"
                        >
                            <div class="text-start">
                                <div class="fw-semibold">{{ $p['nome'] }}</div>
                                <div class="text-muted small">
                                    @if($p['codigo_barras']) <i class="ti ti-barcode"></i> {{ $p['codigo_barras'] }} — @endif
                                    Estoque: {{ $p['estoque_atual'] }} {{ $p['unidade'] }}
                                </div>
                            </div>
                            <span class="badge bg-primary fs-6">{{ moeda($p['preco_venda']) }}</span>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <div class="card pdv-cart-wrap">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="ti ti-shopping-cart me-2 text-primary"></i>Carrinho
                        <span class="badge bg-primary rounded-pill ms-1">{{ count($carrinho) }}</span>
                    </span>
                    @if(count($carrinho))
                    <button wire:click="limparVenda" type="button" class="btn btn-sm btn-outline-danger"
                        wire:confirm="Limpar todo o carrinho?">
                        <i class="ti ti-trash me-1"></i>Limpar
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @forelse($carrinho as $key => $item)
                    <div wire:key="cart-{{ $key }}" class="pdv-cart-item d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $item['nome'] }}</div>
                            <div class="text-muted" style="font-size:.78rem">
                                {{ moeda($item['preco']) }} / {{ $item['unidade'] }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] - 1 }})"
                                type="button" class="btn btn-outline-secondary pdv-qty-btn">−</button>
                            <span class="badge bg-secondary px-2" style="min-width:36px;font-size:.9rem">{{ $item['quantidade'] }}</span>
                            <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] + 1 }})"
                                type="button" class="btn btn-outline-secondary pdv-qty-btn">+</button>
                        </div>
                        <div class="text-end" style="min-width:95px">
                            <div class="fw-bold text-primary">{{ moeda($item['subtotal']) }}</div>
                        </div>
                        <button wire:click="removerItem('{{ $key }}')" type="button"
                            class="btn btn-sm btn-outline-danger pdv-qty-btn" title="Remover">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted d-flex flex-column align-items-center justify-content-center" style="min-height:280px">
                        <i class="ti ti-shopping-cart-off mb-2" style="font-size:3rem;opacity:.25"></i>
                        <div>Carrinho vazio</div>
                        <div class="small">Busque um produto acima para começar a venda</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===== Painel de Pagamento ===== --}}
        <div class="col-lg-5">
            <div class="card pdv-pay border-0 shadow-sm">
                <div class="pdv-pay-head">
                    <i class="ti ti-credit-card me-2"></i>Pagamento
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

                    <div class="pdv-total-box p-3 mb-3">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold">{{ moeda($this->subtotal) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Desconto</span>
                            <div class="input-group input-group-sm" style="width:130px">
                                <span class="input-group-text">{{ simbolo_moeda() }}</span>
                                <input type="number" wire:model.live="desconto" class="form-control text-end" min="0" step="0.01" max="{{ $this->subtotal }}">
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">TOTAL</span>
                            <span class="pdv-total-final">{{ moeda($this->total) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Forma de Pagamento</label>
                        <div class="row g-2">
                            @foreach([
                                'dinheiro'       => ['Dinheiro', 'ti-cash'],
                                'pix'            => ['PIX',      'ti-qrcode'],
                                'cartao_debito'  => ['Débito',   'ti-credit-card'],
                                'cartao_credito' => ['Crédito',  'ti-credit-card-pay'],
                                'fiado'          => ['Fiado',    'ti-user-check'],
                            ] as $val => [$label, $icon])
                            <div class="col-4" wire:key="pg-{{ $val }}">
                                <div wire:click="$set('formaPagamento', '{{ $val }}')"
                                    class="pdv-pgto text-center {{ $formaPagamento === $val ? 'ativo' : '' }}">
                                    <i class="ti {{ $icon }} d-block mb-1"></i>
                                    <span class="small">{{ $label }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($formaPagamento === 'dinheiro')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Valor Recebido</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text fw-bold">{{ simbolo_moeda() }}</span>
                            <input type="number" wire:model.live="valorPago" class="form-control" min="0" step="0.01" placeholder="0,00">
                        </div>
                        @if($this->troco > 0)
                        <div class="alert alert-success py-2 mt-2 mb-0 d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-coin me-1"></i>Troco</span>
                            <strong class="fs-5">{{ moeda($this->troco) }}</strong>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($formaPagamento === 'pix')
                    <div class="mb-3 text-center">
                        @if($this->pixConfigurado && $this->total > 0)
                            <label class="form-label small fw-semibold d-block text-start">QR Code PIX — cliente escaneia para pagar</label>
                            <div id="pix-qr" wire:ignore class="d-flex justify-content-center my-2"></div>
                            <input type="hidden" id="pix-payload" value="{{ $this->pixPayload }}">
                            <div class="input-group input-group-sm mt-2">
                                <input type="text" id="pix-code" class="form-control" value="{{ $this->pixPayload }}" readonly style="font-size:.7rem">
                                <button type="button" class="btn btn-outline-primary" onclick="copiarPix()">
                                    <i class="ti ti-copy"></i> Copiar
                                </button>
                            </div>
                            <div class="small text-muted mt-1">PIX Copia e Cola — valido por esta venda</div>
                        @else
                            <div class="alert alert-warning small mb-0 text-start">
                                <i class="ti ti-alert-triangle me-1"></i>
                                Configure sua <strong>chave PIX</strong> em
                                <a href="{{ route('configuracoes.index') }}">Configurações</a> para gerar o QR Code automaticamente.
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
                                {{ $i }}x de {{ $this->total > 0 ? moeda($this->total / $i) : moeda(0) }}
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
                        class="btn btn-success w-100 pdv-btn-finalizar"
                        {{ (empty($carrinho) || !$caixaAberto) ? 'disabled' : '' }}
                    >
                        <span wire:loading.remove wire:target="finalizarVenda">
                            <i class="ti ti-circle-check me-2"></i>Finalizar Venda
                        </span>
                        <span wire:loading wire:target="finalizarVenda">
                            <span class="spinner-border spinner-border-sm me-2"></span>Processando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    function renderPixQR() {
        const el = document.getElementById('pix-qr');
        const inp = document.getElementById('pix-payload');
        if (!el || !inp || !inp.value) { if (el) el.innerHTML = ''; return; }
        if (el.dataset.rendered === inp.value) return;
        el.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            new QRCode(el, { text: inp.value, width: 190, height: 190, correctLevel: QRCode.CorrectLevel.M });
            el.dataset.rendered = inp.value;
        }
    }
    window.copiarPix = function () {
        const t = document.getElementById('pix-code');
        if (!t) return;
        t.select(); t.setSelectionRange(0, 99999);
        navigator.clipboard?.writeText(t.value).catch(() => document.execCommand('copy'));
        const btn = event.currentTarget;
        const html = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-check"></i> Copiado!';
        setTimeout(() => btn.innerHTML = html, 1500);
    };

    renderPixQR();
    Livewire.hook('commit', ({ succeed }) => { succeed(() => setTimeout(renderPixQR, 60)); });

    $wire.on('alerta', ({ mensagem }) => alert(mensagem));
</script>
@endscript
