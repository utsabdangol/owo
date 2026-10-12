<?php
/**
 * OWO — Order History (requires login)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();
$user = currentUser();
$db   = getDB();

$orders = $db->prepare("
    SELECT o.*,
           COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.user_id = :uid
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders->execute([':uid' => $user['id']]);
$orders = $orders->fetchAll();

$pageTitle = 'My Orders — OWO';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container">
  <div class="page-header">
    <h1>My Orders</h1>
    <p>Signed in as <?= e($user['email']) ?></p>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
      </svg>
      <h3>No orders yet</h3>
      <p>When you place an order it will appear here.</p>
      <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary">Shop Now</a>
    </div>
  <?php else: ?>

    <div style="overflow-x:auto;">
      <table class="orders-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Date</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><strong>#OWO-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
              <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
              <td><?= (int)$order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?></td>
              <td><?= formatPrice((float)$order['total_amount']) ?></td>
              <td>
                <span class="status-badge status-badge--<?= e($order['status']) ?>">
                  <?= e(ucfirst($order['status'])) ?>
                </span>
              </td>
              <td>
                <a href="<?= APP_URL ?>/order-detail.php?id=<?= $order['id'] ?>" class="btn btn-ghost btn-sm">
                  View
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
