<?php
// views/courses/index.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page">
  <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;">
    <div>
      <h1 class="page-title">📚 Cursos</h1>
      <p class="page-subtitle">Aprenda, evolua e ganhe pontos por cada aula concluída</p>
    </div>
    <?php if ($discount > 0): ?>
    <div class="badge badge-success" style="font-size:0.9rem;padding:0.4rem 1rem;">
      🎉 Seu desconto: <?= number_format($discount, 0) ?>% OFF
    </div>
    <?php endif; ?>
  </div>

  <?php if (empty($courses)): ?>
    <div class="card" style="text-align:center;padding:3rem;">
      <div style="font-size:3rem;margin-bottom:1rem;">📚</div>
      <h3 style="font-family:var(--font-head);">Nenhum curso disponível ainda</h3>
      <p style="color:var(--text-muted);margin-top:0.5rem;">Volte em breve!</p>
    </div>
  <?php else: ?>
  <div class="course-grid">
    <?php foreach ($courses as $c): ?>
    <div class="course-card animate-in">
      <?php if ($c['thumbnail']): ?>
        <img src="<?= htmlspecialchars($c['thumbnail']) ?>" class="course-thumb" alt="<?= htmlspecialchars($c['title']) ?>">
      <?php else: ?>
        <div class="course-thumb-placeholder">📚</div>
      <?php endif; ?>

      <div class="course-body">
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.5rem;">
          <span class="level-badge level-<?= $c['level_required'] ?>"><?= ucfirst($c['level_required']) ?>+</span>
          <?php if ($c['is_free']): ?>
            <span class="badge badge-success">Gratuito</span>
          <?php endif; ?>
          <?php if ($c['has_access']): ?>
            <span class="badge badge-info">✓ Acesso liberado</span>
          <?php endif; ?>
        </div>

        <div class="course-title"><?= htmlspecialchars($c['title']) ?></div>
        <div class="course-meta">
          <?= htmlspecialchars(mb_substr($c['description'] ?? '', 0, 100)) ?>...
          <div style="margin-top:0.5rem;font-size:0.78rem;">
            📦 <?= $c['module_count'] ?> módulos · 🎥 <?= $c['lesson_count'] ?> aulas
            <?php if ($c['instructor']): ?> · 👤 <?= htmlspecialchars($c['instructor']) ?><?php endif; ?>
          </div>
        </div>

        <div class="course-footer">
          <div>
            <?php if ($c['is_free'] || $c['has_access']): ?>
              <span class="course-price course-price-free">Grátis</span>
            <?php elseif ($discount > 0 && $c['final_price'] < $c['price']): ?>
              <div>
                <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.82rem;">R$ <?= number_format($c['price'], 2, ',', '.') ?></span>
                <span class="course-price"> R$ <?= number_format($c['final_price'], 2, ',', '.') ?></span>
              </div>
            <?php else: ?>
              <span class="course-price">R$ <?= number_format($c['price'], 2, ',', '.') ?></span>
            <?php endif; ?>

            <?php if ($c['points_price'] > 0 && !$c['has_access']): ?>
              <div style="font-size:0.78rem;color:var(--primary);margin-top:2px;">ou <?= number_format($c['points_price']) ?> pts</div>
            <?php endif; ?>
          </div>

          <a href="<?= BASE_URL ?>/courses/<?= $c['id'] ?>" class="btn btn-primary btn-sm">
            <?= $c['has_access'] ? 'Continuar →' : 'Ver curso' ?>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
