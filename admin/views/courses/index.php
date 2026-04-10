<?php // admin/views/courses/index.php ?>
<div class="a-flex-between a-mb-2">
  <div></div>
  <a href="<?= BASE_URL ?>/admin/courses/create" class="a-btn a-btn-primary">+ Novo Curso</a>
</div>
<div class="a-card">
  <table class="a-table">
    <thead><tr><th>Curso</th><th>Preço</th><th>Pts</th><th>Módulos</th><th>Nível</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($courses as $c): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars($c['title']) ?></strong>
          <?php if ($c['instructor']): ?><br><span style="font-size:0.78rem;color:var(--text-muted);">👤 <?= htmlspecialchars($c['instructor']) ?></span><?php endif; ?>
        </td>
        <td><?= $c['is_free'] ? '<span class="a-badge a-badge-success">Grátis</span>' : 'R$ '.number_format($c['price'],2,',','.') ?></td>
        <td style="color:var(--primary);"><?= $c['points_price'] > 0 ? number_format($c['points_price']).' pts' : '—' ?></td>
        <td><?= $c['module_count'] ?> mód / <?= $c['lesson_count'] ?> aulas</td>
        <td><span class="a-badge a-badge-info"><?= ucfirst($c['level_required']) ?>+</span></td>
        <td><span class="a-badge <?= $c['is_active']?'a-badge-success':'a-badge-danger' ?>"><?= $c['is_active']?'Ativo':'Inativo' ?></span></td>
        <td>
          <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/admin/courses/edit/<?= $c['id'] ?>" class="a-btn a-btn-secondary a-btn-sm">✏️</a>
            <a href="<?= BASE_URL ?>/admin/courses/modules/<?= $c['id'] ?>" class="a-btn a-btn-secondary a-btn-sm">📦 Módulos</a>
            <form method="POST" action="<?= BASE_URL ?>/admin/courses/delete/<?= $c['id'] ?>" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <button type="submit" class="a-btn a-btn-danger a-btn-sm" data-confirm="Remover curso?">🗑</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($courses)): ?><tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Nenhum curso.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
