<?php
// admin/dashboard.php - placeholder, completed in Step 10
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = 'Admin Panel';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="container">
    <?php show_flash(); ?>
    <h2>Admin Panel</h2>
    <p class="text-muted mt-1">Welcome, <?= e($_SESSION['full_name']) ?>. The full admin dashboard arrives in Step 10.</p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>