<?php
/**
 * OWO E-Commerce - Khalti Payment Helper
 * Handles payment initiation and verification with Khalti's API v2.
 *
 * Flow:
 *  1. initiateKhaltiPayment()  → returns payment URL from Khalti
 *  2. User completes payment on Khalti
 *  3. Khalti redirects to return_url with pidx & status
 *  4. verifyKhaltiPayment()    → verifies transaction & returns details
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Initiate a Khalti payment session.
 *
 * @param int    $orderId       Your internal order ID
 * @param float  $amountNPR     Amount in NPR (will be converted to paisa internally)
 * @param string $orderName     Short description shown on Khalti screen
 * @param string $customerName  Buyer's name
 * @param string $customerEmail Buyer's email
 * @param string $customerPhone Buyer's phone (10 digits)
 * @return array ['success' => bool, 'payment_url' => string|null, 'pidx' => string|null, 'error' => string|null]
 */
function initiateKhaltiPayment(
    int    $orderId,
    float  $amountNPR,
    string $orderName,
    string $customerName,
    string $customerEmail,
    string $customerPhone
): array {
    $amountPaisa = (int) round($amountNPR * 100); // Khalti uses paisa

    $payload = [
        'return_url'     => APP_URL . '/payment-verify.php',
        'website_url'    => APP_URL,
        'amount'         => $amountPaisa,
        'purchase_order_id'   => (string) $orderId,
        'purchase_order_name' => $orderName,
        'customer_info'  => [
            'name'  => $customerName,
            'email' => $customerEmail,
            'phone' => $customerPhone,
        ],
    ];

    $ch = curl_init(KHALTI_BASE_URL . 'epayment/initiate/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Key ' . KHALTI_SECRET_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'payment_url' => null, 'pidx' => null, 'error' => 'cURL error: ' . $curlErr];
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['payment_url'], $data['pidx'])) {
        return [
            'success'     => true,
            'payment_url' => $data['payment_url'],
            'pidx'        => $data['pidx'],
            'error'       => null,
        ];
    }

    $errMsg = $data['detail'] ?? $data['message'] ?? 'Unknown error from Khalti';
    return ['success' => false, 'payment_url' => null, 'pidx' => null, 'error' => $errMsg];
}

/**
 * Verify a Khalti payment using pidx returned from callback.
 *
 * @param string $pidx  The payment index returned by Khalti on callback
 * @return array ['success' => bool, 'status' => string, 'transaction_id' => string|null, 'amount_paisa' => int, 'error' => string|null]
 */
function verifyKhaltiPayment(string $pidx): array {
    $payload = json_encode(['pidx' => $pidx]);

    $ch = curl_init(KHALTI_BASE_URL . 'epayment/lookup/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Key ' . KHALTI_SECRET_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['success' => false, 'status' => 'error', 'transaction_id' => null, 'amount_paisa' => 0, 'error' => 'cURL error: ' . $curlErr];
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['status'])) {
        $isCompleted = strtolower($data['status']) === 'completed';
        return [
            'success'        => $isCompleted,
            'status'         => $data['status'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'amount_paisa'   => $data['total_amount'] ?? 0,
            'error'          => $isCompleted ? null : 'Payment not completed. Status: ' . $data['status'],
        ];
    }

    $errMsg = $data['detail'] ?? $data['message'] ?? 'Unknown Khalti verification error';
    return ['success' => false, 'status' => 'error', 'transaction_id' => null, 'amount_paisa' => 0, 'error' => $errMsg];
}
