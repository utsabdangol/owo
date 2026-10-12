<?php
/**
 * OWO E-Commerce - Auth Helpers
 * Session-based authentication for customers and admins.
 */

require_once __DIR__ . '/../config/config.php';

// ─── Session Bootstrap ────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

// ─── Customer Auth ────────────────────────────────────────────────────────────

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(string $redirect = '/owo/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function loginUser(array $user): void {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email']= $user['email'];
    session_regenerate_id(true);
}

function logoutUser(): void {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
    session_destroy();
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
    ];
}

// ─── Admin Auth ───────────────────────────────────────────────────────────────

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }
}

function loginAdmin(array $admin): void {
    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    session_regenerate_id(true);
}

function logoutAdmin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
    session_destroy();
}

// ─── CSRF Protection ──────────────────────────────────────────────────────────

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
}

// ─── Flash Messages ───────────────────────────────────────────────────────────

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
