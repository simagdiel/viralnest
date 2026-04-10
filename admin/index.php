<?php
// admin/index.php - Totalmente standalone, sem include de views para login
ini_set('display_errors', 0);
error_reporting(0);

$rootDir    = dirname(__DIR__);
$configFile = $rootDir . '/config/config.php';

// Sem config = instalar
if (!file_exists($configFile) || strpos(file_get_contents($configFile), '{{DB_HOST}}') !== false) {
    header('Location: ../install/index.php'); exit;
}

require_once $configFile;

if (!defined('BASE_PATH_DIR')) define('BASE_PATH_DIR', $rootDir);

// Sessao antes de qualquer output
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// Autoload
spl_autoload_register(function ($class) use ($rootDir) {
    foreach (array($rootDir.'/core/', $rootDir.'/models/', $rootDir.'/controllers/') as $dir) {
        $f = $dir . $class . '.php';
        if (file_exists($f)) { require_once $f; return; }
    }
});

require_once $rootDir . '/core/Database.php';
require_once $rootDir . '/models/Setting.php';

// URL base do admin detectada automaticamente - nunca depende de BASE_URL
$scheme    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host      = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /viral/admin
$adminUrl  = $scheme . '://' . $host . $scriptDir;         // https://site.com/viral/admin

// Rota
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page   = trim(str_replace($scriptDir, '', $uri), '/');
if ($page === '') $page = 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];

// ── HELPER: renderizar pagina simples ──────────────────────────────────────
function adminPage($title, $body) {
    $siteN = Setting::get('site_name', 'ViralNest');
    $css   = BASE_URL . '/assets/css/admin.css';
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . htmlspecialchars($title) . ' — ' . htmlspecialchars($siteN) . '</title>
<link rel="stylesheet" href="' . $css . '">
</head><body style="background:#0A0F1A;color:#F1F5F9;display:grid;place-items:center;min-height:100vh;font-family:sans-serif;">
<div style="width:100%;max-width:440px;padding:1.5rem;">' . $body . '</div></body></html>';
}

// ── PRIMEIRO ACESSO: criar usuario admin ───────────────────────────────────
$db = Database::getInstance();
$adminCount = (int)($db->fetchOne("SELECT COUNT(*) c FROM admin_users")['c'] ?? 0);
$isFirstAccess = ($adminCount === 0);

if ($isFirstAccess && $page !== 'setup') {
    header('Location: ' . $adminUrl . '/setup'); exit;
}

