<?php
// views/ranking/index.php
$pageTitle = 'Ranking';
include BASE_PATH . '/views/layout/header.php';

$userModel = new User();
$ranking = $userModel->getRanking(Setting::int('ranking_limit', 50));
$db = Database::getInstance();
$inviteRanking = $db->fetchAll(
    "SELECT u.id, u.name, u.avatar, u.level,
            COUNT(inv.id) AS invite_count
     FROM users u LEFT JOIN users inv ON inv.invited_by = u.id
     WHERE u.status = 'active'
     GROUP BY u.id ORDER BY invite_count DESC LIMIT ?",
    [Setting::int('ranking_limit', 50)]
);
?>

<div class="page">
  <div class="page-header">
    <h1 class="page-title">🏆 Ranking da Comunidade</h1>
    <p class="page-subtitle">Os membros mais ativos e engajados</p>
  </div>

  <div class="tabs" id="rankingTabs">
    <div class="tab active" data-tab="points" data-group="ranking">🌟 Por Pontos</div>
    <div class="tab" data-tab="invites" data-group="ranking">🚀 Por Convites</div>
  </div>

  <!-- Points Ranking -->
  <div class="tab-panel" data-tab="points" data-group="ranking" style="display:block;">
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:60px;">#</th>
              <th>Membro</th>
              <th>Nível</th>
              <th>Plano</th>
              <th>Convites</th>
              <th>Pontos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ranking as $i => $member): ?>
            <?php $pos = $i + 1; ?>
            <tr <?= $member['id'] == $user['id'] ? 'style="background:rgba(245,158,11,0.06);"' : '' ?>>
              <td>
                <span class="rank-position <?= $pos <= 3 ? 'rank-' . $pos : '' ?>">
                  <?= $pos === 1 ? '🥇' : ($pos === 2 ? '🥈' : ($pos === 3 ? '🥉' : "#$pos")) ?>
                </span>
              </td>
              <td>
                <div class="user-cell">
                  <img src="<?= $member['avatar'] ?: BASE_URL . '/assets/img/default-avatar.svg' ?>" class="user-avatar" alt="">
                  <div class="user-name">
                    <?= htmlspecialchars($member['name']) ?>
                    <?= $member['id'] == $user['id'] ? ' <span style="color:var(--primary);font-size:0.78rem;">(você)</span>' : '' ?>
                  </div>
                </div>
              </td>
              <td><span class="level-badge level-<?= $member['level'] ?>"><?= ucfirst($member['level']) ?></span></td>
              <td>
                <?php if ($member['plan_name']): ?>
                  <span class="badge badge-warning"><?= htmlspecialchars($member['plan_name']) ?></span>
                <?php else: ?>
                  <span class="badge badge-muted">Gratuito</span>
                <?php endif; ?>
              </td>
              <td style="color:var(--text-muted);"><?= number_format($member['invite_count']) ?></td>
              <td><strong style="color:var(--primary);font-family:var(--font-head);"><?= number_format($member['points']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Invites Ranking -->
  <div class="tab-panel" data-tab="invites" data-group="ranking" style="display:none;">
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:60px;">#</th>
              <th>Membro</th>
              <th>Nível</th>
              <th>Convites</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inviteRanking as $i => $member): ?>
            <?php $pos = $i + 1; ?>
            <tr <?= $member['id'] == $user['id'] ? 'style="background:rgba(245,158,11,0.06);"' : '' ?>>
              <td>
                <span class="rank-position <?= $pos <= 3 ? 'rank-' . $pos : '' ?>">
                  <?= $pos === 1 ? '🥇' : ($pos === 2 ? '🥈' : ($pos === 3 ? '🥉' : "#$pos")) ?>
                </span>
              </td>
              <td>
                <div class="user-cell">
                  <img src="<?= $member['avatar'] ?: BASE_URL . '/assets/img/default-avatar.svg' ?>" class="user-avatar" alt="">
                  <div class="user-name"><?= htmlspecialchars($member['name']) ?></div>
                </div>
              </td>
              <td><span class="level-badge level-<?= $member['level'] ?>"><?= ucfirst($member['level']) ?></span></td>
              <td><strong style="color:var(--primary);font-size:1.1rem;"><?= number_format($member['invite_count']) ?> 🚀</strong></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include BASE_PATH . '/views/layout/footer.php'; ?>
