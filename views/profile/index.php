<?php
// views/profile/index.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page">
  <div class="page-header">
    <h1 class="page-title">Meu Perfil</h1>
  </div>

  <div class="grid-2" style="align-items:start;">
    <!-- Info & Edit -->
    <div>
      <div class="card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;gap:1.25rem;margin-bottom:1.5rem;">
          <div style="position:relative;">
            <img src="<?= $user['avatar'] ?: BASE_URL . '/assets/img/default-avatar.svg' ?>"
                 alt="Avatar" style="width:80px;height:80px;border-radius:50%;border:3px solid var(--primary);object-fit:cover;">
          </div>
          <div>
            <div style="font-family:var(--font-head);font-size:1.3rem;font-weight:800;"><?= htmlspecialchars($user['name']) ?></div>
            <div class="level-badge level-<?= $user['level'] ?>" style="margin-top:0.35rem;"><?= ucfirst($user['level']) ?></div>
            <div style="color:var(--text-muted);font-size:0.82rem;margin-top:0.35rem;">Membro desde <?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
          </div>
        </div>

        <!-- Level progress -->
        <?php
        $lvlInfo = $levelInfo;
        ?>
        <div style="margin-bottom:1.5rem;">
          <div style="display:flex;justify-content:space-between;font-size:0.82rem;color:var(--text-muted);margin-bottom:0.35rem;">
            <span><?= ucfirst($lvlInfo['current']) ?></span>
            <?php if ($lvlInfo['next']): ?><span><?= ucfirst($lvlInfo['next']) ?></span><?php endif; ?>
          </div>
          <div class="progress-bar-wrap">
            <div class="progress-bar-fill" data-width="<?= $lvlInfo['progress'] ?>" style="width:0%"></div>
          </div>
          <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;"><?= number_format($user['points']) ?> pontos · <?= $lvlInfo['progress'] ?>% para próximo nível</div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/profile/update" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <div class="form-group">
            <label class="form-label">Nome</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">WhatsApp</label>
            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Avatar</label>
            <input type="file" name="avatar" class="form-control" accept="image/*">
          </div>
          <hr class="divider">
          <div class="form-group">
            <label class="form-label">Senha atual (para alterar senha)</label>
            <input type="password" name="current_password" class="form-control" placeholder="Apenas se quiser mudar a senha">
          </div>
          <div class="form-group">
            <label class="form-label">Nova senha</label>
            <input type="password" name="new_password" class="form-control" placeholder="Mínimo 8 caracteres">
          </div>
          <button type="submit" class="btn btn-primary">Salvar alterações</button>
        </form>
      </div>

      <!-- Plano ativo -->
      <?php if ($activePlan): ?>
      <div class="card">
        <div class="card-title">Plano ativo</div>
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <div style="font-size:1.5rem;">💎</div>
          <div>
            <div style="font-weight:700;"><?= htmlspecialchars($activePlan['name']) ?></div>
            <?php if ($activePlan['expires_at'] && $activePlan['billing_cycle'] !== 'lifetime'): ?>
              <div style="font-size:0.8rem;color:var(--text-muted);">Válido até <?= date('d/m/Y', strtotime($activePlan['expires_at'])) ?></div>
            <?php else: ?>
              <div style="font-size:0.8rem;color:#4ADE80;">Vitalício</div>
            <?php endif; ?>
          </div>
          <a href="<?= BASE_URL ?>/plans" class="btn btn-secondary btn-sm" style="margin-left:auto;">Upgrade</a>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Histórico de pontos + cursos -->
    <div>
      <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-title">Histórico de pontos</div>
        <?php if (empty($pointsHistory)): ?>
          <p style="color:var(--text-muted);font-size:0.88rem;">Nenhum ponto registrado ainda.</p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:0.5rem;max-height:320px;overflow-y:auto;">
          <?php foreach ($pointsHistory as $pt): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;border-bottom:1px solid var(--border);">
            <div>
              <div style="font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($pt['description'] ?: ucfirst(str_replace('_',' ',$pt['action_type']))) ?></div>
              <div style="font-size:0.75rem;color:var(--text-muted);"><?= date('d/m/Y H:i', strtotime($pt['created_at'])) ?></div>
            </div>
            <div style="font-weight:700;color:<?= $pt['points'] > 0 ? '#4ADE80' : '#f87171' ?>;">
              <?= $pt['points'] > 0 ? '+' : '' ?><?= number_format($pt['points']) ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($myCourses)): ?>
      <div class="card">
        <div class="card-title">Meus cursos</div>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
          <?php foreach ($myCourses as $c): ?>
          <div style="display:flex;align-items:center;gap:0.75rem;">
            <div style="width:40px;height:40px;border-radius:8px;background:var(--surface-2);display:grid;place-items:center;font-size:1.2rem;flex-shrink:0;">📚</div>
            <div style="flex:1;">
              <div style="font-size:0.88rem;font-weight:600;"><?= htmlspecialchars($c['title']) ?></div>
              <div style="font-size:0.75rem;color:var(--text-muted);"><?= ucfirst($c['access_type']) ?> · <?= date('d/m/Y', strtotime($c['granted_at'])) ?></div>
            </div>
            <a href="<?= BASE_URL ?>/courses/<?= $c['id'] ?>" class="btn btn-secondary btn-sm">Acessar</a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
