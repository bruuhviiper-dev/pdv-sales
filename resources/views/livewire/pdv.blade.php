<div class="pdv-grid-wrap">

    {{-- ===================== COLUNA: PRODUTOS ===================== --}}
    <div class="pdv-col-produtos">

        @if(!$caixaAberto)
        <div class="alert alert-warning d-flex align-items-center gap-3 mb-0">
            <i class="ti ti-alert-triangle-filled fs-4"></i>
            <div class="flex-grow-1">
                <strong>Caixa fechado!</strong> Abra o caixa antes de realizar vendas.
            </div>
            <a href="{{ route('caixa.index') }}" class="btn btn-sm btn-warning">
                <i class="ti ti-lock-open me-1"></i>Abrir Caixa
            </a>
        </div>
        @endif

        {{-- Busca / leitor --}}
        <div class="pdv-busca">
            <i class="ti ti-barcode busca-icon"></i>
            <input
                id="pdvBusca"
                type="text"
                wire:model.live.debounce.250ms="busca"
                class="form-control"
                placeholder="Buscar produto ou bipar código de barras…   (F4)"
                {{ !$caixaAberto ? 'disabled' : '' }}
                autocomplete="off"
                autofocus
            >
        </div>

        {{-- Categorias --}}
        <div class="pdv-cats">
            <button type="button" wire:click="selecionarCategoria(null)"
                class="pdv-cat {{ !$categoriaId && strlen($busca) < 2 ? 'ativo' : '' }}">
                <i class="ti ti-apps me-1"></i>Todos
            </button>
            @foreach($this->categorias as $cat)
            <button type="button" wire:key="cat-{{ $cat->id }}" wire:click="selecionarCategoria({{ $cat->id }})"
                class="pdv-cat {{ $categoriaId === $cat->id ? 'ativo' : '' }}">
                {{ $cat->nome }}
            </button>
            @endforeach
        </div>

        {{-- Grade de produtos --}}
        <div class="pdv-produtos">
            @forelse($this->grade as $p)
            <button type="button" wire:key="grid-{{ $p['id'] }}" wire:click="adicionarProduto({{ $p['id'] }})"
                class="pdv-prod" {{ !$caixaAberto ? 'disabled' : '' }}>
                <div class="nome">{{ $p['nome'] }}</div>
                <div class="est"><i class="ti ti-stack-2" style="font-size:.8rem"></i> {{ $p['estoque_atual'] }} {{ $p['unidade'] }}</div>
                <div class="preco">{{ moeda($p['preco_venda']) }}</div>
            </button>
            @empty
            <div class="pdv-prod-empty">
                <i class="ti ti-package-off" style="font-size:2.6rem;opacity:.3"></i>
                <div class="mt-2">
                    {{ strlen($busca) >= 2 ? 'Nenhum produto encontrado para "'.$busca.'"' : 'Selecione uma categoria ou busque um produto acima' }}
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===================== COLUNA: VENDA ===================== --}}
    <div class="pdv-col-venda">
        <div class="card pdv-venda-card">

            {{-- Cabeçalho do carrinho (fixo) --}}
            <div class="pdv-cart-head">
                <span>
                    <i class="ti ti-shopping-cart me-2 text-primary"></i>Carrinho
                    <span class="badge bg-primary rounded-pill ms-1">{{ count($carrinho) }}</span>
                </span>
                @if(count($carrinho))
                <button wire:click="limparVenda" type="button" class="btn btn-sm btn-outline-danger"
                    wire:confirm="Limpar todo o carrinho?" title="Limpar carrinho">
                    <i class="ti ti-trash"></i>
                </button>
                @endif
            </div>

            {{-- Itens (única área que rola) --}}
            <div class="pdv-cart-scroll">
                @forelse($carrinho as $key => $item)
                <div wire:key="cart-{{ $key }}" class="pdv-cart-item">
                    <div class="pdv-cart-info">
                        <div class="fw-semibold text-truncate">{{ $item['nome'] }}</div>
                        <div class="text-muted text-truncate" style="font-size:.76rem">{{ moeda($item['preco']) }} / {{ $item['unidade'] }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] - 1 }})"
                            type="button" class="btn btn-outline-secondary pdv-qty-btn">−</button>
                        <span class="fw-bold text-center" style="min-width:26px">{{ $item['quantidade'] }}</span>
                        <button wire:click="atualizarQuantidade('{{ $key }}', {{ $item['quantidade'] + 1 }})"
                            type="button" class="btn btn-outline-secondary pdv-qty-btn">+</button>
                    </div>
                    <div class="text-end fw-bold text-primary" style="min-width:72px;flex-shrink:0">{{ moeda($item['subtotal']) }}</div>
                    <button wire:click="removerItem('{{ $key }}')" type="button"
                        class="btn btn-sm btn-link text-danger p-0 ms-1" style="flex-shrink:0" title="Remover">
                        <i class="ti ti-x fs-5"></i>
                    </button>
                </div>
                @empty
                <div class="pdv-cart-empty">
                    <i class="ti ti-shopping-cart-off" style="font-size:2.8rem;opacity:.25"></i>
                    <div class="mt-2">Carrinho vazio</div>
                    <div class="small">Toque num produto ao lado</div>
                </div>
                @endforelse
            </div>

            {{-- Painel fixo: cliente + pagamento + total + finalizar --}}
            <div class="pdv-venda-pinned">
                <select wire:model.live="clienteId" class="form-select form-select-sm mb-2">
                    <option value="">— Consumidor Final —</option>
                    @foreach($clientes as $cli)
                    <option value="{{ $cli->id }}">{{ $cli->nome }}</option>
                    @endforeach
                </select>

                {{-- Formas de pagamento (linha única) --}}
                <div class="d-flex gap-1 mb-2">
                    @foreach([
                        'dinheiro'       => ['Dinheiro', 'ti-cash'],
                        'pix'            => ['PIX',      'ti-qrcode'],
                        'cartao_debito'  => ['Débito',   'ti-credit-card'],
                        'cartao_credito' => ['Crédito',  'ti-credit-card-pay'],
                        'fiado'          => ['Fiado',    'ti-user-check'],
                    ] as $val => [$label, $icon])
                    <div class="flex-fill" wire:key="pg-{{ $val }}" style="min-width:0">
                        <div wire:click="$set('formaPagamento', '{{ $val }}')"
                            class="pdv-pgto h-100 {{ $formaPagamento === $val ? 'ativo' : '' }}">
                            <i class="ti {{ $icon }} d-block mb-1"></i>
                            <span>{{ $label }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Extras por forma de pagamento (compactos) --}}
                @if($formaPagamento === 'dinheiro')
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text fw-bold">{{ simbolo_moeda() }}</span>
                    <input type="number" wire:model.live="valorPago" class="form-control" min="0" step="0.01" placeholder="Valor recebido">
                    @if($this->troco > 0)
                    <span class="input-group-text bg-success-subtle text-success fw-bold">Troco {{ moeda($this->troco) }}</span>
                    @endif
                </div>
                @endif

                @if($formaPagamento === 'pix')
                <div class="mb-2">
                    @if(!$this->pixConfigurado)
                        <div class="alert alert-warning small mb-0 py-2">
                            <i class="ti ti-alert-triangle me-1"></i>Configure a <strong>chave PIX</strong> em <a href="{{ route('configuracoes.index') }}">Configurações</a>.
                        </div>
                    @elseif($this->total <= 0)
                        <div class="alert alert-light border small mb-0 py-2">
                            <i class="ti ti-info-circle me-1"></i>Adicione itens ao carrinho para gerar o QR Code PIX.
                        </div>
                    @else
                        <input type="hidden" id="pix-payload" value="{{ $this->pixPayload }}">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalPix">
                            <i class="ti ti-qrcode me-1"></i>Mostrar QR Code para o cliente
                        </button>
                    @endif
                </div>
                @endif

                @if($formaPagamento === 'cartao_credito')
                <select wire:model="parcelas" class="form-select form-select-sm mb-2">
                    @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ $i }}x de {{ $this->total > 0 ? moeda($this->total / $i) : moeda(0) }} {{ $i === 1 ? '(à vista)' : '' }}</option>
                    @endfor
                </select>
                @endif

                {{-- Total --}}
                <div class="pdv-total-box">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold">{{ moeda($this->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Desconto</span>
                        <div class="input-group input-group-sm" style="width:120px">
                            <span class="input-group-text">{{ simbolo_moeda() }}</span>
                            <input type="number" wire:model.live="desconto" class="form-control text-end" min="0" step="0.01" max="{{ $this->subtotal }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">TOTAL</span>
                        <span class="pdv-total-final">{{ moeda($this->total) }}</span>
                    </div>
                </div>

                <button wire:click="finalizarVenda" wire:loading.attr="disabled" type="button"
                    class="btn btn-success w-100 pdv-btn-finalizar"
                    {{ (empty($carrinho) || !$caixaAberto) ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="finalizarVenda"><i class="ti ti-circle-check me-2"></i>Finalizar Venda <span class="opacity-75 small">(F2)</span></span>
                    <span wire:loading wire:target="finalizarVenda"><span class="spinner-border spinner-border-sm me-2"></span>Processando…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: QR CODE PIX ===================== --}}
    <div class="modal fade" id="modalPix" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0" style="border-radius:1rem">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold"><i class="ti ti-qrcode me-1 text-primary"></i>PIX — {{ moeda($this->total) }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-2">
                    <div id="pix-qr" wire:ignore class="d-flex justify-content-center my-2"></div>
                    <div class="input-group input-group-sm">
                        <input type="text" id="pix-code" class="form-control" value="{{ $this->pixPayload }}" readonly style="font-size:.65rem">
                        <button type="button" class="btn btn-outline-primary" onclick="copiarPix()"><i class="ti ti-copy"></i></button>
                    </div>
                    <div class="small text-muted mt-2">O cliente escaneia ou usa o PIX Copia e Cola.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: VENDA CONCLUÍDA ===================== --}}
    <div class="modal fade" id="modalVendaOk" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius:1rem;overflow:hidden">
                @if($ultimaVenda)
                <div class="text-center text-white py-4" style="background:linear-gradient(135deg,var(--pdv-success),var(--pdv-success-hover))">
                    <i class="ti ti-circle-check-filled" style="font-size:3rem"></i>
                    <h4 class="fw-bold mt-2 mb-0">Venda concluída!</h4>
                    <div class="opacity-75 small">{{ $ultimaVenda['numero'] }} · {{ $ultimaVenda['forma_pagamento'] }}</div>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-muted small">Total da venda</div>
                    <div class="fw-bold mb-3" style="font-size:1.8rem;color:var(--pdv-ink)">{{ moeda($ultimaVenda['total']) }}</div>
                    @if($ultimaVenda['troco'] > 0)
                    <div class="text-muted small">Troco</div>
                    <div class="modal-troco mb-2">{{ moeda($ultimaVenda['troco']) }}</div>
                    @endif

                    @if(!empty($ultimaVenda['nfce_status']))
                        @if(in_array($ultimaVenda['nfce_status'], ['autorizada', 'processando']))
                        <div class="alert alert-success py-2 small d-flex align-items-center justify-content-center gap-2 mb-2">
                            <i class="ti ti-file-check"></i>
                            NFC-e {{ $ultimaVenda['nfce_status'] === 'autorizada' ? 'autorizada' : 'em processamento' }}
                            @if($ultimaVenda['nfce_url'])
                            · <a href="{{ $ultimaVenda['nfce_url'] }}" target="_blank">ver nota</a>
                            @endif
                        </div>
                        @else
                        <div class="alert alert-warning py-2 small mb-2">
                            <i class="ti ti-alert-triangle me-1"></i>NFC-e não emitida: {{ $ultimaVenda['nfce_msg'] ?? 'verifique as configurações.' }}
                        </div>
                        @endif
                    @endif

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('vendas.recibo', $ultimaVenda['id']) }}" target="_blank" class="btn btn-primary py-2">
                            <i class="ti ti-receipt me-1"></i>Gerar Recibo
                        </a>
                        <button type="button" class="btn btn-success pdv-btn-finalizar py-2" data-bs-dismiss="modal" wire:click="novaVenda">
                            <i class="ti ti-circle-plus me-1"></i>Nova Venda <span class="opacity-75 small">(Enter)</span>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@script
