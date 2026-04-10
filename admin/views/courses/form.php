<?php // admin/views/courses/form.php
$c = $course ?? null;
$action = $c ? BASE_URL.'/admin/courses/edit/'.$c['id'] : BASE_URL.'/admin/courses/create';
?>
<div style="max-width:700px;">
  <div class="a-flex-between a-mb-2">
    <div></div>
    <a href="<?= BASE_URL ?>/admin/courses" class="a-btn a-btn-secondary a-btn-sm">← Voltar</a>
  </div>
  <div class="a-card">
    <div class="a-card-title"><?= $c ? 'Editar: '.htmlspecialchars($c['title']) : 'Novo Curso' ?></div>
    <form method="POST" action="<?= $action ?>">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <div class="a-form-group">
        <label class="a-form-label">Título *</label>
        <input type="text" name="title" class="a-form-control" value="<?= htmlspecialchars($c['title']??'') ?>" required>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Descrição</label>
        <textarea name="description" class="a-form-control" rows="4"><?= htmlspecialchars($c['description']??'') ?></textarea>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Thumbnail (URL da imagem)</label>
        <input type="url" name="thumbnail" class="a-form-control" value="<?= htmlspecialchars($c['thumbnail']??'') ?>" placeholder="https://...">
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Instrutor</label>
        <input type="text" name="instructor" class="a-form-control" value="<?= htmlspecialchars($c['instructor']??'') ?>">
      </div>
      <div class="a-form-row">
        <div class="a-form-group">
          <label class="a-form-label">Preço (R$)</label>
          <input type="number" name="price" class="a-form-control" value="<?= $c['price']??'0.00' ?>" step="0.01" min="0">
        </div>
        <div class="a-form-group">
          <label class="a-form-label">Preço em Pontos</label>
          <input type="number" name="points_price" class="a-form-control" value="<?= $c['points_price']??'0' ?>" min="0">
          <div class="a-form-desc">0 = não permite troca por pontos</div>
        </div>
      </div>
      <div class="a-form-row">
        <div class="a-form-group">
          <label class="a-form-label">Nível mínimo</label>
          <select name="level_required" class="a-form-control">
            <?php foreach (['explorer','mentor','guardian','master','legend'] as $lvl): ?>
              <option value="<?= $lvl ?>" <?= ($c['level_required']??'explorer')===$lvl?'selected':'' ?>><?= ucfirst($lvl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="a-form-group">
          <label class="a-form-label">Ordem</label>
          <input type="number" name="sort_order" class="a-form-control" value="<?= $c['sort_order']??'0' ?>" min="0">
        </div>
      </div>
      <div style="display:flex;gap:1.5rem;margin-bottom:1rem;">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
          <input type="checkbox" name="is_free" <?= ($c['is_free']??0)?'checked':'' ?>> Gratuito
        </label>
        <?php if ($c): ?>
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
          <input type="checkbox" name="is_active" <?= ($c['is_active']??1)?'checked':'' ?>> Ativo
        </label>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="a-btn a-btn-primary">💾 Salvar curso</button>
        <a href="<?= BASE_URL ?>/admin/courses" class="a-btn a-btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
