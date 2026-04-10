<?php // admin/views/dashboard.php ?>
<div class="a-metrics">
  <div class="a-metric">
    <div class="a-metric-icon">👥</div>
    <div class="a-metric-val"><?= number_format($stats['users']) ?></div>
    <div class="a-metric-label">Usuários totais</div>
  </div>
  <div class="a-metric">
    <div class="a-metric-icon">✅</div>
    <div class="a-metric-val"><?= number_format($stats['active_users']) ?></div>
    <div class="a-metric-label">Usuários ativos</div>
  </div>
  <div class="a-metric">
    <div class="a-metric-icon">🆕</div>
    <div class="a-metric-val"><?= number_format($stats['today_users']) ?></div>
    <div class="a-metric-label">Novos hoje</div>
  </div>
  <div class="a-metric">
    <div class="a-metric-icon">⭐</div>
    <div class="a-metric-val"><?= number_format($stats['points_issued']) ?></div>
    <div class="a-metric-label">Pontos emitidos</div>
  </div>
  <div class="a-metric">
    <div class="a-metric-icon">🚀</div>
    <div class="a-metric-val"><?= number_format($stats['invites_used']) ?></div>
    <div class="a-metric-label">Convites usados</div>
  </div>
  <div class="a-metric">
    <div class="a-metric-icon">💎</div>
    <div class="a-metric-val"><?= number_format($stats['active_subs']) ?></div>
    <div class="a-metric-label">Assinaturas ativas</div>
  </div>
  <div class="a-metric">
    <div class="a-metric-icon">💰</div>
    <div class="a-metric-val">R$ <?= number_format($stats['revenue'], 2, ',', '.') ?></div>
    <div class="a-metric-label">Receita total</div>
  </div>
  <div class="a-metric">
    <div class="a-metric-icon">📚</div>
    <div class="a-metric-val"><?= number_format($stats['courses']) ?></div>
    <div class="a-metric-label">Cursos</div>
  </div>
</div>

<?php if ($cycle): ?>
<div class="a-card a-mb-2">
  <div class="a-flex-between">
    <div>
      <div class="a-card-title">Ciclo Ativo: <?= htmlspecialchars($cycle['name']) ?></div>
      <div class="a-text-muted" style="font-size:0.82rem;"><?= number_format($cycle['current_users']) ?> / <?= number_format($cycle['max_users']) ?> vagas preenchidas</div>
    </div>
    <div style="text-align:right;">
      <?php $pct = $cycle['max_users'] > 0 ? round($cycle['current_users']/$cycle['max_users']*100) : 0; ?>
      <div style="font-family:var(--font-head);font-size:1.5rem;font-weight:800;color:var(--primary);"><?= $pct ?>%</div>
      <a href="<?= BASE_URL ?>/admin/cycles" class="a-btn a-btn-secondary a-btn-sm">Gerenciar</a>
    </div>
  </div>
  <div style="margin-top:0.75rem;background:var(--surface-2);border-radius:99px;height:8px;overflow:hidden;">
    <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,var(--primary),#FBBF24);border-radius:99px;transition:width 1s;"></div>
  </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
  <div class="a-card">
    <div class="a-card-title">Usuários recentes</div>
    <table class="a-table">
      <thead><tr><th>Nome</th><th>E-mail</th><th>Nível</th><th>Data</th></tr></thead>
      <tbody>
        <?php foreach ($recentUsers as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td style="color:var(--text-muted);font-size:0.8rem;"><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="a-badge a-badge-info"><?= ucfirst($u['level']) ?></span></td>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="a-card">
    <div class="a-card-title">Transações recentes</div>
    <table class="a-table">
      <thead><tr><th>Usuário</th><th>Tipo</th><th>Valor</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($recentTx as $tx): ?>
        <tr>
          <td style="font-size:0.82rem;"><?= htmlspecialchars($tx['name']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted);"><?= ucfirst($tx['type']) ?></td>
          <td style="color:var(--primary);font-weight:600;">R$ <?= number_format($tx['amount'],2,',','.') ?></td>
          <td><span class="a-badge <?= $tx['status']==='paid'?'a-badge-success':($tx['status']==='failed'?'a-badge-danger':'a-badge-warning') ?>"><?= $tx['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentTx)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--text-muted);">Nenhuma transação</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
