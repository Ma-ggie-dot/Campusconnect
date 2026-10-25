<?php
// includes/student_nav.php - sidebar for all student pages
$current = basename($_SERVER['PHP_SELF']);

$menu = [
  'dashboard.php'     => ['fa-gauge',              'Dashboard'],
  'announcements.php' => ['fa-bullhorn',           'Announcements'],
  'events.php'        => ['fa-calendar-days',      'Events'],
  'accommodation.php' => ['fa-house',              'Accommodation'],
  'lost-found.php'    => ['fa-magnifying-glass',   'Lost & Found'],
  'marketplace.php'   => ['fa-cart-shopping',      'Marketplace'],
  'requests.php'      => ['fa-screwdriver-wrench', 'Service Requests'],
];
?>
<aside class="side" aria-label="Student menu">
  <?php foreach ($menu as $file => [$icon, $label]): ?>
    <a href="<?= BASE_URL ?>/student/<?= $file ?>" class="<?= $current === $file ? 'active' : '' ?>">
      <i class="fa-solid <?= $icon ?>"></i> <?= e($label) ?>
    </a>
  <?php endforeach; ?>
</aside>