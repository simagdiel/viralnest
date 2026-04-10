<?php // admin/views/cycles.php ?>
<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">
  <div class="a-card">
    <div class="a-card-title">Ciclos cadastrados</div>
    <table class="a-table">
      <thead><tr><th>Nome</th><th>Vagas</th><th>Preenchidas</th><th>Requer convite</th><th>Status</th><th>Ação</th></tr></thead>
      <tbody>
        <?php foreach ($cycles as $c): ?>
        <tr>
          <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
          <td><?= number_format($c['max_users']) ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:0.5rem;">
              <span><?= number_format($c['current_users']) ?></span>
              <div style="flex:1;background:var(--surface-2);border-radius:99px;height:6px;width:80px;overflow:hidden;">
                <?php $pct = $c['max_users']>0?round($c['current_users']/$c['max_users']*100):0; ?>
                <div style="width:<?= $pct ?>%;height:100%;background:var(--primary);"></div>
              </div>
              <span style="font-size:0.75rem;color:var(--text-muted);"><?= $pct ?>%</span>
            </div>
          </td>
          <td><?= $c['require_invite'] ? '🔒 Sim' : '🔓 Não' ?></td>
          <td>
            <span class="a-badge <?= $c['status']==='active'?'a-badge-success':($c['status']==='closed'?'a-badge-danger':'a-badge-warning') ?>">
              <?= $c['status'] ?>
            </span>
          </td>
          <td>
            <form method="POST" action="<?= BASE_URL ?>/admin/cycles/store" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <input type="hidden" name="action" value="status">
              <input type="hidden" name="cycle_id" value="<?= $c['id'] ?>">
              <select name="status" onchange="this.form.submit()" class="a-form-control" style="width:auto;padding:0.3rem 0.5rem;font-size:0.8rem;">
                <option value="active" <?= $c['status']==='active'?'selected':'' ?>>Ativo</option>
                <option value="closed" <?= $c['status']==='closed'?'selected':'' ?>>Fechado</option>
                <option value="upcoming" <?= $c['status']==='upcoming'?'selected':'' ?>>Em breve</option>
              </select>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($cycles)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">Nenhum ciclo.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="a-card">
    <div class="a-card-title">Novo Ciclo</div>
    <form method="POST" action="<?= BASE_URL ?>/admin/cycles/store">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <input type="hidden" name="action" value="create">
      <div class="a-form-group">
        <label class="a-form-label">Nome do ciclo</label>
        <input type="text" name="name" class="a-form-control" placeholder="Ex: Ciclo 2 — Verão" required>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Máximo de vagas</label>
        <input type="number" name="max_users" class="a-form-control" value="500" min="1" required>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Status inicial</label>
        <select name="status" class="a-form-control">
          <option value="upcoming">Em breve</option>
          <option value="active">Ativo</option>
        </select>
      </div>
      <div class="a-form-group">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.85rem;">
          <input type="checkbox" name="require_invite"> Exigir convite
        </label>
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Data início</label>
        <input type="datetime-local" name="start_date" class="a-form-control">
      </div>
      <div class="a-form-group">
        <label class="a-form-label">Data fim</label>
        <input type="datetime-local" name="end_date" class="a-form-control">
      </div>
      <button type="submit" class="a-btn a-btn-primary" style="width:100%;">+ Criar ciclo</button>
    </form>
  </div>
</div>
