<?php
// views/dashboard/index.php
$pageTitle = 'Dashboard';
include BASE_PATH . '/views/layout/header.php';

$userModel = new User();
$levelInfo = $userModel->getLevelProgress($user);
$invitedUsers = $userModel->getInvitedUsers($user['id']);
$activePlan = $userModel->getActivePlan($user['id']);
$rankingAll = $userModel->getRanking(Setting::int('ranking_limit', 50));
$userRank = 0;
foreach ($rankingAll as $i => $r) {
    if ($r['id'] == $user['id']) { $userRank = $i + 1; break; }
}

$inviteUrl = BASE_URL . '/register?invite=' . $user['invite_code'];
?>

<div class="page">
  <div class="page-header">
    <h1 class="page-title">Olá, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>! 👋</h1>
    <p class="page-subtitle">Bem-vindo de volta à comunidade.</p>
  </div>

  <!-- Metrics -->
  <div class="metrics-grid">
    <div class="metric-card animate-in animate-in-delay-1">
      <div class="metric-icon metric-icon-gold">⭐</div>
      <div>
        <div class="metric-value" data-count="<?= $user['points'] ?>"><?= number_format($user['points']) ?></div>
        <div class="metric-label">Pontos Acumulados</div>
      </div>
    </div>
    <div class="metric-card animate-in animate-in-delay-2">
      <div class="metric-icon metric-icon-blue">👥</div>
      <div>
        <div class="metric-value" data-count="<?= count($invitedUsers) ?>"><?= count($invitedUsers) ?></div>
        <div class="metric-label">Pessoas Convidadas</div>
      </div>
    </div>
    <div class="metric-card animate-in animate-in-delay-3">
      <div class="metric-icon metric-icon-green">🏆</div>
      <div>
        <div class="metric-value"><?= $userRank > 0 ? '#' . $userRank : '—' ?></div>
        <div class="metric-label">Posição no Ranking</div>
      </div>
    </div>
    <div class="metric-card animate-in animate-in-delay-4">
      <div class="metric-icon metric-icon-purple">💎</div>
      <div>
        <div class="metric-value"><?= $activePlan ? htmlspecialchars($activePlan['name']) : 'Gratuito' ?></div>
        <div class="metric-label">Plano Atual</div>
      </div>
    </div>
  </div>

  <div class="grid-2" style="margin-bottom:1.5rem;">
    <!-- Level Card -->
    <div class="card animate-in">
      <div class="card-title">Seu Nível</div>
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
        <?php
