<?php
// includes/admin_nav.php - sidebar for all admin pages
$current = basename($_SERVER['PHP_SELF']);

$groups = [
  'Overview' => [
    'dashboard.php'     => ['fa-gauge',         'Dashboard'],
  ],
  'Content' => [
    'announcements.php' => ['fa-bullhorn',      'Announcements'],
    'events.php'        => ['fa-calendar-days', 'Events'],
  ],
  'Listings' => [
    'accommodation.php' => ['fa-house',         'Accommodation'],
    // More links are added in later steps
  ],
];

$currentLabel = 'Menu';
foreach ($groups as $items) {
    foreach ($items as $file => [$icon, $label]) {
        if ($file === $current) { $currentLabel = $label; }
    }
}

$sideName = $_SESSION['full_name'] ?? 'Admin';
?>
<div class="side-wrap">
  <button type="button" class="side-toggle" id="sideToggle" aria-expanded="false" aria-controls="sideMenu">
    <span><i class="fa-solid fa-bars-staggered"></i><?= e($currentLabel) ?></span>
    <i class="fa-solid fa-chevron-down chev"></i>
  </button>

  <aside class="side" id="sideMenu" aria-label="Admin menu">
    <div class="side-user">
      <div class="avatar"><?= e(strtoupper(mb_substr($sideName, 0, 1))) ?></div>
      <div><strong><?= e($sideName) ?></strong><span>Administrator</span></div>
    </div>

    <?php foreach ($groups as $groupName => $items): ?>
      <div class="side-label"><?= e($groupName) ?></div>
      <?php foreach ($items as $file => [$icon, $label]): ?>
        <a href="<?= BASE_URL ?>/admin/<?= $file ?>" class="<?= $current === $file ? 'active' : '' ?>">
          <i class="fa-solid <?= $icon ?>"></i> <?= e($label) ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </aside>
</div>