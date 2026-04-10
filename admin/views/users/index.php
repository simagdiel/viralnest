<?php // admin/views/users/index.php ?>
<div class="a-flex-between a-mb-2">
  <form method="GET" style="display:flex;gap:0.5rem;">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar nome ou e-mail..." class="a-form-control" style="width:280px;">
    <button type="submit" class="a-btn a-btn-primary">Buscar</button>
    <?php if ($search): ?><a href="<?= BASE_URL ?>/admin/users" class="a-btn a-btn-secondary">Limpar</a><?php endif; ?>
  </form>
  <div style="color:var(--text-muted);font-size:0.85rem;"><?= number_format($total) ?> usuário(s)</div>
</div>

<div class="a-card">
  <div class="a-table-wrap">
    <table class="a-table">
      <thead>
        <tr>
          <th>#</th><th>Usuário</th><th>Pontos</th><th>Nível</th><th>Plano</th>
          <th>Convites</th><th>Status</th><th>Entrou</th><th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:0.8rem;"><?= $u['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:0.6rem;">
              <img src="<?= $u['avatar'] ?: BASE_URL.'/assets/img/default-avatar.svg' ?>" style="width:30px;height:30px;border-radius:50%;border:1px solid var(--border);" alt="">
              <div>
                <div style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($u['name']) ?></div>
                <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></div>
              </div>
            </div>
          </td>
          <td><strong style="color:var(--primary);"><?= number_format($u['points']) ?></strong></td>
          <td><span class="a-badge a-badge-info"><?= ucfirst($u['level']) ?></span></td>
          <td><span class="a-badge a-badge-muted"><?= $u['plan_name'] ?? 'Gratuito' ?></span></td>
          <td style="color:var(--text-muted);"><?= $u['invite_code'] ?></td>
          <td>
            <span class="a-badge <?= $u['status']==='active'?'a-badge-success':($u['status']==='suspended'?'a-badge-danger':'a-badge-warning') ?>">
              <?= $u['status'] ?>
            </span>
          </td>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
              <a href="<?= BASE_URL ?>/admin/users/edit/<?= $u['id'] ?>" class="a-btn a-btn-secondary a-btn-sm">✏️</a>
              <?php if (!empty($u['phone'])): ?>
              <button onclick="openMsgModal(<?= $u['id'] ?>,'<?= htmlspecialchars($u['name'],ENT_QUOTES) ?>')" class="a-btn a-btn-secondary a-btn-sm" title="WhatsApp">💬</button>
              <?php endif; ?>
              <form method="POST" action="<?= BASE_URL ?>/admin/users/delete/<?= $u['id'] ?>" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                <button type="submit" class="a-btn a-btn-danger a-btn-sm" data-confirm="Remover este usuário?">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
          <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:2rem;">Nenhum usuário encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
  <div class="a-flex" style="justify-content:center;margin-top:1rem;gap:0.35rem;">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a href="?p=<?= $i ?><?= $search ? '&q='.urlencode($search) : '' ?>" class="a-page-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal WhatsApp -->
<div id="msgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:999;align-items:center;justify-content:center;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:1.5rem;width:100%;max-width:440px;">
    <div style="font-family:var(--font-head);font-weight:700;margin-bottom:1rem;" id="msgTitle">Enviar WhatsApp</div>
    <form method="POST" id="msgForm">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <div class="a-form-group">
        <label class="a-form-label">Mensagem</label>
        <textarea name="message" class="a-form-control" rows="4" placeholder="Digite a mensagem..." required></textarea>
      </div>
      <div style="display:flex;gap:0.75rem;">
        <button type="submit" class="a-btn a-btn-primary">Enviar 💬</button>
        <button type="button" onclick="document.getElementById('msgModal').style.display='none'" class="a-btn a-btn-secondary">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openMsgModal(id, name) {
  document.getElementById('msgTitle').textContent = 'WhatsApp para ' + name;
  document.getElementById('msgForm').action = '<?= BASE_URL ?>/admin/users/message/' + id;
  document.getElementById('msgModal').style.display = 'flex';
}
</script>
