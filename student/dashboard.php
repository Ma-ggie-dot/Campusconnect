<?php
// student/dashboard.php - student home screen
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();
$uid = $_SESSION['user_id'];

// ---- Summary numbers ----
function count_for(PDO $pdo, string $sql, array $params): int {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

$openRequests = count_for($pdo,
    "SELECT COUNT(*) FROM service_requests WHERE user_id = ? AND status <> 'resolved'", [$uid]);

$myListings = count_for($pdo,
    "SELECT (SELECT COUNT(*) FROM accommodations WHERE user_id = ?)
          + (SELECT COUNT(*) FROM marketplace_items WHERE user_id = ?)
          + (SELECT COUNT(*) FROM lost_found WHERE user_id = ?)", [$uid, $uid, $uid]);

$myEvents = count_for($pdo,
    "SELECT COUNT(*) FROM event_registrations r
     JOIN events e ON e.id = r.event_id
     WHERE r.user_id = ? AND e.event_date >= NOW()", [$uid]);

$unread = count_for($pdo,
    "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0", [$uid]);

// ---- Latest announcements ----
$announcements = $pdo->query(
    "SELECT title, category, created_at FROM announcements ORDER BY created_at DESC LIMIT 4"
)->fetchAll();

// ---- Upcoming events ----
$events = $pdo->query(
    "SELECT title, venue, event_date FROM events WHERE event_date >= NOW()
     ORDER BY event_date ASC LIMIT 4"
)->fetchAll();

// ---- Recent approved listings (rooms and marketplace) ----
$listings = $pdo->query(
    "SELECT 'Accommodation' AS type, title, price, created_at, 'accommodation.php' AS link
       FROM accommodations WHERE status = 'approved'
     UNION ALL
     SELECT 'Marketplace', title, price, created_at, 'marketplace.php'
       FROM marketplace_items WHERE status = 'approved'
     ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

// ---- My latest requests ----
$stmt = $pdo->prepare(
    "SELECT subject, department, status, created_at FROM service_requests
     WHERE user_id = ? ORDER BY created_at DESC LIMIT 5"
);
$stmt->execute([$uid]);
$requests = $stmt->fetchAll();

$firstName = explode(' ', trim($_SESSION['full_name']))[0];
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/student_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>

    <div class="welcome">
      <h2>Hello, <?= e($firstName) ?> 👋</h2>
      <p>Here is what is happening on campus today.</p>
    </div>

    <!-- Summary cards -->
    <div class="stats">
      <div class="stat">
        <div class="stat-icon amber"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div><strong><?= $openRequests ?></strong><span>Open requests</span></div>
      </div>
      <div class="stat">
        <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
        <div><strong><?= $myListings ?></strong><span>My posts</span></div>
      </div>
      <div class="stat">
        <div class="stat-icon green"><i class="fa-solid fa-calendar-check"></i></div>
        <div><strong><?= $myEvents ?></strong><span>Events joined</span></div>
      </div>
      <div class="stat">
        <div class="stat-icon blue"><i class="fa-solid fa-bell"></i></div>
        <div><strong><?= $unread ?></strong><span>Unread alerts</span></div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="quick">
      <a href="<?= BASE_URL ?>/student/requests.php" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus"></i> New request</a>
      <a href="<?= BASE_URL ?>/student/lost-found.php" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-magnifying-glass"></i> Report lost item</a>
      <a href="<?= BASE_URL ?>/student/accommodation.php" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-house"></i> Find a room</a>
      <a href="<?= BASE_URL ?>/student/marketplace.php" class="btn btn-outline btn-sm">
        <i class="fa-solid fa-tag"></i> Sell an item</a>
    </div>

    <div class="panels">

      <!-- Announcements -->
      <section class="panel">
        <div class="panel-head">
          <h3>Latest announcements</h3>
          <a href="<?= BASE_URL ?>/student/announcements.php">View all</a>
        </div>
        <?php if ($announcements): foreach ($announcements as $a): ?>
          <div class="row-item">
            <div>
              <h4><?= e($a['title']) ?></h4>
              <small><?= e(ucfirst($a['category'])) ?> &middot; <?= date('d M Y', strtotime($a['created_at'])) ?></small>
            </div>
          </div>
        <?php endforeach; else: ?>
          <div class="empty">No announcements yet.</div>
        <?php endif; ?>
      </section>

      <!-- Events -->
      <section class="panel">
        <div class="panel-head">
          <h3>Upcoming events</h3>
          <a href="<?= BASE_URL ?>/student/events.php">View all</a>
        </div>
        <?php if ($events): foreach ($events as $ev): $t = strtotime($ev['event_date']); ?>
          <div class="row-item">
            <div class="row-left">
              <div class="date-chip"><b><?= date('d', $t) ?></b><span><?= date('M', $t) ?></span></div>
              <div>
                <h4><?= e($ev['title']) ?></h4>
                <small><i class="fa-solid fa-location-dot"></i> <?= e($ev['venue']) ?> &middot; <?= date('g:i A', $t) ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; else: ?>
          <div class="empty">No upcoming events.</div>
        <?php endif; ?>
      </section>

      <!-- Listings -->
      <section class="panel">
        <div class="panel-head">
          <h3>Recent listings</h3>
        </div>
        <?php if ($listings): foreach ($listings as $l): ?>
          <a class="row-item" href="<?= BASE_URL ?>/student/<?= e($l['link']) ?>" style="color:inherit;">
            <div>
              <h4><?= e($l['title']) ?></h4>
              <small><?= e($l['type']) ?> &middot; <?= date('d M Y', strtotime($l['created_at'])) ?></small>
            </div>
            <span class="price">KSh <?= number_format($l['price']) ?></span>
          </a>
        <?php endforeach; else: ?>
          <div class="empty">No approved listings yet.</div>
        <?php endif; ?>
      </section>

      <!-- My requests -->
      <section class="panel">
        <div class="panel-head">
          <h3>My service requests</h3>
          <a href="<?= BASE_URL ?>/student/requests.php">View all</a>
        </div>
        <?php if ($requests): foreach ($requests as $r): ?>
          <div class="row-item">
            <div>
              <h4><?= e($r['subject']) ?></h4>
              <small><?= e(strtoupper($r['department'])) ?> &middot; <?= date('d M Y', strtotime($r['created_at'])) ?></small>
            </div>
            <span class="badge badge-<?= e($r['status']) ?>"><?= e(str_replace('_', ' ', $r['status'])) ?></span>
          </div>
        <?php endforeach; else: ?>
          <div class="empty">You have not made any requests yet.</div>
        <?php endif; ?>
      </section>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>