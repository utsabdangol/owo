<?php
/**
 * OWO E-Commerce - General Helpers
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Format price with currency symbol.
 */
function formatPrice(float $amount): string {
    return CURRENCY_SYM . ' ' . number_format($amount, 2);
}

/**
 * Sanitize output for safe HTML display.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL.
 */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/**
 * Get product image URL, fallback to placeholder if not found.
 */
function productImageUrl(string $imagePath): string {
    if (!empty($imagePath) && file_exists(__DIR__ . '/../uploads/products/' . basename($imagePath))) {
        return UPLOAD_URL . basename($imagePath);
    }
    return APP_URL . '/assets/img/placeholder.php';
}

/**
 * Upload a product image and return the filename, or null on failure.
 */
function uploadProductImage(array $file): ?string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize      = 5 * 1024 * 1024; // 5 MB

    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($file['type'], $allowedTypes)) return null;
    if ($file['size'] > $maxSize) return null;

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_', true) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }

    return null;
}

/**
 * Validate email address.
 */
function isValidEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Paginate a query result count.
 * Returns ['offset' => int, 'total_pages' => int, 'current_page' => int]
 */
function paginate(int $total, int $perPage = 12): array {
    $currentPage = max(1, (int) ($_GET['page'] ?? 1));
    $totalPages  = max(1, (int) ceil($total / $perPage));
    $currentPage = min($currentPage, $totalPages);
    $offset      = ($currentPage - 1) * $perPage;

    return [
        'offset'       => $offset,
        'total_pages'  => $totalPages,
        'current_page' => $currentPage,
        'per_page'     => $perPage,
    ];
}

/**
 * Render HTML pagination links.
 */
function paginationLinks(array $pag, string $baseUrl): string {
    if ($pag['total_pages'] <= 1) return '';

    $html = '<nav class="pagination">';
    for ($i = 1; $i <= $pag['total_pages']; $i++) {
        $active = $i === $pag['current_page'] ? ' active' : '';
        $sep    = strpos($baseUrl, '?') !== false ? '&' : '?';
        $html  .= '<a href="' . e($baseUrl . $sep . 'page=' . $i) . '" class="page-link' . $active . '">' . $i . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

/**
 * Get available sizes as array from stored string (e.g. "S,M,L,XL").
 */
function parseSizes(string $sizes): array {
    return array_filter(array_map('trim', explode(',', $sizes)));
}
