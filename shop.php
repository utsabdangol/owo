<?php
/**
 * OWO — Shop / All Products Page
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$db = getDB();

// ── Filters from GET ────────────────────────────────────────
$category = trim($_GET['category'] ?? '');
$sort     = $_GET['sort'] ?? 'newest';
$search   = trim($_GET['q'] ?? '');

// ── Build WHERE clause ──────────────────────────────────────
$where  = 'WHERE is_deleted = 0';
$params = [];

if ($category && $category !== 'all') {
    $where   .= ' AND category = :cat';
    $params[':cat'] = $category;
}
if ($search) {
    $where   .= ' AND (name LIKE :q OR description LIKE :q2)';
    $params[':q']  = '%' . $search . '%';
    $params[':q2'] = '%' . $search . '%';
}

// ── Sort ────────────────────────────────────────────────────
$orderBy = match ($sort) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name'       => 'name ASC',
    default      => 'created_at DESC',
};

// ── Count for pagination ─────────────────────────────────────
$countStmt = $db->prepare("SELECT COUNT(*) FROM products $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$pag = paginate($total, 12);

// ── Fetch products ───────────────────────────────────────────
$stmt = $db->prepare("SELECT * FROM products $where ORDER BY $orderBy LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $pag['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'],   PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// ── Categories for filter bar ─────────────────────────────
$cats = $db->query("SELECT DISTINCT category FROM products WHERE is_deleted=0 ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Shop — OWO';
$metaDesc  = 'Browse the full OWO collection — tees, hoodies, cargos, accessories and more.';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container">

  <!-- ── Page Header ─────────────────────────────────────── -->
  <div class="page-header">
    <h1>Shop<?= $category ? ' / ' . e(ucfirst($category)) : '' ?></h1>
    <p><?= $total ?> item<?= $total !== 1 ? 's' : '' ?><?= $search ? ' for "' . e($search) . '"' : '' ?></p>
  </div>

  <!-- ── Search Bar ──────────────────────────────────────── -->
  <form method="GET" style="display:flex;gap:10px;margin-bottom:24px;max-width:480px;">
    <input
      class="form-input"
      type="search"
      name="q"
      placeholder="Search products…"
      value="<?= e($search) ?>"
      id="searchInput"
    >
    <button class="btn btn-primary" type="submit">Search</button>
  </form>

  <!-- ── Filter Bar ──────────────────────────────────────── -->
  <div class="filter-bar">
    <button class="filter-chip <?= (!$category || $category === 'all') ? 'active' : '' ?>" data-cat="all">All</button>
    <?php foreach ($cats as $cat): ?>
      <button class="filter-chip <?= $category === $cat ? 'active' : '' ?>" data-cat="<?= e($cat) ?>">
        <?= e(ucfirst($cat)) ?>
      </button>
    <?php endforeach; ?>

    <div class="filter-right">
      <form method="GET" id="sortForm">
        <?php if ($category): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
        <?php if ($search):   ?><input type="hidden" name="q"        value="<?= e($search) ?>"><?php endif; ?>
        <select class="filter-select" name="sort" onchange="document.getElementById('sortForm').submit()" id="sortSelect">
          <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest</option>
          <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: Low–High</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High–Low</option>
          <option value="name"       <?= $sort === 'name'       ? 'selected' : '' ?>>Name A–Z</option>
        </select>
      </form>
    </div>
  </div>

  <!-- ── Product Grid ──────────────────────────────────────── -->
  <?php if (empty($products)): ?>
    <div class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
      <h3>No products found</h3>
      <p>Try a different category or search term.</p>
      <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary">Browse All</a>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
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
            <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
              <p style="font-size:.75rem;color:var(--danger);font-weight:600;">Only <?= $product['stock'] ?> left</p>
            <?php elseif ($product['stock'] === 0): ?>
              <p style="font-size:.75rem;color:var(--grey-400);">Out of stock</p>
            <?php endif; ?>
          </div>
          <div class="product-card__footer">
            <span class="btn btn-outline btn-sm btn-full" style="pointer-events:none;">View Product</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?= paginationLinks($pag, APP_URL . '/shop.php?' . http_build_query(array_filter(['category'=>$category,'sort'=>$sort,'q'=>$search]))) ?>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
