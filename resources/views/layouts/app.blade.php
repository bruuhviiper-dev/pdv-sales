<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PDV') — {{ \App\Models\Configuracao::get('empresa_nome', config('app.name')) }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    @livewireStyles
    <style>
        :root { --sidebar-width: 250px; }
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-width); background: #1e293b;
            overflow-y: auto; z-index: 1000; transition: transform .25s ease;
        }
        .sidebar .brand { padding: 1.2rem 1.5rem; border-bottom: 1px solid #334155; }
        .sidebar .brand h5 { color: #fff; margin: 0; font-weight: 700; font-size: .95rem; letter-spacing: .01em; }
        .sidebar .brand small { color: #94a3b8; font-size: .72rem; }
        .sidebar .nav-link {
            color: #94a3b8; padding: .55rem 1.5rem; font-size: .855rem;
            display: flex; align-items: center; gap: .6rem;
            border-radius: 0; transition: all .15s; border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover { color: #e2e8f0; background: rgba(255,255,255,.06); }
        .sidebar .nav-link.active { color: #fff; background: rgba(37,99,235,.25); border-left-color: #2563eb; }
        .sidebar .nav-link .bi { font-size: 1rem; width: 18px; text-align: center; }
        .sidebar .nav-section {
            color: #475569; font-size: .67rem; text-transform: uppercase;
            letter-spacing: .1em; padding: 1rem 1.5rem .3rem; font-weight: 700;
        }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: .65rem 1.5rem; position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .page-content { padding: 1.5rem; }
        .card { border: 1px solid #e2e8f0; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; font-weight: 600; padding: .85rem 1.25rem; }
        .stat-card .icon { width: 48px; height: 48px; border-radius: .6rem; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .table th { font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
        .badge { font-weight: 500; }
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
        <div class="brand d-flex align-items-center gap-2">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-shop text-white" style="font-size:.95rem"></i>
            </div>
            <div>
                <h5 class="mb-0">{{ \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV') }}</h5>
                <small>Gestão Comercial</small>
            </div>
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
                    <i class="bi bi-list fs-5"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-decoration-none">Início</a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-md-inline">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d/m/Y') }}
                </span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <div style="width:28px;height:28px;background:#2563eb;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-person-fill text-white" style="font-size:.75rem"></i>
                        </div>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-2">
                            <div class="fw-semibold small">{{ auth()->user()->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ auth()->user()->email }}</div>
                            <div class="mt-1">
                                @foreach(auth()->user()->getRoleNames() as $role)
                                <span class="badge bg-primary" style="font-size:.65rem">{{ ucfirst($role) }}</span>
                                @endforeach
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger small">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair do Sistema
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-content">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
