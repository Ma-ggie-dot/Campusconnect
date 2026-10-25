<?php
// auth/login.php - login for students and admins
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php');
}

$errors = [];
$maxAttempts = 5;
$lockSeconds = 300;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } elseif (!empty($_SESSION['lock_until']) && time() < $_SESSION['lock_until']) {
        $mins = ceil(($_SESSION['lock_until'] - time()) / 60);
        $errors[] = "Too many failed attempts. Try again in $mins minute(s).";
    } else {
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $errors[] = 'Your account has been suspended. Contact the administrator.';
            } else {
                // Success: prevent session fixation, then store user details
                session_regenerate_id(true);
                unset($_SESSION['attempts'], $_SESSION['lock_until']);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php');
            }
        } else {
            $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
            if ($_SESSION['attempts'] >= $maxAttempts) {
                $_SESSION['lock_until'] = time() + $lockSeconds;
                $_SESSION['attempts'] = 0;
            }
            $errors[] = 'Incorrect email or password.';
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrap">
  <div class="auth-card">
    <h2>Welcome back</h2>
    <p class="text-muted mt-1">Log in to access your campus services.</p>

    <div class="mt-3"><?php show_flash(); ?></div>

    <?php if ($errors): ?>
      <div class="alert alert-danger alert-persist">
        <?php foreach ($errors as $err): ?>
          <div><?= e($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <?= csrf_field() ?>

      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= old('email') ?>" placeholder="you@example.com" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Your password" required>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p class="text-center text-muted mt-3">
      New here? <a href="<?= BASE_URL ?>/auth/register.php">Create an account</a>
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>