<?php // admin/views/groups.php ?>
<div class="a-card">
  <div class="a-card-title">Todos os grupos</div>
  <div class="a-table-wrap">
    <table class="a-table">
      <thead><tr><th>Nome</th><th>Líder</th><th>Membros</th><th>Privado</th><th>Status</th><th>Criado em</th></tr></thead>
      <tbody>
        <?php foreach ($groups as $g): ?>
        <tr>
          <td><strong><?= htmlspecialchars($g['name']) ?></strong><br><span style="color:var(--text-muted);font-size:0.78rem;"><?= htmlspecialchars(mb_substr($g['description']??'',0,60)) ?></span></td>
          <td><?= htmlspecialchars($g['leader_name']) ?></td>
          <td><span class="a-badge a-badge-info">👥 <?= $g['member_count'] ?></span></td>
          <td><?= $g['is_private'] ? '🔒' : '🔓' ?></td>
          <td><span class="a-badge <?= $g['status']==='active'?'a-badge-success':'a-badge-danger' ?>"><?= $g['status'] ?></span></td>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('d/m/Y', strtotime($g['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($groups)): ?><tr><td colspan="6" style="text-align:center;color:var(--text-muted);">Nenhum grupo.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
