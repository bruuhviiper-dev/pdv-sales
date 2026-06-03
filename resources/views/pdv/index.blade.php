@extends('layouts.app')
@section('title', 'PDV — Frente de Caixa')
@section('breadcrumb')
    <li class="breadcrumb-item active">PDV — Frente de Caixa</li>
@endsection

@push('styles')
<style>
    /* ====== PDV ====== */
    .pdv-busca .form-control {
        border: 2px solid #e2e8f0; font-size: 1.05rem; padding: .85rem 1rem .85rem 2.6rem;
    }
    .pdv-busca { position: relative; }
    .pdv-busca .busca-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.2rem; pointer-events: none; }
    .pdv-sugestoes { max-height: 320px; overflow-y: auto; }
    .pdv-sugestao { transition: background .12s; }
    .pdv-sugestao:hover { background: #eef2ff !important; }

    /* Carrinho */
    .pdv-cart-wrap { min-height: 340px; }
    .pdv-cart-item { transition: background .12s; }
    .pdv-cart-item:hover { background: #f8fafc; }
    .pdv-qty-btn { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 600; line-height: 1; border-radius: .45rem; }

    /* Painel de pagamento fixo */
    .pdv-pay { position: sticky; top: calc(var(--topbar-h) + 1.5rem); }
    .pdv-pay-head {
        background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff;
        border-radius: .75rem .75rem 0 0; padding: .85rem 1.25rem; font-weight: 600;
    }
    .pdv-total-box { background: #f8fafc; border-radius: .6rem; }
    .pdv-total-final { font-size: 1.75rem; font-weight: 800; color: #4f46e5; line-height: 1; }

    /* Métodos de pagamento — touch friendly */
    .pdv-pgto { cursor: pointer; transition: all .15s; border: 2px solid #e2e8f0 !important; padding: .7rem .4rem; border-radius: .6rem; background: #fff; }
    .pdv-pgto:hover { border-color: #c7d2fe !important; background: #f8fafc; }
    .pdv-pgto.ativo { border-color: #6366f1 !important; background: #eef2ff; box-shadow: 0 2px 8px rgba(99,102,241,.18); }
    .pdv-pgto.ativo i, .pdv-pgto.ativo span { color: #4f46e5 !important; font-weight: 600; }
    .pdv-pgto i { font-size: 1.4rem; }

    .pdv-btn-finalizar { padding: .9rem; font-size: 1.05rem; font-weight: 700; border-radius: .6rem; }

    @media (max-width: 991px) {
        .pdv-pay { position: static; }
    }
</style>
@endpush

@section('content')
    @livewire('pdv')
@endsection
