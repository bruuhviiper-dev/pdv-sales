<?php
/**
 * Sistema PDV — Instalador Web
 * Acesse: seudominio.com/install.php
 *
 * Robusto para hospedagem compartilhada: não depende de exec()/shell.
 * Roda migrations e seeders pelo próprio kernel do Laravel.
 */

define('INSTALL_VERSION', '2.0.0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

$step  = isset($_GET['step']) ? (int) $_GET['step'] : 1;
$error = '';

/** Sobe o framework para rodar comandos artisan sem shell. */
function bootKernel(): array
{
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    return [$app, $kernel];
}

/* ───────── Passo 2: Banco de dados + configuração do .env ───────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $db_host = trim($_POST['db_host'] ?? '127.0.0.1');
    $db_port = trim($_POST['db_port'] ?? '3306');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? 'root');
    $db_pass = $_POST['db_pass'] ?? '';

    try {
        $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $envPath = __DIR__ . '/.env';
        $base    = is_file($envPath) ? file_get_contents($envPath) : file_get_contents($envPath . '.example');

        // Banco
        $base = preg_replace('/^DB_CONNECTION=.*/m', "DB_CONNECTION=mysql", $base);
        $base = preg_replace('/^DB_HOST=.*/m',       "DB_HOST={$db_host}", $base);
        $base = preg_replace('/^DB_PORT=.*/m',       "DB_PORT={$db_port}", $base);
        $base = preg_replace('/^DB_DATABASE=.*/m',   "DB_DATABASE={$db_name}", $base);
        $base = preg_replace('/^DB_USERNAME=.*/m',   "DB_USERNAME={$db_user}", $base);
        $base = preg_replace('/^DB_PASSWORD=.*/m',   'DB_PASSWORD="' . $db_pass . '"', $base);

        // App em modo produção
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $appUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $base = preg_replace('/^APP_KEY=.*/m',   "APP_KEY={$appKey}", $base);
        $base = preg_replace('/^APP_ENV=.*/m',   "APP_ENV=production", $base);
        $base = preg_replace('/^APP_DEBUG=.*/m', "APP_DEBUG=false", $base);
        $base = preg_replace('/^APP_URL=.*/m',   "APP_URL={$appUrl}", $base);

        file_put_contents($envPath, $base);

        header('Location: install.php?step=3');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível conectar ao banco: ' . $e->getMessage();
    }
}

/* ───────── Passo 3: Conta do administrador + dados da loja ───────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 3) {
    $empresaNome = trim($_POST['empresa_nome'] ?? 'Minha Loja');
    $adminNome   = trim($_POST['admin_nome'] ?? 'Administrador');
    $adminEmail  = trim($_POST['admin_email'] ?? '');
    $adminSenha  = $_POST['admin_senha'] ?? '';
    $tipo        = $_POST['tipo'] ?? 'nova'; // nova | demo

    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Informe um e-mail válido para o administrador.';
    } elseif (strlen($adminSenha) < 6) {
        $error = 'A senha do administrador deve ter pelo menos 6 caracteres.';
    } else {
        try {
            [$app, $kernel] = bootKernel();

            // Tabelas
            $kernel->call('migrate', ['--force' => true]);

            // Seeds essenciais sempre; demais só no modo "demo"
            $kernel->call('db:seed', ['--class' => 'Database\\Seeders\\RolesSeeder', '--force' => true]);
            $kernel->call('db:seed', ['--class' => 'Database\\Seeders\\UsersSeeder', '--force' => true]);
            $kernel->call('db:seed', ['--class' => 'Database\\Seeders\\ConfiguracoesSeeder', '--force' => true]);

            if ($tipo === 'demo') {
                foreach (['CategoriasSeeder','ProdutosSeeder','ClientesSeeder','VendasSeeder','ContasSeeder'] as $s) {
                    $kernel->call('db:seed', ['--class' => "Database\\Seeders\\{$s}", '--force' => true]);
                }
            }

            // Administrador com as credenciais escolhidas
            $user = \App\Models\User::where('email', 'admin@admin.com')->first();
            if (!$user) {
                $user = new \App\Models\User();
            }
            $user->name = $adminNome;
            $user->email = $adminEmail;
            $user->password = \Illuminate\Support\Facades\Hash::make($adminSenha);
            $user->email_verified_at = now();
            $user->save();
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }

            // Loja nova: remove usuários de demonstração
            if ($tipo === 'nova') {
                \App\Models\User::whereIn('email', ['operador@sistema.com', 'estoque@sistema.com'])->delete();
            }

            // Nome da loja + link de storage (logos/fotos)
            \App\Models\Configuracao::set('empresa_nome', $empresaNome);
            try { $kernel->call('storage:link'); } catch (Throwable $e) { /* alguns hosts já têm o link */ }

            $_SESSION_email = $adminEmail; // só p/ exibir
            header('Location: install.php?step=4&e=' . urlencode($adminEmail));
            exit;
        } catch (Throwable $e) {
            $error = 'Erro ao instalar: ' . $e->getMessage();
        }
    }
}

