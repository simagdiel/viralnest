<?php
// admin/views/layout.php
$siteName   = Setting::get('site_name', 'ViralNest');
$primaryClr = Setting::get('primary_color', '#F59E0B');
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function aNavActive(string $slug): string {
    global $currentUri;
    return (strpos($currentUri, '/admin/' . $slug) !== false) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — <?= htmlspecialchars($siteName) ?> Admin</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    :root { --primary: <?= htmlspecialchars($primaryClr) ?>; }
  </style>
</head>
<body>

<div style="display:flex;min-height:100vh;">
  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-logo">
      <span style="font-size:1.3rem;">🚀</span>
      <span><?= htmlspecialchars($siteName) ?></span>
      <span style="font-size:0.65rem;background:rgba(245,158,11,0.15);color:var(--primary);padding:2px 8px;border-radius:99px;margin-left:auto;">ADMIN</span>
    </div>

    <nav class="admin-nav">
      <div class="admin-nav-section">Principal</div>
      <a href="<?= BASE_URL ?>/admin/" class="<?= (aNavActive('') || substr($currentUri,-7)==='/admin/' || substr($currentUri,-6)==='/admin') ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>

      <div class="admin-nav-section">Comunidade</div>
      <a href="<?= BASE_URL ?>/admin/users" class="<?= aNavActive('users') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Usuários
      </a>
      <a href="<?= BASE_URL ?>/admin/invites" class="<?= aNavActive('invites') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        Convites
      </a>
      <a href="<?= BASE_URL ?>/admin/cycles" class="<?= aNavActive('cycles') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Ciclos
      </a>
      <a href="<?= BASE_URL ?>/admin/groups" class="<?= aNavActive('groups') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Grupos
      </a>
      <a href="<?= BASE_URL ?>/admin/ranking" class="<?= aNavActive('ranking') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 3 8 6 3 6 3 11 8 11 8 21"/><polyline points="16 3 16 6 21 6 21 11 16 11 16 21"/><line x1="8" y1="16" x2="16" y2="16"/></svg>
        Ranking
      </a>

      <div class="admin-nav-section">Educação</div>
      <a href="<?= BASE_URL ?>/admin/courses" class="<?= aNavActive('courses') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        Cursos
      </a>
      <a href="<?= BASE_URL ?>/admin/plans" class="<?= aNavActive('plans') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Planos
      </a>

      <div class="admin-nav-section">Financeiro</div>
      <a href="<?= BASE_URL ?>/admin/gateways" class="<?= aNavActive('gateways') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        Gateways
      </a>
      <a href="<?= BASE_URL ?>/admin/transactions" class="<?= aNavActive('transactions') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Transações
      </a>

      <div class="admin-nav-section">Sistema</div>
      <a href="<?= BASE_URL ?>/admin/whatsapp" class="<?= aNavActive('whatsapp') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        WhatsApp
      </a>
      <a href="<?= BASE_URL ?>/admin/settings" class="<?= aNavActive('settings') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Configurações
      </a>
      <a href="<?= BASE_URL ?>/admin/logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sair
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div class="a-flex" style="gap:0.75rem;">
        <button onclick="document.getElementById('adminSidebar').classList.toggle('open')" style="background:none;border:none;color:var(--text);cursor:pointer;display:none;" id="adminHamburger">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <span class="admin-topbar-title"><?= htmlspecialchars($pageTitle ?? 'Admin') ?></span>
      </div>
      <div class="a-flex" style="gap:0.75rem;">
        <a href="<?= BASE_URL ?>/" target="_blank" class="a-btn a-btn-secondary a-btn-sm">Ver site ↗</a>
        <div style="font-size:0.8rem;color:var(--text-muted);">
          <?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?>
        </div>
      </div>
    </header>

    <div class="admin-page">
      <?php if ($flash): ?>
        <div class="a-alert a-alert-<?= $flash['type'] ?> a-animate" style="margin-bottom:1rem;"><?= htmlspecialchars($flash['msg']) ?></div>
      <?php endif; ?>

      <?php include $viewFile; ?>
    </div>
  </div>
</div>

<script>
// Show hamburger on mobile
if (window.innerWidth <= 900) document.getElementById('adminHamburger').style.display='block';
window.addEventListener('resize', () => {
  document.getElementById('adminHamburger').style.display = window.innerWidth <= 900 ? 'block' : 'none';
});

// Settings tabs
document.querySelectorAll('.settings-nav-item').forEach(item => {
  item.addEventListener('click', (e) => {
    e.preventDefault();
    document.querySelectorAll('.settings-nav-item').forEach(i => i.classList.remove('active'));
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    item.classList.add('active');
    const panel = document.getElementById('panel-' + item.dataset.panel);
    if (panel) panel.classList.add('active');
  });
});

// Auto dismiss alerts
setTimeout(() => {
  document.querySelectorAll('.a-alert').forEach(el => {
    el.style.transition = 'opacity 0.5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  });
}, 4000);

// Confirm deletes
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => { if (!confirm(el.dataset.confirm)) e.preventDefault(); });
});
</script>
</body>
</html>
