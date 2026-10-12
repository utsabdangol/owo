<?php
/**
 * OWO Admin — Login Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if (isAdminLoggedIn()) { redirect(APP_URL . '/admin/index.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $errors[] = 'Username and password are required.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            loginAdmin($admin);
            redirect(APP_URL . '/admin/index.php');
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — OWO</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-auth-page">
  <div class="admin-auth-box">
    <div class="admin-auth-logo">OWO</div>
    <p class="admin-auth-sub">Admin Panel · Sign In</p>

    <?php if (!empty($errors)): ?>
      <div class="flash flash--error" style="margin-bottom:20px;">
        <span><?= e($errors[0]) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" id="adminLoginForm">
      <div class="form-group">
        <label class="form-label" for="adminUser">Username</label>
        <input
          class="form-input" type="text" name="username" id="adminUser"
          value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" required
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="adminPass">Password</label>
        <input
          class="form-input" type="password" name="password" id="adminPass"
          autocomplete="current-password" required
        >
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;padding:12px;">
        Sign In to Admin
      </button>
    </form>

    <p style="text-align:center;margin-top:16px;font-size:.75rem;color:var(--grey-400);">
      Default: admin / admin123 (change after first login)
    </p>
  </div>
</div>
</body>
</html>
