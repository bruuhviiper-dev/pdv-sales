<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PDV') — {{ \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/tabler-icons.min.css') }}">
    @livewireStyles
    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-collapsed-w: 68px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255,255,255,.07);
            --sidebar-active: rgba(99,102,241,.25);
            --sidebar-active-border: #6366f1;
            --topbar-h: 58px;
            --transition: .25s cubic-bezier(.4,0,.2,1);
        }
        * { box-sizing: border-box; }
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: .9rem;
            margin: 0;
        }

        /* ─── SIDEBAR ─── */
        .sidebar {
            position: fixed; inset: 0 auto 0 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            z-index: 1050;
            transition: width var(--transition);
            overflow: hidden;
            box-shadow: 4px 0 24px rgba(0,0,0,.18);
        }
        .sidebar.collapsed { width: var(--sidebar-collapsed-w); }

        /* Brand */
        .sidebar-brand {
            display: flex; align-items: center; gap: .9rem;
            padding: 1.1rem 1.1rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
            min-height: 62px;
            flex-shrink: 0;
        }
        .brand-logo {
            width: 36px; height: 36px; flex-shrink: 0;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-radius: .6rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(99,102,241,.4);
        }
        .brand-logo i { font-size: 1.2rem; color: #fff; }
        .brand-text { overflow: hidden; white-space: nowrap; }
        .brand-text .name {
            color: #f1f5f9; font-weight: 700; font-size: .9rem;
            display: block; line-height: 1.2;
            text-overflow: ellipsis; overflow: hidden;
        }
        .brand-text .sub { color: #64748b; font-size: .7rem; }

        /* Toggle button — fica FORA da sidebar (fixed) para não ser cortado pelo overflow */
        .sidebar-toggle {
            position: fixed; top: 16px;
            left: calc(var(--sidebar-w) - 13px);
            width: 26px; height: 26px; border-radius: 50%;
            background: #6366f1; border: 2px solid #fff;
            color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem; z-index: 1100;
            transition: left var(--transition), transform var(--transition), background .2s;
            box-shadow: 0 2px 10px rgba(0,0,0,.25);
        }
        .sidebar-toggle:hover { background: #4f46e5; }
        .sidebar.collapsed ~ .sidebar-toggle { left: calc(var(--sidebar-collapsed-w) - 13px); }
        .sidebar.collapsed ~ .sidebar-toggle i { transform: rotate(180deg); }

        /* Scroll area */
        .sidebar-scroll {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            padding: .5rem 0 1rem;
        }
        .sidebar-scroll::-webkit-scrollbar { width: 3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

        /* Section group */
        .nav-group { margin-bottom: .15rem; }
        .nav-group-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: .65rem 1.1rem .3rem;
            cursor: pointer; user-select: none;
        }
        .nav-group-label {
            color: #475569; font-size: .65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .1em;
            white-space: nowrap; overflow: hidden;
            transition: opacity var(--transition);
        }
        .nav-group-arrow {
            color: #475569; font-size: .7rem;
            transition: transform .2s;
            flex-shrink: 0;
        }
        .nav-group.open .nav-group-arrow { transform: rotate(180deg); }
        .nav-group-items { overflow: hidden; }

        /* When sidebar collapsed */
        .sidebar.collapsed .nav-group-label { opacity: 0; }
        .sidebar.collapsed .nav-group-header { padding: .5rem .8rem .2rem; }
        .sidebar.collapsed .nav-group-arrow { display: none; }
        .sidebar.collapsed .brand-text { opacity: 0; pointer-events: none; }

        /* Nav items */
        .nav-item { padding: 0 .6rem; margin-bottom: 1px; }
        .nav-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .55rem .75rem;
            color: #94a3b8; border-radius: .5rem;
            text-decoration: none; white-space: nowrap;
            transition: all .15s; position: relative;
            border: 1px solid transparent;
        }
        .nav-link:hover { color: #e2e8f0; background: var(--sidebar-hover); }
        .nav-link.active {
            color: #c7d2fe;
            background: var(--sidebar-active);
            border-color: rgba(99,102,241,.3);
        }
        .nav-link.active::before {
            content: '';
            position: absolute; left: -6px; top: 6px; bottom: 6px;
            width: 3px; border-radius: 0 2px 2px 0;
            background: var(--sidebar-active-border);
        }
        .nav-icon {
            font-size: 1.1rem; flex-shrink: 0;
            width: 22px; text-align: center;
        }
        .nav-label {
            font-size: .855rem; overflow: hidden;
            transition: opacity var(--transition);
            flex: 1;
        }
        .sidebar.collapsed .nav-label { opacity: 0; width: 0; }
        .sidebar.collapsed .nav-item { padding: 0 .5rem; }
        .sidebar.collapsed .nav-link { padding: .6rem .65rem; justify-content: center; gap: 0; }

        /* Tooltip on collapsed */
        .sidebar.collapsed .nav-link::after {
            content: attr(data-label);
            position: absolute; left: calc(100% + 10px);
            background: #1e293b; color: #e2e8f0;
            padding: .3rem .7rem; border-radius: .4rem;
            font-size: .78rem; white-space: nowrap;
            opacity: 0; pointer-events: none;
            transition: opacity .15s;
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
            z-index: 9999;
        }
        .sidebar.collapsed .nav-link:hover::after { opacity: 1; }

        /* Sidebar bottom */
        .sidebar-bottom {
            border-top: 1px solid rgba(255,255,255,.06);
            padding: .75rem .6rem;
            flex-shrink: 0;
        }
        .sidebar-user {
            display: flex; align-items: center; gap: .75rem;
            padding: .5rem .75rem;
            border-radius: .5rem;
            transition: background .15s;
            text-decoration: none;
            color: #94a3b8;
        }
        .sidebar-user:hover { background: var(--sidebar-hover); color: #e2e8f0; }
        .user-avatar {
            width: 32px; height: 32px; flex-shrink: 0;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; color: #fff; font-weight: 700;
        }
        .user-info { overflow: hidden; flex: 1; }
        .user-name { font-size: .8rem; color: #e2e8f0; font-weight: 600; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: .68rem; color: #64748b; display: block; }
        .sidebar.collapsed .user-info { display: none; }
        .sidebar.collapsed .sidebar-user { justify-content: center; padding: .5rem .65rem; }

        /* ─── MAIN ─── */
        .main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            transition: margin-left var(--transition);
        }
        .main-wrap.collapsed { margin-left: var(--sidebar-collapsed-w); }

        /* Topbar */
        .topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .topbar-right { display: flex; align-items: center; gap: .75rem; }
        .breadcrumb { margin: 0; font-size: .8rem; }
        .breadcrumb-item { color: #94a3b8; }
        .breadcrumb-item a { color: #64748b; text-decoration: none; }
        .breadcrumb-item.active { color: #374151; font-weight: 500; }

        /* Page content */
        .page-content { padding: 1.5rem; }

        /* Cards */
        .card { border: 1px solid #e2e8f0; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; font-weight: 600; padding: .85rem 1.25rem; border-radius: .75rem .75rem 0 0 !important; }
        .stat-card .icon { width: 44px; height: 44px; border-radius: .6rem; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .table th { font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
        .badge { font-weight: 500; }
        .btn { border-radius: .5rem; }
        .form-control, .form-select { border-radius: .5rem; border-color: #e2e8f0; }
        .form-control:focus, .form-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .btn-primary { background: #6366f1; border-color: #6366f1; }
        .btn-primary:hover { background: #4f46e5; border-color: #4f46e5; }
        .alert { border-radius: .6rem; }

        /* Mobile overlay */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 1040;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: var(--sidebar-w) !important; }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-wrap, .main-wrap.collapsed { margin-left: 0; }
            .sidebar-overlay.active { display: block; }
            .sidebar-toggle { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

<aside class="sidebar" id="sidebar">
    @php $empresaLogo = \App\Models\Configuracao::get('empresa_logo'); @endphp
    <div class="sidebar-brand">
        <div class="brand-logo">
            @if($empresaLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaLogo))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaLogo) }}" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:.6rem">
            @else
                <i class="ti ti-building-store"></i>
            @endif
        </div>
        <div class="brand-text">
            <span class="name">{{ \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV') }}</span>
            <span class="sub">Gestão Comercial</span>
        </div>
    </div>

    <div class="sidebar-scroll">

        {{-- Principal --}}
        <div class="nav-group open" id="group-principal">
            <div class="nav-group-header" onclick="toggleGroup('principal')">
                <span class="nav-group-label">Principal</span>
                <i class="ti ti-chevron-down nav-group-arrow"></i>
            </div>
            <div class="nav-group-items">
                <div class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-label="Dashboard">
                        <i class="ti ti-layout-dashboard nav-icon"></i>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </div>
                @hasanyrole('admin|operador')
                <div class="nav-item">
                    <a href="{{ route('pdv.index') }}" class="nav-link {{ request()->routeIs('pdv.*') ? 'active' : '' }}" data-label="PDV — Caixa">
                        <i class="ti ti-shopping-cart nav-icon"></i>
                        <span class="nav-label">PDV — Caixa</span>
                    </a>
                </div>
                @endhasanyrole
            </div>
        </div>

        {{-- Cadastros --}}
        <div class="nav-group open" id="group-cadastros">
            <div class="nav-group-header" onclick="toggleGroup('cadastros')">
                <span class="nav-group-label">Cadastros</span>
                <i class="ti ti-chevron-down nav-group-arrow"></i>
            </div>
            <div class="nav-group-items">
                @hasanyrole('admin|estoquista')
                <div class="nav-item">
                    <a href="{{ route('produtos.index') }}" class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}" data-label="Produtos">
                        <i class="ti ti-box nav-icon"></i>
                        <span class="nav-label">Produtos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('categorias.index') }}" class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" data-label="Categorias">
                        <i class="ti ti-tags nav-icon"></i>
                        <span class="nav-label">Categorias</span>
                    </a>
                </div>
                @endhasanyrole
                @hasanyrole('admin|operador')
                <div class="nav-item">
                    <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" data-label="Clientes">
                        <i class="ti ti-users nav-icon"></i>
                        <span class="nav-label">Clientes</span>
                    </a>
                </div>
                @endhasanyrole
            </div>
        </div>

        {{-- Operações --}}
        <div class="nav-group open" id="group-operacoes">
            <div class="nav-group-header" onclick="toggleGroup('operacoes')">
                <span class="nav-group-label">Operações</span>
                <i class="ti ti-chevron-down nav-group-arrow"></i>
            </div>
            <div class="nav-group-items">
                @hasanyrole('admin|estoquista')
                <div class="nav-item">
                    <a href="{{ route('estoque.index') }}" class="nav-link {{ request()->routeIs('estoque.*') ? 'active' : '' }}" data-label="Estoque">
                        <i class="ti ti-archive nav-icon"></i>
                        <span class="nav-label">Estoque</span>
                    </a>
                </div>
                @endhasanyrole
                @hasanyrole('admin|operador')
                <div class="nav-item">
                    <a href="{{ route('vendas.index') }}" class="nav-link {{ request()->routeIs('vendas.*') ? 'active' : '' }}" data-label="Vendas">
                        <i class="ti ti-receipt-2 nav-icon"></i>
                        <span class="nav-label">Vendas</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('caixa.index') }}" class="nav-link {{ request()->routeIs('caixa.*') ? 'active' : '' }}" data-label="Caixa">
                        <i class="ti ti-coin nav-icon"></i>
                        <span class="nav-label">Caixa</span>
                    </a>
                </div>
                @endhasanyrole
            </div>
        </div>

        {{-- Financeiro / Relatórios --}}
        <div class="nav-group open" id="group-financeiro">
            <div class="nav-group-header" onclick="toggleGroup('financeiro')">
                <span class="nav-group-label">Análise</span>
                <i class="ti ti-chevron-down nav-group-arrow"></i>
            </div>
            <div class="nav-group-items">
                @role('admin')
                <div class="nav-item">
                    <a href="{{ route('financeiro.index') }}" class="nav-link {{ request()->routeIs('financeiro.*') ? 'active' : '' }}" data-label="Financeiro">
                        <i class="ti ti-wallet nav-icon"></i>
                        <span class="nav-label">Financeiro</span>
                    </a>
                </div>
                @endrole
                @hasanyrole('admin|estoquista')
                <div class="nav-item">
                    <a href="{{ route('relatorios.index') }}" class="nav-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}" data-label="Relatórios">
                        <i class="ti ti-chart-bar nav-icon"></i>
                        <span class="nav-label">Relatórios</span>
                    </a>
                </div>
                @endhasanyrole
            </div>
        </div>

        {{-- Sistema (admin only) --}}
        @role('admin')
        <div class="nav-group open" id="group-sistema">
            <div class="nav-group-header" onclick="toggleGroup('sistema')">
                <span class="nav-group-label">Sistema</span>
                <i class="ti ti-chevron-down nav-group-arrow"></i>
            </div>
            <div class="nav-group-items">
                <div class="nav-item">
                    <a href="{{ route('configuracoes.index') }}" class="nav-link {{ request()->routeIs('configuracoes.*') ? 'active' : '' }}" data-label="Configurações">
                        <i class="ti ti-settings nav-icon"></i>
                        <span class="nav-label">Configurações</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" data-label="Usuários">
                        <i class="ti ti-users-group nav-icon"></i>
                        <span class="nav-label">Usuários</span>
                    </a>
                </div>
            </div>
        </div>
        @endrole

    </div>

    {{-- User at bottom --}}
    <div class="sidebar-bottom">
        <a href="{{ route('profile.edit') }}" class="sidebar-user" data-label="Minha Conta">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-role">{{ auth()->user()->getRoleNames()->map(fn($r) => ucfirst($r))->implode(', ') }}</span>
            </div>
        </a>
    </div>
</aside>

<button class="sidebar-toggle" id="sidebarToggle" title="Retrair / expandir menu">
    <i class="ti ti-chevron-left"></i>
</button>

<div class="main-wrap" id="mainWrap">
    <div class="topbar">
        <div class="topbar-left">
            <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="openMobileSidebar()">
                <i class="ti ti-menu-2"></i>
            </button>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="ti ti-home-2" style="font-size:.85rem"></i></a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <div class="topbar-right">
            <span class="text-muted small d-none d-md-inline">
                <i class="ti ti-calendar me-1"></i>{{ now()->format('d/m/Y') }}
            </span>

            <div class="dropdown">
                <button class="btn btn-sm d-flex align-items-center gap-2 border-0 text-secondary" data-bs-toggle="dropdown">
                    <div class="user-avatar" style="width:30px;height:30px;font-size:.7rem;background:linear-gradient(135deg,#6366f1,#4f46e5)">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="d-none d-md-inline fw-medium" style="font-size:.85rem">{{ auth()->user()->name }}</span>
                    <i class="ti ti-chevron-down" style="font-size:.75rem"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:200px;border-radius:.75rem">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ auth()->user()->email }}</div>
                        @foreach(auth()->user()->getRoleNames() as $role)
                        <span class="badge mt-1" style="background:#6366f1;font-size:.62rem">{{ ucfirst($role) }}</span>
                        @endforeach
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item small py-2">
                            <i class="ti ti-user-cog me-2 text-muted"></i>Minha Conta
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item small py-2 text-danger">
                                <i class="ti ti-logout-2 me-2"></i>Sair do Sistema
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-3">
            <i class="ti ti-circle-check-filled fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-3">
            <i class="ti ti-alert-circle-filled fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
@livewireScripts
<script>
// ── Sidebar collapse ──
const sidebar   = document.getElementById('sidebar');
const mainWrap  = document.getElementById('mainWrap');
const toggle    = document.getElementById('sidebarToggle');
const STORAGE_KEY = 'pdv_sidebar_collapsed';

function setSidebar(collapsed) {
    sidebar.classList.toggle('collapsed', collapsed);
    mainWrap.classList.toggle('collapsed', collapsed);
    localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
}

// Restore state
if (localStorage.getItem(STORAGE_KEY) === '1') setSidebar(true);

toggle?.addEventListener('click', () => setSidebar(!sidebar.classList.contains('collapsed')));

// ── Group accordion ──
function toggleGroup(id) {
    const group = document.getElementById('group-' + id);
    if (!group || sidebar.classList.contains('collapsed')) return;
    const isOpen = group.classList.contains('open');
    group.classList.toggle('open', !isOpen);
    const items = group.querySelector('.nav-group-items');
    if (items) {
        items.style.maxHeight = isOpen ? '0' : items.scrollHeight + 'px';
        items.style.overflow  = isOpen ? 'hidden' : 'visible';
        items.style.transition = 'max-height .22s ease';
    }
}

// Init open groups
document.querySelectorAll('.nav-group.open .nav-group-items').forEach(el => {
    el.style.maxHeight = 'none';
});

// ── Mobile ──
function openMobileSidebar() {
    sidebar.classList.add('mobile-open');
    document.getElementById('sidebarOverlay').classList.add('active');
}
function closeMobileSidebar() {
    sidebar.classList.remove('mobile-open');
    document.getElementById('sidebarOverlay').classList.remove('active');
}
</script>
@stack('scripts')
</body>
</html>
