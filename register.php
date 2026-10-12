<?php
/**
 * OWO — Register Page
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) { redirect(APP_URL . '/'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (!$name)               $errors[] = 'Full name is required.';
    if (!isValidEmail($email)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $db = getDB();

        // Check email uniqueness
        $check = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("INSERT INTO users (name, email, password_hash) VALUES (:n, :e, :h)")
               ->execute([':n' => $name, ':e' => $email, ':h' => $hash]);

            $userId = (int) $db->lastInsertId();
            loginUser(['id' => $userId, 'name' => $name, 'email' => $email]);
            setFlash('success', 'Welcome to OWO, ' . $name . '!');
            redirect(APP_URL . '/');
        }
    }
}

$pageTitle = 'Register — OWO';
require_once __DIR__ . '/partials/header.php';
?>

<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">OWO</div>
    <p class="auth-sub">Create your account</p>

    <?php if (!empty($errors)): ?>
      <div class="flash flash--error" style="border-radius:8px;margin-bottom:20px;">
        <?php foreach ($errors as $err): ?>
          <p>• <?= e($err) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
      <div class="form-group">
        <label class="form-label" for="regName">Full Name</label>
        <input
          class="form-input" type="text" name="name" id="regName"
          value="<?= e($_POST['name'] ?? '') ?>" autocomplete="name" required
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="regEmail">Email</label>
        <input
          class="form-input" type="email" name="email" id="regEmail"
          value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email" required
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="regPassword">Password</label>
        <input
          class="form-input" type="password" name="password" id="regPassword"
          autocomplete="new-password" minlength="8" required
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="regConfirm">Confirm Password</label>
        <input
          class="form-input" type="password" name="confirm" id="regConfirm"
          autocomplete="new-password" required
        >
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
        Create Account
      </button>
    </form>

    <p class="auth-footer">
      Already have an account? <a href="<?= APP_URL ?>/login.php">Sign in</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
