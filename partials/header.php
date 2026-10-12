<?php
/**
 * OWO - Shared HTML Header Partial
 * Include at the top of every customer-facing page.
 * $pageTitle should be set before including this file.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cart.php';
require_once __DIR__ . '/../includes/helpers.php';

$flash     = getFlash();
$cartCount = cartCount();
$pageTitle = $pageTitle ?? 'OWO — Modern Streetwear';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($metaDesc ?? 'OWO — Minimal modern streetwear. Shop tees, hoodies, cargos and more.') ?>">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<!-- ── NAVBAR ─────────────────────────────────────────────────── -->
<header class="navbar" id="navbar">
  <div class="container navbar__inner">

    <a href="<?= APP_URL ?>/" class="navbar__logo">OWO</a>

    <nav class="navbar__links" id="navLinks">
      <a href="<?= APP_URL ?>/"          class="nav-link">Home</a>
      <a href="<?= APP_URL ?>/shop.php"  class="nav-link">Shop</a>
      <?php if (isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/orders.php" class="nav-link">My Orders</a>
        <a href="<?= APP_URL ?>/logout.php" class="nav-link">Logout</a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login.php"    class="nav-link">Login</a>
        <a href="<?= APP_URL ?>/register.php" class="nav-link">Register</a>
      <?php endif; ?>
    </nav>

    <div class="navbar__actions">
      <a href="<?= APP_URL ?>/cart.php" class="cart-icon" id="cartIcon" aria-label="Cart">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
      <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>
</header>

<main class="page-main">

<?php if ($flash): ?>
<div class="flash flash--<?= e($flash['type']) ?>" role="alert" id="flashMsg">
  <span><?= e($flash['message']) ?></span>
  <button class="flash__close" onclick="this.parentElement.remove()" aria-label="Close">×</button>
</div>
<?php endif; ?>
