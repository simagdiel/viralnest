<?php
// admin/AdminController.php

class AdminController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function render(string $view, array $data = []): void {
        extract($data);
        $flash = Auth::getFlash();
        include __DIR__ . '/views/layout.php';
        // view is included inside layout.php via $viewFile
    }

    private function view(string $view, array $data = []): void {
        extract($data);
        $flash    = Auth::getFlash();
        $viewFile = __DIR__ . '/views/' . $view . '.php';
        include __DIR__ . '/views/layout.php';
    }

    // ─── DASHBOARD ───
    public function dashboard(): void {
        $stats = [
            'users'         => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users")['c'] ?? 0),
            'active_users'  => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users WHERE status='active'")['c'] ?? 0),
            'today_users'   => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users WHERE DATE(created_at)=CURDATE()")['c'] ?? 0),
            'points_issued' => (int)($this->db->fetchOne("SELECT COALESCE(SUM(points),0) c FROM points WHERE points>0")['c'] ?? 0),
            'invites_used'  => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users WHERE used_invite_code IS NOT NULL")['c'] ?? 0),
            'groups'        => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM groups")['c'] ?? 0),
            'courses'       => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM courses")['c'] ?? 0),
            'revenue'       => (float)($this->db->fetchOne("SELECT COALESCE(SUM(amount),0) c FROM transactions WHERE status='paid'")['c'] ?? 0),
            'active_subs'   => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM user_subscriptions WHERE status='active'")['c'] ?? 0),
        ];
        $recentUsers   = $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 8");
        $recentTx      = $this->db->fetchAll("SELECT t.*, u.name FROM transactions t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 8");
        $cycle         = $this->db->fetchOne("SELECT * FROM cycles WHERE status='active' LIMIT 1");
        $pageTitle     = 'Dashboard';
        $this->view('dashboard', compact('stats','recentUsers','recentTx','cycle','pageTitle'));
    }

    // ─── USERS ───
    public function users(): void {
        $search = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['p'] ?? 1));
        $limit  = 20; $offset = ($page-1)*$limit;
        $where  = $search ? "WHERE u.name LIKE ? OR u.email LIKE ?" : "";
        $params = $search ? ["%$search%", "%$search%"] : [];
        $users  = $this->db->fetchAll("SELECT u.*, (SELECT name FROM plans p JOIN user_subscriptions us ON us.plan_id=p.id WHERE us.user_id=u.id AND us.status='active' ORDER BY p.price DESC LIMIT 1) AS plan_name FROM users u $where ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset", $params);
        $total  = (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users u $where", $params)['c'] ?? 0);
        $pages  = ceil($total / $limit);
        $pageTitle = 'Usuários';
        $this->view('users/index', compact('users','total','page','pages','search','pageTitle'));
    }

    public function userEdit(int $id): void {
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) { Auth::flash('danger','Usuário não encontrado.'); header('Location: '.BASE_URL.'/admin/users'); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $data = [
                'name'   => trim($_POST['name'] ?? $user['name']),
                'status' => $_POST['status'] ?? $user['status'],
                'level'  => $_POST['level'] ?? $user['level'],
            ];
            if (!empty($_POST['points_add'])) {
                $pts = (int)$_POST['points_add'];
                $userModel = new User();
                $userModel->addPoints($id, $pts, 'admin_grant', 'Pontos concedidos pelo admin');
            }
            $this->db->update('users', $data, 'id = ?', [$id]);
            Auth::flash('success', 'Usuário atualizado!');
            header('Location: '.BASE_URL.'/admin/users'); exit;
        }
        $pageTitle = 'Editar Usuário';
        $this->view('users/edit', compact('user','pageTitle'));
    }

    public function userDelete(int $id): void {
        Auth::csrfCheck();
        $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
        Auth::flash('success', 'Usuário removido.');
        header('Location: '.BASE_URL.'/admin/users'); exit;
    }

    public function userMessage(int $id): void {
        Auth::csrfCheck();
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        $msg  = trim($_POST['message'] ?? '');
        if ($user && $msg && !empty($user['phone'])) {
            $ws = new WhatsellService();
            $ws->sendCustom($user, $msg);
            Auth::flash('success', 'Mensagem enviada!');
        } else {
            Auth::flash('danger', 'Usuário sem telefone ou mensagem vazia.');
        }
        header('Location: '.BASE_URL.'/admin/users'); exit;
    }

    // ─── CYCLES ───
    public function cycles(): void {
        $cycles = $this->db->fetchAll("SELECT * FROM cycles ORDER BY created_at DESC");
        $pageTitle = 'Ciclos';
        $this->view('cycles', compact('cycles','pageTitle'));
    }

    public function cycleStore(): void {
        Auth::csrfCheck();
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $this->db->insert('cycles', [
                'name'           => trim($_POST['name']),
                'max_users'      => (int)$_POST['max_users'],
                'status'         => $_POST['status'],
                'require_invite' => isset($_POST['require_invite']) ? 1 : 0,
                'start_date'     => $_POST['start_date'] ?: null,
                'end_date'       => $_POST['end_date'] ?: null,
            ]);
            Auth::flash('success', 'Ciclo criado!');
        } elseif ($action === 'status') {
            $this->db->update('cycles', ['status' => $_POST['status']], 'id = ?', [(int)$_POST['cycle_id']]);
            Auth::flash('success', 'Status atualizado!');
        }
        header('Location: '.BASE_URL.'/admin/cycles'); exit;
    }

    // ─── INVITES ───
    public function invites(): void {
        $invites = $this->db->fetchAll(
            "SELECT u.name AS owner_name, u.email AS owner_email,
                    u2.name AS used_by_name, u.invite_code, u.created_at,
                    (SELECT COUNT(*) FROM users WHERE invited_by=u.id) AS invite_count
             FROM users u LEFT JOIN users u2 ON u2.invited_by=u.id
             GROUP BY u.id ORDER BY invite_count DESC LIMIT 100"
        );
        $pageTitle = 'Convites';
        $this->view('invites', compact('invites','pageTitle'));
    }

    // ─── GROUPS ───
    public function groups(): void {
        $groups = $this->db->fetchAll(
            "SELECT g.*, u.name AS leader_name, COUNT(gm.id) AS member_count
             FROM groups g JOIN users u ON u.id=g.leader_id
             LEFT JOIN group_members gm ON gm.group_id=g.id
             GROUP BY g.id ORDER BY g.created_at DESC"
        );
        $pageTitle = 'Grupos';
        $this->view('groups', compact('groups','pageTitle'));
    }

    // ─── RANKING ───
    public function ranking(): void {
        $ranking = $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.points, u.level,
                    (SELECT COUNT(*) FROM users WHERE invited_by=u.id) AS invites
             FROM users u WHERE u.status='active' ORDER BY u.points DESC LIMIT 100"
        );
        $pageTitle = 'Ranking';
        $this->view('ranking', compact('ranking','pageTitle'));
    }

    // ─── COURSES ───
    public function courses(): void {
        $courses = (new Course())->getAll(false);
        $pageTitle = 'Cursos';
        $this->view('courses/index', compact('courses','pageTitle'));
    }

    public function courseCreate(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $cm = new Course();
            $cm->create([
                'title'          => trim($_POST['title']),
                'description'    => trim($_POST['description'] ?? ''),
                'thumbnail'      => trim($_POST['thumbnail'] ?? ''),
                'price'          => (float)($_POST['price'] ?? 0),
                'points_price'   => (int)($_POST['points_price'] ?? 0),
                'instructor'     => trim($_POST['instructor'] ?? ''),
                'level_required' => $_POST['level_required'] ?? 'explorer',
                'is_free'        => isset($_POST['is_free']) ? 1 : 0,
                'is_active'      => 1,
            ]);
            Auth::flash('success', 'Curso criado!');
            header('Location: '.BASE_URL.'/admin/courses'); exit;
        }
        $pageTitle = 'Novo Curso';
        $this->view('courses/form', compact('pageTitle'));
    }

    public function courseEdit(int $id): void {
        $cm = new Course();
        $course = $cm->findById($id);
        if (!$course) { Auth::flash('danger','Curso não encontrado.'); header('Location: '.BASE_URL.'/admin/courses'); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $this->db->update('courses', [
                'title'          => trim($_POST['title']),
                'description'    => trim($_POST['description'] ?? ''),
                'thumbnail'      => trim($_POST['thumbnail'] ?? ''),
                'price'          => (float)($_POST['price'] ?? 0),
                'points_price'   => (int)($_POST['points_price'] ?? 0),
                'instructor'     => trim($_POST['instructor'] ?? ''),
                'level_required' => $_POST['level_required'] ?? 'explorer',
                'is_free'        => isset($_POST['is_free']) ? 1 : 0,
                'is_active'      => isset($_POST['is_active']) ? 1 : 0,
                'sort_order'     => (int)($_POST['sort_order'] ?? 0),
            ], 'id = ?', [$id]);
            Auth::flash('success', 'Curso atualizado!');
            header('Location: '.BASE_URL.'/admin/courses'); exit;
        }
        $pageTitle = 'Editar Curso';
        $this->view('courses/form', compact('course','pageTitle'));
    }

    public function courseDelete(int $id): void {
        Auth::csrfCheck();
        $this->db->query("DELETE FROM courses WHERE id = ?", [$id]);
        Auth::flash('success', 'Curso removido.');
        header('Location: '.BASE_URL.'/admin/courses'); exit;
    }

    public function modules(int $courseId): void {
        $cm = new Course();
        $course = $cm->findById($courseId);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $this->db->insert('modules', ['course_id'=>$courseId,'title'=>trim($_POST['title']),'description'=>trim($_POST['description']??''),'sort_order'=>(int)($_POST['sort_order']??0)]);
                Auth::flash('success','Módulo criado!');
            } elseif ($action === 'delete') {
                $this->db->query("DELETE FROM modules WHERE id=? AND course_id=?", [(int)$_POST['module_id'],$courseId]);
                Auth::flash('success','Módulo removido.');
            }
            header('Location: '.BASE_URL.'/admin/courses/modules/'.$courseId); exit;
        }
        $modules = $cm->getModulesWithLessons($courseId);
        $pageTitle = 'Módulos — '.$course['title'];
        $this->view('courses/modules', compact('course','modules','pageTitle','courseId'));
    }

    public function lessons(int $moduleId): void {
        $module = $this->db->fetchOne("SELECT m.*, c.title AS course_title FROM modules m JOIN courses c ON c.id=m.course_id WHERE m.id=?",[$moduleId]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $url  = trim($_POST['video_url'] ?? '');
                $type = !empty($_POST['video_type']) ? $_POST['video_type'] : Course::detectVideoType($url);
                $this->db->insert('lessons', [
                    'module_id'      => $moduleId,
                    'title'          => trim($_POST['title']),
                    'description'    => trim($_POST['description']??''),
                    'video_url'      => $url,
                    'video_type'     => $type,
                    'duration_minutes'=> (int)($_POST['duration']??0),
                    'is_preview'     => isset($_POST['is_preview'])?1:0,
                    'points_reward'  => (int)($_POST['points_reward']??0),
                    'sort_order'     => (int)($_POST['sort_order']??0),
                ]);
                Auth::flash('success','Aula criada!');
            } elseif ($action === 'delete') {
                $this->db->query("DELETE FROM lessons WHERE id=? AND module_id=?",[(int)$_POST['lesson_id'],$moduleId]);
                Auth::flash('success','Aula removida.');
            }
            header('Location: '.BASE_URL.'/admin/modules/lessons/'.$moduleId); exit;
        }
        $lessons = $this->db->fetchAll("SELECT * FROM lessons WHERE module_id=? ORDER BY sort_order",[$moduleId]);
        $pageTitle = 'Aulas — '.$module['title'];
        $this->view('courses/lessons', compact('module','lessons','pageTitle','moduleId'));
    }

    // ─── PLANS ───
    public function plans(): void {
        $plans = $this->db->fetchAll("SELECT * FROM plans ORDER BY sort_order, price");
        $pageTitle = 'Planos';
        $this->view('plans/index', compact('plans','pageTitle'));
    }

    public function planStore(): void {
        Auth::csrfCheck();
        $features = array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')));
        $data = [
            'name'              => trim($_POST['name']),
            'slug'              => strtolower(preg_replace('/[^a-z0-9]+/','-',trim($_POST['name']))),
            'description'       => trim($_POST['description']??''),
            'price'             => (float)($_POST['price']??0),
            'billing_cycle'     => $_POST['billing_cycle']??'monthly',
            'features'          => json_encode(array_values($features)),
            'course_discount'   => (float)($_POST['course_discount']??0),
            'points_multiplier' => (float)($_POST['points_multiplier']??1),
            'max_groups'        => (int)($_POST['max_groups']??1),
            'badge_color'       => $_POST['badge_color']??'#FFD700',
            'is_active'         => 1,
            'sort_order'        => (int)($_POST['sort_order']??0),
        ];
        if (!empty($_POST['plan_id'])) {
            $this->db->update('plans', $data, 'id=?', [(int)$_POST['plan_id']]);
            Auth::flash('success','Plano atualizado!');
        } else {
            $this->db->insert('plans', $data);
            Auth::flash('success','Plano criado!');
        }
        header('Location: '.BASE_URL.'/admin/plans'); exit;
    }

    public function planEdit(int $id): void {
        $plan = $this->db->fetchOne("SELECT * FROM plans WHERE id=?",[$id]);
        $pageTitle = 'Editar Plano';
        $this->view('plans/form', compact('plan','pageTitle'));
    }

    public function planDelete(int $id): void {
        Auth::csrfCheck();
        $this->db->query("DELETE FROM plans WHERE id=?",[$id]);
        Auth::flash('success','Plano removido.');
        header('Location: '.BASE_URL.'/admin/plans'); exit;
    }

    // ─── GATEWAYS ───
    public function gateways(): void {
        $gw = new GatewayService();
        $gateways  = $gw->getAllGateways();
        $pageTitle = 'Gateways de Pagamento';
        $this->view('gateways', compact('gateways','pageTitle'));
    }

    public function gatewaySave(): void {
        Auth::csrfCheck();
        $gw      = new GatewayService();
        $gateway = $_POST['gateway'] ?? '';
        $fields  = GatewayService::getCredentialFields($gateway);
        $creds   = [];
        foreach ($fields as $f) {
            $creds[$f['key']] = trim($_POST['creds'][$f['key']] ?? '');
        }
        $gw->saveCredentials($gateway, $creds, isset($_POST['is_active']), isset($_POST['sandbox']));
        Auth::flash('success', ucfirst($gateway) . ' configurado!');
        header('Location: '.BASE_URL.'/admin/gateways'); exit;
    }

    // ─── WHATSAPP ───
    public function whatsapp(): void {
        $logs = $this->db->fetchAll("SELECT wl.*, u.name FROM whatsapp_logs wl LEFT JOIN users u ON u.id=wl.user_id ORDER BY wl.created_at DESC LIMIT 50");
        $settings = Setting::getByCategory('whatsapp');
        $pageTitle = 'WhatsApp / Whatsell';
        $this->view('whatsapp', compact('logs','settings','pageTitle'));
    }

    public function whatsappTest(): void {
        Auth::csrfCheck();
        $phone = trim($_POST['phone'] ?? '');
        $msg   = trim($_POST['message'] ?? 'Teste de conexão ViralNest 🚀');
        $ws    = new WhatsellService();
        $ok    = $ws->send($phone, $msg, null, 'test');
        Auth::flash($ok ? 'success' : 'danger', $ok ? 'Mensagem enviada!' : 'Falha ao enviar. Verifique o token e o número.');
        header('Location: '.BASE_URL.'/admin/whatsapp'); exit;
    }

    // ─── SETTINGS ───
    public function settings(): void {
        $allSettings = Setting::getAll();
        $categories  = array_unique(array_column($allSettings, 'category'));
        $pageTitle   = 'Configurações';
        $this->view('settings', compact('allSettings','categories','pageTitle'));
    }

    public function settingsSave(): void {
        Auth::csrfCheck();
        foreach ($_POST['settings'] ?? [] as $key => $value) {
            Setting::set($key, $value);
        }
        Setting::clearCache();
        Auth::flash('success', 'Configurações salvas!');
        header('Location: '.BASE_URL.'/admin/settings'); exit;
    }

    // ─── TRANSACTIONS ───
    public function transactions(): void {
        $transactions = $this->db->fetchAll(
            "SELECT t.*, u.name, u.email FROM transactions t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 100"
        );
        $pageTitle = 'Transações';
        $this->view('transactions', compact('transactions','pageTitle'));
    }
}
