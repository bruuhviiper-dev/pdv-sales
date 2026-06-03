<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistema PDV</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/tabler-icons.min.css') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e293b 100%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container { width: 100%; max-width: 440px; padding: 1rem; }
        .login-card {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 25px 60px rgba(0,0,0,.4);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .logo-box {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.2rem;
            box-shadow: 0 8px 24px rgba(37,99,235,.5);
        }
        .logo-box i { font-size: 2rem; color: #fff; }
        .login-header h4 { color: #fff; font-weight: 700; margin: 0; letter-spacing: -.01em; }
        .login-header p { color: #94a3b8; font-size: .85rem; margin: .3rem 0 0; }
        .login-body { padding: 2rem; }
        .form-label { font-weight: 600; font-size: .85rem; color: #374151; margin-bottom: .4rem; }
        .form-control {
            border: 1.5px solid #e5e7eb; border-radius: .6rem;
            padding: .7rem 1rem; font-size: .9rem; transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }
        .input-group-text {
            border: 1.5px solid #e5e7eb; border-right: 0;
            border-radius: .6rem 0 0 .6rem; background: #f9fafb; color: #9ca3af;
        }
        .input-group .form-control { border-left: 0; border-radius: 0 .6rem .6rem 0; }
        .input-group:focus-within .input-group-text { border-color: #2563eb; }
        .btn-login {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none; border-radius: .6rem;
            padding: .75rem; font-weight: 600; font-size: .95rem;
            color: #fff; width: 100%; transition: all .2s;
            box-shadow: 0 4px 15px rgba(37,99,235,.3);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            box-shadow: 0 6px 20px rgba(37,99,235,.4);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }
        .login-footer {
            background: #f8fafc; padding: 1rem 2rem;
            border-top: 1px solid #f1f5f9; text-align: center;
        }
        .login-footer small { color: #94a3b8; font-size: .78rem; }
        .alert-danger { border-radius: .6rem; font-size: .875rem; }
        .credentials-hint {
            background: #f0f9ff; border: 1px solid #bae6fd;
            border-radius: .6rem; padding: .75rem 1rem;
            font-size: .8rem; color: #0369a1;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        @php
            $empresaNome = \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV');
            $empresaLogo = \App\Models\Configuracao::get('empresa_logo');
            $temLogo = $empresaLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaLogo);
        @endphp
        <div class="login-header">
            <div class="logo-box">
                @if($temLogo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaLogo) }}" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:1rem">
                @else
                    <i class="ti ti-building-store"></i>
                @endif
            </div>
            <h4>{{ $empresaNome }}</h4>
            <p>Sistema de Gestão Comercial</p>
        </div>

        <div class="login-body">
            @if(session('status'))
            <div class="alert alert-success mb-3 small">{{ session('status') }}</div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger mb-3">
                <i class="ti ti-alert-circle me-2"></i>
                E-mail ou senha incorretos. Tente novamente.
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="email">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-mail"></i></span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="seu@email.com"
                            required
                            autofocus
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-lock"></i></span>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button class="btn btn-outline-secondary border border-start-0" type="button" id="togglePassword" style="border-radius:0 .6rem .6rem 0;border-left:0 !important">
                            <i class="ti ti-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Lembrar-me</label>
                    </div>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none">Esqueci a senha</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    <i class="ti ti-login-2 me-2"></i>Entrar no Sistema
                </button>
            </form>
        </div>

        <div class="login-footer">
            <small>
                <i class="ti ti-shield-check me-1 text-success"></i>
                Conexão segura &nbsp;|&nbsp; Sistema PDV v1.0
            </small>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'ti ti-eye-off';
    } else {
        pwd.type = 'password';
        icon.className = 'ti ti-eye';
    }
});
</script>
</body>
</html>
