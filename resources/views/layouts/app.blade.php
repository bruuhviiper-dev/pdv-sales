<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema PDV') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @livewireStyles
    <style>
        :root { --sidebar-width: 250px; }
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-width); background: #1e293b;
            overflow-y: auto; z-index: 1000;
        }
        .sidebar .brand { padding: 1.2rem 1.5rem; border-bottom: 1px solid #334155; }
        .sidebar .brand h5 { color: #fff; margin: 0; font-weight: 700; font-size: 1rem; }
        .sidebar .brand small { color: #94a3b8; font-size: .75rem; }
        .sidebar .nav-link {
            color: #94a3b8; padding: .6rem 1.5rem; font-size: .875rem;
            display: flex; align-items: center; gap: .6rem; border-radius: 0; transition: all .2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: #2563eb; }
        .sidebar .nav-section {
            color: #64748b; font-size: .7rem; text-transform: uppercase;
            letter-spacing: .08em; padding: 1rem 1.5rem .3rem; font-weight: 600;
        }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: .75rem 1.5rem; position: sticky; top: 0; z-index: 100; }
        .page-content { padding: 1.5rem; }
        .card { border: 1px solid #e2e8f0; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; font-weight: 600; }
        .stat-card .icon { width: 48px; height: 48px; border-radius: .5rem; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <h5><i class="bi bi-shop me-2 text-primary"></i>{{ \App\Models\Configuracao::get('empresa_nome', config('app.name')) }}</h5>
            <small>Gestão Comercial</small>
        </div>
        <nav class="py-2">
            <div class="nav-section">Principal</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <a href="{{ route('pdv.index') }}" class="nav-link {{ request()->routeIs('pdv.*') ? 'active' : '' }}">
                <i class="bi bi-cart3"></i> PDV — Caixa
            </a>
            <div class="nav-section">Cadastros</div>
            <a href="{{ route('produtos.index') }}" class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Produtos
            </a>
            <a href="{{ route('categorias.index') }}" class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categorias
            </a>
            <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Clientes
            </a>
            <div class="nav-section">Operações</div>
            <a href="{{ route('estoque.index') }}" class="nav-link {{ request()->routeIs('estoque.*') ? 'active' : '' }}">
                <i class="bi bi-archive"></i> Estoque
            </a>
            <a href="{{ route('vendas.index') }}" class="nav-link {{ request()->routeIs('vendas.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Vendas
            </a>
            <a href="{{ route('caixa.index') }}" class="nav-link {{ request()->routeIs('caixa.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Caixa
            </a>
            <div class="nav-section">Financeiro</div>
            <a href="{{ route('financeiro.index') }}" class="nav-link {{ request()->routeIs('financeiro.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Contas
            </a>
            <a href="{{ route('relatorios.index') }}" class="nav-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Relatórios
            </a>
            <div class="nav-section">Sistema</div>
            @role('admin')
            <a href="{{ route('configuracoes.index') }}" class="nav-link {{ request()->routeIs('configuracoes.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Configurações
            </a>
            <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i> Usuários
            </a>
            @endrole
        </nav>
    </div>

    <div class="main-content">
        <div class="topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="bi bi-list"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">@yield('breadcrumb')</ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">{{ now()->format('d/m/Y H:i') }}</span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted">{{ implode(', ', auth()->user()->getRoleNames()->toArray()) }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-1"></i>Sair
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
