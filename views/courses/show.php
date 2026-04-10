<?php
// views/courses/show.php
include BASE_PATH_DIR . '/views/layout/header.php';
$gw = new GatewayService();
$activeGateways = $gw->getActiveGateways();
?>
<div class="page">
  <!-- Breadcrumb -->
  <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1.25rem;">
    <a href="<?= BASE_URL ?>/courses" style="color:var(--text-muted);">Cursos</a> › <?= htmlspecialchars($course['title']) ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">
    <!-- Conteúdo -->
    <div>
      <h1 class="page-title" style="font-size:1.8rem;"><?= htmlspecialchars($course['title']) ?></h1>
      <?php if ($course['instructor']): ?>
        <div style="color:var(--text-muted);margin-top:0.35rem;">👤 <?= htmlspecialchars($course['instructor']) ?></div>
      <?php endif; ?>

      <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin:1rem 0;">
        <span class="level-badge level-<?= $course['level_required'] ?>">Nível <?= ucfirst($course['level_required']) ?>+</span>
        <span class="badge badge-muted">📦 <?= $course['module_count'] ?> módulos</span>
        <span class="badge badge-muted">🎥 <?= $course['lesson_count'] ?> aulas</span>
      </div>

      <?php if ($hasAccess): ?>
      <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-title">Seu progresso</div>
        <div class="progress-bar-wrap" style="height:12px;">
          <div class="progress-bar-fill course-progress-fill" data-width="<?= $progress['percent'] ?>" style="width:0%"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.82rem;color:var(--text-muted);margin-top:0.5rem;">
          <span><?= $progress['completed'] ?>/<?= $progress['total'] ?> aulas</span>
          <span><?= $progress['percent'] ?>% concluído</span>
        </div>
      </div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-title">Descrição</div>
        <p style="color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($course['description'] ?? '')) ?></p>
      </div>

      <!-- Módulos e aulas -->
      <div class="card">
        <div class="card-title">Conteúdo do curso</div>
        <?php foreach ($modules as $mod): ?>
        <div style="margin-bottom:1rem;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;">
          <div style="background:var(--surface-2);padding:0.75rem 1rem;font-weight:600;display:flex;justify-content:space-between;">
            <span>📦 <?= htmlspecialchars($mod['title']) ?></span>
            <span style="color:var(--text-muted);font-size:0.82rem;"><?= count($mod['lessons']) ?> aulas</span>
          </div>
          <?php foreach ($mod['lessons'] as $ls): ?>
          <div style="padding:0.65rem 1rem;border-top:1px solid var(--border);display:flex;align-items:center;gap:0.75rem;">
            <?php $done = in_array($ls['id'], $completedLessons); ?>
            <span style="width:22px;height:22px;border-radius:50%;background:<?= $done ? 'rgba(34,197,94,0.2)' : 'var(--surface-3)' ?>;display:grid;place-items:center;flex-shrink:0;font-size:0.75rem;">
              <?= $done ? '✓' : ($ls['is_preview'] ? '👁' : '🔒') ?>
            </span>
            <span style="flex:1;font-size:0.88rem;<?= $done ? 'color:var(--text-muted);text-decoration:line-through;' : '' ?>"><?= htmlspecialchars($ls['title']) ?></span>
            <?php if ($ls['duration_minutes']): ?>
              <span style="font-size:0.75rem;color:var(--text-muted);"><?= $ls['duration_minutes'] ?>min</span>
            <?php endif; ?>
            <?php if ($hasAccess || $ls['is_preview']): ?>
              <a href="<?= BASE_URL ?>/lessons/<?= $ls['id'] ?>" class="btn btn-secondary btn-sm">▶</a>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Sidebar de compra -->
    <div style="position:sticky;top:calc(var(--topbar-h) + 1rem);">
      <div class="card">
        <?php if ($course['thumbnail']): ?>
          <img src="<?= htmlspecialchars($course['thumbnail']) ?>" style="width:100%;border-radius:var(--radius-sm);margin-bottom:1rem;max-height:160px;object-fit:cover;" alt="">
        <?php endif; ?>

        <?php if ($hasAccess): ?>
          <div class="badge badge-success" style="font-size:0.9rem;padding:0.5rem 1rem;width:100%;text-align:center;margin-bottom:1rem;">✓ Você tem acesso</div>
          <?php if (!empty($modules[0]['lessons'][0])): ?>
            <a href="<?= BASE_URL ?>/lessons/<?= $modules[0]['lessons'][0]['id'] ?>" class="btn btn-primary btn-block btn-lg">
              ▶ Continuar estudando
            </a>
          <?php endif; ?>

        <?php elseif ($course['is_free']): ?>
          <form method="POST" action="<?= BASE_URL ?>/courses/<?= $course['id'] ?>/buy">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="gateway" value="free">
            <button type="submit" class="btn btn-success btn-block btn-lg">🎁 Acessar gratuitamente</button>
          </form>

        <?php else: ?>
          <div style="margin-bottom:1.25rem;">
            <?php if ($discount > 0): ?>
              <div style="text-decoration:line-through;color:var(--text-muted);font-size:0.9rem;">R$ <?= number_format($course['price'], 2, ',', '.') ?></div>
              <div style="font-family:var(--font-head);font-size:2rem;font-weight:800;color:var(--primary);">R$ <?= number_format($finalPrice, 2, ',', '.') ?></div>
              <div class="badge badge-success"><?= number_format($discount, 0) ?>% de desconto pelo seu nível/plano</div>
            <?php else: ?>
              <div style="font-family:var(--font-head);font-size:2rem;font-weight:800;color:var(--primary);">R$ <?= number_format($course['price'], 2, ',', '.') ?></div>
            <?php endif; ?>
          </div>

          <?php if (!empty($activeGateways)): ?>
          <form method="POST" action="<?= BASE_URL ?>/courses/<?= $course['id'] ?>/buy">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <div class="form-group">
              <label class="form-label">Forma de pagamento</label>
              <select name="gateway" class="form-control" required>
                <?php foreach ($activeGateways as $gwy): ?>
                  <option value="<?= $gwy['gateway'] ?>"><?= ucfirst($gwy['gateway']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Comprar agora →</button>
          </form>
          <?php endif; ?>

          <?php if ($course['points_price'] > 0): ?>
          <hr class="divider">
          <div style="text-align:center;">
            <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:0.75rem;">
              Ou use seus pontos (<?= number_format($user['points']) ?> disponíveis)
            </p>
            <?php if ($user['points'] >= $course['points_price']): ?>
            <form method="POST" action="<?= BASE_URL ?>/courses/<?= $course['id'] ?>/buy-points">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <button type="submit" class="btn btn-secondary btn-block" onclick="return confirm('Trocar <?= $course['points_price'] ?> pontos por este curso?')">
                ⭐ Trocar <?= number_format($course['points_price']) ?> pontos
              </button>
            </form>
            <?php else: ?>
              <div style="color:var(--text-muted);font-size:0.82rem;">Você precisa de <?= number_format($course['points_price']) ?> pontos (faltam <?= number_format($course['points_price'] - $user['points']) ?>)</div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
