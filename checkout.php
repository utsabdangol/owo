<?php
/**
 * OWO — Checkout Page
 * Collects billing info → creates a pending order → initiates Khalti payment
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/khalti.php';

// Redirect if cart empty
$items = cartItems();
if (empty($items)) {
    setFlash('info', 'Your cart is empty.');
    redirect(APP_URL . '/cart.php');
}

$total    = cartTotal();
$shipping = $total >= 3000 ? 0 : 150;
$grand    = $total + $shipping;

$user   = currentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & validate billing form
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$name)             $errors[] = 'Full name is required.';
    if (!isValidEmail($email)) $errors[] = 'Valid email is required.';
    if (!$phone)            $errors[] = 'Phone number is required.';
    if (!$address)          $errors[] = 'Shipping address is required.';

    if (empty($errors)) {
        $db = getDB();
        $db->beginTransaction();
        try {
            // 1. Insert pending order
            $stmt = $db->prepare("
                INSERT INTO orders
                  (user_id, guest_name, guest_email, guest_phone, shipping_address, total_amount, status)
                VALUES
                  (:uid, :name, :email, :phone, :addr, :amount, 'pending')
            ");
            $stmt->execute([
                ':uid'    => $user ? $user['id'] : null,
                ':name'   => $name,
                ':email'  => $email,
                ':phone'  => $phone,
                ':addr'   => $address,
                ':amount' => $grand,
            ]);
            $orderId = (int) $db->lastInsertId();

            // 2. Insert order items
            $itemStmt = $db->prepare("
                INSERT INTO order_items
                  (order_id, product_id, product_name, quantity, size, price_at_purchase)
                VALUES
                  (:oid, :pid, :pname, :qty, :size, :price)
            ");
            foreach ($items as $item) {
                $itemStmt->execute([
                    ':oid'   => $orderId,
                    ':pid'   => $item['product_id'],
                    ':pname' => $item['name'],
                    ':qty'   => $item['quantity'],
                    ':size'  => $item['size'],
                    ':price' => $item['price'],
                ]);
            }

            $db->commit();

            // 3. Initiate Khalti payment
            $khalti = initiateKhaltiPayment(
                $orderId,
                $grand,
                'OWO Order #' . $orderId,
                $name,
                $email,
                $phone
            );

            if ($khalti['success']) {
                // Save pidx for verification
                $db->prepare("UPDATE orders SET khalti_pidx = :pidx WHERE id = :id")
                   ->execute([':pidx' => $khalti['pidx'], ':id' => $orderId]);

                redirect($khalti['payment_url']);
            } else {
                // Payment initiation failed — keep order as pending for retry
                setFlash('error', 'Could not connect to Khalti: ' . $khalti['error'] . '. Please try again.');
                redirect(APP_URL . '/checkout.php');
            }

        } catch (Exception $e) {
            $db->rollBack();
            setFlash('error', 'Order creation failed. Please try again.');
            redirect(APP_URL . '/checkout.php');
        }
    }
}

$pageTitle = 'Checkout — OWO';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container">
  <div class="page-header">
    <h1>Checkout</h1>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="flash flash--error" style="margin-bottom:24px;">
      <div>
        <?php foreach ($errors as $err): ?>
          <p>• <?= e($err) ?></p>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="checkout-layout">

    <!-- Billing Form -->
    <div class="checkout-form">
      <h2>Billing Information</h2>
      <form method="POST" id="checkoutForm">

        <div class="form-group">
          <label class="form-label" for="name">Full Name *</label>
          <input
            class="form-input" type="text" name="name" id="name"
            value="<?= e($user['name'] ?? ($_POST['name'] ?? '')) ?>" required
          >
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="email">Email *</label>
            <input
              class="form-input" type="email" name="email" id="email"
              value="<?= e($user['email'] ?? ($_POST['email'] ?? '')) ?>" required
            >
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone *</label>
            <input
              class="form-input" type="tel" name="phone" id="phone"
              value="<?= e($_POST['phone'] ?? '') ?>" placeholder="98XXXXXXXX" required
            >
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="address">Shipping Address *</label>
          <textarea
            class="form-textarea" name="address" id="address"
            placeholder="House no., Street, Area, City" required
          ><?= e($_POST['address'] ?? '') ?></textarea>
        </div>

        <!-- Order Items Preview -->
        <h3 style="margin:28px 0 16px;">Your Order</h3>
        <div style="border:1px solid var(--grey-100);border-radius:8px;overflow:hidden;">
          <?php foreach ($items as $item): ?>
            <div style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid var(--grey-100);">
              <img
                src="<?= e(productImageUrl($item['image'])) ?>"
                alt="<?= e($item['name']) ?>"
                style="width:56px;height:70px;object-fit:cover;border-radius:6px;background:var(--grey-50);"
              >
              <div style="flex:1;">
                <p style="font-weight:600;font-size:.9rem;"><?= e($item['name']) ?></p>
                <p style="font-size:.8rem;color:var(--grey-400);">Size: <?= e($item['size']) ?> × <?= (int)$item['quantity'] ?></p>
              </div>
              <p style="font-weight:600;"><?= formatPrice($item['price'] * $item['quantity']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>

        <button type="submit" class="khalti-btn" id="payWithKhalti" style="margin-top:24px;">
          <svg width="22" height="22" viewBox="0 0 28 28" fill="none">
            <circle cx="14" cy="14" r="14" fill="#5C2D91"/>
            <path d="M9 14.5l3.5 3.5L19 10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Pay <?= formatPrice($grand) ?> with Khalti
        </button>

      </form>
    </div>

    <!-- Order Summary Sidebar -->
    <div class="payment-box">
      <h3 style="margin-bottom:20px;">Summary</h3>
      <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($total) ?></span></div>
      <div class="summary-row">
        <span>Shipping</span>
        <span><?= $shipping === 0 ? '<span style="color:var(--success)">Free</span>' : formatPrice($shipping) ?></span>
      </div>
      <div class="summary-row total"><span>Total</span><span><?= formatPrice($grand) ?></span></div>

      <hr class="divider">
      <div style="text-align:center;">
        <img
          src="https://web.khalti.com/static/img/logo1.png"
          alt="Khalti Secure Payment"
          style="height:28px;opacity:.7;margin:0 auto;"
          onerror="this.style.display='none'"
        >
        <p style="font-size:.75rem;color:var(--grey-400);margin-top:8px;">
          Secured by Khalti payment gateway.<br>Your card details are never stored.
        </p>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
