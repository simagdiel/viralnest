<?php // admin/views/invites.php ?>
<div class="a-card">
  <div class="a-card-title">Rede de convites (top 100)</div>
  <div class="a-table-wrap">
    <table class="a-table">
      <thead><tr><th>Membro</th><th>E-mail</th><th>Código</th><th>Convites realizados</th><th>Membro desde</th></tr></thead>
      <tbody>
        <?php foreach ($invites as $inv): ?>
        <tr>
          <td><strong><?= htmlspecialchars($inv['owner_name']) ?></strong></td>
          <td style="color:var(--text-muted);font-size:0.82rem;"><?= htmlspecialchars($inv['owner_email']) ?></td>
          <td><code style="color:var(--primary);font-size:0.85rem;"><?= $inv['invite_code'] ?></code></td>
          <td>
            <span style="font-family:var(--font-head);font-weight:700;color:var(--primary);font-size:1rem;"><?= number_format($inv['invite_count']) ?></span>
          </td>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('d/m/Y', strtotime($inv['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