$lvlEmoji = '🌱';
if ($levelInfo['current'] === 'explorer')  $lvlEmoji = '🧭';
elseif ($levelInfo['current'] === 'mentor')   $lvlEmoji = '📚';
elseif ($levelInfo['current'] === 'guardian') $lvlEmoji = '🛡️';
elseif ($levelInfo['current'] === 'master')   $lvlEmoji = '⚡';
elseif ($levelInfo['current'] === 'legend')   $lvlEmoji = '👑';
?>
<div style="font-size:2.5rem;"><?= $lvlEmoji ?></div>
        <div>
          <div class="level-badge level-<?= $levelInfo['current'] ?>"><?= ucfirst($levelInfo['current']) ?></div>
          <div style="color:var(--text-muted);font-size:0.82rem;margin-top:0.35rem;"><?= number_format($user['points']) ?> pontos</div>
        </div>
      </div>
      <?php if ($levelInfo['next']): ?>
        <div class="progress-bar-wrap">
          <div class="progress-bar-fill" data-width="<?= $levelInfo['progress'] ?>" style="width:0%"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.78rem;color:var(--text-muted);margin-top:0.35rem;">
          <span><?= $levelInfo['progress'] ?>%</span>
          <span>Próximo: <strong style="color:var(--primary)"><?= ucfirst($levelInfo['next']) ?></strong> (faltam <?= number_format($levelInfo['points_needed']) ?> pts)</span>
        </div>
      <?php else: ?>
        <div class="badge badge-warning" style="margin-top:0.5rem;">🏅 Nível máximo atingido!</div>
      <?php endif; ?>
    </div>

    <!-- Invite Card -->
    <div class="card animate-in animate-in-delay-1">
      <div class="card-title">Seu Link de Convite</div>
      <p style="color:var(--text-muted);font-size:0.88rem;margin-bottom:1rem;">
        Convide amigos e ganhe <strong style="color:var(--primary)"><?= Setting::int('points_invite', 100) ?> pontos</strong> por cada um que se cadastrar!
      </p>
      <div class="invite-link">
        <div class="invite-url" id="inviteUrl"><?= htmlspecialchars($inviteUrl) ?></div>
        <button class="btn btn-primary btn-sm" data-copy="#inviteUrl">📋 Copiar</button>
      </div>
      <div style="margin-top:1rem;display:flex;gap:0.5rem;">
        <a href="https://wa.me/?text=<?= urlencode('Entrei nessa comunidade incrível! Use meu convite: ' . $inviteUrl) ?>"
           target="_blank" class="btn btn-secondary btn-sm">📱 WhatsApp</a>
        <a href="https://t.me/share/url?url=<?= urlencode($inviteUrl) ?>&text=<?= urlencode('Entre na ' . $siteName . '!') ?>"
           target="_blank" class="btn btn-secondary btn-sm">✈️ Telegram</a>
      </div>
    </div>
  </div>

  <!-- Invited Users -->
  <?php if (!empty($invitedUsers)): ?>
  <div class="card animate-in" style="margin-bottom:1.5rem;">
    <div class="card-title">Pessoas que você convidou (<?= count($invitedUsers) ?>)</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Usuário</th>
            <th>Nível</th>
            <th>Pontos</th>
            <th>Entrou em</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($invitedUsers, 0, 10) as $inv): ?>
          <tr>
            <td>
              <div class="user-cell">
                <img src="<?= $inv['avatar'] ?: BASE_URL . '/assets/img/default-avatar.svg' ?>" alt="" class="user-avatar">
                <div>
                  <div class="user-name"><?= htmlspecialchars($inv['name']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="level-badge level-<?= $inv['level'] ?>"><?= ucfirst($inv['level']) ?></span></td>
            <td><strong style="color:var(--primary)"><?= number_format($inv['points']) ?></strong></td>
            <td style="color:var(--text-muted);font-size:0.82rem;"><?= date('d/m/Y', strtotime($inv['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Quick links -->
  <div class="grid-3">
    <a href="<?= BASE_URL ?>/courses" class="card animate-in" style="text-decoration:none;transition:all 0.2s;cursor:pointer;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
      <div style="font-size:2rem;margin-bottom:0.75rem;">📚</div>
      <div class="card-title" style="margin-bottom:0.25rem;">Cursos</div>
      <p style="color:var(--text-muted);font-size:0.85rem;">Aprenda e ganhe pontos</p>
    </a>
    <a href="<?= BASE_URL ?>/groups" class="card animate-in animate-in-delay-1" style="text-decoration:none;transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
      <div style="font-size:2rem;margin-bottom:0.75rem;">👥</div>
      <div class="card-title" style="margin-bottom:0.25rem;">Grupos</div>
      <p style="color:var(--text-muted);font-size:0.85rem;">Conecte-se com a comunidade</p>
    </a>
    <a href="<?= BASE_URL ?>/plans" class="card animate-in animate-in-delay-2" style="text-decoration:none;transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
      <div style="font-size:2rem;margin-bottom:0.75rem;">💎</div>
      <div class="card-title" style="margin-bottom:0.25rem;">Planos Premium</div>
      <p style="color:var(--text-muted);font-size:0.85rem;">Desbloqueie benefícios exclusivos</p>
    </a>
  </div>
</div>

<?php include BASE_PATH . '/views/layout/footer.php'; ?>
