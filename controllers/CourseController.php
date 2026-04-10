<?php
// controllers/CourseController.php

class CourseController {
    private Course $courseModel;
    private User   $userModel;

    public function __construct() {
        $this->courseModel = new Course();
        $this->userModel   = new User();
    }

    public function index(): void {
        $user     = Auth::user();
        $pageTitle = 'Cursos';
        $db       = Database::getInstance();
        $courses  = $this->courseModel->getAll(true);
        $discount = $this->userModel->getCourseDiscount($user['id']);

        // Para cada curso, checar acesso e aplicar desconto
        foreach ($courses as &$c) {
            $c['has_access'] = $this->courseModel->userHasAccess($user['id'], $c['id']);
            $c['final_price'] = $discount > 0 ? $this->courseModel->getDiscountedPrice($c['price'], $discount) : $c['price'];
            $c['discount_pct'] = $discount;
        }

        $activePlan = $this->userModel->getActivePlan($user['id']);
        include BASE_PATH_DIR . '/views/courses/index.php';
    }

    public function show(int $id): void {
        $user   = Auth::user();
        $course = $this->courseModel->findById($id);
        if (!$course || !$course['is_active']) {
            Auth::flash('danger', 'Curso não encontrado.');
            header('Location: ' . BASE_URL . '/courses');
            exit;
        }

        $pageTitle  = $course['title'];
        $hasAccess  = $this->courseModel->userHasAccess($user['id'], $id);
        $modules    = $this->courseModel->getModulesWithLessons($id);
        $progress   = $hasAccess ? $this->courseModel->getUserProgress($user['id'], $id) : ['percent'=>0,'completed'=>0,'total'=>0];
        $discount   = $this->userModel->getCourseDiscount($user['id']);
        $finalPrice = $discount > 0 ? $this->courseModel->getDiscountedPrice($course['price'], $discount) : $course['price'];

        // Marcar progresso das aulas
        $db = Database::getInstance();
        $completedLessons = [];
        if ($hasAccess) {
            $rows = $db->fetchAll(
                "SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND completed = 1",
                [$user['id']]
            );
            $completedLessons = array_column($rows, 'lesson_id');
        }

        include BASE_PATH_DIR . '/views/courses/show.php';
    }

    public function lesson(int $id): void {
        $user   = Auth::user();
        $lesson = $this->courseModel->getLessonById($id);
        if (!$lesson) {
            Auth::flash('danger', 'Aula não encontrada.');
            header('Location: ' . BASE_URL . '/courses');
            exit;
        }

        $course    = $this->courseModel->findById($lesson['course_id']);
        $hasAccess = $lesson['is_preview'] || $this->courseModel->userHasAccess($user['id'], $lesson['course_id']);

        if (!$hasAccess) {
            Auth::flash('warning', 'Você não tem acesso a esta aula. Adquira o curso primeiro.');
            header('Location: ' . BASE_URL . '/courses/' . $lesson['course_id']);
            exit;
        }

        $pageTitle   = $lesson['title'];
        $embed       = Course::buildEmbed($lesson['video_url'] ?? '', $lesson['video_type'] ?? 'youtube');
        $modules     = $this->courseModel->getModulesWithLessons($lesson['course_id']);
        $db          = Database::getInstance();
        $isCompleted = (bool)$db->fetchOne(
            "SELECT id FROM lesson_progress WHERE user_id = ? AND lesson_id = ? AND completed = 1",
            [$user['id'], $id]
        );

        include BASE_PATH_DIR . '/views/courses/lesson.php';
    }

    public function completeLesson(): void {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $lessonId = (int)($data['lesson_id'] ?? 0);
        $userId   = Auth::id();

        if (!$lessonId) { echo json_encode(['success'=>false]); exit; }

        $lesson  = $this->courseModel->getLessonById($lessonId);
        if (!$lesson) { echo json_encode(['success'=>false]); exit; }

        $done    = $this->courseModel->markLessonComplete($userId, $lessonId);
        $progress = $this->courseModel->getUserProgress($userId, $lesson['course_id']);

        // Verificar se completou o curso
        if ($progress['percent'] === 100) {
            $coursePoints = Setting::int('points_complete_course', 200);
            $this->userModel->addPoints($userId, $coursePoints, 'complete_course', 'Curso concluído: ' . ($lesson['module_title'] ?? ''), $lesson['course_id']);
            $this->userModel->createNotification($userId, 'Curso concluído! 🎓', 'Você ganhou ' . $coursePoints . ' pontos de bônus!', 'course');
        }

        echo json_encode([
            'success'      => $done,
            'points'       => $done ? $lesson['points_reward'] : 0,
            'progress_bar' => $progress['percent'],
        ]);
        exit;
    }

