<?php
/**
 * OWO E-Commerce - Cart Helper
 * Manages cart stored in PHP session.
 */

require_once __DIR__ . '/../includes/auth.php';

// ─── Cart Structure in $_SESSION['cart'] ─────────────────────────────────────
// [
//   'product_id:size' => [
//       'product_id' => int,
//       'name'       => string,
//       'price'      => float,
//       'size'       => string,
//       'quantity'   => int,
//       'image'      => string,
//   ],
//   ...
// ]

function cartKey(int $productId, string $size): string {
    return "{$productId}:{$size}";
}

function cartInit(): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function cartAdd(int $productId, string $name, float $price, string $size, int $qty = 1, string $image = ''): void {
    cartInit();
    $key = cartKey($productId, $size);
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $qty;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $productId,
            'name'       => $name,
            'price'      => $price,
            'size'       => $size,
            'quantity'   => $qty,
            'image'      => $image,
        ];
    }
}

function cartUpdate(int $productId, string $size, int $qty): void {
    cartInit();
    $key = cartKey($productId, $size);
    if (isset($_SESSION['cart'][$key])) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$key]);
        } else {
            $_SESSION['cart'][$key]['quantity'] = $qty;
        }
    }
}

function cartRemove(int $productId, string $size): void {
    cartInit();
    $key = cartKey($productId, $size);
    unset($_SESSION['cart'][$key]);
}

function cartClear(): void {
    $_SESSION['cart'] = [];
}

function cartItems(): array {
    cartInit();
    return $_SESSION['cart'];
}

function cartCount(): int {
    cartInit();
    return array_sum(array_column($_SESSION['cart'], 'quantity'));
}

function cartTotal(): float {
    cartInit();
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
