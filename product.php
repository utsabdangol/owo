<?php
/**
 * OWO — Product Detail Page
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/cart.php';

$db = getDB();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { redirect(APP_URL . '/shop.php'); }

$stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND is_deleted = 0");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(APP_URL . '/shop.php');
}

// Handle Add to Cart POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $size = trim($_POST['size'] ?? '');
    $qty  = max(1, (int) ($_POST['qty'] ?? 1));

    if (!$size) {
        setFlash('error', 'Please select a size.');
    } elseif ($product['stock'] < 1) {
        setFlash('error', 'This product is out of stock.');
    } else {
        cartAdd(
            (int) $product['id'],
            $product['name'],
            (float) $product['price'],
            $size,
            $qty,
            $product['image_path'] ?? ''
        );
        setFlash('success', '"' . $product['name'] . '" added to cart.');
        redirect(APP_URL . '/product.php?id=' . $id);
    }
}

// Related products
$related = $db->prepare("SELECT * FROM products WHERE category = :cat AND id != :id AND is_deleted = 0 LIMIT 4");
$related->execute([':cat' => $product['category'], ':id' => $id]);
$relatedProducts = $related->fetchAll();

$sizes = parseSizes($product['sizes']);

$pageTitle = e($product['name']) . ' — OWO';
$metaDesc  = substr(strip_tags($product['description'] ?? ''), 0, 155);
require_once __DIR__ . '/partials/header.php';
?>

<div class="container">

  <!-- ── Breadcrumb ─────────────────────────────────────────── -->
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="<?= APP_URL ?>/">Home</a>
    <span class="breadcrumb__sep">›</span>
    <a href="<?= APP_URL ?>/shop.php">Shop</a>
    <span class="breadcrumb__sep">›</span>
    <a href="<?= APP_URL ?>/shop.php?category=<?= e($product['category']) ?>"><?= e(ucfirst($product['category'])) ?></a>
    <span class="breadcrumb__sep">›</span>
    <span><?= e($product['name']) ?></span>
  </nav>

  <!-- ── Product Detail ────────────────────────────────────── -->
  <div class="product-detail">

    <!-- Gallery -->
    <div class="product-gallery">
      <div class="gallery-main">
        <img
          id="mainProductImg"
          src="<?= e(productImageUrl($product['image_path'])) ?>"
          alt="<?= e($product['name']) ?>"
        >
      </div>
    </div>

    <!-- Info -->
    <div class="product-info">
      <p class="label"><?= e(ucfirst($product['category'])) ?></p>
      <h1 class="product-info__name"><?= e($product['name']) ?></h1>
      <p class="product-info__price"><?= formatPrice((float)$product['price']) ?></p>

      <p class="product-info__desc"><?= nl2br(e($product['description'] ?? '')) ?></p>

      <?php if ($product['stock'] < 1): ?>
        <div style="padding:14px;background:#fff5f5;border-radius:8px;color:var(--danger);font-weight:600;margin-bottom:20px;">
          Out of Stock — Check back soon.
        </div>
      <?php else: ?>

        <form method="POST" action="<?= APP_URL ?>/product.php?id=<?= $id ?>">
          <input type="hidden" name="add_to_cart" value="1">
          <input type="hidden" name="size" id="selectedSize" value="">

          <!-- Size selector -->
          <div class="size-selector">
            <p class="label size-selector__label">Select Size</p>
            <div class="size-options">
              <?php foreach ($sizes as $size): ?>
                <button type="button" class="size-btn" data-size="<?= e($size) ?>" id="size-<?= e($size) ?>">
                  <?= e($size) ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Quantity -->
          <div class="qty-control">
            <span class="label">Qty</span>
            <button type="button" class="qty-btn" data-action="dec" id="qtyDec">−</button>
            <input
              type="number" name="qty" id="qtyInput" class="qty-input"
              value="1" min="1" max="<?= (int)$product['stock'] ?>"
            >
            <button type="button" class="qty-btn" data-action="inc" id="qtyInc">+</button>
          </div>

          <?php if ($product['stock'] < 10): ?>
            <p style="font-size:.8rem;color:var(--danger);margin-bottom:16px;">
              Only <?= (int)$product['stock'] ?> items left in stock.
            </p>
          <?php endif; ?>

          <button type="submit" class="btn btn-primary btn-lg btn-full" id="addToCartBtn">
            Add to Cart
          </button>
        </form>

      <?php endif; ?>

      <!-- Meta info -->
      <hr class="divider">
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;justify-content:space-between;font-size:.85rem;">
          <span class="text-muted">SKU</span>
          <span>#OWO-<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;">
          <span class="text-muted">Category</span>
          <span><?= e(ucfirst($product['category'])) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;">
          <span class="text-muted">Available Sizes</span>
          <span><?= e($product['sizes']) ?></span>
        </div>
      </div>
    </div>

  </div><!-- /product-detail -->

  <!-- ── Related Products ────────────────────────────────────── -->
  <?php if (!empty($relatedProducts)): ?>
    <section class="section" style="padding-top:56px;">
      <div class="section-header" style="text-align:left;">
        <p class="label">More from <?= e(ucfirst($product['category'])) ?></p>
        <h2>You Might Also Like</h2>
      </div>
      <div class="product-grid">
        <?php foreach ($relatedProducts as $rel): ?>
          <a href="<?= APP_URL ?>/product.php?id=<?= $rel['id'] ?>" class="product-card">
            <div class="product-card__img">
              <img src="<?= e(productImageUrl($rel['image_path'])) ?>" alt="<?= e($rel['name']) ?>" loading="lazy">
            </div>
            <div class="product-card__body">
              <h3 class="product-card__name"><?= e($rel['name']) ?></h3>
              <p class="product-card__price"><?= formatPrice((float)$rel['price']) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
