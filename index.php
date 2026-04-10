<?php
// index.php - Front Controller ViralNest

// Detectar BASE_PATH automaticamente
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath  = rtrim($scriptDir === '/' ? '' : $scriptDir, '/');

// Carregar config (gerada pelo installer)
header('Content-Type: text/html; charset=utf-8');

$configFile = __DIR__ . '/config/config.php';
if (!file_exists($configFile)) {
    header('Location: ' . $basePath . '/install/');
    exit;
}

define('BASE_PATH_DIR', __DIR__);
require_once $configFile;

if (!defined('BASE_PATH')) define('BASE_PATH', $basePath);
if (!defined('BASE_URL'))  define('BASE_URL',  rtrim(BASE_URL_RAW ?? ('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $basePath), '/'));

// Autoload simples
spl_autoload_register(function ($class) {
    $dirs = [
        BASE_PATH_DIR . '/core/',
        BASE_PATH_DIR . '/models/',
        BASE_PATH_DIR . '/controllers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

// Bootstrap
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
Auth::startSession();
require_once __DIR__ . '/models/Setting.php';

// Rota atual
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = '/' . trim(str_replace($basePath, '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// ─── ROTAS ───────────────────────────────────────────────────────────────────

// Redirect raiz
if ($uri === '/' || $uri === '') {
    if (Auth::check()) header('Location: ' . BASE_URL . '/dashboard');
    else                header('Location: ' . BASE_URL . '/login');
    exit;
}

// Auth
if ($uri === '/login') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->login();
    exit;
}
if ($uri === '/register' || strpos($uri, '/register') === 0) {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->register();
    exit;
}
if ($uri === '/logout') {
    Auth::logout();
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Área protegida - verificar login
Auth::requireLogin();
require_once __DIR__ . '/models/User.php';

// Dashboard
if ($uri === '/dashboard') {
    require_once __DIR__ . '/controllers/UserController.php';
    (new UserController())->dashboard();
    exit;
}

// Profile
if ($uri === '/profile') {
    require_once __DIR__ . '/controllers/UserController.php';
    (new UserController())->profile();
    exit;
}
if ($uri === '/profile/update' && $method === 'POST') {
    require_once __DIR__ . '/controllers/UserController.php';
    (new UserController())->updateProfile();
    exit;
}

// Invites
if ($uri === '/invite') {
    require_once __DIR__ . '/controllers/InviteController.php';
    (new InviteController())->index();
    exit;
}

// Ranking
if ($uri === '/ranking') {
    require_once __DIR__ . '/controllers/UserController.php';
    (new UserController())->ranking();
    exit;
}

// Groups
if ($uri === '/groups') {
    require_once __DIR__ . '/controllers/GroupController.php';
    (new GroupController())->index();
    exit;
}
if ($uri === '/groups/create') {
    require_once __DIR__ . '/controllers/GroupController.php';
    (new GroupController())->create();
    exit;
}
if (preg_match('#^/groups/(\d+)/join$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/GroupController.php';
    (new GroupController())->join((int)$m[1]);
    exit;
}
if (preg_match('#^/groups/(\d+)$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/GroupController.php';
    (new GroupController())->show((int)$m[1]);
    exit;
}

// Courses
if ($uri === '/courses') {
    require_once __DIR__ . '/controllers/CourseController.php';
    (new CourseController())->index();
    exit;
}
if (preg_match('#^/courses/(\d+)$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/CourseController.php';
    (new CourseController())->show((int)$m[1]);
    exit;
}
if (preg_match('#^/courses/(\d+)/buy$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/CourseController.php';
    (new CourseController())->buy((int)$m[1]);
    exit;
}
if (preg_match('#^/courses/(\d+)/buy-points$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/CourseController.php';
    (new CourseController())->buyWithPoints((int)$m[1]);
    exit;
}
if (preg_match('#^/lessons/(\d+)$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/CourseController.php';
    (new CourseController())->lesson((int)$m[1]);
    exit;
}
if ($uri === '/lessons/complete' && $method === 'POST') {
    require_once __DIR__ . '/controllers/CourseController.php';
    (new CourseController())->completeLesson();
    exit;
}

// Plans
if ($uri === '/plans') {
    require_once __DIR__ . '/controllers/PlanController.php';
    (new PlanController())->index();
    exit;
}
if (preg_match('#^/plans/(\d+)/subscribe$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/PlanController.php';
    (new PlanController())->subscribe((int)$m[1]);
    exit;
}

// Notifications
if ($uri === '/api/notifications/read' && $method === 'POST') {
    $u = new User();
    $u->markNotificationsRead(Auth::id());
    echo json_encode(['ok' => true]);
    exit;
}

// Webhook callbacks
if (preg_match('#^/webhook/(\w+)$#', $uri, $m)) {
    require_once __DIR__ . '/controllers/WebhookController.php';
    (new WebhookController())->handle($m[1]);
    exit;
}

// 404
http_response_code(404);
$pageTitle = 'Página não encontrada';
include __DIR__ . '/views/layout/header.php';
echo '<div class="page"><div class="card" style="text-align:center;padding:3rem;"><div style="font-size:4rem;margin-bottom:1rem;">😕</div><h2 style="font-family:var(--font-head);font-size:1.5rem;">Página não encontrada</h2><p style="color:var(--text-muted);margin:1rem 0;">A página que você buscou não existe.</p><a href="' . BASE_URL . '/dashboard" class="btn btn-primary">Voltar ao Dashboard</a></div></div>';
include __DIR__ . '/views/layout/footer.php';