if ($page === 'setup') {
    $error = '';
    if ($method === 'POST') {
        $nome  = trim(isset($_POST['nome'])  ? $_POST['nome']  : '');
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $pass  = isset($_POST['pass']) ? $_POST['pass'] : '';
        if (!$nome || !$email || strlen($pass) < 6) {
            $error = 'Preencha todos os campos. Senha minima 6 caracteres.';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $db->insert('admin_users', array(
                'name'     => $nome,
                'email'    => $email,
                'password' => $hash,
                'role'     => 'super',
            ));
            header('Location: ' . $adminUrl . '/login'); exit;
        }
    }
    adminPage('Configurar Admin', '
      <div style="text-align:center;margin-bottom:1.5rem;">
        <div style="font-size:2rem;margin-bottom:0.5rem;">&#128640;</div>
        <div style="font-size:1.3rem;font-weight:800;color:#F59E0B;">Primeiro acesso</div>
        <div style="color:#94A3B8;font-size:0.85rem;margin-top:0.3rem;">Crie o usuario administrador</div>
      </div>
      ' . ($error ? '<div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem;border-radius:8px;margin-bottom:1rem;">' . htmlspecialchars($error) . '</div>' : '') . '
      <form method="POST" action="' . $adminUrl . '/setup">
        <div style="margin-bottom:0.75rem;">
          <label style="display:block;font-size:0.82rem;color:#94A3B8;margin-bottom:0.3rem;">Nome</label>
          <input type="text" name="nome" required style="width:100%;background:#1E293B;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:0.65rem 1rem;color:#F1F5F9;font-size:0.9rem;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:0.75rem;">
          <label style="display:block;font-size:0.82rem;color:#94A3B8;margin-bottom:0.3rem;">E-mail</label>
          <input type="email" name="email" required style="width:100%;background:#1E293B;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:0.65rem 1rem;color:#F1F5F9;font-size:0.9rem;box-sizing:border-box;">
        </div>
        <div style="margin-bottom:0.75rem;">
          <label style="display:block;font-size:0.82rem;color:#94A3B8;margin-bottom:0.3rem;">Senha (minimo 6 caracteres)</label>
          <input type="password" name="pass" required minlength="6" style="width:100%;background:#1E293B;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:0.65rem 1rem;color:#F1F5F9;font-size:0.9rem;box-sizing:border-box;">
        </div>
        <button type="submit" style="width:100%;padding:0.8rem;background:#F59E0B;border:none;border-radius:8px;font-weight:700;font-size:1rem;cursor:pointer;color:#000;margin-top:0.5rem;">Criar administrador</button>
      </form>');
    exit;
}

// ── LOGIN ──────────────────────────────────────────────────────────────────
if ($page === 'login') {
    if (!empty($_SESSION['admin_logged'])) {
        header('Location: ' . $adminUrl . '/'); exit;
    }
    $error = '';
    if ($method === 'POST') {
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $pass  = isset($_POST['password']) ? $_POST['password'] : '';
        $admin = $db->fetchOne("SELECT * FROM admin_users WHERE email = ? LIMIT 1", array($email));
        if ($admin && password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id']     = $admin['id'];
            $_SESSION['admin_name']   = $admin['name'];
            $_SESSION['admin_email']  = $admin['email'];
            $_SESSION['admin_role']   = $admin['role'];
            $_SESSION['admin_logged'] = true;
            header('Location: ' . $adminUrl . '/'); exit;
        }
        $error = 'E-mail ou senha incorretos.';
    }

    $flash = isset($_SESSION['admin_flash']) ? $_SESSION['admin_flash'] : null;
    unset($_SESSION['admin_flash']);

    adminPage('Login', '
      <div style="text-align:center;margin-bottom:1.5rem;">
        <div style="font-size:2rem;margin-bottom:0.5rem;">&#128640;</div>
        <div style="font-size:1.3rem;font-weight:800;color:#F59E0B;">' . htmlspecialchars(Setting::get('site_name','ViralNest')) . '</div>
        <div style="color:#94A3B8;font-size:0.85rem;margin-top:0.3rem;">Painel Administrativo</div>
      </div>
      ' . ($error ? '<div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem;border-radius:8px;margin-bottom:1rem;">' . htmlspecialchars($error) . '</div>' : '') . '
      ' . ($flash ? '<div style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#FCD34D;padding:0.75rem;border-radius:8px;margin-bottom:1rem;">' . htmlspecialchars($flash['msg']) . '</div>' : '') . '
      <form method="POST" action="' . $adminUrl . '/login">
        <div style="margin-bottom:0.75rem;">
          <label style="display:block;font-size:0.82rem;color:#94A3B8;margin-bottom:0.3rem;">E-mail</label>
          <input type="email" name="email" required autofocus
            style="width:100%;background:#1E293B;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:0.65rem 1rem;color:#F1F5F9;font-size:0.9rem;box-sizing:border-box;"
            value="' . htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : '') . '">
        </div>
        <div style="margin-bottom:0.75rem;">
          <label style="display:block;font-size:0.82rem;color:#94A3B8;margin-bottom:0.3rem;">Senha</label>
          <input type="password" name="password" required
            style="width:100%;background:#1E293B;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:0.65rem 1rem;color:#F1F5F9;font-size:0.9rem;box-sizing:border-box;">
        </div>
        <button type="submit" style="width:100%;padding:0.8rem;background:#F59E0B;border:none;border-radius:8px;font-weight:700;font-size:1rem;cursor:pointer;color:#000;margin-top:0.5rem;">Entrar no painel</button>
      </form>
      <div style="text-align:center;margin-top:1rem;font-size:0.82rem;">
        <a href="' . BASE_URL . '/" style="color:#F59E0B;">&larr; Voltar ao site</a>
      </div>');
    exit;
}

// ── LOGOUT ─────────────────────────────────────────────────────────────────
if ($page === 'logout') {
    $_SESSION = array();
    session_destroy();
    header('Location: ' . $adminUrl . '/login'); exit;
}

// ── PROTEGER ROTAS ─────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged'])) {
    header('Location: ' . $adminUrl . '/login'); exit;
}

require_once $rootDir . '/core/Auth.php';
require_once $rootDir . '/models/User.php';
require_once $rootDir . '/models/Course.php';
require_once $rootDir . '/core/WhatsellService.php';
require_once $rootDir . '/core/GatewayService.php';
require_once __DIR__ . '/AdminController.php';

$ctrl = new AdminController();

if ($page === 'dashboard' || $page === '') {
    $ctrl->dashboard();
} elseif ($page === 'users') {
    $ctrl->users();
} elseif (strpos($page, 'users/edit/') === 0) {
    $ctrl->userEdit((int)substr($page, 11));
} elseif (strpos($page, 'users/delete/') === 0) {
    $ctrl->userDelete((int)substr($page, 13));
} elseif (strpos($page, 'users/message/') === 0) {
    $ctrl->userMessage((int)substr($page, 14));
} elseif ($page === 'cycles') {
    $ctrl->cycles();
} elseif ($page === 'cycles/store' && $method === 'POST') {
    $ctrl->cycleStore();
} elseif ($page === 'invites') {
    $ctrl->invites();
} elseif ($page === 'groups') {
    $ctrl->groups();
} elseif ($page === 'ranking') {
    $ctrl->ranking();
} elseif ($page === 'courses') {
    $ctrl->courses();
} elseif ($page === 'courses/create') {
    $ctrl->courseCreate();
} elseif (strpos($page, 'courses/edit/') === 0) {
    $ctrl->courseEdit((int)substr($page, 13));
} elseif (strpos($page, 'courses/delete/') === 0) {
    $ctrl->courseDelete((int)substr($page, 15));
} elseif (strpos($page, 'courses/modules/') === 0) {
    $ctrl->modules((int)substr($page, 16));
} elseif (strpos($page, 'modules/lessons/') === 0) {
    $ctrl->lessons((int)substr($page, 16));
} elseif ($page === 'plans') {
    $ctrl->plans();
} elseif ($page === 'plans/store' && $method === 'POST') {
    $ctrl->planStore();
} elseif (strpos($page, 'plans/edit/') === 0) {
    $ctrl->planEdit((int)substr($page, 11));
} elseif (strpos($page, 'plans/delete/') === 0) {
    $ctrl->planDelete((int)substr($page, 12));
} elseif ($page === 'gateways') {
    $ctrl->gateways();
} elseif ($page === 'gateways/save' && $method === 'POST') {
    $ctrl->gatewaySave();
} elseif ($page === 'whatsapp') {
    $ctrl->whatsapp();
} elseif ($page === 'whatsapp/test' && $method === 'POST') {
    $ctrl->whatsappTest();
} elseif ($page === 'settings') {
    $ctrl->settings();
} elseif ($page === 'settings/save' && $method === 'POST') {
    $ctrl->settingsSave();
} elseif ($page === 'transactions') {
    $ctrl->transactions();
} else {
    http_response_code(404);
    echo '<p style="font-family:sans-serif;padding:2rem;color:#f87171;">404: ' . htmlspecialchars($page) . '</p>';
}
