<?php // admin/views/courses/lessons.php ?>
<div class="a-flex-between a-mb-2">
  <div>
    <a href="<?= BASE_URL ?>/admin/courses/modules/<?= $module['course_id'] ?>" class="a-btn a-btn-secondary a-btn-sm">← Módulos</a>
    <span style="margin:0 0.5rem;color:var(--text-muted);">›</span>
    <span style="font-size:0.9rem;"><?= htmlspecialchars($module['title']) ?></span>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start;">
  <!-- Lista de aulas -->
  <div class="a-card">
    <div class="a-card-title">Aulas do módulo (<?= count($lessons) ?>)</div>
    <?php if (empty($lessons)): ?>
      <p style="color:var(--text-muted);text-align:center;padding:1rem;">Nenhuma aula. Adicione a primeira →</p>
    <?php endif; ?>
    <div style="display:flex;flex-direction:column;gap:0.75rem;">
      <?php foreach ($lessons as $ls): ?>
      <div style="padding:0.9rem;background:var(--surface-2);border-radius:var(--radius-sm);border:1px solid var(--border);">
        <div class="a-flex-between" style="margin-bottom:0.4rem;">
          <div>
            <strong style="font-size:0.9rem;"><?= htmlspecialchars($ls['title']) ?></strong>
            <span class="a-badge a-badge-muted" style="margin-left:0.5rem;font-size:0.68rem;"><?= strtoupper($ls['video_type']) ?></span>
            <?php if ($ls['is_preview']): ?><span class="a-badge a-badge-info" style="font-size:0.68rem;">Preview</span><?php endif; ?>
          </div>
          <form method="POST" action="<?= BASE_URL ?>/admin/modules/lessons/<?= $moduleId ?>">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="lesson_id" value="<?= $ls['id'] ?>">
            <button type="submit" class="a-btn a-btn-danger a-btn-sm" data-confirm="Remover esta aula?">🗑</button>
          </form>
        </div>
        <?php if ($ls['video_url']): ?>
        <div style="font-size:0.75rem;color:var(--text-muted);font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <?= htmlspecialchars(substr($ls['video_url'],0,80)) ?>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:1rem;margin-top:0.4rem;font-size:0.78rem;color:var(--text-muted);">
          <?php if ($ls['duration_minutes']): ?><span>⏱ <?= $ls['duration_minutes'] ?>min</span><?php endif; ?>
          <?php if ($ls['points_reward']): ?><span style="color:var(--primary);">+<?= $ls['points_reward'] ?> pts</span><?php endif; ?>
          <span>Ordem: <?= $ls['sort_order'] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Formulário nova aula -->
  <div class="a-card">
    <div class="a-card-title">+ Nova Aula</div>
    <form method="POST" action="<?= BASE_URL ?>/admin/modules/lessons/<?= $moduleId ?>">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <input type="hidden" name="action" value="create">
      <div class="a-form-group">
        <label class="a-form-label">Título *</label>
        <input type="text" name="title" class="a-form-control" required placeholder="Ex: Introdução ao módulo">
      </div>
      <div class="a-form-group">
        <label class="a-form-label">URL ou Iframe do vídeo</label>
        <textarea name="video_url" class="a-form-control" rows="3" placeholder="Cole a URL do YouTube, Google Drive, Vimeo ou o código iframe" id="videoUrlInput"></textarea>
        <div class="a-form-desc">
          Detectado: <strong id="videoTypeDetected" style="color:var(--primary);">—</strong>
        </div>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Tipo de vídeo</label>
        <select name="video_type" class="a-form-control" id="videoTypeSelect">
          <option value="">Detectar automaticamente</option>
          <option value="youtube">YouTube</option>
          <option value="drive">Google Drive</option>
          <option value="vimeo">Vimeo</option>
          <option value="iframe">iframe / outro</option>
        </select>
      </div>
      <div class="a-form-row">
        <div class="a-form-group">
          <label class="a-form-label">Duração (min)</label>
          <input type="number" name="duration" class="a-form-control" value="0" min="0">
        </div>
        <div class="a-form-group">
          <label class="a-form-label">Pontos ao concluir</label>
          <input type="number" name="points_reward" class="a-form-control" value="30" min="0">
        </div>
      </div>
      <div class="a-form-row">
        <div class="a-form-group">
          <label class="a-form-label">Ordem</label>
          <input type="number" name="sort_order" class="a-form-control" value="<?= count($lessons) ?>" min="0">
        </div>
        <div class="a-form-group" style="display:flex;align-items:flex-end;padding-bottom:0.25rem;">
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
            <input type="checkbox" name="is_preview"> Preview grátis
          </label>
        </div>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Descrição</label>
        <textarea name="description" class="a-form-control" rows="2"></textarea>
      </div>
      <button type="submit" class="a-btn a-btn-primary" style="width:100%;">+ Adicionar aula</button>
    </form>
  </div>
</div>

<script>
// Detecção automática de tipo de vídeo
const urlInput = document.getElementById('videoUrlInput');
const detected = document.getElementById('videoTypeDetected');
const typeSelect = document.getElementById('videoTypeSelect');

urlInput.addEventListener('input', function() {
  const url = this.value.trim();
  let type = '—';
  if (/youtu(be\.com|\.be)/i.test(url)) type = 'YouTube';
  else if (/drive\.google\.com/i.test(url)) type = 'Google Drive';
  else if (/vimeo\.com/i.test(url)) type = 'Vimeo';
  else if (/^<iframe/i.test(url)) type = 'iframe';
  else if (url.length > 5) type = 'iframe / URL';
  detected.textContent = type;
});
</script>
