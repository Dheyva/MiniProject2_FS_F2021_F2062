<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
guest_only();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(post_str('email'));
    $password = (string)($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    }
    if ($email === '')                                   $errors['email'] = 'Email address is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors['email'] = 'Enter a valid email address.';
    if ($password === '')                                $errors['password'] = 'Password is required.';

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id, full_name, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // password_verify() against the stored hash; same generic message for unknown email / wrong password
        if ($user && password_verify($password, $user['password'])) {
          $role = strtolower(trim((string)$user['role']));
            session_regenerate_id(true);               // prevent session fixation
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['full_name'] = $user['full_name'];
          $_SESSION['role']      = $role;

            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $u = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                $u->bind_param('si', $newHash, $user['id']);
                $u->execute();
                $u->close();
            }
            flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            session_write_close();
            redirect(home_for_role($role));              // role based routing
        }
        $errors['general'] = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<div class="card auth-card">
  <div class="card-body p-4">
    <h3 class="mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Login</h3>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" data-validate="login" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               id="email" name="email" value="<?= e($email) ?>">
        <div class="invalid-feedback"><?= isset($errors['email']) ? e($errors['email']) : '' ?></div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
               id="password" name="password">
        <div class="invalid-feedback"><?= isset($errors['password']) ? e($errors['password']) : '' ?></div>
      </div>
      <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-1"></i>Log In</button>
    </form>
    <p class="text-center small mt-3 mb-0">Don't have an account? <a href="register.php">Register here</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
