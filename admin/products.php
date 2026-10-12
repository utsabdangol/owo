<?php
/**
 * OWO Admin — Product Management
 */
$pageTitle  = 'Products';
$activePage = 'products';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$db = getDB();

// Handle Delete (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int) $_POST['delete_id'];
    $db->prepare("UPDATE products SET is_deleted = 1 WHERE id = :id")->execute([':id' => $delId]);
    setFlash('success', 'Product deleted.');
    redirect(APP_URL . '/admin/products.php');
}

// Search / Filter
$search = trim($_GET['q'] ?? '');
$where  = 'WHERE is_deleted = 0';
$params = [];
if ($search) {
    $where .= ' AND (name LIKE :q OR category LIKE :q2)';
    $params[':q']  = '%' . $search . '%';
    $params[':q2'] = '%' . $search . '%';
}

$total = $db->prepare("SELECT COUNT(*) FROM products $where");
$total->execute($params);
$total = (int) $total->fetchColumn();

$pag  = paginate($total, 15);
$stmt = $db->prepare("SELECT * FROM products $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $pag['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'],   PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

require_once __DIR__ . '/partials/layout-header.php';
?>

<div class="admin-table-wrap">
  <div class="admin-table-header">
    <h2>All Products (<?= $total ?>)</h2>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <form method="GET" class="search-bar">
        <input class="search-input" type="search" name="q" placeholder="Search products…" value="<?= e($search) ?>">
        <button class="btn btn-outline btn-sm" type="submit">Search</button>
      </form>
      <a href="<?= APP_URL ?>/admin/product-add.php" class="btn btn-primary btn-sm" id="addProductBtn">+ Add Product</a>
    </div>
  </div>

  <?php if (empty($products)): ?>
    <div class="empty-state">
      <h3>No products found.</h3>
      <p>Add your first product to get started.</p>
    </div>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Sizes</th>
            <th>Added</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <img
                  class="product-thumb"
                  src="<?= e(productImageUrl($p['image_path'])) ?>"
                  alt="<?= e($p['name']) ?>"
                >
              </td>
              <td>
                <a href="<?= APP_URL ?>/product.php?id=<?= $p['id'] ?>" target="_blank" style="font-weight:600;">
                  <?= e($p['name']) ?>
                </a>
              </td>
              <td><?= e(ucfirst($p['category'])) ?></td>
              <td><?= formatPrice((float)$p['price']) ?></td>
              <td>
                <span style="color:<?= $p['stock'] == 0 ? 'var(--danger)' : ($p['stock'] <= 5 ? 'var(--warning)' : 'inherit') ?>;font-weight:<?= $p['stock'] <= 5 ? '700' : '400' ?>;">
                  <?= (int)$p['stock'] ?>
                </span>
              </td>
              <td><?= e($p['sizes']) ?></td>
              <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
              <td>
                <div class="actions-col">
                  <a href="<?= APP_URL ?>/admin/product-edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" id="edit-<?= $p['id'] ?>">Edit</a>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                    <button
                      type="submit" class="btn btn-danger btn-sm"
                      data-confirm="Delete '<?= e($p['name']) ?>'? This cannot be undone."
                      id="del-<?= $p['id'] ?>"
                    >Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?= paginationLinks($pag, APP_URL . '/admin/products.php?' . http_build_query(['q' => $search])) ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/layout-footer.php'; ?>
