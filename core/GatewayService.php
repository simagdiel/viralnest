<?php
// core/GatewayService.php

class GatewayService {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getGateway(string $name): ?array {
        return $this->db->fetchOne("SELECT * FROM gateway_settings WHERE gateway = ?", [$name]);
    }

    public function getAllGateways(): array {
        return $this->db->fetchAll("SELECT * FROM gateway_settings ORDER BY gateway");
    }

    public function getActiveGateways(): array {
        return $this->db->fetchAll("SELECT * FROM gateway_settings WHERE is_active = 1");
    }

    public function getCredentials(string $gateway): array {
        $row = $this->getGateway($gateway);
        if (!$row) return [];
        $creds = json_decode($row['credentials'] ?? '{}', true);
        return $creds ?: [];
    }

    public function saveCredentials(string $gateway, array $credentials, bool $isActive, bool $sandbox): void {
        // Criptografar credenciais
        $encrypted = $this->encryptCredentials(json_encode($credentials));
        $this->db->update('gateway_settings', [
            'credentials'  => $encrypted,
            'is_active'    => $isActive ? 1 : 0,
            'sandbox_mode' => $sandbox ? 1 : 0,
        ], 'gateway = ?', [$gateway]);
    }

    private function encryptCredentials(string $data): string {
        $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default_key_change_me';
        $iv  = openssl_random_pseudo_bytes(16);
        $enc = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $enc);
    }

    private function decryptCredentials(string $data): string {
        $key     = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default_key_change_me';
        $decoded = base64_decode($data);
        $iv      = substr($decoded, 0, 16);
        $enc     = substr($decoded, 16);
        return openssl_decrypt($enc, 'AES-256-CBC', $key, 0, $iv);
    }

    public function getDecryptedCredentials(string $gateway): array {
        $row = $this->getGateway($gateway);
        if (!$row || empty($row['credentials'])) return [];
        try {
            $decrypted = $this->decryptCredentials($row['credentials']);
            return json_decode($decrypted, true) ?: [];
        } catch (Exception $e) {
            // Tentar JSON puro (migração)
            return json_decode($row['credentials'], true) ?: [];
        }
    }

    // ---- MERCADO PAGO ----
    public function createMercadoPagoPayment(array $order): array {
        $creds = $this->getDecryptedCredentials('mercadopago');
        $row   = $this->getGateway('mercadopago');
        $token = $creds['access_token'] ?? '';
        $isSandbox = $row['sandbox_mode'] ?? 1;

        $payload = [
            'transaction_amount' => (float)$order['amount'],
            'description'        => $order['description'],
            'payment_method_id'  => 'pix',
            'payer'              => ['email' => $order['email']],
            'external_reference' => $order['reference'],
        ];

        $response = $this->httpPost(
            'https://api.mercadopago.com/v1/payments',
            $payload,
            ['Authorization: Bearer ' . $token]
        );
        return $response;
    }

    // ---- ASAAS ----
    public function createAsaasCharge(array $order): array {
        $creds   = $this->getDecryptedCredentials('asaas');
        $row     = $this->getGateway('asaas');
        $apiKey  = $creds['api_key'] ?? '';
        $sandbox = $row['sandbox_mode'] ?? 1;
        $baseUrl = $sandbox ? 'https://sandbox.asaas.com/api/v3' : 'https://api.asaas.com/api/v3';

        // Criar/buscar cliente
        $customer = $this->httpPost($baseUrl . '/customers', [
            'name'  => $order['name'],
            'email' => $order['email'],
            'cpfCnpj' => $order['cpf'] ?? '',
        ], ['access_token: ' . $apiKey]);

        $customerId = $customer['id'] ?? '';

        $charge = $this->httpPost($baseUrl . '/payments', [
            'customer'    => $customerId,
            'billingType' => 'PIX',
            'value'       => (float)$order['amount'],
            'dueDate'     => date('Y-m-d'),
            'description' => $order['description'],
            'externalReference' => $order['reference'],
        ], ['access_token: ' . $apiKey]);

        return $charge;
    }

    // ---- EFIBANK ----
    public function createEfibankPix(array $order): array {
        $creds       = $this->getDecryptedCredentials('efibank');
        $row         = $this->getGateway('efibank');
        $clientId    = $creds['client_id'] ?? '';
        $clientSecret= $creds['client_secret'] ?? '';
        $sandbox     = $row['sandbox_mode'] ?? 1;

        $baseUrl = $sandbox
            ? 'https://pix-h.api.efipay.com.br'
            : 'https://pix.api.efipay.com.br';

        // Autenticar
        $auth = $this->httpPost($baseUrl . '/oauth/token',
            ['grant_type' => 'client_credentials'],
            ['Authorization: Basic ' . base64_encode("$clientId:$clientSecret")]
        );
        $token = $auth['access_token'] ?? '';

        // Criar cobrança imediata
        $txid  = uniqid('vn');
        $cob   = $this->httpPut($baseUrl . '/v2/cob/' . $txid, [
            'calendario' => ['expiracao' => 3600],
            'valor'      => ['original' => number_format($order['amount'], 2, '.', '')],
            'chave'      => $creds['pix_key'] ?? '',
            'infoAdicionais' => [['nome' => 'Produto', 'valor' => $order['description']]],
        ], ['Authorization: Bearer ' . $token]);

        return $cob;
    }

    // ---- INTER ----
    public function createInterPix(array $order): array {
        $creds      = $this->getDecryptedCredentials('inter');
        $clientId   = $creds['client_id'] ?? '';
        $clientSecret = $creds['client_secret'] ?? '';
        $certPath   = $creds['cert_path'] ?? '';
        $keyPath    = $creds['key_path'] ?? '';

        // Autenticar OAuth2 (Inter usa mTLS)
        $auth = $this->httpPost(
            'https://cdpj.partners.bancointer.com.br/oauth/v2/token',
            ['grant_type' => 'client_credentials', 'scope' => 'pagamento-pix.write'],
            ['Authorization: Basic ' . base64_encode("$clientId:$clientSecret")],
            $certPath, $keyPath
        );
        $token = $auth['access_token'] ?? '';

        $pix = $this->httpPost(
            'https://cdpj.partners.bancointer.com.br/banking/v2/pix',
            [
                'valor'     => number_format($order['amount'], 2, '.', ''),
                'descricao' => $order['description'],
            ],
            ['Authorization: Bearer ' . $token],
            $certPath, $keyPath
        );
        return $pix;
    }

    // ---- Utilitários HTTP ----
    private function httpPost(string $url, array $data, array $headers = [], string $cert = '', string $key = ''): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if ($cert && $key) {
            curl_setopt($ch, CURLOPT_SSLCERT, $cert);
            curl_setopt($ch, CURLOPT_SSLKEY, $key);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true) ?: [];
    }

    private function httpPut(string $url, array $data, array $headers = []): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_TIMEOUT        => 30,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true) ?: [];
    }

    // Salvar transação
    public function logTransaction(int $userId, string $type, int $refId, string $gateway, string $txId, float $amount, string $status, array $payload = []): int {
        return $this->db->insert('transactions', [
            'user_id'       => $userId,
            'type'          => $type,
            'reference_id'  => $refId,
            'gateway'       => $gateway,
            'gateway_tx_id' => $txId,
            'amount'        => $amount,
            'status'        => $status,
            'payload'       => json_encode($payload),
        ]);
    }

    // Definição dos campos de credenciais por gateway
    public static function getCredentialFields(string $gateway): array {
        if ($gateway === 'mercadopago') {
            return array(
                array('key' => 'access_token',   'label' => 'Access Token',          'type' => 'text',     'help' => 'Token de acesso da sua conta Mercado Pago'),
                array('key' => 'public_key',     'label' => 'Public Key',             'type' => 'text',     'help' => 'Chave publica para frontend'),
                array('key' => 'webhook_secret', 'label' => 'Webhook Secret',         'type' => 'password', 'help' => 'Secret para validar webhooks'),
            );
        }
        if ($gateway === 'asaas') {
            return array(
                array('key' => 'api_key',        'label' => 'API Key',                'type' => 'password', 'help' => 'Chave de API do Asaas'),
                array('key' => 'wallet_id',      'label' => 'Wallet ID',              'type' => 'text',     'help' => 'ID da sua carteira Asaas'),
            );
        }
        if ($gateway === 'efibank') {
            return array(
                array('key' => 'client_id',      'label' => 'Client ID',              'type' => 'text',     'help' => 'Client ID da aplicacao EfiBank'),
                array('key' => 'client_secret',  'label' => 'Client Secret',          'type' => 'password', 'help' => 'Client Secret da aplicacao EfiBank'),
                array('key' => 'pix_key',        'label' => 'Chave PIX',              'type' => 'text',     'help' => 'Sua chave PIX cadastrada no EfiBank'),
                array('key' => 'cert_path',      'label' => 'Caminho do Certificado', 'type' => 'text',     'help' => 'Caminho absoluto do .pem no servidor'),
            );
        }
        if ($gateway === 'inter') {
            return array(
                array('key' => 'client_id',      'label' => 'Client ID',              'type' => 'text',     'help' => 'Client ID OAuth2 do Banco Inter'),
                array('key' => 'client_secret',  'label' => 'Client Secret',          'type' => 'password', 'help' => 'Client Secret OAuth2 do Banco Inter'),
                array('key' => 'cert_path',      'label' => 'Caminho do Certificado', 'type' => 'text',     'help' => 'Caminho do certificado .crt no servidor'),
                array('key' => 'key_path',       'label' => 'Caminho da Chave',       'type' => 'text',     'help' => 'Caminho da chave privada .key no servidor'),
                array('key' => 'pix_key',        'label' => 'Chave PIX',              'type' => 'text',     'help' => 'Chave PIX do Banco Inter'),
            );
        }
        return array();
    }
}
