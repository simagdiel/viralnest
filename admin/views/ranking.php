<?php // admin/views/ranking.php ?>
<div class="a-card">
  <div class="a-card-title">Ranking (Top <?= count($ranking) ?>)</div>
  <div class="a-table-wrap">
    <table class="a-table">
      <thead><tr><th style="width:50px;">#</th><th>Usuário</th><th>E-mail</th><th>Nível</th><th>Convites</th><th>Pontos</th></tr></thead>
      <tbody>
        <?php foreach ($ranking as $i => $u): $pos = $i+1; ?>
        <tr>
          <td style="font-family:var(--font-head);font-weight:800;color:<?= $pos===1?'#FFD700':($pos===2?'#C0C0C0':($pos===3?'#CD7F32':'var(--text-muted)')) ?>;">
            <?= $pos===1?'🥇':($pos===2?'🥈':($pos===3?'🥉':"#$pos")) ?>
          </td>
          <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
          <td style="color:var(--text-muted);font-size:0.82rem;"><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="a-badge a-badge-info"><?= ucfirst($u['level']) ?></span></td>
          <td><?= number_format($u['invites']) ?></td>
          <td><strong style="color:var(--primary);"><?= number_format($u['points']) ?></strong></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
