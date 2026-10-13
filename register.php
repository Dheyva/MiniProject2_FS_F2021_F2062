<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
guest_only();

$errors = [];
$old = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = post_str('full_name');
    $old['email']     = strtolower(post_str('email'));
    $password         = (string)($_POST['password'] ?? '');
    $confirm          = (string)($_POST['confirm_password'] ?? '');

    if (!csrf_valid()) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    }

    // ---- server-side validation ----
    $len = mb_strlen($old['full_name']);
    if ($old['full_name'] === '')                                  $errors['full_name'] = 'Full name is required.';
    elseif ($len < 3 || $len > 100)                                $errors['full_name'] = 'Full name must be 3-100 characters.';
    elseif (!preg_match('/^[A-Za-z@\' .\-\/]+$/', $old['full_name'])) $errors['full_name'] = 'Full name may contain letters and spaces only.';

    if ($old['email'] === '')                                      $errors['email'] = 'Email address is required.';
    elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 100)
           $errors['email'] = 'Enter a valid email address.';

    if ($password === '')                                          $errors['password'] = 'Password is required.';
    elseif (strlen($password) < 8)                                 $errors['password'] = 'Password must be at least 8 characters.';
    elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))
           $errors['password'] = 'Password must contain letters and numbers.';

    if ($confirm === '')                                           $errors['confirm_password'] = 'Please confirm your password.';
    elseif ($password !== $confirm)                                $errors['confirm_password'] = 'Passwords do not match.';

    // ---- unique email check ----
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['email'] = 'This email is already registered.';
        }
        $stmt->close();
    }

    // ---- insert (role is ALWAYS student for self registration) ----
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'student';
        $stmt = $conn->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $old['full_name'], $old['email'], $hash, $role);
        try {
            $stmt->execute();
            flash('success', 'Registration successful! Please log in.');
            redirect('login.php');
        } catch (mysqli_sql_exception $ex) {
            $errors['general'] = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register';
$activePage = 'register';
require __DIR__ . '/includes/header.php';

function field_error(array $errors, string $k): string { return isset($errors[$k]) ? e($errors[$k]) : ''; }
?>
<div class="card auth-card">
  <div class="card-body p-4">
    <h3 class="mb-3"><i class="bi bi-person-plus-fill me-2"></i>Create Account</h3>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" data-validate="register" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
               id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" maxlength="100">
        <div class="invalid-feedback"><?= field_error($errors, 'full_name') ?></div>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               id="email" name="email" value="<?= e($old['email']) ?>" maxlength="100">
        <div class="invalid-feedback"><?= field_error($errors, 'email') ?></div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
               id="password" name="password">
        <div class="invalid-feedback"><?= field_error($errors, 'password') ?></div>
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
               id="confirm_password" name="confirm_password">
        <div class="invalid-feedback"><?= field_error($errors, 'confirm_password') ?></div>
      </div>
      <button type="submit" class="btn btn-primary w-100"><i class="bi bi-person-check me-1"></i>Register</button>
    </form>
    <p class="text-center small mt-3 mb-0">Already have an account? <a href="login.php">Log in here</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
