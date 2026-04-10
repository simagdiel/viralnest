<?php
// core/WhatsellService.php

class WhatsellService {
    private string $endpoint;
    private string $token;
    private bool $enabled;
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->enabled  = Setting::bool('whatsell_enabled');
        $this->token    = Setting::get('whatsell_token', '');
        $this->endpoint = Setting::get('whatsell_endpoint', 'https://api.whatsell.online/api/messages/send');
    }

    public function send(string $phone, string $message, int $userId = null, string $eventType = 'system'): bool {
        $phone = preg_replace('/\D/', '', $phone);
        if (!$this->enabled || empty($this->token) || empty($phone)) {
            $this->log($userId, $phone, $message, $eventType, 'failed', 'Whatsell desabilitado ou sem token');
            return false;
        }

        $payload = json_encode(['number' => $phone, 'body' => $message]);

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $success = ($httpCode >= 200 && $httpCode < 300);
        $this->log($userId, $phone, $message, $eventType, $success ? 'sent' : 'failed', $response);
        return $success;
    }

    // Notificação: boas-vindas no cadastro
    public function notifyRegister(array $user): void {
        if (!Setting::bool('whatsell_notify_register') || empty($user['phone'])) return;
        $msg = Setting::parseTemplate(Setting::get('whatsell_msg_register', 'Bem-vindo à {site_name}!'), [
            'name' => $user['name'],
        ]);
        $this->send($user['phone'], $msg, $user['id'], 'register');
    }

    // Notificação: subida de nível
    public function notifyLevelUp(array $user, string $newLevel): void {
        if (!Setting::bool('whatsell_notify_level_up') || empty($user['phone'])) return;
        $msg = Setting::parseTemplate(Setting::get('whatsell_msg_level_up', 'Você subiu para {level}!'), [
            'name'  => $user['name'],
            'level' => ucfirst($newLevel),
        ]);
        $this->send($user['phone'], $msg, $user['id'], 'level_up');
    }

    // Notificação: convite usado
    public function notifyInviteUsed(array $owner, array $invited, int $points): void {
        if (!Setting::bool('whatsell_notify_invite') || empty($owner['phone'])) return;
        $msg = Setting::parseTemplate(Setting::get('whatsell_msg_invite', '{invited} entrou pela sua indicação! +{points} pontos!'), [
            'name'    => $owner['name'],
            'invited' => $invited['name'],
            'points'  => $points,
        ]);
        $this->send($owner['phone'], $msg, $owner['id'], 'invite_used');
    }

    // Notificação: pagamento confirmado
    public function notifyPayment(array $user, string $productName): void {
        if (!Setting::bool('whatsell_notify_payment') || empty($user['phone'])) return;
        $msg = Setting::parseTemplate(Setting::get('whatsell_msg_payment', 'Pagamento de {product} confirmado!'), [
            'name'    => $user['name'],
            'product' => $productName,
        ]);
        $this->send($user['phone'], $msg, $user['id'], 'payment');
    }

    // Mensagem personalizada por admin
    public function sendCustom(array $user, string $message): bool {
        if (empty($user['phone'])) return false;
        return $this->send($user['phone'], $message, $user['id'], 'custom');
    }

    private function log(int $userId = null, string $phone, string $message, string $event, string $status, string $response = ''): void {
        $this->db->insert('whatsapp_logs', [
            'user_id'    => $userId,
            'phone'      => $phone,
            'message'    => $message,
            'event_type' => $event,
            'status'     => $status,
            'response'   => $response,
        ]);
    }
}
