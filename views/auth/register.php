<?php
// views/auth/register.php
$siteName   = Setting::get('site_name', 'ViralNest');
$siteTagline= Setting::get('site_tagline', 'A comunidade que cresce com você');
$siteLogo   = Setting::get('site_logo', '');
$primaryClr = Setting::get('primary_color', '#F59E0B');
$accentClr  = Setting::get('accent_color', '#FBBF24');
$darkBg     = Setting::get('dark_bg', '#0F172A');
$flash      = Auth::getFlash();
$inviteCode = $_GET['invite'] ?? '';
$requireInvite = $requireInvite ?? false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar conta — <?= htmlspecialchars($siteName) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <style>:root { --cfg-primary:<?= $primaryClr ?>; --cfg-accent:<?= $accentClr ?>; --cfg-dark-bg:<?= $darkBg ?>; }</style>
</head>
<body>
<div class="auth-bg">
  <div class="auth-card animate-in" style="max-width:520px;">
    <div class="auth-logo">
      <?php if ($siteLogo): ?>
        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo">
      <?php else: ?>
        <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--accent));display:grid;place-items:center;font-size:1.8rem;">🚀</div>
      <?php endif; ?>
      <h1><?= htmlspecialchars($siteName) ?></h1>
      <p><?= $requireInvite ? '🔒 Entrada apenas por convite' : '🎉 Vagas disponíveis agora!' ?></p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if (!Setting::bool('allow_registration')): ?>
      <div class="alert alert-warning" style="text-align:center;">
        <strong>Cadastros temporariamente fechados.</strong><br>
        Peça um convite a um membro da comunidade.
      </div>
      <div class="text-center mt-2">
        <a href="<?= BASE_URL ?>/login" class="btn btn-secondary">← Voltar para o login</a>
      </div>
    <?php else: ?>

    <?php if ($inviteCode): ?>
      <div class="invite-box" style="margin-bottom:1.25rem;text-align:center;">
        🎁 <strong>Convite válido!</strong> Você foi convidado para a comunidade.
      </div>
    <?php elseif ($requireInvite): ?>
      <div class="alert alert-warning">
        <strong>Convite necessário.</strong> O ciclo de entrada gratuita foi encerrado.
        Solicite um convite a um membro da comunidade.
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/register">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

      <div class="form-group">
        <label class="form-label">Nome completo</label>
        <input type="text" name="name" class="form-control" placeholder="Seu nome" required
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-control" placeholder="seu@email.com" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">WhatsApp (para notificações)</label>
        <input type="tel" name="phone" class="form-control" placeholder="+55 11 91234-5678"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Senha</label>
        <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
      </div>

      <div class="form-group">
        <label class="form-label">Confirmar senha</label>
        <input type="password" name="password_confirm" class="form-control" placeholder="Repita a senha" required>
      </div>

      <div class="form-group">
        <label class="form-label">Código de convite <?= $requireInvite ? '<span style="color:#f87171">*</span>' : '(opcional)' ?></label>
        <input type="text" name="invite_code" class="form-control"
               placeholder="Ex: ABC12345"
               value="<?= htmlspecialchars($inviteCode ?: ($_POST['invite_code'] ?? '')) ?>"
               <?= $requireInvite ? 'required' : '' ?>>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:0.5rem;">
        Criar minha conta 🚀
      </button>
    </form>

    <div class="text-center mt-2" style="color:var(--text-muted);font-size:0.88rem;">
      Já tem conta?
      <a href="<?= BASE_URL ?>/login" style="color:var(--primary);font-weight:600;">Entrar</a>
    </div>

    <?php endif; ?>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