    public function buy(int $courseId): void {
        Auth::csrfCheck();
        $user   = Auth::user();
        $course = $this->courseModel->findById($courseId);

        if (!$course || $this->courseModel->userHasAccess($user['id'], $courseId)) {
            header('Location: ' . BASE_URL . '/courses/' . $courseId);
            exit;
        }

        $gateway = $_POST['gateway'] ?? '';
        $discount = $this->userModel->getCourseDiscount($user['id']);
        $price    = $this->courseModel->getDiscountedPrice($course['price'], $discount);

        if ($price <= 0 || $course['is_free']) {
            // Liberar grátis
            $this->courseModel->grantAccess($user['id'], $courseId, 'purchased', null, 0);
            Auth::flash('success', 'Acesso ao curso liberado!');
            header('Location: ' . BASE_URL . '/courses/' . $courseId);
            exit;
        }

        // Redirecionar para checkout com gateway
        $gw = new GatewayService();
        $order = [
            'amount'      => $price,
            'description' => 'Curso: ' . $course['title'],
            'reference'   => 'course_' . $courseId . '_' . $user['id'],
            'email'       => $user['email'],
            'name'        => $user['name'],
        ];

        try {
            if ($gateway === 'mercadopago')     $result = $gw->createMercadoPagoPayment($order);
            elseif ($gateway === 'asaas')        $result = $gw->createAsaasCharge($order);
            elseif ($gateway === 'efibank')      $result = $gw->createEfibankPix($order);
            elseif ($gateway === 'inter')        $result = $gw->createInterPix($order);
            else throw new Exception('Gateway invalido');
            $result = $result ?? array();

            // Salvar transação pendente
            $txId = $result['id'] ?? ($result['txid'] ?? uniqid());
            $gw->logTransaction($user['id'], 'course', $courseId, $gateway, (string)$txId, $price, 'pending', $result);

            // Armazenar dados do pagamento na sessão
            $_SESSION['pending_payment'] = [
                'type'      => 'course',
                'ref_id'    => $courseId,
                'gateway'   => $gateway,
                'tx_id'     => $txId,
                'amount'    => $price,
                'result'    => $result,
            ];

            $pageTitle = 'Finalizar pagamento';
            $pixCode   = $result['point_of_interaction']['transaction_data']['qr_code'] ?? ($result['pix']['qrcode'] ?? ($result['pixCopiaECola'] ?? ''));
            $pixImage  = $result['point_of_interaction']['transaction_data']['qr_code_base64'] ?? ($result['pix']['qrcode_image'] ?? '');

            include BASE_PATH_DIR . '/views/courses/checkout.php';
        } catch (Exception $e) {
            Auth::flash('danger', 'Erro ao processar pagamento: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/courses/' . $courseId);
        }
        exit;
    }

    public function buyWithPoints(int $courseId): void {
        Auth::csrfCheck();
        $user   = Auth::user();
        $course = $this->courseModel->findById($courseId);

        if (!$course || $this->courseModel->userHasAccess($user['id'], $courseId)) {
            header('Location: ' . BASE_URL . '/courses/' . $courseId);
            exit;
        }

        $pointsNeeded = (int)$course['points_price'];
        if ($pointsNeeded <= 0) {
            Auth::flash('danger', 'Este curso não pode ser adquirido com pontos.');
            header('Location: ' . BASE_URL . '/courses/' . $courseId);
            exit;
        }

        $currentUser = $this->userModel->findById($user['id']);
        if ($currentUser['points'] < $pointsNeeded) {
            Auth::flash('danger', "Pontos insuficientes. Você precisa de {$pointsNeeded} pontos.");
            header('Location: ' . BASE_URL . '/courses/' . $courseId);
            exit;
        }

        // Deduzir pontos e liberar acesso
        if ($this->userModel->spendPoints($user['id'], $pointsNeeded)) {
            $this->courseModel->grantAccess($user['id'], $courseId, 'points', null, 0, $pointsNeeded);

            // Notificação
            $ws = new WhatsellService();
            $ws->notifyPayment($user, $course['title']);
            $this->userModel->createNotification($user['id'], 'Curso desbloqueado!', "Você trocou {$pointsNeeded} pontos pelo curso: {$course['title']}", 'course');

            Auth::flash('success', "Curso desbloqueado com {$pointsNeeded} pontos! Bons estudos! 📚");
        } else {
            Auth::flash('danger', 'Erro ao processar troca de pontos.');
        }

        header('Location: ' . BASE_URL . '/courses/' . $courseId);
        exit;
    }
}
