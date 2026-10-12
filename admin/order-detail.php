<?php
/**
 * OWO Admin — Order Detail & Status Update
 */
$pageTitle  = 'Order Detail';
$activePage = 'orders';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$db      = getDB();
$orderId = (int) ($_GET['id'] ?? 0);
if (!$orderId) { redirect(APP_URL . '/admin/orders.php'); }

// Load order
$orderStmt = $db->prepare("
    SELECT o.*, COALESCE(u.name, o.guest_name) AS buyer_name,
           COALESCE(u.email, o.guest_email) AS buyer_email,
           COALESCE(u.phone, o.guest_phone) AS buyer_phone
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    WHERE o.id = :id
");
$orderStmt->execute([':id' => $orderId]);
$order = $orderStmt->fetch();
if (!$order) { setFlash('error','Order not found.'); redirect(APP_URL . '/admin/orders.php'); }

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $validStatuses = ['pending','paid','shipped','delivered','cancelled'];
    $newStatus = $_POST['new_status'];
    if (in_array($newStatus, $validStatuses)) {
        $db->prepare("UPDATE orders SET status = :s WHERE id = :id")
           ->execute([':s' => $newStatus, ':id' => $orderId]);
        setFlash('success', 'Order status updated to ' . ucfirst($newStatus) . '.');
        redirect(APP_URL . '/admin/order-detail.php?id=' . $orderId);
    }
}

// Load items
$items = $db->prepare("SELECT * FROM order_items WHERE order_id = :id");
$items->execute([':id' => $orderId]);
$items = $items->fetchAll();

require_once __DIR__ . '/partials/layout-header.php';
?>

<nav class="breadcrumb">
  <a href="<?= APP_URL ?>/admin/orders.php">Orders</a>
  <span class="breadcrumb__sep">›</span>
  <span>#OWO-<?= str_pad($orderId,4,'0',STR_PAD_LEFT) ?></span>
</nav>

<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

  <!-- Order Items -->
  <div class="admin-table-wrap">
    <div class="admin-table-header">
      <h2>Order #OWO-<?= str_pad($orderId,4,'0',STR_PAD_LEFT) ?></h2>
      <span class="status-badge status-badge--<?= e($order['status']) ?>"><?= ucfirst(e($order['status'])) ?></span>
    </div>
    <table class="admin-table">
      <thead>
        <tr><th>Product</th><th>Size</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= e($item['product_name']) ?></td>
            <td><?= e($item['size']) ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td><?= formatPrice((float)$item['price_at_purchase']) ?></td>
            <td><?= formatPrice((float)$item['price_at_purchase'] * $item['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="text-align:right;font-weight:700;padding:16px 20px;">Order Total</td>
          <td style="font-weight:700;padding:16px 20px;"><?= formatPrice((float)$order['total_amount']) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- Sidebar: Buyer Info + Status Update -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Buyer Info -->
    <div class="form-card">
      <h2>Buyer Info</h2>
      <div style="display:flex;flex-direction:column;gap:10px;font-size:.875rem;">
        <div><span class="text-muted">Name</span><br><strong><?= e($order['buyer_name'] ?? '—') ?></strong></div>
        <div><span class="text-muted">Email</span><br><?= e($order['buyer_email'] ?? '—') ?></div>
        <div><span class="text-muted">Phone</span><br><?= e($order['buyer_phone'] ?? '—') ?></div>
        <div><span class="text-muted">Address</span><br><?= nl2br(e($order['shipping_address'] ?? '—')) ?></div>
      </div>
    </div>

    <!-- Payment Info -->
    <div class="form-card">
      <h2>Payment</h2>
      <div style="display:flex;flex-direction:column;gap:10px;font-size:.875rem;">
        <div><span class="text-muted">Method</span><br>Khalti</div>
        <div><span class="text-muted">Transaction ID</span><br><?= e($order['khalti_transaction_id'] ?? '—') ?></div>
        <div><span class="text-muted">PIDX</span><br><span style="word-break:break-all;"><?= e($order['khalti_pidx'] ?? '—') ?></span></div>
        <div><span class="text-muted">Date</span><br><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
      </div>
    </div>

    <!-- Update Status -->
    <div class="form-card">
      <h2>Update Status</h2>
      <form method="POST" id="statusUpdateForm">
        <div class="form-group">
          <label class="form-label" for="newStatus">New Status</label>
          <select class="form-select" name="new_status" id="newStatus">
            <?php foreach (['pending','paid','shipped','delivered','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                <?= ucfirst($s) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-full" id="updateStatusBtn">Update Status</button>
      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/partials/layout-footer.php'; ?>
