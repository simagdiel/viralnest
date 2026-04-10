<?php
// controllers/UserController.php

class UserController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function dashboard(): void {
        $user = Auth::user();
        $siteName = Setting::get('site_name', 'ViralNest');
        include BASE_PATH_DIR . '/views/dashboard/index.php';
    }

    public function ranking(): void {
        $user = Auth::user();
        include BASE_PATH_DIR . '/views/ranking/index.php';
    }

    public function profile(): void {
        $user = Auth::user();
        $pageTitle = 'Meu Perfil';
        $activePlan = $this->userModel->getActivePlan($user['id']);
        $levelInfo  = $this->userModel->getLevelProgress($user);
        $db = Database::getInstance();
        $pointsHistory = $db->fetchAll(
            "SELECT * FROM points WHERE user_id = ? ORDER BY created_at DESC LIMIT 30",
            [$user['id']]
        );
        $myCourses = $db->fetchAll(
            "SELECT c.*, uc.access_type, uc.granted_at FROM user_courses uc
             JOIN courses c ON c.id = uc.course_id WHERE uc.user_id = ? ORDER BY uc.granted_at DESC",
            [$user['id']]
        );
        include BASE_PATH_DIR . '/views/profile/index.php';
    }

    public function updateProfile(): void {
        Auth::csrfCheck();
        $user = Auth::user();
        $db   = Database::getInstance();

        $name  = trim($_POST['name'] ?? '');
        $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');

        $data = [];
        if (strlen($name) >= 2) $data['name'] = $name;
        if ($phone) $data['phone'] = $phone;

        // Avatar upload
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $uploadDir = BASE_PATH_DIR . '/assets/img/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                    $data['avatar'] = BASE_URL . '/assets/img/uploads/' . $filename;
                }
            }
        }

        // Senha
        if (!empty($_POST['new_password'])) {
            if (!password_verify($_POST['current_password'] ?? '', $user['password'])) {
                Auth::flash('danger', 'Senha atual incorreta.');
                header('Location: ' . BASE_URL . '/profile');
                exit;
            }
            if (strlen($_POST['new_password']) < 8) {
                Auth::flash('danger', 'Nova senha muito curta.');
                header('Location: ' . BASE_URL . '/profile');
                exit;
            }
            $data['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        if (!empty($data)) {
            $db->update('users', $data, 'id = ?', [$user['id']]);
        }

        Auth::flash('success', 'Perfil atualizado com sucesso!');
        header('Location: ' . BASE_URL . '/profile');
        exit;
    }
}
