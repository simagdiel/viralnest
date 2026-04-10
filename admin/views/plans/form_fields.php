<?php // admin/views/plans/form_fields.php - partial reutilizável
$p = $plan ?? null;
?>
<div class="a-form-row">
  <div class="a-form-group">
    <label class="a-form-label">Nome *</label>
    <input type="text" name="name" class="a-form-control" value="<?= htmlspecialchars($p['name']??'') ?>" required>
  </div>
  <div class="a-form-group">
    <label class="a-form-label">Preço (R$)</label>
    <input type="number" name="price" class="a-form-control" value="<?= $p['price']??'0.00' ?>" step="0.01" min="0">
  </div>
</div>
<div class="a-form-row">
  <div class="a-form-group">
    <label class="a-form-label">Ciclo de cobrança</label>
    <select name="billing_cycle" class="a-form-control">
      <?php foreach (['monthly'=>'Mensal','quarterly'=>'Trimestral','annual'=>'Anual','lifetime'=>'Vitalício'] as $k=>$v): ?>
        <option value="<?= $k ?>" <?= ($p['billing_cycle']??'monthly')===$k?'selected':'' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="a-form-group">
    <label class="a-form-label">Cor do badge</label>
    <input type="color" name="badge_color" class="a-form-control" value="<?= $p['badge_color']??'#FFD700' ?>" style="height:38px;padding:0.15rem;">
  </div>
</div>
<div class="a-form-group">
  <label class="a-form-label">Descrição</label>
  <input type="text" name="description" class="a-form-control" value="<?= htmlspecialchars($p['description']??'') ?>">
</div>
<div class="a-form-group">
  <label class="a-form-label">Funcionalidades (uma por linha)</label>
  <?php
    $feats = [];
    if (!empty($p['features'])) $feats = json_decode($p['features'],true) ?? [];
  ?>
  <textarea name="features" class="a-form-control" rows="5" placeholder="Acesso à comunidade&#10;Convites ilimitados&#10;Badge exclusivo"><?= htmlspecialchars(implode("\n",$feats)) ?></textarea>
</div>
<div class="a-form-row">
  <div class="a-form-group">
    <label class="a-form-label">Desconto em cursos (%)</label>
    <input type="number" name="course_discount" class="a-form-control" value="<?= $p['course_discount']??'0' ?>" step="0.01" min="0" max="100">
  </div>
  <div class="a-form-group">
    <label class="a-form-label">Multiplicador de pontos</label>
    <input type="number" name="points_multiplier" class="a-form-control" value="<?= $p['points_multiplier']??'1' ?>" step="0.1" min="1">
  </div>
</div>
<div class="a-form-row">
  <div class="a-form-group">
    <label class="a-form-label">Máx. grupos simultâneos</label>
    <input type="number" name="max_groups" class="a-form-control" value="<?= $p['max_groups']??'1' ?>" min="0">
    <div class="a-form-desc">0 = ilimitado (use 99)</div>
  </div>
  <div class="a-form-group">
    <label class="a-form-label">Ordem</label>
    <input type="number" name="sort_order" class="a-form-control" value="<?= $p['sort_order']??'0' ?>" min="0">
  </div>
</div>
