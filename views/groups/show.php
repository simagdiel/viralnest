<?php
// views/groups/show.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;">
    <div>
      <h1 class="page-title"><?= htmlspecialchars($group['name']) ?></h1>
      <div style="color:var(--text-muted);margin-top:0.25rem;">Líder: <strong style="color:var(--primary);"><?= htmlspecialchars($group['leader_name']) ?></strong></div>
    </div>
    <?php if (!$isMember): ?>
    <form method="POST" action="<?= BASE_URL ?>/groups/<?= $group['id'] ?>/join">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <button type="submit" class="btn btn-primary">+ Entrar no grupo</button>
    </form>
    <?php else: ?>
      <span class="badge badge-success">✓ Você é membro</span>
    <?php endif; ?>
  </div>

  <?php if ($group['description']): ?>
  <div class="card" style="margin-bottom:1.5rem;">
    <p style="color:var(--text-muted);line-height:1.7;"><?= nl2br(htmlspecialchars($group['description'])) ?></p>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title">👥 Membros (<?= count($members) ?>)</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Membro</th><th>Nível</th><th>Pontos</th><th>Papel</th><th>Entrou em</th></tr></thead>
        <tbody>
          <?php foreach ($members as $m): ?>
          <tr>
            <td>
              <div class="user-cell">
                <img src="<?= $m['avatar'] ?: BASE_URL.'/assets/img/default-avatar.svg' ?>" class="user-avatar" alt="">
                <span class="user-name"><?= htmlspecialchars($m['name']) ?></span>
              </div>
            </td>
            <td><span class="level-badge level-<?= $m['level'] ?>"><?= ucfirst($m['level']) ?></span></td>
            <td style="color:var(--primary);font-weight:600;"><?= number_format($m['points']) ?></td>
            <td><span class="badge <?= $m['role']==='moderator'?'badge-warning':'badge-muted' ?>"><?= ucfirst($m['role']) ?></span></td>
            <td style="color:var(--text-muted);font-size:0.8rem;"><?= date('d/m/Y', strtotime($m['joined_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
