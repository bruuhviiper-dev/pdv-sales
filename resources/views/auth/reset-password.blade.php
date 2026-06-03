<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha — Sistema PDV</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/tabler-icons.min.css') }}">
    <style>
        body { min-height:100vh; background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1e293b 100%); display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',sans-serif; }
        .auth-card { width:100%; max-width:420px; padding:1rem; }
        .card { border-radius:1.25rem; box-shadow:0 25px 60px rgba(0,0,0,.4); border:0; }
        .card-header-custom { background:linear-gradient(135deg,#1e293b,#0f172a); padding:2rem; text-align:center; border-radius:1.25rem 1.25rem 0 0; }
        .logo-box { width:64px;height:64px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:1rem;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 8px 24px rgba(99,102,241,.5); }
        .logo-box i { font-size:1.8rem;color:#fff; }
        .card-header-custom h5 { color:#fff;font-weight:700;margin:0; }
        .card-header-custom p { color:#94a3b8;font-size:.82rem;margin:.3rem 0 0; }
        .form-label { font-weight:600;font-size:.85rem;color:#374151; }
        .form-control { border-radius:.5rem;border:1.5px solid #e5e7eb;padding:.7rem 1rem; }
        .form-control:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12); }
        .btn-submit { background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;border-radius:.5rem;padding:.7rem;font-weight:600;width:100%;color:#fff; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="card">
        <div class="card-header-custom">
            <div class="logo-box"><i class="ti ti-lock-open"></i></div>
            <h5>Definir Nova Senha</h5>
            <p>Escolha uma senha segura para sua conta</p>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $request->email) }}"
                        class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nova Senha</label>
                    <input type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirmar Senha</label>
                    <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="ti ti-check me-2"></i>Redefinir Senha
                </button>
            </form>
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
