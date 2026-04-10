<?php // admin/views/gateways.php
$gatewayInfo = [
    'mercadopago' => ['name'=>'Mercado Pago',  'icon'=>'💳', 'desc'=>'PIX, Cartão de crédito, Boleto'],
    'asaas'       => ['name'=>'Asaas',          'icon'=>'🏦', 'desc'=>'PIX, Boleto, Cartão de crédito'],
    'efibank'     => ['name'=>'EfiBank',         'icon'=>'💚', 'desc'=>'PIX Efí, Boleto bancário'],
    'inter'       => ['name'=>'Banco Inter',     'icon'=>'🧡', 'desc'=>'PIX Inter, cobrança bancária'],
];
?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:1.5rem;">
  <?php foreach ($gateways as $gw):
    $info   = $gatewayInfo[$gw['gateway']] ?? ['name'=>$gw['gateway'],'icon'=>'💰','desc'=>''];
    $fields = GatewayService::getCredentialFields($gw['gateway']);
    $savedCreds = [];
    try {
      $gwSvc = new GatewayService();
      $savedCreds = $gwSvc->getDecryptedCredentials($gw['gateway']);
    } catch(Exception $e) {}
  ?>
  <div class="a-card gateway-card <?= $gw['is_active'] ? 'enabled' : '' ?>">
    <div class="gateway-header">
      <span class="gateway-logo"><?= $info['icon'] ?></span>
      <div>
        <div class="gateway-name"><?= $info['name'] ?></div>
        <div style="font-size:0.75rem;color:var(--text-muted);"><?= $info['desc'] ?></div>
      </div>
      <div class="gateway-status-dot <?= $gw['is_active'] ? 'on' : 'off' ?>"></div>
    </div>

    <div class="gateway-body">
      <form method="POST" action="<?= BASE_URL ?>/admin/gateways/save">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="gateway" value="<?= $gw['gateway'] ?>">

        <div class="gateway-toggle">
          <label class="toggle-switch">
            <input type="checkbox" name="is_active" <?= $gw['is_active'] ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
          <span style="font-size:0.85rem;font-weight:500;">Ativo</span>
          <label style="margin-left:auto;display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:var(--text-muted);">
            <input type="checkbox" name="sandbox" <?= $gw['sandbox_mode'] ? 'checked' : '' ?>> Sandbox
          </label>
        </div>

        <div class="gateway-fields">
          <?php foreach ($fields as $field): ?>
          <div class="a-form-group" style="margin-bottom:0.65rem;">
            <label class="a-form-label"><?= htmlspecialchars($field['label']) ?></label>
            <input type="<?= $field['type'] ?>"
                   name="creds[<?= $field['key'] ?>]"
                   class="a-form-control"
                   value="<?= $field['type'] === 'password' ? (empty($savedCreds[$field['key']]) ? '' : '••••••••') : htmlspecialchars($savedCreds[$field['key']] ?? '') ?>"
                   placeholder="<?= htmlspecialchars($field['help']) ?>">
          </div>
          <?php endforeach; ?>
        </div>

        <button type="submit" class="a-btn a-btn-primary" style="width:100%;margin-top:0.75rem;">
          💾 Salvar <?= $info['name'] ?>
        </button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="a-card" style="margin-top:1.5rem;">
  <div class="a-card-title">ℹ️ Sobre os gateways</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;font-size:0.85rem;color:var(--text-muted);">
    <div><strong style="color:var(--text);">Mercado Pago:</strong> Crie uma aplicação em <a href="https://www.mercadopago.com.br/developers" target="_blank" style="color:var(--primary);">developers.mercadopago.com</a> e copie o Access Token.</div>
    <div><strong style="color:var(--text);">Asaas:</strong> Acesse <a href="https://www.asaas.com" target="_blank" style="color:var(--primary);">asaas.com</a>, vá em Configurações → Integrações e copie a API Key.</div>
    <div><strong style="color:var(--text);">EfiBank:</strong> Crie uma aplicação no <a href="https://dev.gerencianet.com.br" target="_blank" style="color:var(--primary);">Portal EfiBank</a> com escopo PIX e faça upload do certificado .pem no servidor.</div>
    <div><strong style="color:var(--text);">Banco Inter:</strong> Acesse o portal Developer do Inter, crie uma aplicação OAuth2 e faça upload dos certificados mTLS no servidor.</div>
  </div>
  <div style="margin-top:0.75rem;padding:0.75rem;background:rgba(245,158,11,0.06);border-radius:8px;font-size:0.82rem;color:var(--text-muted);">
    ⚠️ As credenciais são criptografadas com AES-256-CBC antes de serem salvas no banco. Mantenha seu <code>ENCRYPTION_KEY</code> no config.php seguro.
  </div>
</div>
