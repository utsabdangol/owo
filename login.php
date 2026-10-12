<?php
/**
 * OWO — Login Page
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

// Already logged in
if (isLoggedIn()) { redirect(APP_URL . '/'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);
            $next = $_GET['next'] ?? (APP_URL . '/');
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect($next);
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login — OWO';
require_once __DIR__ . '/partials/header.php';
?>

<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">OWO</div>
    <p class="auth-sub">Sign in to your account</p>

    <?php if (!empty($errors)): ?>
      <div class="flash flash--error" style="border-radius:8px;margin-bottom:20px;">
        <span><?= e($errors[0]) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
      <div class="form-group">
        <label class="form-label" for="loginEmail">Email</label>
        <input
          class="form-input" type="email" name="email" id="loginEmail"
          value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email" required
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="loginPassword">Password</label>
        <input
          class="form-input" type="password" name="password" id="loginPassword"
          autocomplete="current-password" required
        >
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
        Sign In
      </button>
    </form>

    <p class="auth-footer">
      Don't have an account? <a href="<?= APP_URL ?>/register.php">Create one</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
