<?php
// views/courses/checkout.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page" style="max-width:540px;margin:0 auto;">
  <div class="page-header" style="text-align:center;">
    <h1 class="page-title">💳 Finalizar Pagamento</h1>
    <p class="page-subtitle">Quase lá! Realize o pagamento para liberar o acesso.</p>
  </div>

  <div class="card animate-in">
    <div style="text-align:center;margin-bottom:1.5rem;">
      <div style="font-size:2.5rem;margin-bottom:0.75rem;">📱</div>
      <div style="font-family:var(--font-head);font-weight:700;font-size:1.1rem;">PIX — R$ <?= number_format($_SESSION['pending_payment']['amount'] ?? 0, 2, ',', '.') ?></div>
      <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem;">Pagamento instantâneo via PIX</div>
    </div>

    <?php if (!empty($pixImage)): ?>
    <div style="text-align:center;margin-bottom:1.25rem;">
      <img src="data:image/png;base64,<?= $pixImage ?>" alt="QR Code PIX" style="width:200px;height:200px;border-radius:12px;border:4px solid var(--primary);">
    </div>
    <?php endif; ?>

    <?php if (!empty($pixCode)): ?>
    <div style="margin-bottom:1.25rem;">
      <div class="form-label">Código PIX Copia e Cola</div>
      <div class="invite-link">
        <div class="invite-url" id="pixCode"><?= htmlspecialchars($pixCode) ?></div>
        <button class="btn btn-primary btn-sm" data-copy="#pixCode">📋 Copiar</button>
      </div>
    </div>
    <?php endif; ?>

    <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.15);border-radius:var(--radius-sm);padding:1rem;font-size:0.85rem;color:var(--text-muted);text-align:center;">
      ⏳ Após o pagamento, seu acesso será liberado automaticamente em até 1 minuto.
    </div>

    <div style="margin-top:1.25rem;display:flex;gap:0.75rem;">
      <a href="<?= BASE_URL ?>/courses" class="btn btn-secondary" style="flex:1;text-align:center;">← Voltar</a>
      <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary" style="flex:1;text-align:center;">Dashboard</a>
    </div>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
