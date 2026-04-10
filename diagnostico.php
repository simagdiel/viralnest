<?php
// diagnostico.php - APAGUE APOS USAR
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$configFile = __DIR__ . '/config/config.php';
require_once $configFile;

// Valores detectados automaticamente
$scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
$self     = $_SERVER['SCRIPT_NAME']; // /viral/diagnostico.php
$basePath = rtrim(dirname($self), '/'); // /viral
$baseUrl  = $scheme . '://' . $host . $basePath;

echo '<style>
body{font-family:monospace;background:#0F172A;color:#F1F5F9;padding:2rem;font-size:14px;}
h2{color:#F59E0B;border-bottom:1px solid #334155;padding-bottom:0.5rem;margin:1.5rem 0 0.75rem;}
.ok{color:#4ADE80;} .err{color:#f87171;} .warn{color:#FCD34D;}
table{border-collapse:collapse;width:100%;margin-bottom:1rem;}
td,th{padding:0.4rem 0.75rem;border:1px solid #334155;text-align:left;}
th{background:#1E293B;color:#94A3B8;}
code{background:#1E293B;padding:2px 6px;border-radius:4px;color:#FBBF24;}
button{padding:0.75rem 2rem;background:#F59E0B;border:none;border-radius:8px;font-weight:700;cursor:pointer;color:#000;font-size:1rem;margin-top:1rem;}
</style>';

echo '<h1 style="color:#F59E0B;">&#128295; Diagnóstico ViralNest</h1>';

// 1. Valores do servidor
echo '<h2>1. Servidor</h2><table>';
$serverVars = ['SCRIPT_NAME','REQUEST_URI','HTTP_HOST','HTTPS','SERVER_SOFTWARE'];
foreach ($serverVars as $v) {
    $val = isset($_SERVER[$v]) ? $_SERVER[$v] : '(não definido)';
    echo "<tr><th>$v</th><td><code>" . htmlspecialchars($val) . "</code></td></tr>";
}
echo '</table>';

// 2. Valores detectados
echo '<h2>2. Valores detectados automaticamente</h2><table>';
echo "<tr><th>basePath correto</th><td><code>" . htmlspecialchars($basePath) . "</code></td></tr>";
echo "<tr><th>baseUrl correto</th><td><code>" . htmlspecialchars($baseUrl) . "</code></td></tr>";
echo '</table>';

// 3. Valores atuais no config
echo '<h2>3. config/config.php atual</h2><table>';
$configVars = ['BASE_URL','BASE_URL_RAW','BASE_PATH','DB_HOST','DB_NAME','DB_USER'];
foreach ($configVars as $v) {
    $val = defined($v) ? constant($v) : '(não definido)';
    $match = '';
    if ($v === 'BASE_URL') $match = ($val === $baseUrl) ? '<span class="ok">✓ correto</span>' : '<span class="err">✗ errado (deveria ser ' . htmlspecialchars($baseUrl) . ')</span>';
    if ($v === 'BASE_PATH') $match = ($val === $basePath) ? '<span class="ok">✓ correto</span>' : '<span class="err">✗ errado (deveria ser ' . htmlspecialchars($basePath) . ')</span>';
    echo "<tr><th>$v</th><td><code>" . htmlspecialchars($val) . "</code> $match</td></tr>";
}
echo '</table>';

// 4. Testar banco
echo '<h2>4. Banco de dados</h2>';
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo '<span class="ok">✓ Conexão OK</span><br><br>';
    echo 'Tabelas encontradas: <code>' . implode(', ', $tables) . '</code><br>';
    
    // Contar admins
    $admins = $pdo->query("SELECT id, email, LEFT(password,15) as hash FROM admin_users")->fetchAll();
    echo '<br><strong>admin_users:</strong><table><tr><th>id</th><th>email</th><th>hash (início)</th></tr>';
    foreach ($admins as $a) {
        echo '<tr><td>'.$a['id'].'</td><td>'.htmlspecialchars($a['email']).'</td><td><code>'.htmlspecialchars($a['hash']).'</code></td></tr>';
    }
    echo '</table>';
} catch (Exception $e) {
    echo '<span class="err">✗ Erro: ' . htmlspecialchars($e->getMessage()) . '</span>';
}

// 5. Testar arquivos CSS/JS
echo '<h2>5. Arquivos estáticos</h2><table>';
$files = [
    'assets/css/main.css'  => BASE_URL . '/assets/css/main.css',
    'assets/css/admin.css' => BASE_URL . '/assets/css/admin.css',
    'assets/js/main.js'    => BASE_URL . '/assets/js/main.js',
];
foreach ($files as $path => $url) {
    $exists = file_exists(__DIR__ . '/' . $path);
    $status = $exists ? '<span class="ok">✓ arquivo existe</span>' : '<span class="err">✗ arquivo não encontrado</span>';
    echo "<tr><th>$path</th><td>$status<br><small>URL: <code>" . htmlspecialchars($url) . "</code></small></td></tr>";
}
echo '</table>';

// 6. Aplicar correção se POST
echo '<h2>6. Corrigir config.php</h2>';
$needsFix = (defined('BASE_URL') && BASE_URL !== $baseUrl) || (defined('BASE_PATH') && BASE_PATH !== $basePath);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix'])) {
    $content = file_get_contents($configFile);
    $content = preg_replace("/define\('BASE_URL',\s*'[^']*'\);/", "define('BASE_URL', '" . addslashes($baseUrl) . "');", $content);
    $content = preg_replace("/define\('BASE_URL_RAW',\s*'[^']*'\);/", "define('BASE_URL_RAW', '" . addslashes($baseUrl) . "');", $content);
    $content = preg_replace("/define\('BASE_PATH',\s*'[^']*'\);/", "define('BASE_PATH', '" . addslashes($basePath) . "');", $content);
    if (file_put_contents($configFile, $content)) {
        echo '<span class="ok">✓ config.php corrigido! Recarregue a página para confirmar.</span>';
        echo '<br><br><a href="' . htmlspecialchars($baseUrl) . '/" style="color:#F59E0B;">→ Ir para o site</a> &nbsp; ';
        echo '<a href="' . htmlspecialchars($baseUrl) . '/admin/" style="color:#F59E0B;">→ Ir para o admin</a>';
    } else {
        echo '<span class="err">✗ Não foi possível salvar. Verifique permissões da pasta config/</span>';
    }
} elseif ($needsFix) {
    echo '<span class="warn">⚠ BASE_URL ou BASE_PATH estão incorretos.</span><br>';
    echo '<form method="POST"><button type="submit" name="fix" value="1">Corrigir config.php agora</button></form>';
} else {
    echo '<span class="ok">✓ config.php está correto.</span>';
}

// 7. Testar senha admin
echo '<h2>7. Testar / Redefinir senha admin</h2>';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_pass'])) {
    $newPass = $_POST['new_pass'];
    $adminId = (int)($_POST['admin_id'] ?? 1);
    if (strlen($newPass) >= 4) {
        $pdo2 = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $pdo2->prepare("UPDATE admin_users SET password=? WHERE id=?")->execute([$hash, $adminId]);
        echo '<span class="ok">✓ Senha do admin #' . $adminId . ' redefinida para: <strong>' . htmlspecialchars($newPass) . '</strong></span>';
        echo '<br>Hash gerado: <code>' . htmlspecialchars($hash) . '</code>';
        echo '<br>Verificação: <span class="ok">' . (password_verify($newPass, $hash) ? '✓ OK' : '✗ FALHOU') . '</span>';
    } else {
        echo '<span class="err">Senha muito curta (min 4 caracteres)</span>';
    }
}
echo '<br><form method="POST" style="margin-top:0.75rem;display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
    Admin ID: <input type="number" name="admin_id" value="1" style="width:60px;padding:0.4rem;background:#1E293B;border:1px solid #334155;border-radius:4px;color:#F1F5F9;">
    Nova senha: <input type="password" name="new_pass" placeholder="min 4 chars" style="padding:0.4rem 0.75rem;background:#1E293B;border:1px solid #334155;border-radius:4px;color:#F1F5F9;">
    <button type="submit" style="padding:0.4rem 1rem;font-size:0.9rem;">Redefinir</button>
</form>';

echo '<br><br><span class="warn">⚠ APAGUE este arquivo após usar!</span>';
