<?php
// admin/dashboard.php - admin home (expanded in Step 10)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$students      = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$announcements = (int)$pdo->query('SELECT COUNT(*) FROM announcements')->fetchColumn();
$upcoming      = (int)$pdo->query('SELECT COUNT(*) FROM events WHERE event_date >= NOW()')->fetchColumn();
$registrations = (int)$pdo->query('SELECT COUNT(*) FROM event_registrations')->fetchColumn();

$pageTitle = 'Admin Panel';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/admin_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>

    <div class="welcome">
      <h2>Admin Panel</h2>
      <p>Welcome, <?= e($_SESSION['full_name']) ?>. Manage campus content from here.</p>
    </div>

    <div class="stats">
      <div class="stat">
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
        <div><strong><?= $students ?></strong><span>Students</span></div>
      </div>
      <div class="stat">
        <div class="stat-icon amber"><i class="fa-solid fa-bullhorn"></i></div>
        <div><strong><?= $announcements ?></strong><span>Announcements</span></div>
      </div>
      <div class="stat">
        <div class="stat-icon green"><i class="fa-solid fa-calendar-days"></i></div>
        <div><strong><?= $upcoming ?></strong><span>Upcoming events</span></div>
      </div>
      <div class="stat">
        <div class="stat-icon blue"><i class="fa-solid fa-ticket"></i></div>
        <div><strong><?= $registrations ?></strong><span>Event sign-ups</span></div>
      </div>
    </div>

    <div class="quick">
      <a href="<?= BASE_URL ?>/admin/announcements.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New announcement</a>
      <a href="<?= BASE_URL ?>/admin/events.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-plus"></i> New event</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>