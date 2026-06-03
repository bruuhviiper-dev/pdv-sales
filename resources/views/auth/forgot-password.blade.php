<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha — Sistema PDV</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/tabler-icons.min.css') }}">
    @include('partials.favicon')
    <style>
        body { min-height:100vh; background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1e293b 100%); display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',sans-serif; }
        .auth-card { width:100%; max-width:420px; padding:1rem; }
        .card { border-radius:1.25rem; box-shadow:0 25px 60px rgba(0,0,0,.4); border:0; }
        .card-header-custom { background:linear-gradient(135deg,#1e293b,#0f172a); padding:2rem; text-align:center; border-radius:1.25rem 1.25rem 0 0; }
        .logo-box { width:78px;height:78px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:1rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 8px 24px rgba(99,102,241,.5);overflow:hidden; }
        .logo-box.has-logo { background:#fff;padding:8px; }
        .logo-box i { font-size:1.9rem;color:#fff; }
        .logo-box img { width:100%;height:100%;object-fit:contain;border-radius:.6rem; }
        .card-header-custom h5 { color:#fff;font-weight:700;margin:0; }
        .card-header-custom p { color:#94a3b8;font-size:.82rem;margin:.3rem 0 0; }
        .form-label { font-weight:600;font-size:.85rem;color:#374151; }
        .form-control { border-radius:.5rem;border:1.5px solid #e5e7eb;padding:.7rem 1rem; }
        .form-control:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12); }
        .btn-submit { background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;border-radius:.5rem;padding:.7rem;font-weight:600;width:100%;color:#fff;box-shadow:0 4px 15px rgba(99,102,241,.3); }
        .btn-submit:hover { background:linear-gradient(135deg,#4f46e5,#4338ca); }
        .input-group-text { border:1.5px solid #e5e7eb;border-right:0;border-radius:.5rem 0 0 .5rem;background:#f9fafb;color:#9ca3af; }
        .input-group .form-control { border-left:0;border-radius:0 .5rem .5rem 0; }
        .input-group:focus-within .input-group-text { border-color:#6366f1; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="card">
        @php
            $empresaNome = \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV');
            $empresaLogo = \App\Models\Configuracao::get('empresa_logo');
            $temLogo = $empresaLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaLogo);
        @endphp
        <div class="card-header-custom">
            <div class="logo-box {{ $temLogo ? 'has-logo' : '' }}">
                @if($temLogo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaLogo) }}" alt="Logo">
                @else
                    <i class="ti ti-building-store"></i>
                @endif
            </div>
            <h5>{{ $empresaNome }}</h5>
            <p>Recuperar senha — informe seu e-mail para receber o link</p>
        </div>
        <div class="p-4">
            @if(session('status'))
            <div class="alert alert-success small mb-3">
                <i class="ti ti-circle-check me-2"></i>{{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">E-mail cadastrado</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-mail"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="seu@email.com" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="ti ti-send me-2"></i>Enviar Link de Redefinição
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="small text-muted text-decoration-none">
                    <i class="ti ti-arrow-left me-1"></i>Voltar para o login
                </a>
            </div>
        </div>
        <div class="py-2 px-4 border-top text-center" style="background:#f8fafc;border-radius:0 0 1.25rem 1.25rem">
            <small class="text-muted" style="font-size:.75rem">
                <i class="ti ti-shield-check me-1 text-success"></i>Sistema PDV v1.0
            </small>
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
