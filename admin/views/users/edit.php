<?php // admin/views/users/edit.php ?>
<div style="max-width:600px;">
  <div class="a-card">
    <div class="a-flex-between a-mb-2">
      <div class="a-card-title">Editar Usuário #<?= $user['id'] ?></div>
      <a href="<?= BASE_URL ?>/admin/users" class="a-btn a-btn-secondary a-btn-sm">← Voltar</a>
    </div>

    <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:var(--surface-2);border-radius:var(--radius-sm);margin-bottom:1.5rem;">
      <img src="<?= $user['avatar'] ?: BASE_URL.'/assets/img/default-avatar.svg' ?>" style="width:56px;height:56px;border-radius:50%;border:2px solid var(--primary);" alt="">
      <div>
        <div style="font-weight:700;"><?= htmlspecialchars($user['name']) ?></div>
        <div style="color:var(--text-muted);font-size:0.82rem;"><?= htmlspecialchars($user['email']) ?></div>
        <div style="font-size:0.8rem;margin-top:0.25rem;">Código de convite: <code style="color:var(--primary);"><?= $user['invite_code'] ?></code></div>
      </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/users/edit/<?= $user['id'] ?>">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <div class="a-form-row">
        <div class="a-form-group">
          <label class="a-form-label">Nome</label>
          <input type="text" name="name" class="a-form-control" value="<?= htmlspecialchars($user['name']) ?>">
        </div>
        <div class="a-form-group">
          <label class="a-form-label">Status</label>
          <select name="status" class="a-form-control">
            <option value="active" <?= $user['status']==='active'?'selected':'' ?>>✅ Ativo</option>
            <option value="suspended" <?= $user['status']==='suspended'?'selected':'' ?>>🚫 Suspenso</option>
            <option value="pending" <?= $user['status']==='pending'?'selected':'' ?>>⏳ Pendente</option>
          </select>
        </div>
      </div>
      <div class="a-form-row">
        <div class="a-form-group">
          <label class="a-form-label">Nível</label>
          <select name="level" class="a-form-control">
            <?php foreach (['explorer','mentor','guardian','master','legend'] as $lvl): ?>
              <option value="<?= $lvl ?>" <?= $user['level']===$lvl?'selected':'' ?>><?= ucfirst($lvl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="a-form-group">
          <label class="a-form-label">Adicionar Pontos</label>
          <input type="number" name="points_add" class="a-form-control" placeholder="Ex: 500" min="0">
          <div class="a-form-desc">Atual: <?= number_format($user['points']) ?> pontos</div>
        </div>
      </div>
      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="a-btn a-btn-primary">💾 Salvar</button>
        <a href="<?= BASE_URL ?>/admin/users" class="a-btn a-btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
