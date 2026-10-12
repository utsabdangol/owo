<?php
/**
 * OWO Admin - Shared Layout Partial
 * Sets up the sidebar + topbar wrapper.
 * Requires: $pageTitle, $activePage to be set before include.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireAdmin();

$admin      = $_SESSION['admin_username'] ?? 'Admin';
$pageTitle  = $pageTitle ?? 'Admin — OWO';
$activePage = $activePage ?? '';

$navItems = [
    'dashboard' => ['label' => 'Dashboard',   'url' => 'index.php',    'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
    'products'  => ['label' => 'Products',    'url' => 'products.php', 'icon' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8L2 7h20z"/>'],
    'orders'    => ['label' => 'Orders',      'url' => 'orders.php',   'icon' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">

  <!-- ── Sidebar ─────────────────────────────────────────── -->
  <aside class="sidebar" id="adminSidebar">
    <div class="sidebar__header">
      <div class="sidebar__logo">OWO</div>
      <div class="sidebar__sub">Admin Panel</div>
    </div>

    <nav class="sidebar__nav">
      <span class="nav-section-label">Main</span>
      <?php foreach ($navItems as $key => $item): ?>
        <a
          href="<?= APP_URL ?>/admin/<?= $item['url'] ?>"
          class="nav-item <?= $activePage === $key ? 'active' : '' ?>"
          id="nav-<?= $key ?>"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <?= $item['icon'] ?>
          </svg>
          <?= $item['label'] ?>
        </a>
      <?php endforeach; ?>

      <span class="nav-section-label" style="margin-top:12px;">Store</span>
      <a href="<?= APP_URL ?>/" target="_blank" class="nav-item" id="nav-storefront">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
        </svg>
        View Storefront
      </a>
    </nav>

    <div class="sidebar__footer">
      <div class="sidebar__user">
        <div class="user-avatar"><?= strtoupper(substr($admin, 0, 1)) ?></div>
        <div>
          <p style="color:var(--white);font-weight:500;"><?= e($admin) ?></p>
          <a href="<?= APP_URL ?>/admin/logout.php" style="font-size:.72rem;color:var(--grey-400);">Sign out</a>
        </div>
      </div>
    </div>
  </aside>

  <!-- ── Main ────────────────────────────────────────────── -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="btn btn-outline btn-sm" id="sidebarToggle" style="display:none;" aria-label="Toggle menu">☰</button>
        <h1><?= e($pageTitle) ?></h1>
      </div>
      <div class="topbar-actions">
        <?php $flash = getFlash(); ?>
      </div>
    </header>

    <main class="admin-content">
      <?php if ($flash): ?>
        <div class="flash flash--<?= e($flash['type']) ?>" id="adminFlash">
          <span><?= e($flash['message']) ?></span>
          <button class="flash__close" onclick="this.parentElement.remove()">×</button>
        </div>
      <?php endif; ?>
