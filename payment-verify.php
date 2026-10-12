<?php
/**
 * OWO — Khalti Payment Verification (Return URL)
 * Khalti redirects here with ?pidx=...&status=...&transaction_id=...&purchase_order_id=...
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/khalti.php';

$pidx    = trim($_GET['pidx']    ?? '');
$status  = trim($_GET['status']  ?? '');
$orderId = (int) ($_GET['purchase_order_id'] ?? 0);

// Validate inputs
if (!$pidx || !$orderId) {
    setFlash('error', 'Invalid payment callback.');
    redirect(APP_URL . '/cart.php');
}

$db    = getDB();

// Load the order
$orderStmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND khalti_pidx = :pidx");
$orderStmt->execute([':id' => $orderId, ':pidx' => $pidx]);
$order = $orderStmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found or already processed.');
    redirect(APP_URL . '/');
}

// Already paid? Go straight to confirmation
if ($order['status'] === 'paid') {
    redirect(APP_URL . '/order-confirmation.php?id=' . $orderId);
}

// ── Verify with Khalti API ───────────────────────────────────
$verification = verifyKhaltiPayment($pidx);

if ($verification['success']) {
    // Mark order as paid
    $db->prepare("
        UPDATE orders
        SET status = 'paid',
            khalti_transaction_id = :txid
        WHERE id = :id
    ")->execute([
        ':txid' => $verification['transaction_id'],
        ':id'   => $orderId,
    ]);

    // Clear the cart
    cartClear();

    redirect(APP_URL . '/order-confirmation.php?id=' . $orderId);

} else {
    // Payment failed or incomplete
    setFlash('error', 'Payment could not be verified: ' . ($verification['error'] ?? 'Unknown error') . '. Please try again or contact support.');
    redirect(APP_URL . '/checkout.php');
}
