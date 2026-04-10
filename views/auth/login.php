<?php
// views/auth/login.php
$siteName   = Setting::get('site_name', 'ViralNest');
$siteTagline= Setting::get('site_tagline', 'A comunidade que cresce com você');
$siteLogo   = Setting::get('site_logo', '');
$primaryClr = Setting::get('primary_color', '#F59E0B');
$accentClr  = Setting::get('accent_color', '#FBBF24');
$darkBg     = Setting::get('dark_bg', '#0F172A');
$flash      = Auth::getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar — <?= htmlspecialchars($siteName) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <style>:root { --cfg-primary:<?= $primaryClr ?>; --cfg-accent:<?= $accentClr ?>; --cfg-dark-bg:<?= $darkBg ?>; }</style>
</head>
<body>
<div class="auth-bg">
  <div class="auth-card animate-in">
    <div class="auth-logo">
      <?php if ($siteLogo): ?>
        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo">
      <?php else: ?>
        <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--accent));display:grid;place-items:center;font-size:1.8rem;">🚀</div>
      <?php endif; ?>
      <h1><?= htmlspecialchars($siteName) ?></h1>
      <p><?= htmlspecialchars($siteTagline) ?></p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom:1rem;"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/login">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

      <div class="form-group">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-control" placeholder="seu@email.com" required autofocus
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Senha</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:0.5rem;">
        Entrar na comunidade
      </button>
    </form>

    <div class="text-center mt-2" style="color:var(--text-muted);font-size:0.88rem;">
      Não tem conta?
      <a href="<?= BASE_URL ?>/register" style="color:var(--primary);font-weight:600;">Criar conta</a>
    </div>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
