<?php // admin/views/transactions.php ?>
<div class="a-card">
  <div class="a-card-title">Todas as transações</div>
  <div class="a-table-wrap">
    <table class="a-table">
      <thead>
        <tr><th>ID</th><th>Usuário</th><th>Tipo</th><th>Gateway</th><th>Valor</th><th>Status</th><th>Data</th></tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $tx): ?>
        <tr>
          <td style="font-family:monospace;font-size:0.78rem;color:var(--text-muted);"><?= $tx['id'] ?></td>
          <td>
            <div style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars($tx['name']) ?></div>
            <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($tx['email']) ?></div>
          </td>
          <td><span class="a-badge a-badge-info"><?= ucfirst($tx['type']) ?></span></td>
          <td style="font-size:0.82rem;"><?= htmlspecialchars($tx['gateway'] ?? '—') ?></td>
          <td><strong style="color:var(--primary);">R$ <?= number_format($tx['amount'],2,',','.') ?></strong></td>
          <td>
            <span class="a-badge <?= $tx['status']==='paid'?'a-badge-success':($tx['status']==='failed'?'a-badge-danger':($tx['status']==='refunded'?'a-badge-warning':'a-badge-muted')) ?>">
              <?= $tx['status'] ?>
            </span>
          </td>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($transactions)): ?><tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Nenhuma transação.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
