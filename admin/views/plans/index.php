<?php // admin/views/plans/index.php ?>
<div class="a-flex-between a-mb-2">
  <div></div>
  <button onclick="document.getElementById('newPlanModal').style.display='flex'" class="a-btn a-btn-primary">+ Novo Plano</button>
</div>

<div class="a-card">
  <table class="a-table">
    <thead><tr><th>Nome</th><th>Preço</th><th>Ciclo</th><th>Desconto cursos</th><th>Mult. pontos</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($plans as $p): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:0.6rem;">
            <div style="width:12px;height:12px;border-radius:50%;background:<?= htmlspecialchars($p['badge_color']) ?>;"></div>
            <strong><?= htmlspecialchars($p['name']) ?></strong>
          </div>
        </td>
        <td><?= $p['price']>0 ? 'R$ '.number_format($p['price'],2,',','.') : '<span class="a-badge a-badge-success">Grátis</span>' ?></td>
        <td style="color:var(--text-muted);font-size:0.82rem;"><?= ucfirst($p['billing_cycle']) ?></td>
        <td><?= $p['course_discount']>0 ? $p['course_discount'].'%' : '—' ?></td>
        <td><?= $p['points_multiplier'] ?>x</td>
        <td><span class="a-badge <?= $p['is_active']?'a-badge-success':'a-badge-danger' ?>"><?= $p['is_active']?'Ativo':'Inativo' ?></span></td>
        <td>
          <div style="display:flex;gap:0.35rem;">
            <a href="<?= BASE_URL ?>/admin/plans/edit/<?= $p['id'] ?>" class="a-btn a-btn-secondary a-btn-sm">✏️</a>
            <form method="POST" action="<?= BASE_URL ?>/admin/plans/delete/<?= $p['id'] ?>" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <button type="submit" class="a-btn a-btn-danger a-btn-sm" data-confirm="Remover plano?">🗑</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal novo plano -->
<div id="newPlanModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:999;align-items:center;justify-content:center;padding:1rem;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:1.5rem;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;">
    <div class="a-flex-between" style="margin-bottom:1.25rem;">
      <div style="font-family:var(--font-head);font-weight:700;">Novo Plano</div>
      <button onclick="document.getElementById('newPlanModal').style.display='none'" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.2rem;">✕</button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/plans/store">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <?php include __DIR__ . '/form_fields.php'; ?>
      <button type="submit" class="a-btn a-btn-primary" style="width:100%;">Criar plano</button>
    </form>
  </div>
</div>
