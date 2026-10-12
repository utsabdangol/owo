<?php
require_once __DIR__ . '/../includes/auth.php';
logoutAdmin();
header('Location: ' . APP_URL . '/admin/login.php');
exit;
