<?php
// auth/register.php - student registration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $full_name  = trim($_POST['full_name'] ?? '');
        $email      = strtolower(trim($_POST['email'] ?? ''));
        $reg_number = strtoupper(trim($_POST['reg_number'] ?? ''));
        $phone      = trim($_POST['phone'] ?? '');
        $password   = $_POST['password'] ?? '';
        $confirm    = $_POST['confirm_password'] ?? '';

        // Validation
        if (strlen($full_name) < 3 || strlen($full_name) > 100) {
            $errors[] = 'Please enter your full name (3 to 100 characters).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($reg_number === '' || strlen($reg_number) > 30) {
            $errors[] = 'Please enter your registration number.';
        }
        if (!preg_match('/^(\+?254|0)[17]\d{8}$/', $phone)) {
            $errors[] = 'Enter a valid Kenyan phone number, e.g. 0712345678.';
        }
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            $errors[] = 'Password must be at least 8 characters and contain a letter and a number.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        // Check email and registration number are not already used
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT email, reg_number FROM users WHERE email = ? OR reg_number = ?');
            $stmt->execute([$email, $reg_number]);
            foreach ($stmt->fetchAll() as $row) {
                if ($row['email'] === $email) {
                    $errors[] = 'That email is already registered.';
                }
                if ($row['reg_number'] === $reg_number) {
                    $errors[] = 'That registration number is already registered.';
                }
            }
        }

        // Save the user
        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, email, reg_number, phone, password_hash)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$full_name, $email, $reg_number, $phone, $hash]);

            set_flash('success', 'Account created successfully! You can now log in.');
            redirect('auth/login.php');
        }
    }
}

$pageTitle = 'Create account';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card">
    <h2>Create your account</h2>
    <p class="text-muted mt-1">Join CampusConnect in under a minute.</p>

    <?php if ($errors): ?>
      <div class="alert alert-danger alert-persist mt-3">
        <?php foreach ($errors as $err): ?>
          <div>• <?= e($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="mt-3" novalidate>
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" class="form-control"
               value="<?= old('full_name') ?>" placeholder="e.g. Jane Wanjiku" required>
      </div>

      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= old('email') ?>" placeholder="you@example.com" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="reg_number">Registration number</label>
          <input type="text" id="reg_number" name="reg_number" class="form-control"
                 value="<?= old('reg_number') ?>" placeholder="e.g. BSC/1234/2024" required>
        </div>
        <div class="form-group">
          <label for="phone">Phone number</label>
          <input type="tel" id="phone" name="phone" class="form-control"
                 value="<?= old('phone') ?>" placeholder="0712345678" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Min 8 characters" required>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm password</label>
          <input type="password" id="confirm_password" name="confirm_password"
                 class="form-control" placeholder="Repeat password" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Create account</button>
    </form>

    <p class="text-center text-muted mt-3">
      Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>