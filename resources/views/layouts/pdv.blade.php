<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDV — Frente de Caixa — {{ \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV') }}</title>
    @include('partials.favicon')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    @livewireStyles
    <style>
        :root { --pdv-head-h: 56px; }
        html, body { height: 100%; }
        body { overflow: hidden; }

        /* ─── Cabeçalho enxuto do PDV ─── */
        .pdv-header {
            height: var(--pdv-head-h);
            background: var(--sidebar-bg);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1rem; color: #e2e8f0;
            box-shadow: 0 2px 12px rgba(0,0,0,.15);
        }
        .pdv-header .brand { display: flex; align-items: center; gap: .7rem; min-width: 0; }
        .pdv-header .brand-logo {
            width: 34px; height: 34px; flex-shrink: 0; border-radius: .5rem; overflow: hidden;
            background: linear-gradient(135deg, var(--pdv-primary), var(--pdv-primary-hover));
            display: flex; align-items: center; justify-content: center;
        }
        .pdv-header .brand-logo.has-logo { background: #fff; padding: 3px; }
        .pdv-header .brand-logo i { color: #fff; font-size: 1.1rem; }
        .pdv-header .brand-logo img { width: 100%; height: 100%; object-fit: contain; }
        .pdv-header .brand .name { font-weight: 700; font-size: .95rem; line-height: 1; white-space: nowrap; }
        .pdv-header .brand .sub  { font-size: .68rem; color: #94a3b8; }
        .pdv-header .meta { display: flex; align-items: center; gap: .6rem; }
        .pdv-chip {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .35rem .7rem; border-radius: 2rem; font-size: .78rem; font-weight: 600;
        }
        .pdv-chip.ok    { background: rgba(34,197,94,.18);  color: #4ade80; }
        .pdv-chip.off   { background: rgba(220,38,38,.18);  color: #f87171; }
        .pdv-clock      { font-variant-numeric: tabular-nums; color: #cbd5e1; font-size: .85rem; }
        .pdv-exit {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.08); color: #e2e8f0; border: 0;
            padding: .4rem .8rem; border-radius: .5rem; font-size: .8rem; text-decoration: none;
            transition: background .15s;
        }
        .pdv-exit:hover { background: rgba(255,255,255,.16); color: #fff; }

        /* ─── Área de trabalho (preenche a tela) ─── */
        .pdv-stage { height: calc(100vh - var(--pdv-head-h)); overflow: hidden; padding: .85rem; }

        @stack('styles')
    </style>
</head>
<body>

<div id="offlineBar" class="d-none position-fixed top-0 start-0 end-0 text-center text-white py-1 small" style="z-index:2000;background:var(--pdv-danger)">
    <i class="ti ti-wifi-off me-1"></i>Você está offline — as vendas serão registradas quando a conexão voltar.
</div>

@php
    $empresaLogo = \App\Models\Configuracao::get('empresa_logo');
    $temLogo = $empresaLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaLogo);
    $caixaAbertoHeader = \App\Models\Caixa::where('status', 'aberto')->exists();
@endphp

<header class="pdv-header">
    <div class="brand">
        <div class="brand-logo {{ $temLogo ? 'has-logo' : '' }}">
            @if($temLogo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaLogo) }}" alt="Logo">
            @else
                <i class="ti ti-building-store"></i>
            @endif
        </div>
        <div>
            <div class="name">{{ \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV') }}</div>
            <div class="sub">Frente de Caixa — {{ auth()->user()->name }}</div>
        </div>
    </div>

    <div class="meta">
        @if($caixaAbertoHeader)
            <span class="pdv-chip ok"><i class="ti ti-lock-open"></i> Caixa aberto</span>
        @else
            <span class="pdv-chip off"><i class="ti ti-lock"></i> Caixa fechado</span>
        @endif
        <span class="pdv-clock d-none d-md-inline" id="pdvClock">{{ now()->format('d/m/Y H:i') }}</span>
        <a href="{{ route('dashboard') }}" class="pdv-exit" title="Voltar ao painel">
            <i class="ti ti-layout-dashboard"></i><span class="d-none d-sm-inline">Painel</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button class="pdv-exit" title="Sair do sistema">
                <i class="ti ti-logout-2"></i><span class="d-none d-sm-inline">Sair</span>
            </button>
        </form>
    </div>
</header>

<main class="pdv-stage">
    @yield('content')
</main>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
@livewireScripts
<script>
// Relógio
(function () {
    const el = document.getElementById('pdvClock');
    if (!el) return;
    setInterval(() => {
        const d = new Date();
        const p = n => String(n).padStart(2, '0');
        el.textContent = `${p(d.getDate())}/${p(d.getMonth()+1)}/${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
    }, 30000);
})();

// PWA + indicador offline — ativo apenas em produção
@production
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
window.addEventListener('offline', () => document.getElementById('offlineBar')?.classList.remove('d-none'));
window.addEventListener('online',  () => document.getElementById('offlineBar')?.classList.add('d-none'));
@else
// Ambiente local/teste: remove qualquer service worker e cache do PWA de testes anteriores
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(rs => rs.forEach(r => r.unregister()));
}
if (window.caches) { caches.keys().then(ks => ks.forEach(k => caches.delete(k))); }
@endproduction
</script>
@stack('scripts')
</body>
</html>
