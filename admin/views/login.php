<?php // admin/views/login.php ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — <?= htmlspecialchars($siteName) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body style="display:grid;place-items:center;min-height:100vh;background:var(--dark-bg);background-image:radial-gradient(ellipse 70% 50% at 50% -10%,rgba(245,158,11,0.1),transparent);">
  <div style="width:100%;max-width:400px;padding:1.5rem;">
    <div class="a-card" style="border-radius:16px;">
      <div style="text-align:center;margin-bottom:1.75rem;">
        <div style="width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,var(--primary),#FBBF24);display:grid;place-items:center;font-size:1.5rem;margin:0 auto 0.75rem;">🚀</div>
        <div style="font-family:var(--font-head);font-size:1.4rem;font-weight:800;color:var(--primary);"><?= htmlspecialchars($siteName) ?></div>
        <div style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem;">Painel Administrativo</div>
      </div>
      <?php if ($flash): ?>
        <div class="a-alert a-alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
      <?php endif; ?>
      <form method="POST" action="<?= BASE_URL ?>/admin/login">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <div class="a-form-group">
          <label class="a-form-label">E-mail</label>
          <input type="email" name="email" class="a-form-control" placeholder="admin@email.com" required autofocus>
        </div>
        <div class="a-form-group">
          <label class="a-form-label">Senha</label>
          <input type="password" name="password" class="a-form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="a-btn a-btn-primary" style="width:100%;margin-top:0.5rem;padding:0.75rem;">Entrar no painel</button>
      </form>
      <div style="text-align:center;margin-top:1rem;font-size:0.82rem;color:var(--text-muted);">
        <a href="<?= BASE_URL ?>/" style="color:var(--primary);">← Voltar ao site</a>
      </div>
    </div>
  </div>
</body>
</html>
