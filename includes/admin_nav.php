<?php
// includes/admin_nav.php - sidebar for all admin pages
$current = basename($_SERVER['PHP_SELF']);

$menu = [
  'dashboard.php'     => ['fa-gauge',    'Dashboard'],
  'announcements.php' => ['fa-bullhorn', 'Announcements'],
  'events.php'        => ['fa-calendar-days', 'Events'],
  // More links are added in later steps
];
?>
<aside class="side" aria-label="Admin menu">
  <?php foreach ($menu as $file => [$icon, $label]): ?>
    <a href="<?= BASE_URL ?>/admin/<?= $file ?>" class="<?= $current === $file ? 'active' : '' ?>">
      <i class="fa-solid <?= $icon ?>"></i> <?= e($label) ?>
    </a>
  <?php endforeach; ?>
</aside>