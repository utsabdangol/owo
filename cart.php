<?php
/**
 * OWO — Cart Page
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/helpers.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $size      = $_POST['size'] ?? '';

    if ($action === 'update') {
        $qty = (int) ($_POST['qty'] ?? 1);
        cartUpdate($productId, $size, $qty);
    } elseif ($action === 'remove') {
        cartRemove($productId, $size);
        setFlash('info', 'Item removed from cart.');
    }
    redirect(APP_URL . '/cart.php');
}

$items = cartItems();
$total = cartTotal();

$pageTitle = 'Cart — OWO';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container">
  <div class="page-header">
    <h1>Your Cart</h1>
    <p><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?></p>
  </div>

  <?php if (empty($items)): ?>
    <div class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      <h3>Your cart is empty</h3>
      <p>Looks like you haven't added anything yet.</p>
      <a href="<?= APP_URL ?>/shop.php" class="btn btn-primary">Start Shopping</a>
    </div>

  <?php else: ?>
    <div class="cart-layout">

      <!-- Cart Items Table -->
      <div>
        <table class="cart-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $key => $item): ?>
              <tr>
                <td>
                  <div class="cart-item">
                    <img
                      class="cart-item__img"
                      src="<?= e(productImageUrl($item['image'])) ?>"
                      alt="<?= e($item['name']) ?>"
                    >
                    <div>
                      <p class="cart-item__name"><?= e($item['name']) ?></p>
                      <p class="cart-item__meta">Size: <?= e($item['size']) ?></p>
                    </div>
                  </div>
                </td>
                <td><?= formatPrice($item['price']) ?></td>
                <td>
                  <form method="POST" class="cart-qty">
                    <input type="hidden" name="action"     value="update">
                    <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                    <input type="hidden" name="size"       value="<?= e($item['size']) ?>">
                    <button type="button" class="qty-btn" data-action="dec">−</button>
                    <input
                      type="number"
                      name="qty"
                      value="<?= (int)$item['quantity'] ?>"
                      min="1" max="99"
                      class="cart-qty-input"
                      id="qty-<?= e($key) ?>"
                      onchange="this.form.submit()"
                    >
                    <button type="button" class="qty-btn" data-action="inc">+</button>
                  </form>
                </td>
                <td><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                <td>
                  <form method="POST">
                    <input type="hidden" name="action"     value="remove">
                    <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                    <input type="hidden" name="size"       value="<?= e($item['size']) ?>">
                    <button
                      type="submit"
                      class="btn btn-ghost btn-sm"
                      data-confirm="Remove <?= e($item['name']) ?> from cart?"
                      id="remove-<?= e($key) ?>"
                    >✕</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="margin-top:20px;display:flex;gap:12px;">
          <a href="<?= APP_URL ?>/shop.php" class="btn btn-ghost">← Continue Shopping</a>
        </div>
      </div>

      <!-- Summary -->
      <div class="cart-summary">
        <h3>Order Summary</h3>
        <div class="summary-row">
          <span>Subtotal</span>
          <span><?= formatPrice($total) ?></span>
        </div>
        <div class="summary-row">
          <span>Shipping</span>
          <span><?= $total >= 3000 ? '<span style="color:var(--success)">Free</span>' : formatPrice(150) ?></span>
        </div>
        <?php
          $shipping = $total >= 3000 ? 0 : 150;
          $grand    = $total + $shipping;
        ?>
        <div class="summary-row total">
          <span>Total</span>
          <span><?= formatPrice($grand) ?></span>
        </div>

        <?php if ($total < 3000): ?>
          <p style="font-size:.8rem;color:var(--grey-400);margin-top:8px;text-align:center;">
            Add <?= formatPrice(3000 - $total) ?> more for free shipping
          </p>
        <?php endif; ?>

        <a href="<?= APP_URL ?>/checkout.php" class="btn btn-primary btn-lg btn-full" style="margin-top:20px;" id="checkoutBtn">
          Proceed to Checkout
        </a>
      </div>

    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
