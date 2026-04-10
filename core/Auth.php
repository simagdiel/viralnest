<?php
// core/Auth.php

class Auth {

    // Garantir sessao ativa com cookie correto para subpasta
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Cookie valido para toda a raiz do dominio
            $cookiePath = '/';
            session_set_cookie_params(array(
                'lifetime' => 0,
                'path'     => $cookiePath,
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax',
            ));
            session_start();
        }
    }

    public static function login(array $user): void {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_level'] = $user['level'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['logged_in']  = true;
        session_regenerate_id(true);
    }

    public static function logout(): void {
        $_SESSION = [];
        session_destroy();
        session_start();
    }

    public static function check(): bool {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (!self::check()) return null;
        static $cache = null;
        if ($cache !== null) return $cache;
        $db   = Database::getInstance();
        $cache = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        return $cache;
    }

    public static function id(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public static function requireLogin(string $redirect = '/login'): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . $redirect);
            exit;
        }
    }

    public static function requireLevel(string $minLevel): void {
        self::requireLogin();
        $order = ['explorer' => 0, 'mentor' => 1, 'guardian' => 2, 'master' => 3, 'legend' => 4];
        $user = self::user();
        $userOrder = $order[$user['level']] ?? 0;
        $minOrder  = $order[$minLevel] ?? 0;
        if ($userOrder < $minOrder) {
            $_SESSION['flash'] = ['type' => 'warning', 'msg' => "Você precisa ser nível {$minLevel} para acessar isso."];
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfCheck(): void {
        // Garantir que a sessao esta ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token      = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        $sessToken  = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';

        // Se nao tem token na sessao, gerar um novo e deixar passar
        // (acontece quando sessao expirou entre GET e POST)
        if (empty($sessToken)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            return;
        }

        if (!hash_equals($sessToken, $token)) {
            header('Content-Type: text/html; charset=utf-8');
            http_response_code(403);
            // Regenerar token para proxima tentativa
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            die('Sessao expirada. <a href="javascript:history.back()">Voltar e tentar novamente</a>.');
        }
    }

    // Admin
    public static function adminLogin(array $admin): void {
        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role']  = $admin['role'];
        $_SESSION['admin_logged'] = true;
        session_regenerate_id(true);
    }

    public static function adminCheck(): bool {
        return !empty($_SESSION['admin_logged']) && !empty($_SESSION['admin_id']);
    }

    public static function adminRequire(): void {
        if (!self::adminCheck()) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }

    public static function flash(string $type, string $msg): void {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    public static function getFlash(): ?array {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }
}
