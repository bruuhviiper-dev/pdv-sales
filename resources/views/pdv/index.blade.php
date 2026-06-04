@extends('layouts.pdv')

@push('styles')
    /* ====== Layout imersivo do PDV ====== */
    .pdv-grid-wrap   { display: flex; gap: .85rem; height: 100%; }
    .pdv-col-produtos{ flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: .7rem; }
    .pdv-col-venda   { flex: 0 0 440px; width: 440px; max-width: 42vw; min-width: 360px; display: flex; }

    /* Busca / leitor de código */
    .pdv-busca { position: relative; }
    .pdv-busca .busca-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--pdv-muted); font-size: 1.3rem; pointer-events: none; }
    .pdv-busca .form-control { border: 2px solid var(--pdv-border); font-size: 1.1rem; padding: .8rem 1rem .8rem 2.8rem; height: 54px; }

    /* Chips de categoria */
    .pdv-cats { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .15rem; scrollbar-width: thin; }
    .pdv-cat  { white-space: nowrap; cursor: pointer; border: 1px solid var(--pdv-border); background: #fff; border-radius: 2rem; padding: .4rem .95rem; font-size: .82rem; font-weight: 600; color: var(--pdv-muted); transition: all .15s; }
    .pdv-cat:hover { border-color: var(--pdv-primary-border); color: var(--pdv-primary-hover); }
    .pdv-cat.ativo { background: var(--pdv-primary); border-color: var(--pdv-primary); color: #fff; }

    /* Grade de produtos (toque) */
    .pdv-produtos { flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); gap: .6rem; align-content: start; padding: .15rem; }
    .pdv-prod { cursor: pointer; border: 1px solid var(--pdv-border); border-radius: .65rem; background: #fff; padding: .7rem; display: flex; flex-direction: column; gap: .3rem; text-align: left; min-height: 110px; transition: all .12s; }
    .pdv-prod:hover  { border-color: var(--pdv-primary); box-shadow: 0 6px 16px rgba(99,102,241,.18); transform: translateY(-2px); }
    .pdv-prod:active { transform: translateY(0); }
    .pdv-prod:disabled { opacity: .5; cursor: not-allowed; }
    .pdv-prod .nome  { font-weight: 600; font-size: .85rem; line-height: 1.15; color: var(--pdv-ink); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pdv-prod .est   { font-size: .7rem; color: var(--pdv-muted); }
    .pdv-prod .preco { font-weight: 800; color: var(--pdv-primary-hover); font-size: 1.05rem; margin-top: auto; }
    .pdv-prod-empty  { grid-column: 1 / -1; text-align: center; color: var(--pdv-muted); padding: 3rem 1rem; }

    /* Coluna da venda — só os itens rolam; controles + total + finalizar ficam fixos */
    .pdv-venda-card  { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
    .pdv-cart-head   { flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; padding: .7rem 1rem; border-bottom: 1px solid var(--pdv-border); font-weight: 600; }
    .pdv-cart-scroll { flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; min-height: 80px; }
    .pdv-cart-scroll::-webkit-scrollbar { width: 7px; }
    .pdv-cart-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .pdv-cart-item  { display: flex; align-items: center; gap: .4rem; padding: .5rem .85rem; border-bottom: 1px solid var(--pdv-border); }
    .pdv-cart-item:hover { background: #f8fafc; }
    .pdv-cart-info  { flex: 1 1 auto; min-width: 0; }   /* min-width:0 garante o truncate e mata o overflow-x */
    .pdv-cart-empty { text-align: center; color: var(--pdv-muted); padding: 2.5rem 1rem; }
    .pdv-qty-btn    { width: 32px; height: 32px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700; line-height: 1; border-radius: .45rem; padding: 0; }

    .pdv-venda-pinned { flex-shrink: 0; border-top: 1px solid var(--pdv-border); padding: .6rem .7rem; background: #fff; box-shadow: 0 -4px 12px rgba(0,0,0,.04); }
    .pdv-total-box  { background: #f8fafc; border-radius: .6rem; padding: .5rem .75rem; margin: .15rem 0 .55rem; }
    .pdv-total-final{ font-size: 1.9rem; font-weight: 800; color: var(--pdv-primary-hover); line-height: 1; }

    /* Métodos de pagamento (toque) — 1 linha compacta */
    .pdv-pgto { cursor: pointer; transition: all .15s; border: 2px solid var(--pdv-border); padding: .4rem .15rem; border-radius: .55rem; background: #fff; text-align: center; }
    .pdv-pgto:hover { border-color: var(--pdv-primary-border); }
    .pdv-pgto.ativo { border-color: var(--pdv-primary); background: var(--pdv-primary-soft); box-shadow: 0 2px 8px rgba(99,102,241,.18); }
    .pdv-pgto.ativo i, .pdv-pgto.ativo span { color: var(--pdv-primary-hover) !important; font-weight: 700; }
    .pdv-pgto i { font-size: 1.25rem; }
    .pdv-pgto span { font-size: .68rem; line-height: 1; }

    .pdv-btn-finalizar { padding: .8rem; font-size: 1.1rem; font-weight: 700; border-radius: .6rem; background: var(--pdv-success); border-color: var(--pdv-success); }
    .pdv-btn-finalizar:hover:not(:disabled) { background: var(--pdv-success-hover); border-color: var(--pdv-success-hover); }

    /* Modal venda concluída */
    .modal-troco { font-size: 2.6rem; font-weight: 800; color: var(--pdv-success); line-height: 1; }

    /* Responsivo: empilha em telas pequenas */
    @media (max-width: 991px) {
        body { overflow: auto; }
        .pdv-stage { height: auto; }
        .pdv-grid-wrap { flex-direction: column; height: auto; }
        .pdv-col-venda { flex: 1 1 auto; max-width: 100%; }
        .pdv-produtos { max-height: 50vh; }
    }
@endpush

@section('content')
    @livewire('pdv')
@endsection
