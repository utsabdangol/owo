<?php
/**
 * OWO Admin — Dashboard
 */
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$db = getDB();

// Stats
$totalOrders   = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products WHERE is_deleted=0")->fetchColumn();
$totalUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

// Recent 5 orders
$recentOrders = $db->query("
    SELECT o.*, COALESCE(u.name, o.guest_name) AS buyer_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();

// Low stock products
$lowStock = $db->query("SELECT * FROM products WHERE is_deleted=0 AND stock <= 5 ORDER BY stock ASC LIMIT 5")->fetchAll();

require_once __DIR__ . '/partials/layout-header.php';
?>

<!-- Stat Cards -->
<div class="stat-grid">
  <div class="stat-card stat-card--accent">
    <span class="stat-card__label">Total Revenue</span>
    <span class="stat-card__value"><?= formatPrice((float)$totalRevenue) ?></span>
    <span class="stat-card__sub">All time (non-cancelled)</span>
  </div>
  <div class="stat-card stat-card--success">
    <span class="stat-card__label">Total Orders</span>
    <span class="stat-card__value"><?= (int)$totalOrders ?></span>
    <span class="stat-card__sub"><?= (int)$pendingOrders ?> pending</span>
  </div>
  <div class="stat-card stat-card--info">
    <span class="stat-card__label">Products</span>
    <span class="stat-card__value"><?= (int)$totalProducts ?></span>
    <span class="stat-card__sub">Active listings</span>
  </div>
  <div class="stat-card stat-card--warning">
    <span class="stat-card__label">Customers</span>
    <span class="stat-card__value"><?= (int)$totalUsers ?></span>
    <span class="stat-card__sub">Registered accounts</span>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

  <!-- Recent Orders Table -->
  <div class="admin-table-wrap">
    <div class="admin-table-header">
      <h2>Recent Orders</h2>
      <a href="<?= APP_URL ?>/admin/orders.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <?php if (empty($recentOrders)): ?>
      <div class="empty-state"><h3>No orders yet.</h3></div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Buyer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
            <tr>
              <td><a href="<?= APP_URL ?>/admin/order-detail.php?id=<?= $order['id'] ?>" style="font-weight:600;">#OWO-<?= str_pad($order['id'],4,'0',STR_PAD_LEFT) ?></a></td>
              <td><?= e($order['buyer_name'] ?? '—') ?></td>
              <td><?= formatPrice((float)$order['total_amount']) ?></td>
              <td><span class="status-badge status-badge--<?= e($order['status']) ?>"><?= ucfirst(e($order['status'])) ?></span></td>
              <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Low Stock -->
  <div class="admin-table-wrap">
    <div class="admin-table-header">
      <h2>⚠ Low Stock</h2>
      <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline btn-sm">All Products</a>
    </div>
    <?php if (empty($lowStock)): ?>
      <div class="empty-state"><p class="text-success">All products well-stocked!</p></div>
    <?php else: ?>
      <table class="admin-table">
        <thead><tr><th>Product</th><th>Stock</th></tr></thead>
        <tbody>
          <?php foreach ($lowStock as $p): ?>
            <tr>
              <td><?= e($p['name']) ?></td>
              <td style="color:<?= $p['stock'] == 0 ? 'var(--danger)' : 'var(--warning)' ?>;font-weight:700;">
                <?= (int)$p['stock'] ?> <?= $p['stock'] == 0 ? '(Out)' : 'left' ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/partials/layout-footer.php'; ?>
