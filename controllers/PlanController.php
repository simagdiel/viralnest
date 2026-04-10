<?php
// controllers/PlanController.php

class PlanController {
    public function index(): void {
        $user = Auth::user();
        $db   = Database::getInstance();
        $pageTitle  = 'Planos';
        $plans      = $db->fetchAll("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order, price");
        $activePlan = (new User())->getActivePlan($user['id']);
        $gw         = new GatewayService();
        $activeGateways = $gw->getActiveGateways();
        include BASE_PATH_DIR . '/views/plans/index.php';
    }

    public function subscribe(int $planId): void {
        Auth::csrfCheck();
        $user = Auth::user();
        $db   = Database::getInstance();
        $plan = $db->fetchOne("SELECT * FROM plans WHERE id = ? AND is_active = 1", [$planId]);

        if (!$plan) {
            Auth::flash('danger', 'Plano não encontrado.');
            header('Location: ' . BASE_URL . '/plans');
            exit;
        }

        if ($plan['price'] <= 0) {
            // Plano gratuito
            $db->insert('user_subscriptions', [
                'user_id'    => $user['id'],
                'plan_id'    => $planId,
                'gateway'    => 'free',
                'status'     => 'active',
                'price_paid' => 0,
                'started_at' => date('Y-m-d H:i:s'),
            ]);
            Auth::flash('success', 'Plano ativado!');
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $gateway = $_POST['gateway'] ?? '';
        $gw      = new GatewayService();
        $order   = [
            'amount'      => $plan['price'],
            'description' => 'Plano: ' . $plan['name'],
            'reference'   => 'plan_' . $planId . '_' . $user['id'],
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
            $txId = $result['id'] ?? ($result['txid'] ?? uniqid());
            $gw->logTransaction($user['id'], 'subscription', $planId, $gateway, (string)$txId, $plan['price'], 'pending', $result);

            $_SESSION['pending_payment'] = [
                'type'   => 'plan',
                'ref_id' => $planId,
                'gateway'=> $gateway,
                'tx_id'  => $txId,
                'amount' => $plan['price'],
                'result' => $result,
            ];

            $pageTitle = 'Assinar ' . $plan['name'];
            $pixCode   = $result['point_of_interaction']['transaction_data']['qr_code'] ?? ($result['pix']['qrcode'] ?? ($result['pixCopiaECola'] ?? ''));
            $pixImage  = $result['point_of_interaction']['transaction_data']['qr_code_base64'] ?? ($result['pix']['qrcode_image'] ?? '');
            $productName = $plan['name'];
            include BASE_PATH_DIR . '/views/plans/checkout.php';
        } catch (Exception $e) {
            Auth::flash('danger', 'Erro: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/plans');
        }
        exit;
    }
}
