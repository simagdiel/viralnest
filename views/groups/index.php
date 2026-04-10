<?php
// views/groups/index.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page">
  <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;">
    <div>
      <h1 class="page-title">👥 Grupos</h1>
      <p class="page-subtitle">Conecte-se com outros membros da comunidade</p>
    </div>
    <?php if ($canCreate && $myGroupCount < $maxGroups): ?>
      <a href="<?= BASE_URL ?>/groups/create" class="btn btn-primary">+ Criar grupo</a>
    <?php endif; ?>
  </div>

  <?php if (!$canCreate): ?>
  <div class="alert alert-warning" style="margin-bottom:1.5rem;">
    🔒 Para criar um grupo você precisa de <strong><?= number_format($minPoints) ?> pontos</strong>. Você tem <?= number_format($user['points']) ?>.
  </div>
  <?php endif; ?>

  <?php if (!empty($myGroups)): ?>
  <div class="card" style="margin-bottom:1.5rem;">
    <div class="card-title">Meus grupos</div>
    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;">
      <?php foreach ($myGroups as $g): ?>
        <a href="<?= BASE_URL ?>/groups/<?= $g['id'] ?>" style="padding:0.5rem 1rem;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);border-radius:99px;color:var(--primary);font-size:0.85rem;font-weight:600;text-decoration:none;">
          👥 <?= htmlspecialchars($g['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="grid-2">
    <?php if (empty($groups)): ?>
      <div class="card" style="text-align:center;padding:3rem;grid-column:1/-1;">
        <div style="font-size:3rem;margin-bottom:1rem;">👥</div>
        <h3 style="font-family:var(--font-head);">Nenhum grupo ainda</h3>
        <p style="color:var(--text-muted);margin-top:0.5rem;">Seja o primeiro a criar um grupo!</p>
      </div>
    <?php else: ?>
    <?php foreach ($groups as $g): ?>
    <div class="card animate-in">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
        <div>
          <div style="font-family:var(--font-head);font-weight:700;font-size:1rem;"><?= htmlspecialchars($g['name']) ?></div>
          <div style="color:var(--text-muted);font-size:0.8rem;">Líder: <?= htmlspecialchars($g['leader_name']) ?></div>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:center;">
          <?php if ($g['is_private']): ?>
            <span class="badge badge-warning">🔒 Privado</span>
          <?php endif; ?>
          <span class="badge badge-muted">👥 <?= $g['member_count'] ?></span>
        </div>
      </div>

      <?php if ($g['description']): ?>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1rem;line-height:1.6;"><?= htmlspecialchars(mb_substr($g['description'], 0, 120)) ?>...</p>
      <?php endif; ?>

      <div style="display:flex;gap:0.75rem;">
        <a href="<?= BASE_URL ?>/groups/<?= $g['id'] ?>" class="btn btn-secondary btn-sm">Ver grupo</a>
        <?php if (!in_array($g['id'], array_column($myGroups, 'id'))): ?>
        <form method="POST" action="<?= BASE_URL ?>/groups/<?= $g['id'] ?>/join">
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <button type="submit" class="btn btn-primary btn-sm">+ Entrar</button>
        </form>
        <?php else: ?>
          <span class="badge badge-success" style="align-self:center;">✓ Membro</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
