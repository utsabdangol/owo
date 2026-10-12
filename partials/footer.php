<?php
/**
 * OWO - Shared HTML Footer Partial
 */
?>
</main><!-- /page-main -->

<!-- ── FOOTER ────────────────────────────────────────────────── -->
<footer class="footer">
  <div class="container footer__inner">

    <div class="footer__brand">
      <span class="footer__logo">OWO</span>
      <p class="footer__tagline">Wear what you mean.</p>
    </div>

    <nav class="footer__links">
      <a href="<?= APP_URL ?>/"         >Home</a>
      <a href="<?= APP_URL ?>/shop.php" >Shop</a>
      <a href="<?= APP_URL ?>/login.php">Account</a>
      <a href="<?= APP_URL ?>/cart.php" >Cart</a>
    </nav>

    <div class="footer__social">
      <a href="#" aria-label="Instagram">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
        </svg>
      </a>
      <a href="#" aria-label="TikTok">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
          <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.96a8.17 8.17 0 004.78 1.52V7.03a4.85 4.85 0 01-1.01-.34z"/>
        </svg>
      </a>
    </div>

  </div>
  <div class="footer__bottom">
    <p>&copy; <?= date('Y') ?> OWO. All rights reserved.</p>
  </div>
</footer>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