/* ───────── Passo 4: Finalizar (remover instalador) ───────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 4) {
    @rename(__FILE__, __DIR__ . '/install.php.done');
    header('Location: login');
    exit;
}

function req($ok, $name) {
    $c = $ok ? 'success' : 'danger';
    $i = $ok ? '✓' : '✗';
    echo "<div class='alert alert-{$c} py-2 mb-2'>{$i} {$name}</div>";
    return $ok;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema PDV — Instalador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background:#f1f5f9; font-family:'Segoe UI',sans-serif; }
        .installer-card { max-width:680px; margin:48px auto; padding:0 12px; }
        .step-badge { width:36px; height:36px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }
        .card { border:0; border-radius:1rem; box-shadow:0 8px 30px rgba(0,0,0,.08); }
        .card-header { border-radius:1rem 1rem 0 0 !important; }
        .btn-primary, .bg-primary { background:#6366f1 !important; border-color:#6366f1 !important; }
    </style>
</head>
<body>
<div class="installer-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold">🏪 Sistema PDV</h3>
        <p class="text-muted">Assistente de Instalação v<?= INSTALL_VERSION ?></p>
    </div>

    <div class="d-flex justify-content-between mb-4">
        <?php foreach ([1=>'Requisitos', 2=>'Banco de Dados', 3=>'Conta & Loja', 4=>'Conclusão'] as $s => $label): ?>
        <div class="text-center" style="flex:1">
            <div class="step-badge <?= $step >= $s ? 'bg-primary text-white' : 'bg-light text-muted' ?> mx-auto mb-1"><?= $s ?></div>
            <div class="small <?= $step >= $s ? 'text-primary fw-semibold' : 'text-muted' ?>"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <?php if ($step === 1):
            $allOk = true;
            ?>
        <div class="card-header fw-bold">Passo 1 — Verificação de Requisitos</div>
        <div class="card-body">
            <?php
            $checks = [
                'PHP 8.2+ (atual: ' . PHP_VERSION . ')' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'Extensão PDO MySQL'        => extension_loaded('pdo_mysql'),
                'Extensão mbstring'         => extension_loaded('mbstring'),
                'Extensão OpenSSL'          => extension_loaded('openssl'),
                'Extensão Tokenizer'        => extension_loaded('tokenizer'),
                'Extensão cURL (NFC-e/API)' => extension_loaded('curl'),
                'Pasta storage/ gravável'         => is_writable(__DIR__ . '/storage'),
                'Pasta bootstrap/cache/ gravável' => is_writable(__DIR__ . '/bootstrap/cache'),
                'Dependências instaladas (vendor/)' => is_file(__DIR__ . '/vendor/autoload.php'),
                'Modelo .env.example presente'      => is_file(__DIR__ . '/.env.example'),
            ];
            foreach ($checks as $name => $ok) { if (!$ok) $allOk = false; req($ok, $name); }
            ?>
            <?php if ($allOk): ?>
            <a href="install.php?step=2" class="btn btn-primary mt-3 w-100">Próximo →</a>
            <?php else: ?>
            <div class="alert alert-warning mt-3">Corrija os itens em vermelho antes de continuar. Em dúvida, fale com o suporte da sua hospedagem.</div>
            <?php endif; ?>
        </div>

        <?php elseif ($step === 2): ?>
        <div class="card-header fw-bold">Passo 2 — Banco de Dados</div>
        <div class="card-body">
            <p class="text-muted">Crie um banco MySQL no painel da sua hospedagem e informe os dados abaixo. O sistema já configura tudo (chave de segurança, modo produção) automaticamente.</p>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Host</label>
                        <input type="text" name="db_host" class="form-control" value="127.0.0.1" required>
                        <div class="form-text">Geralmente <code>localhost</code> ou <code>127.0.0.1</code>.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Porta</label>
                        <input type="text" name="db_port" class="form-control" value="3306" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nome do Banco <span class="text-danger">*</span></label>
                        <input type="text" name="db_name" class="form-control" placeholder="ex.: loja_pdv" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Usuário do Banco <span class="text-danger">*</span></label>
                        <input type="text" name="db_user" class="form-control" value="root" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Senha do Banco</label>
                        <input type="password" name="db_pass" class="form-control" placeholder="em branco se não houver">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">Testar conexão e continuar →</button>
            </form>
        </div>

        <?php elseif ($step === 3): ?>
        <div class="card-header fw-bold">Passo 3 — Conta do Administrador e Loja</div>
        <div class="card-body">
            <p class="text-muted">Defina o acesso do dono e o nome da loja. Estas serão suas credenciais de login.</p>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nome da Loja <span class="text-danger">*</span></label>
                        <input type="text" name="empresa_nome" class="form-control" placeholder="ex.: Mercado do Bairro" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Seu Nome <span class="text-danger">*</span></label>
                        <input type="text" name="admin_nome" class="form-control" value="Administrador" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Seu E-mail (login) <span class="text-danger">*</span></label>
                        <input type="email" name="admin_email" class="form-control" placeholder="voce@email.com" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Senha de acesso <span class="text-danger">*</span></label>
                        <input type="password" name="admin_senha" class="form-control" placeholder="mínimo 6 caracteres" minlength="6" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">Como deseja começar?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="t-nova" value="nova" checked>
                            <label class="form-check-label" for="t-nova"><strong>Loja nova</strong> — começa vazia, pronta para cadastrar seus produtos (recomendado).</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="t-demo" value="demo">
                            <label class="form-check-label" for="t-demo"><strong>Com dados de exemplo</strong> — produtos/clientes/vendas fictícios só para testar.</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100 mt-3">Instalar sistema →</button>
                <div class="text-muted small text-center mt-2">A instalação leva alguns segundos. Não feche a página.</div>
            </form>
        </div>

        <?php elseif ($step === 4): ?>
        <div class="card-header fw-bold bg-success text-white">✓ Instalação concluída!</div>
        <div class="card-body text-center">
            <div style="font-size:3.5rem">🎉</div>
            <h4 class="mt-2">Tudo pronto!</h4>
            <div class="alert alert-info mt-3 text-start">
                <strong>Seu acesso:</strong><br>
                👤 E-mail: <code><?= htmlspecialchars($_GET['e'] ?? 'seu e-mail') ?></code><br>
                🔑 Senha: a que você definiu agora.
            </div>
            <div class="alert alert-warning text-start small">
                Próximos passos no sistema: <strong>Configurações</strong> → dados da empresa, PIX e NFC-e.
                Depois abra o <strong>Caixa</strong> e comece a vender no <strong>PDV</strong>.
            </div>
            <form method="POST">
                <button type="submit" class="btn btn-success btn-lg w-100">Acessar o sistema →</button>
            </form>
            <div class="text-muted small mt-3">Por segurança, o instalador será desativado automaticamente.</div>
        </div>
        <?php endif; ?>
    </div>

    <div class="text-center text-muted small mt-3">Precisa de ajuda? Acione o suporte.</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
