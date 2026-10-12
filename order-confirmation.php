<?php
/**
 * OWO — Order Confirmation Page
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

$orderId = (int) ($_GET['id'] ?? 0);
if (!$orderId) { redirect(APP_URL . '/'); }

$db = getDB();

// Load order + items
$order = $db->prepare("SELECT * FROM orders WHERE id = :id");
$order->execute([':id' => $orderId]);
$order = $order->fetch();

if (!$order || $order['status'] === 'pending') {
    setFlash('error', 'Order not found or not yet paid.');
    redirect(APP_URL . '/');
}

$itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :id");
$itemStmt->execute([':id' => $orderId]);
$orderItems = $itemStmt->fetchAll();

$pageTitle = 'Order Confirmed — OWO';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container confirmation-page">

  <div class="confirmation-icon">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="20 6 9 17 4 12"/>
    </svg>
  </div>

  <p class="label" style="color:var(--success);margin-bottom:8px;">Payment Successful</p>
  <h1>Thank you, <?= e($order['guest_name'] ?? 'valued customer') ?>!</h1>
  <p>
    Your order <strong>#OWO-<?= str_pad($orderId, 4, '0', STR_PAD_LEFT) ?></strong> has been confirmed.<br>
    We'll send updates to <strong><?= e($order['guest_email']) ?></strong>.
  </p>

  <!-- Order Details Card -->
  <div style="max-width:540px;margin:40px auto 0;text-align:left;background:var(--grey-50);border-radius:12px;overflow:hidden;">

    <!-- Items -->
    <?php foreach ($orderItems as $item): ?>
      <div style="display:flex;align-items:center;gap:16px;padding:16px 20px;border-bottom:1px solid var(--grey-100);">
        <div style="flex:1;">
          <p style="font-weight:600;font-size:.9rem;"><?= e($item['product_name']) ?></p>
          <p style="font-size:.8rem;color:var(--grey-400);">Size: <?= e($item['size']) ?> × <?= (int)$item['quantity'] ?></p>
        </div>
        <p style="font-weight:600;"><?= formatPrice($item['price_at_purchase'] * $item['quantity']) ?></p>
      </div>
    <?php endforeach; ?>

    <!-- Totals -->
    <div style="padding:20px;">
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.9rem;">
        <span>Order Total</span>
        <strong><?= formatPrice((float)$order['total_amount']) ?></strong>
      </div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.85rem;color:var(--grey-600);">
        <span>Khalti Transaction ID</span>
        <span><?= e($order['khalti_transaction_id'] ?? '—') ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.85rem;color:var(--grey-600);">
        <span>Shipping to</span>
        <span style="text-align:right;max-width:240px;"><?= e($order['shipping_address']) ?></span>
      </div>
    </div>

  </div>

  <div style="display:flex;gap:12px;justify-content:center;margin-top:36px;">
    <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary">Continue Shopping</a>
    <?php if (isLoggedIn()): ?>
      <a href="<?= APP_URL ?>/orders.php" class="btn btn-outline">View My Orders</a>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
