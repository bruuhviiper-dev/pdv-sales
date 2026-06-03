<?php
/**
 * Sistema PDV - Instalador Web
 * Acesse: seudominio.com/install.php
 */

define('INSTALL_VERSION', '1.0.0');
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Passo 2: Testar banco de dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $db_host = trim($_POST['db_host'] ?? '127.0.0.1');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? 'root');
    $db_pass = $_POST['db_pass'] ?? '';

    try {
        $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Salvar configurações no .env
        $envPath = __DIR__ . '/.env';
        $envContent = file_get_contents($envPath . '.example') ?: file_get_contents($envPath);
        $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST={$db_host}", $envContent);
        $envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$db_name}", $envContent);
        $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$db_user}", $envContent);
        $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$db_pass}", $envContent);
        $envContent = preg_replace('/DB_CONNECTION=.*/', "DB_CONNECTION=mysql", $envContent);
        file_put_contents($envPath, $envContent);

        header('Location: install.php?step=3');
        exit;
    } catch (PDOException $e) {
        $error = 'Erro de conexão: ' . $e->getMessage();
    }
}

// Passo 3: Dados da empresa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 3) {
    // Executar migrations via artisan
    $output = [];
    exec('php artisan migrate --seed --force 2>&1', $output);
    $migrationOk = stripos(implode(' ', $output), 'error') === false;

    if ($migrationOk) {
        // Salvar dados da empresa
        $empresaNome = $_POST['empresa_nome'] ?? 'Minha Loja';
        $empresaCnpj = $_POST['empresa_cnpj'] ?? '';
        $empresaTel = $_POST['empresa_telefone'] ?? '';

        header('Location: install.php?step=4');
        exit;
    } else {
        $error = 'Erro ao executar migrations: ' . implode('<br>', $output);
    }
}

// Passo 5: Finalizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 5) {
    // Remover o instalador
    rename(__FILE__, __DIR__ . '/install.php.done');
    header('Location: /login');
    exit;
}

function checkRequirement($check, $name) {
    $ok = $check;
    $color = $ok ? 'success' : 'danger';
    $icon = $ok ? '✓' : '✗';
    echo "<div class='alert alert-{$color} py-2 mb-2'>{$icon} {$name}</div>";
    return $ok;
}

$allOk = true;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema PDV — Instalador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .installer-card { max-width: 680px; margin: 60px auto; }
        .step-badge { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
    </style>
</head>
<body>
<div class="installer-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold"><i>🏪</i> Sistema PDV</h3>
        <p class="text-muted">Assistente de Instalação v<?= INSTALL_VERSION ?></p>
    </div>

    <!-- Barra de progresso -->
    <div class="d-flex justify-content-between mb-4">
        <?php foreach ([1=>'Requisitos', 2=>'Banco de Dados', 3=>'Migração', 4=>'Conclusão'] as $s => $label): ?>
        <div class="text-center" style="flex:1">
            <div class="step-badge <?= $step >= $s ? 'bg-primary text-white' : 'bg-light text-muted' ?> mx-auto mb-1"><?= $s ?></div>
            <div class="small <?= $step >= $s ? 'text-primary fw-semibold' : 'text-muted' ?>"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <?php if ($step === 1): ?>
        <!-- PASSO 1: Requisitos -->
        <div class="card-header fw-bold">Passo 1 — Verificação de Requisitos</div>
        <div class="card-body">
            <?php
            $checks = [
                version_compare(PHP_VERSION, '8.2.0', '>=') => 'PHP 8.2+ (atual: ' . PHP_VERSION . ')',
                extension_loaded('pdo_mysql') => 'Extensão PDO MySQL',
                extension_loaded('mbstring') => 'Extensão mbstring',
                extension_loaded('openssl') => 'Extensão OpenSSL',
                extension_loaded('tokenizer') => 'Extensão Tokenizer',
                is_writable(__DIR__ . '/storage') => 'Pasta storage/ gravável',
                is_writable(__DIR__ . '/bootstrap/cache') => 'Pasta bootstrap/cache/ gravável',
                file_exists(__DIR__ . '/.env') => 'Arquivo .env existe',
            ];
            foreach ($checks as $check => $name) {
                $ok = (bool)$check;
                if (!$ok) $allOk = false;
                checkRequirement($ok, $name);
            }
            ?>
            <?php if ($allOk): ?>
            <a href="install.php?step=2" class="btn btn-primary mt-3 w-100">Próximo →</a>
            <?php else: ?>
            <div class="alert alert-warning mt-3">Corrija os requisitos acima antes de continuar.</div>
            <?php endif; ?>
        </div>

        <?php elseif ($step === 2): ?>
        <!-- PASSO 2: Banco de dados -->
        <div class="card-header fw-bold">Passo 2 — Configuração do Banco de Dados</div>
        <div class="card-body">
            <p class="text-muted">Insira os dados do banco MySQL. Crie o banco antes de continuar.</p>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Host do Banco de Dados</label>
                    <input type="text" name="db_host" class="form-control" value="127.0.0.1" required>
                    <div class="form-text">Geralmente: localhost ou 127.0.0.1</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nome do Banco de Dados <span class="text-danger">*</span></label>
                    <input type="text" name="db_name" class="form-control" placeholder="sistema_pdv" required>
                    <div class="form-text">Crie este banco no phpMyAdmin antes</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Usuário do Banco</label>
                    <input type="text" name="db_user" class="form-control" value="root" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha do Banco</label>
                    <input type="password" name="db_pass" class="form-control" placeholder="deixe em branco se não tiver senha">
                </div>
                <button type="submit" class="btn btn-primary w-100">Testar e Continuar →</button>
            </form>
        </div>

        <?php elseif ($step === 3): ?>
        <!-- PASSO 3: Migração -->
        <div class="card-header fw-bold">Passo 3 — Criação das Tabelas</div>
        <div class="card-body">
            <p>O sistema irá criar todas as tabelas e inserir os dados de exemplo.</p>
            <div class="alert alert-info">
                <strong>Dados de exemplo incluídos:</strong><br>
                • 50 produtos de mercearia/papelaria<br>
                • 10 clientes cadastrados<br>
                • 30 dias de vendas de demonstração<br>
                • Usuários: admin, operador, estoquista
            </div>
            <form method="POST">
                <button type="submit" class="btn btn-success w-100">Instalar Banco de Dados →</button>
            </form>
        </div>

        <?php elseif ($step === 4): ?>
        <!-- PASSO 4: Conclusão -->
        <div class="card-header fw-bold bg-success text-white">✓ Instalação Concluída!</div>
        <div class="card-body text-center">
            <div style="font-size:4rem">🎉</div>
            <h4 class="mt-3">Sistema PDV instalado com sucesso!</h4>
            <div class="alert alert-info mt-4 text-start">
                <strong>Credenciais de acesso:</strong><br>
                👤 Admin: <code>admin@admin.com</code> / <code>admin123</code><br>
                👤 Operador: <code>operador@sistema.com</code> / <code>operador123</code><br>
                👤 Estoquista: <code>estoque@sistema.com</code> / <code>estoque123</code>
            </div>
            <form method="POST">
                <button type="submit" class="btn btn-success btn-lg w-100">
                    Acessar o Sistema →
                </button>
            </form>
            <div class="text-muted small mt-3">O instalador será removido automaticamente por segurança.</div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
