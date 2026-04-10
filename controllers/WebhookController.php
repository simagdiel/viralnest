<?php
// controllers/WebhookController.php
// Recebe callbacks de pagamento dos gateways

class WebhookController {
    private Database $db;
    private User $userModel;
    private Course $courseModel;

    public function __construct() {
        $this->db          = Database::getInstance();
        $this->userModel   = new User();
        $this->courseModel = new Course();
    }

    public function handle(string $gateway): void {
        $payload = file_get_contents('php://input');
        $data    = json_decode($payload, true) ?? [];

        http_response_code(200);
        header('Content-Type: application/json');

        switch ($gateway) {
            case 'mercadopago': $this->handleMercadoPago($data); break;
            case 'asaas':       $this->handleAsaas($data);       break;
            case 'efibank':     $this->handleEfibank($data);     break;
            case 'inter':       $this->handleInter($data);       break;
            default: echo json_encode(['ignored' => true]);
        }
    }

    private function handleMercadoPago(array $data): void {
        if (($data['type'] ?? '') !== 'payment') { echo json_encode(['ok'=>true]); return; }
        $txId  = $data['data']['id'] ?? '';
        $this->confirmByTxId((string)$txId, 'mercadopago');
    }

    private function handleAsaas(array $data): void {
        if (!in_array($data['event'] ?? '', ['PAYMENT_CONFIRMED','PAYMENT_RECEIVED'])) { echo json_encode(['ok'=>true]); return; }
        $txId = $data['payment']['id'] ?? '';
        $this->confirmByTxId((string)$txId, 'asaas');
    }

    private function handleEfibank(array $data): void {
        foreach ($data['pix'] ?? [] as $pix) {
            $txId = $pix['endToEndId'] ?? $pix['txid'] ?? '';
            if ($txId) $this->confirmByTxId((string)$txId, 'efibank');
        }
        echo json_encode(['ok' => true]);
    }

    private function handleInter(array $data): void {
        $txId = $data['endToEndId'] ?? $data['idTransacao'] ?? '';
        if ($txId) $this->confirmByTxId((string)$txId, 'inter');
        echo json_encode(['ok' => true]);
    }

    private function confirmByTxId(string $txId, string $gateway): void {
        $tx = $this->db->fetchOne(
            "SELECT * FROM transactions WHERE gateway_tx_id = ? AND gateway = ? AND status != 'paid'",
            [$txId, $gateway]
        );

        if (!$tx) { echo json_encode(['ok'=>true,'msg'=>'tx not found or already paid']); return; }

        // Marcar como pago
        $this->db->update('transactions', ['status'=>'paid'], 'id = ?', [$tx['id']]);

        $user = $this->userModel->findById($tx['user_id']);
        if (!$user) { echo json_encode(['ok'=>false,'msg'=>'user not found']); return; }

        // Processar conforme tipo
        if ($tx['type'] === 'course') {
            $course = $this->courseModel->findById($tx['reference_id']);
            if ($course) {
                $this->courseModel->grantAccess($user['id'], $course['id'], 'purchased', $gateway, $tx['amount']);
                // Notificação WhatsApp
                $ws = new WhatsellService();
                $ws->notifyPayment($user, $course['title']);
                // Notificação interna
                $this->userModel->createNotification($user['id'], 'Curso liberado! 📚', "Acesso ao curso \"{$course['title']}\" confirmado.", 'payment');
            }
        } elseif ($tx['type'] === 'subscription') {
            $plan = $this->db->fetchOne("SELECT * FROM plans WHERE id = ?", [$tx['reference_id']]);
            if ($plan) {
                $billing = $plan['billing_cycle'];
                if ($billing === 'lifetime') {
                    $expires = null;
                } else {
                    if ($billing === 'quarterly') $period = '3 months';
                    elseif ($billing === 'annual') $period = '1 year';
                    else $period = '1 month';
                    $expires = date('Y-m-d H:i:s', strtotime('+' . $period));
                }
                // Cancelar assinaturas anteriores
                $this->db->query("UPDATE user_subscriptions SET status='cancelled' WHERE user_id=? AND status='active'", [$user['id']]);
                // Criar nova
                $this->db->insert('user_subscriptions', [
                    'user_id'    => $user['id'],
                    'plan_id'    => $plan['id'],
                    'gateway'    => $gateway,
                    'gateway_subscription_id' => $txId,
                    'status'     => 'active',
                    'price_paid' => $tx['amount'],
                    'started_at' => date('Y-m-d H:i:s'),
                    'expires_at' => $expires,
                ]);
                $ws = new WhatsellService();
                $ws->notifyPayment($user, 'Plano ' . $plan['name']);
                $this->userModel->createNotification($user['id'], 'Plano ativado! 💎', "Plano \"{$plan['name']}\" ativado com sucesso.", 'payment');
            }
        }

        echo json_encode(['ok' => true, 'processed' => $tx['type']]);
    }
}
