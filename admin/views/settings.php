<?php // admin/views/settings.php
$categoryLabels = [
    'general'      => '⚙️ Geral',
    'registration' => '📝 Cadastro',
    'points'       => '⭐ Pontos',
    'levels'       => '🏆 Níveis',
    'groups'       => '👥 Grupos',
    'ranking'      => '📊 Ranking',
    'whatsapp'     => '💬 WhatsApp',
    'social'       => '🔗 Redes Sociais',
];
$firstCategory = $categories[0] ?? 'general';
?>

<form method="POST" action="<?= BASE_URL ?>/admin/settings/save" id="settingsForm">
  <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

  <div class="settings-grid">
    <!-- Nav lateral -->
    <div class="settings-nav">
      <?php foreach ($categories as $cat): ?>
      <a href="#" class="settings-nav-item <?= $cat === $firstCategory ? 'active' : '' ?>" data-panel="<?= $cat ?>">
        <?= $categoryLabels[$cat] ?? ucfirst($cat) ?>
      </a>
      <?php endforeach; ?>
      <hr style="border-color:var(--border);margin:0.75rem 0;">
      <button type="submit" class="a-btn a-btn-primary" style="width:100%;margin-top:0.25rem;">💾 Salvar tudo</button>
    </div>

    <!-- Painéis -->
    <div>
      <?php foreach ($categories as $cat):
        $catSettings = array_filter($allSettings, function($s) use ($cat) { return $s['category'] === $cat; });
      ?>
      <div class="settings-panel <?= $cat === $firstCategory ? 'active' : '' ?>" id="panel-<?= $cat ?>">
        <div class="a-card">
          <div class="a-card-title"><?= $categoryLabels[$cat] ?? ucfirst($cat) ?></div>
          <?php foreach ($catSettings as $s): ?>
          <div class="a-form-group">
            <label class="a-form-label"><?= htmlspecialchars(ucwords(str_replace('_',' ',$s['setting_key']))) ?></label>
            <?php
            $key = $s['setting_key'];
            $val = $s['setting_value'] ?? '';
            $type = $s['setting_type'];
            $inputName = "settings[{$key}]";
            ?>

            <?php if ($type === 'boolean'): ?>
              <div class="gateway-toggle" style="max-width:300px;">
                <label class="toggle-switch">
                  <input type="checkbox" name="<?= $inputName ?>" value="true" <?= in_array(strtolower($val), ['true','1','yes','on']) ? 'checked' : '' ?>>
                  <span class="toggle-slider"></span>
                </label>
                <span style="font-size:0.85rem;"><?= in_array(strtolower($val), ['true','1','yes','on']) ? 'Ativado' : 'Desativado' ?></span>
              </div>
              <!-- Boolean como hidden para enviar false quando desmarcado -->
              <?php /* handled via JS */ ?>

            <?php elseif ($type === 'color'): ?>
              <div style="display:flex;align-items:center;gap:0.75rem;">
                <input type="color" name="<?= $inputName ?>" value="<?= htmlspecialchars($val) ?>"
                       class="a-form-control" style="width:60px;padding:0.15rem;">
                <input type="text" value="<?= htmlspecialchars($val) ?>" class="a-form-control"
                       style="max-width:120px;font-family:monospace;" readonly
                       onclick="this.select()">
              </div>

            <?php elseif ($type === 'textarea'): ?>
              <textarea name="<?= $inputName ?>" class="a-form-control" rows="3"><?= htmlspecialchars($val) ?></textarea>

            <?php elseif ($type === 'number'): ?>
              <input type="number" name="<?= $inputName ?>" value="<?= htmlspecialchars($val) ?>" class="a-form-control" style="max-width:200px;">

            <?php else: ?>
              <input type="text" name="<?= $inputName ?>" value="<?= htmlspecialchars($val) ?>" class="a-form-control">
            <?php endif; ?>

            <?php if ($s['description']): ?>
              <div class="a-form-desc"><?= htmlspecialchars($s['description']) ?></div>
            <?php endif; ?>
          </div>
          <hr class="a-divider">
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</form>

<script>
// Sync color picker com text input
document.querySelectorAll('input[type="color"]').forEach(picker => {
  const text = picker.nextElementSibling;
  picker.addEventListener('input', () => { text.value = picker.value; });
});

// Tratar checkboxes de boolean: enviar "false" quando desmarcado
document.getElementById('settingsForm').addEventListener('submit', function() {
  this.querySelectorAll('input[type="checkbox"]').forEach(cb => {
    if (!cb.checked) {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = cb.name;
      hidden.value = 'false';
      this.appendChild(hidden);
      cb.name = '';
    }
  });
});
</script>
