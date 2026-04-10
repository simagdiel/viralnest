<?php
// views/plans/index.php
include BASE_PATH_DIR . '/views/layout/header.php';
$billingLabels = ['monthly'=>'/mês','quarterly'=>'/trimestre','annual'=>'/ano','lifetime'=>'vitalício'];
?>
<div class="page">
  <div class="page-header" style="text-align:center;margin-bottom:2rem;">
    <h1 class="page-title" style="font-size:2rem;">💎 Planos & Benefícios</h1>
    <p class="page-subtitle" style="font-size:1rem;">Acelere seu crescimento com benefícios exclusivos</p>
  </div>

  <?php if ($activePlan): ?>
  <div class="alert alert-info" style="text-align:center;margin-bottom:1.5rem;">
    🌟 Você está no plano <strong><?= htmlspecialchars($activePlan['name']) ?></strong>
    <?php if ($activePlan['expires_at'] && $activePlan['billing_cycle'] !== 'lifetime'): ?>
      — válido até <?= date('d/m/Y', strtotime($activePlan['expires_at'])) ?>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="plans-grid">
    <?php foreach ($plans as $plan):
      $features = json_decode($plan['features'] ?? '[]', true);
      $isCurrent = $activePlan && $activePlan['id'] == $plan['id'];
      $isPopular = $plan['slug'] === 'mentor';
    ?>
    <div class="plan-card <?= $isPopular ? 'featured' : '' ?>">
      <?php if ($isPopular): ?>
        <div class="plan-badge-top">⭐ Mais Popular</div>
      <?php endif; ?>

      <div style="display:flex;align-items:center;gap:0.75rem;">
        <div style="width:40px;height:40px;border-radius:10px;background:<?= htmlspecialchars($plan['badge_color']) ?>22;display:grid;place-items:center;font-size:1.3rem;">
          <?= $plan['slug'] === 'free' ? '🌱' : ($plan['slug'] === 'explorer' ? '🔭' : ($plan['slug'] === 'mentor' ? '📚' : '👑')) ?>
        </div>
        <div class="plan-name"><?= htmlspecialchars($plan['name']) ?></div>
      </div>

      <div>
        <?php if ($plan['price'] <= 0): ?>
          <div class="plan-price">Grátis</div>
        <?php else: ?>
          <div class="plan-price">R$ <?= number_format($plan['price'], 2, ',', '.') ?><span><?= $billingLabels[$plan['billing_cycle']] ?? '' ?></span></div>
        <?php endif; ?>
      </div>

      <ul class="plan-features">
        <?php foreach ($features as $f): ?>
          <li><?= htmlspecialchars($f) ?></li>
        <?php endforeach; ?>
        <?php if ($plan['course_discount'] > 0): ?>
          <li><?= number_format($plan['course_discount'], 0) ?>% desconto em cursos</li>
        <?php endif; ?>
        <?php if ($plan['points_multiplier'] > 1): ?>
          <li><?= number_format($plan['points_multiplier'], 0) ?>x pontos em todas as ações</li>
        <?php endif; ?>
      </ul>

      <?php if ($isCurrent): ?>
        <div class="btn btn-success btn-block" style="text-align:center;cursor:default;">✓ Plano atual</div>
      <?php elseif ($plan['price'] <= 0): ?>
        <form method="POST" action="<?= BASE_URL ?>/plans/<?= $plan['id'] ?>/subscribe">
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <input type="hidden" name="gateway" value="free">
          <button type="submit" class="btn btn-secondary btn-block">Ativar gratuito</button>
        </form>
      <?php else: ?>
        <?php if (!empty($activeGateways)): ?>
        <div>
          <form method="POST" action="<?= BASE_URL ?>/plans/<?= $plan['id'] ?>/subscribe">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <select name="gateway" class="form-control" style="margin-bottom:0.75rem;font-size:0.85rem;">
              <?php foreach ($activeGateways as $gwy): ?>
                <option value="<?= $gwy['gateway'] ?>"><?= ucfirst($gwy['gateway']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-block">Assinar agora →</button>
          </form>
        </div>
        <?php else: ?>
          <div class="btn btn-secondary btn-block" style="text-align:center;cursor:default;font-size:0.82rem;">Pagamento indisponível</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Tabela comparativa -->
  <div class="card" style="margin-top:2rem;">
    <div class="card-title">Comparativo de planos</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Benefício</th>
            <?php foreach ($plans as $p): ?>
              <th style="text-align:center;"><?= htmlspecialchars($p['name']) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Desconto em cursos</td>
            <?php foreach ($plans as $p): ?>
              <td style="text-align:center;"><?= $p['course_discount'] > 0 ? $p['course_discount'].'%' : '—' ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td>Multiplicador de pontos</td>
            <?php foreach ($plans as $p): ?>
              <td style="text-align:center;"><?= $p['points_multiplier'] ?>x</td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td>Grupos simultâneos</td>
            <?php foreach ($plans as $p): ?>
              <td style="text-align:center;"><?= $p['max_groups'] >= 99 ? 'Ilimitado' : $p['max_groups'] ?></td>
            <?php endforeach; ?>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
