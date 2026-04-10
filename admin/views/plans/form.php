<?php // admin/views/plans/form.php
$p = $plan ?? null;
?>
<div style="max-width:600px;">
  <div class="a-flex-between a-mb-2">
    <div></div>
    <a href="<?= BASE_URL ?>/admin/plans" class="a-btn a-btn-secondary a-btn-sm">← Voltar</a>
  </div>
  <div class="a-card">
    <div class="a-card-title">Editar: <?= htmlspecialchars($p['name']??'') ?></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/plans/store">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <input type="hidden" name="plan_id" value="<?= $p['id']??'' ?>">
      <?php include __DIR__ . '/form_fields.php'; ?>
      <div style="display:flex;gap:0.75rem;margin-top:1rem;">
        <button type="submit" class="a-btn a-btn-primary">💾 Salvar</button>
        <a href="<?= BASE_URL ?>/admin/plans" class="a-btn a-btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
