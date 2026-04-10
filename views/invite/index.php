<?php
// views/invite/index.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page">
  <div class="page-header">
    <h1 class="page-title">🚀 Meus Convites</h1>
    <p class="page-subtitle">Convide pessoas e ganhe <strong style="color:var(--primary);"><?= $pointsPerInvite ?> pontos</strong> por cada cadastro.</p>
  </div>

  <div class="grid-2" style="margin-bottom:1.5rem;">
    <div class="card animate-in">
      <div class="card-title">Seu link de convite</div>
      <div class="invite-link" style="margin-bottom:1rem;">
        <div class="invite-url" id="inviteUrl"><?= htmlspecialchars($inviteUrl) ?></div>
        <button class="btn btn-primary btn-sm" data-copy="#inviteUrl">📋 Copiar</button>
      </div>
      <div style="display:flex;gap:0.6rem;flex-wrap:wrap;">
        <a href="https://wa.me/?text=<?= urlencode('Entre na nossa comunidade usando meu convite: '.$inviteUrl) ?>" target="_blank" class="btn btn-secondary btn-sm">📱 WhatsApp</a>
        <a href="https://t.me/share/url?url=<?= urlencode($inviteUrl) ?>" target="_blank" class="btn btn-secondary btn-sm">✈️ Telegram</a>
        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($inviteUrl) ?>&text=<?= urlencode('Entre na nossa comunidade!') ?>" target="_blank" class="btn btn-secondary btn-sm">🐦 Twitter</a>
      </div>
    </div>
    <div class="card animate-in animate-in-delay-1">
      <div class="card-title">Seu código de convite</div>
      <div style="font-family:var(--font-head);font-size:2.5rem;font-weight:800;color:var(--primary);letter-spacing:0.1em;text-align:center;padding:1rem 0;"><?= htmlspecialchars($user['invite_code']) ?></div>
      <p style="color:var(--text-muted);font-size:0.85rem;text-align:center;">Compartilhe este código ou o link acima</p>
    </div>
  </div>

  <div class="card animate-in animate-in-delay-2">
    <div class="card-title">Pessoas que você convidou (<?= count($invitedUsers) ?>)</div>
    <?php if (empty($invitedUsers)): ?>
      <div style="text-align:center;padding:2rem;color:var(--text-muted);">
        <div style="font-size:2.5rem;margin-bottom:0.75rem;">👥</div>
        Nenhum convite usado ainda. Comece a compartilhar!
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Membro</th><th>Nível</th><th>Pontos</th><th>Entrou em</th></tr></thead>
        <tbody>
          <?php foreach ($invitedUsers as $inv): ?>
          <tr>
            <td>
              <div class="user-cell">
                <img src="<?= $inv['avatar'] ?: BASE_URL.'/assets/img/default-avatar.svg' ?>" class="user-avatar" alt="">
                <div class="user-name"><?= htmlspecialchars($inv['name']) ?></div>
              </div>
            </td>
            <td><span class="level-badge level-<?= $inv['level'] ?>"><?= ucfirst($inv['level']) ?></span></td>
            <td style="color:var(--primary);font-weight:600;"><?= number_format($inv['points']) ?></td>
            <td style="color:var(--text-muted);font-size:0.82rem;"><?= date('d/m/Y', strtotime($inv['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
