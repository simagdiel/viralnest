<?php // admin/views/courses/modules.php ?>
<div class="a-flex-between a-mb-2">
  <div>
    <a href="<?= BASE_URL ?>/admin/courses" class="a-btn a-btn-secondary a-btn-sm">← Cursos</a>
    <span style="margin:0 0.5rem;color:var(--text-muted);">›</span>
    <span style="font-size:0.9rem;"><?= htmlspecialchars($course['title']) ?></span>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">
  <div>
    <?php foreach ($modules as $mod): ?>
    <div class="a-card a-mb-2">
      <div class="a-flex-between">
        <div>
          <strong>📦 <?= htmlspecialchars($mod['title']) ?></strong>
          <span style="margin-left:0.75rem;font-size:0.78rem;color:var(--text-muted);"><?= count($mod['lessons']) ?> aulas</span>
        </div>
        <div style="display:flex;gap:0.5rem;">
          <a href="<?= BASE_URL ?>/admin/modules/lessons/<?= $mod['id'] ?>" class="a-btn a-btn-secondary a-btn-sm">🎥 Aulas</a>
          <form method="POST" action="<?= BASE_URL ?>/admin/courses/modules/<?= $courseId ?>">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="module_id" value="<?= $mod['id'] ?>">
            <button type="submit" class="a-btn a-btn-danger a-btn-sm" data-confirm="Remover módulo e todas as aulas?">🗑</button>
          </form>
        </div>
      </div>
      <?php if (!empty($mod['lessons'])): ?>
      <div style="margin-top:0.75rem;border-top:1px solid var(--border);padding-top:0.75rem;">
        <?php foreach ($mod['lessons'] as $ls): ?>
        <div style="display:flex;align-items:center;gap:0.6rem;padding:0.3rem 0;font-size:0.83rem;color:var(--text-muted);">
          <span><?= strtoupper($ls['video_type'] ?? 'URL') ?></span>
          <span style="color:var(--text);"><?= htmlspecialchars($ls['title']) ?></span>
          <?php if ($ls['is_preview']): ?><span class="a-badge a-badge-info" style="font-size:0.68rem;">Preview</span><?php endif; ?>
          <?php if ($ls['points_reward']): ?><span style="color:var(--primary);margin-left:auto;">+<?= $ls['points_reward'] ?>pts</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (empty($modules)): ?>
      <div class="a-card" style="text-align:center;padding:2rem;color:var(--text-muted);">Nenhum módulo ainda. Crie o primeiro →</div>
    <?php endif; ?>
  </div>

  <div class="a-card">
    <div class="a-card-title">+ Novo Módulo</div>
    <form method="POST" action="<?= BASE_URL ?>/admin/courses/modules/<?= $courseId ?>">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <input type="hidden" name="action" value="create">
      <div class="a-form-group">
        <label class="a-form-label">Título do módulo *</label>
        <input type="text" name="title" class="a-form-control" required placeholder="Ex: Introdução">
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Descrição</label>
        <textarea name="description" class="a-form-control" rows="2"></textarea>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Ordem</label>
        <input type="number" name="sort_order" class="a-form-control" value="0" min="0">
      </div>
      <button type="submit" class="a-btn a-btn-primary" style="width:100%;">+ Criar módulo</button>
    </form>
  </div>
</div>
