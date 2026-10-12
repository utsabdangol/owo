<?php
/**
 * OWO — Customer Order Detail
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

requireLogin();
$user    = currentUser();
$db      = getDB();
$orderId = (int) ($_GET['id'] ?? 0);

$orderStmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :uid");
$orderStmt->execute([':id' => $orderId, ':uid' => $user['id']]);
$order = $orderStmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect(APP_URL . '/orders.php');
}

$items = $db->prepare("SELECT * FROM order_items WHERE order_id = :id");
$items->execute([':id' => $orderId]);
$items = $items->fetchAll();

$pageTitle = 'Order #OWO-' . str_pad($orderId, 4, '0', STR_PAD_LEFT) . ' — OWO';
require_once __DIR__ . '/partials/header.php';
?>
<div class="container" style="max-width:740px;padding:40px 24px;">
  <nav class="breadcrumb">
    <a href="<?= APP_URL ?>/orders.php">My Orders</a>
    <span class="breadcrumb__sep">›</span>
    <span>Order #OWO-<?= str_pad($orderId, 4, '0', STR_PAD_LEFT) ?></span>
  </nav>

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <h1>Order #OWO-<?= str_pad($orderId, 4, '0', STR_PAD_LEFT) ?></h1>
    <span class="status-badge status-badge--<?= e($order['status']) ?>"><?= ucfirst(e($order['status'])) ?></span>
  </div>

  <!-- Items -->
  <div style="border:1px solid var(--grey-100);border-radius:12px;overflow:hidden;margin-bottom:28px;">
    <?php foreach ($items as $item): ?>
      <div style="display:flex;align-items:center;gap:16px;padding:16px 20px;border-bottom:1px solid var(--grey-100);">
        <div style="flex:1;">
          <p style="font-weight:600;"><?= e($item['product_name']) ?></p>
          <p style="font-size:.8rem;color:var(--grey-400);">Size: <?= e($item['size']) ?> × <?= (int)$item['quantity'] ?></p>
        </div>
        <p style="font-weight:700;"><?= formatPrice((float)$item['price_at_purchase'] * $item['quantity']) ?></p>
      </div>
    <?php endforeach; ?>
    <div style="padding:20px;display:flex;justify-content:space-between;font-weight:700;">
      <span>Order Total</span>
      <span><?= formatPrice((float)$order['total_amount']) ?></span>
    </div>
  </div>

  <!-- Order Info -->
  <div style="background:var(--grey-50);border-radius:12px;padding:24px;display:grid;gap:12px;">
    <div style="display:flex;justify-content:space-between;font-size:.9rem;">
      <span class="text-muted">Date</span>
      <span><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.9rem;">
      <span class="text-muted">Shipping to</span>
      <span style="text-align:right;max-width:300px;"><?= e($order['shipping_address']) ?></span>
    </div>
    <?php if ($order['khalti_transaction_id']): ?>
    <div style="display:flex;justify-content:space-between;font-size:.9rem;">
      <span class="text-muted">Transaction ID</span>
      <span><?= e($order['khalti_transaction_id']) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <div style="margin-top:28px;">
    <a href="<?= APP_URL ?>/orders.php" class="btn btn-outline">← Back to Orders</a>
  </div>
</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
