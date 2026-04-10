<?php
// views/layout/header.php
$siteName   = Setting::get('site_name', 'ViralNest');
$siteTagline= Setting::get('site_tagline', 'A comunidade que cresce com você');
$siteLogo   = Setting::get('site_logo', '');
$primaryClr = Setting::get('primary_color', '#F59E0B');
$secondaryClr= Setting::get('secondary_color', '#1E293B');
$accentClr  = Setting::get('accent_color', '#FBBF24');
$darkBg     = Setting::get('dark_bg', '#0F172A');

$user = Auth::user();
$unreadNotif = $user ? (new User())->countUnreadNotifications($user['id']) : 0;
$flash = Auth::getFlash();

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath    = defined('BASE_PATH') ? BASE_PATH : '';
function navActive(string $path): string {
    global $currentPath, $basePath;
    return (strpos($currentPath, $basePath . $path) !== false) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= Auth::csrfToken() ?>">
  <title><?= htmlspecialchars($pageTitle ?? $siteName) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
  <style>
    :root {
      --cfg-primary: <?= htmlspecialchars($primaryClr) ?>;
      --cfg-secondary: <?= htmlspecialchars($secondaryClr) ?>;
      --cfg-accent: <?= htmlspecialchars($accentClr) ?>;
      --cfg-dark-bg: <?= htmlspecialchars($darkBg) ?>;
    }
  </style>
</head>
<body>
<div class="app-layout">

  <!-- Sidebar Overlay (mobile) -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <?php if ($siteLogo): ?>
        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" class="sidebar-logo-img">
      <?php else: ?>
        <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--accent));display:grid;place-items:center;font-size:1.1rem;">🚀</div>
      <?php endif; ?>
      <span class="sidebar-logo-text"><?= htmlspecialchars($siteName) ?></span>
    </div>

    <nav class="sidebar-nav">
      <span class="nav-section-title">Principal</span>
      <a href="<?= BASE_URL ?>/dashboard" class="nav-item <?= navActive('/dashboard') ?>">
        <?= svgIcon('grid') ?> Dashboard
      </a>
      <a href="<?= BASE_URL ?>/invite" class="nav-item <?= navActive('/invite') ?>">
        <?= svgIcon('share') ?> Meus Convites
      </a>
      <a href="<?= BASE_URL ?>/ranking" class="nav-item <?= navActive('/ranking') ?>">
        <?= svgIcon('trophy') ?> Ranking
      </a>

      <span class="nav-section-title">Comunidade</span>
      <a href="<?= BASE_URL ?>/groups" class="nav-item <?= navActive('/groups') ?>">
        <?= svgIcon('users') ?> Grupos
      </a>

      <span class="nav-section-title">Aprendizado</span>
      <a href="<?= BASE_URL ?>/courses" class="nav-item <?= navActive('/courses') ?>">
        <?= svgIcon('book') ?> Cursos
      </a>
      <a href="<?= BASE_URL ?>/plans" class="nav-item <?= navActive('/plans') ?>">
        <?= svgIcon('star') ?> Planos
      </a>

      <span class="nav-section-title">Conta</span>
      <a href="<?= BASE_URL ?>/profile" class="nav-item <?= navActive('/profile') ?>">
        <?= svgIcon('user') ?> Perfil
      </a>
      <a href="<?= BASE_URL ?>/logout" class="nav-item" data-confirm="Deseja sair?">
        <?= svgIcon('logout') ?> Sair
      </a>
    </nav>

    <?php if ($user): ?>
    <div class="sidebar-user">
      <div class="sidebar-user-card">
        <img src="<?= $user['avatar'] ?: BASE_URL . '/assets/img/default-avatar.svg' ?>"
             alt="Avatar" class="sidebar-avatar">
        <div>
          <div class="sidebar-user-name"><?= htmlspecialchars($user['name']) ?></div>
          <div class="sidebar-user-level">⭐ <?= ucfirst($user['level']) ?></div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </aside>

  <!-- Main -->
  <div class="main-content">
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:1rem;">
        <button class="hamburger" id="hamburger" aria-label="Menu">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
        <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? $siteName) ?></span>
      </div>
      <div class="topbar-actions">
        <?php if ($user): ?>
        <div style="position:relative;">
          <button class="notif-btn" id="notifBtn" title="Notificações">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <?php if ($unreadNotif > 0): ?><span class="notif-dot"></span><?php endif; ?>
          </button>
          <div id="notifDropdown" style="display:none;position:absolute;right:0;top:calc(100%+8px);width:300px;background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow);z-index:200;overflow:hidden;">
            <div style="padding:0.75rem 1rem;border-bottom:1px solid var(--border);font-weight:600;font-size:0.85rem;">Notificações</div>
            <div style="max-height:300px;overflow-y:auto;padding:0.5rem;">
              <?php
              $notifs = (new User())->getNotifications($user['id'], 8);
              if (empty($notifs)): ?>
                <p style="text-align:center;color:var(--text-muted);padding:1rem;font-size:0.85rem;">Nenhuma notificação</p>
              <?php else: foreach ($notifs as $n): ?>
                <div style="padding:0.6rem 0.5rem;border-radius:8px;<?= !$n['is_read'] ? 'background:rgba(245,158,11,0.06)' : '' ?>">
                  <div style="font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($n['title']) ?></div>
                  <div style="font-size:0.78rem;color:var(--text-muted);"><?= htmlspecialchars($n['message']) ?></div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:0.6rem;">
          <img src="<?= $user['avatar'] ?: BASE_URL . '/assets/img/default-avatar.svg' ?>"
               alt="Avatar" style="width:32px;height:32px;border-radius:50%;border:2px solid var(--primary);">
          <span style="font-size:0.85rem;font-weight:600;"><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </header>

    <!-- Flash messages -->
    <?php if ($flash): ?>
    <div class="flash-alert alert alert-<?= $flash['type'] ?>" style="margin:1rem 1.75rem 0;">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <div class="page-content">
<?php

// SVG Icons helper
function svgIcon(string $name): string {
    $icons = [
        'grid'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        'share'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
        'trophy' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 3 8 6 3 6 3 11 8 11 8 21"/><polyline points="16 3 16 6 21 6 21 11 16 11 16 21"/><line x1="8" y1="16" x2="16" y2="16"/></svg>',
        'users'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'book'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'star'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'user'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'logout' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>
