<?php
/**
 * OWO E-Commerce - Configuration File
 * Store all environment-sensitive credentials here.
 * Swap KHALTI_SECRET_KEY for live key when going to production.
 */

// ─── Database Configuration ───────────────────────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'owo_store');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');

// ─── Khalti Payment Gateway (Sandbox) ────────────────────────────────────────
// Replace with live keys when going to production
define('KHALTI_PUBLIC_KEY',  '44307d4f1ee74192a9d7082a179b2f4a');
define('KHALTI_SECRET_KEY',  '4ce4efdb9b3a45d9837f8dfe5e459714');
define('KHALTI_BASE_URL',    'https://a.khalti.com/api/v2/');  // Sandbox endpoint

// ─── Application Settings ─────────────────────────────────────────────────────
define('APP_NAME',    'OWO');
define('APP_URL',     'http://localhost/owo');
define('CURRENCY',    'NPR');
define('CURRENCY_SYM','Rs.');

// ─── Upload Paths ─────────────────────────────────────────────────────────────
define('UPLOAD_DIR',  __DIR__ . '/../uploads/products/');
define('UPLOAD_URL',  APP_URL . '/uploads/products/');

// ─── Session Config ───────────────────────────────────────────────────────────
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