<script>
    const focusBusca = () => { const i = document.getElementById('pdvBusca'); if (i) { i.focus(); i.select?.(); } };

    // ── QR Code PIX ──
    function renderPixQR() {
        const el = document.getElementById('pix-qr');
        const inp = document.getElementById('pix-payload');
        if (!el || !inp || !inp.value) { if (el) el.innerHTML = ''; return; }
        if (el.dataset.rendered === inp.value) return;
        el.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            new QRCode(el, { text: inp.value, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M });
            el.dataset.rendered = inp.value;
        }
    }
    window.copiarPix = function () {
        const t = document.getElementById('pix-code');
        if (!t) return;
        t.select(); t.setSelectionRange(0, 99999);
        navigator.clipboard?.writeText(t.value).catch(() => document.execCommand('copy'));
        const btn = event.currentTarget, html = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-check"></i>';
        setTimeout(() => btn.innerHTML = html, 1500);
    };
    renderPixQR();
    Livewire.hook('commit', ({ succeed }) => { succeed(() => setTimeout(renderPixQR, 60)); });
    document.getElementById('modalPix')?.addEventListener('shown.bs.modal', renderPixQR);

    // ── Modal venda concluída ──
    $wire.on('venda-finalizada', () => {
        const m = document.getElementById('modalVendaOk');
        if (m && window.bootstrap) bootstrap.Modal.getOrCreateInstance(m).show();
    });

    // ── Atalhos de teclado ──
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2') { e.preventDefault(); $wire.finalizarVenda(); }
        else if (e.key === 'F4') { e.preventDefault(); focusBusca(); }
        else if (e.key === 'Escape' && $wire.busca) { e.preventDefault(); $wire.set('busca', ''); }
        else if (e.key === 'Enter') {
            const m = document.getElementById('modalVendaOk');
            if (m && m.classList.contains('show')) {
                e.preventDefault();
                bootstrap.Modal.getOrCreateInstance(m).hide();
                $wire.novaVenda();
                setTimeout(focusBusca, 200);
            }
        }
    });

    // Volta o foco à busca após fechar o modal
    document.getElementById('modalVendaOk')?.addEventListener('hidden.bs.modal', () => setTimeout(focusBusca, 100));

    $wire.on('alerta', ({ mensagem }) => alert(mensagem));
</script>
@endscript
