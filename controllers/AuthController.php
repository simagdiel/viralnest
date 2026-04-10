<?php
// controllers/AuthController.php

class AuthController {
    private User $userModel;
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->userModel = new User();
    }

    public function login(): void {
        if (Auth::check()) { header('Location: ' . BASE_URL . '/dashboard'); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF nao aplicado no login
            $email    = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $user = $this->userModel->authenticate($email, $password);
            if ($user) {
                Auth::login($user);
                header('Location: ' . BASE_URL . '/dashboard');
            } else {
                Auth::flash('danger', 'E-mail ou senha incorretos.');
                header('Location: ' . BASE_URL . '/login');
            }
            exit;
        }

        include BASE_PATH_DIR . '/views/auth/login.php';
    }

    public function register(): void {
        if (Auth::check()) { header('Location: ' . BASE_URL . '/dashboard'); exit; }

        if (!Setting::bool('allow_registration')) {
            include BASE_PATH_DIR . '/views/auth/register.php';
            return;
        }

        // Verificar ciclo ativo
        $cycle = $this->db->fetchOne("SELECT * FROM cycles WHERE status = 'active' LIMIT 1");
        $requireInvite = Setting::bool('require_invite_after_cycle');

        if ($cycle && $cycle['require_invite']) $requireInvite = true;
        if ($cycle && !$cycle['require_invite'] && $cycle['current_users'] >= $cycle['max_users']) $requireInvite = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();

            $name     = trim($_POST['name'] ?? '');
            $email    = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $phone    = preg_replace('/\D/', '', isset($_POST['phone']) ? $_POST['phone'] : '');
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $passConf = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
            $invCode  = strtoupper(trim(isset($_POST['invite_code']) ? $_POST['invite_code'] : ''));

            // Validações
            if (strlen($name) < 2) { Auth::flash('danger', 'Nome muito curto.'); header('Location: ' . BASE_URL . '/register'); exit; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { Auth::flash('danger', 'E-mail inválido.'); header('Location: ' . BASE_URL . '/register'); exit; }
            if (strlen($password) < 8) { Auth::flash('danger', 'Senha precisa ter pelo menos 8 caracteres.'); header('Location: ' . BASE_URL . '/register'); exit; }
            if ($password !== $passConf) { Auth::flash('danger', 'As senhas não coincidem.'); header('Location: ' . BASE_URL . '/register'); exit; }

            // Email já cadastrado
            if ($this->userModel->findByEmail($email)) {
                Auth::flash('danger', 'E-mail já cadastrado.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // Verificar convite se necessário
            $invitedBy = null;
            $ownerUser = null;
            if (!empty($invCode)) {
                $ownerUser = $this->userModel->findByInviteCode($invCode);
                if (!$ownerUser) {
                    Auth::flash('danger', 'Código de convite inválido.');
                    header('Location: ' . BASE_URL . '/register?invite=' . $invCode);
                    exit;
                }
                $invitedBy = $ownerUser['id'];
            } elseif ($requireInvite) {
                Auth::flash('danger', 'Um código de convite é necessário para se cadastrar agora.');
                header('Location: ' . BASE_URL . '/register');
                exit;
            }

            // Criar usuário
            $userId = $this->userModel->create([
                'name'             => $name,
                'email'            => $email,
                'password'         => $password,
                'phone'            => $phone,
                'invited_by'       => $invitedBy,
                'used_invite_code' => $invCode ?: null,
                'cycle_id'         => $cycle['id'] ?? null,
            ]);

            // Atualizar ciclo
            if ($cycle) {
                $this->db->update('cycles', ['current_users' => $cycle['current_users'] + 1], 'id = ?', [$cycle['id']]);
            }

            // Pontuar quem convidou
            if ($ownerUser) {
                $pts = Setting::int('points_invite', 100);
                $this->userModel->addPoints($ownerUser['id'], $pts, 'invite', 'Convite aceito por ' . $name, $userId);

                // Notificar via WhatsApp
                $ws = new WhatsellService();
                $ws->notifyInviteUsed($ownerUser, ['name' => $name], $pts);
                $this->userModel->createNotification($ownerUser['id'], 'Convite aceito!', "{$name} entrou usando seu convite! +{$pts} pontos.", 'invite');
            }

            // Notificar novo usuário
            $newUser = $this->userModel->findById($userId);
            $ws = new WhatsellService();
            if ($phone) $ws->notifyRegister($newUser);

            Auth::login($newUser);
            Auth::flash('success', 'Bem-vindo(a) à comunidade! 🎉 Você ganhou ' . Setting::int('points_register', 50) . ' pontos de boas-vindas.');
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        include BASE_PATH_DIR . '/views/auth/register.php';
    }
}
