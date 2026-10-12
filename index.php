<?php
/**
 * OWO — Homepage
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = 'OWO — Wear What You Mean';
$metaDesc  = 'OWO is a minimal modern streetwear brand. Shop tees, hoodies, cargos and more.';

// Fetch 4 featured products
$db       = getDB();
$featured = $db->query("SELECT * FROM products WHERE is_deleted = 0 ORDER BY created_at DESC LIMIT 8")->fetchAll();

require_once __DIR__ . '/partials/header.php';
?>

<!-- ── MARQUEE ──────────────────────────────────────────────── -->
<div class="marquee-wrap">
  <div class="marquee-track">
    <?php for ($i = 0; $i < 2; $i++): ?>
      <span>New Arrivals</span>
      <span><em>OWO</em></span>
      <span>Free Shipping Over Rs. 3000</span>
      <span><em>OWO</em></span>
      <span>Minimal. Modern. Real.</span>
      <span><em>OWO</em></span>
      <span>Wear What You Mean</span>
      <span><em>OWO</em></span>
    <?php endfor; ?>
  </div>
</div>

<!-- ── HERO ─────────────────────────────────────────────────── -->
<section class="hero">
  <div class="container">
    <div class="hero__content">
      <p class="hero__eyebrow label">New Season — SS 2025</p>
      <h1 class="hero__title">
        Wear<br>What You<br><em>Mean.</em>
      </h1>
      <p class="hero__sub">
        Minimal silhouettes. Considered fabrics. Clothes built for people who move through the world with intention.
      </p>
      <div class="hero__cta">
        <a href="<?= APP_URL ?>/shop.php" class="btn btn-accent btn-lg">Shop Now</a>
        <a href="<?= APP_URL ?>/shop.php?category=new" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff;">New Arrivals</a>
      </div>
    </div>
  </div>
  <div class="hero__scroll">
    <div class="hero__scroll-line"></div>
    <span>Scroll</span>
  </div>
</section>

<!-- ── FEATURED PRODUCTS ─────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <p class="label">The Collection</p>
      <h2>Featured Pieces</h2>
      <p>Each item is designed with restraint — nothing extra, nothing missing.</p>
    </div>

    <?php if (empty($featured)): ?>
      <div class="empty-state">
        <h3>No products yet.</h3>
        <p>Check back soon — we're stocking up.</p>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($featured as $product): ?>
          <a href="<?= APP_URL ?>/product.php?id=<?= $product['id'] ?>" class="product-card">
            <div class="product-card__img">
              <img
                src="<?= e(productImageUrl($product['image_path'])) ?>"
                alt="<?= e($product['name']) ?>"
                loading="lazy"
              >
              <span class="product-card__category"><?= e(ucfirst($product['category'])) ?></span>
            </div>
            <div class="product-card__body">
              <h3 class="product-card__name"><?= e($product['name']) ?></h3>
              <p class="product-card__price"><?= formatPrice((float)$product['price']) ?></p>
            </div>
            <div class="product-card__footer">
              <span class="btn btn-outline btn-sm btn-full" style="pointer-events:none;">View Product</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="text-center mt-24">
      <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary btn-lg">View All Products</a>
    </div>
  </div>
</section>

<!-- ── BRAND PILLARS ─────────────────────────────────────────── -->
<section class="section" style="background:var(--grey-50);">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:40px;">
      <?php
        $pillars = [
          ['icon'=>'✦','title'=>'Intentional Design','desc'=>'Every cut and detail is deliberate. No excess.'],
          ['icon'=>'◈','title'=>'Quality Materials','desc'=>'220gsm+ cotton, recycled nylon, washed twill.'],
          ['icon'=>'◉','title'=>'Limited Drops','desc'=>'We produce in small quantities. When it\'s gone, it\'s gone.'],
          ['icon'=>'◆','title'=>'Built to Last','desc'=>'Clothes that outlast trends and stay in your wardrobe.'],
        ];
        foreach ($pillars as $p):
      ?>
        <div style="padding:32px;background:#fff;border-radius:12px;border:1px solid var(--grey-100);">
          <div style="font-size:1.5rem;margin-bottom:16px;color:var(--accent);"><?= $p['icon'] ?></div>
          <h3 style="margin-bottom:8px;font-size:1rem;"><?= e($p['title']) ?></h3>
          <p style="color:var(--grey-600);font-size:.875rem;line-height:1.7;"><?= e($p['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA BANNER ────────────────────────────────────────────── -->
<section class="section" style="background:var(--black);color:var(--white);text-align:center;">
  <div class="container">
    <p class="label" style="color:var(--accent);">Limited Stock</p>
    <h2 style="color:var(--white);margin:12px 0 16px;">The OWO Hoodie is back.</h2>
    <p style="color:var(--grey-400);margin-bottom:32px;max-width:400px;margin-left:auto;margin-right:auto;">
      Heavyweight fleece, dropped shoulders, kangaroo pocket. Available in sizes S–XXL while stocks last.
    </p>
    <a href="<?= APP_URL ?>/shop.php?category=hoodies" class="btn btn-accent btn-lg">Shop Hoodies</a>
  </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
