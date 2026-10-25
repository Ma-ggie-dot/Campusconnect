<?php
// student/announcements.php - read and filter announcements
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();

$allowed = ['academic', 'event', 'deadline', 'general'];
$cat = $_GET['cat'] ?? 'all';
if ($cat !== 'all' && !in_array($cat, $allowed, true)) { $cat = 'all'; }
$q = trim($_GET['q'] ?? '');

$sql = "SELECT a.*, u.full_name FROM announcements a
        JOIN users u ON u.id = a.posted_by WHERE 1=1";
$params = [];
if ($cat !== 'all') { $sql .= " AND a.category = ?"; $params[] = $cat; }
if ($q !== '') {
    $sql .= " AND (a.title LIKE ? OR a.body LIKE ?)";
    $params[] = "%$q%"; $params[] = "%$q%";
}
$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pageTitle = 'Announcements';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/student_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>
    <div class="page-head"><h2>Announcements</h2></div>

    <div class="toolbar">
      <div class="filters">
        <a class="chip <?= $cat === 'all' ? 'active' : '' ?>" href="?cat=all">All</a>
        <?php foreach ($allowed as $c): ?>
          <a class="chip <?= $cat === $c ? 'active' : '' ?>" href="?cat=<?= $c ?>"><?= ucfirst($c) ?></a>
        <?php endforeach; ?>
      </div>

      <form class="search-form" method="GET">
        <input type="hidden" name="cat" value="<?= e($cat) ?>">
        <input type="search" name="q" class="form-control" placeholder="Search announcements" value="<?= e($q) ?>">
        <button class="btn btn-primary btn-sm" type="submit">Search</button>
      </form>
    </div>

    <div class="list">
      <?php if ($rows): foreach ($rows as $a): ?>
        <article class="item-card">
          <span class="badge badge-cat badge-<?= e($a['category']) ?>"><?= e($a['category']) ?></span>
          <h3><?= e($a['title']) ?></h3>
          <p><?= e($a['body']) ?></p>
          <div class="item-meta">
            <i class="fa-regular fa-clock"></i> <?= date('d M Y, g:i A', strtotime($a['created_at'])) ?>
            &middot; Posted by <?= e($a['full_name']) ?>
          </div>
        </article>
      <?php endforeach; else: ?>
        <div class="panel"><div class="empty">No announcements found.</div></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>