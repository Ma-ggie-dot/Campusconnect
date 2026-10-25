<?php
// student/events.php - browse events, register or cancel
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();
$uid = $_SESSION['user_id'];

// ---- Register / cancel ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form submission. Please try again.');
    } else {
        $eventId = (int)($_POST['event_id'] ?? 0);
        $action  = $_POST['action'] ?? '';

        $chk = $pdo->prepare('SELECT id FROM events WHERE id = ? AND event_date >= NOW()');
        $chk->execute([$eventId]);

        if (!$chk->fetch()) {
            set_flash('danger', 'That event is no longer available.');
        } elseif ($action === 'register') {
            $pdo->prepare('INSERT IGNORE INTO event_registrations (event_id, user_id) VALUES (?, ?)')
                ->execute([$eventId, $uid]);
            set_flash('success', 'You are registered for this event.');
        } elseif ($action === 'cancel') {
            $pdo->prepare('DELETE FROM event_registrations WHERE event_id = ? AND user_id = ?')
                ->execute([$eventId, $uid]);
            set_flash('info', 'Your registration has been cancelled.');
        }
    }
    redirect('student/events.php');
}

// ---- Filters ----
$allowed = ['academic', 'club', 'sports', 'workshop', 'other'];
$cat  = $_GET['cat'] ?? 'all';
if ($cat !== 'all' && !in_array($cat, $allowed, true)) { $cat = 'all'; }
$when = ($_GET['when'] ?? 'upcoming') === 'past' ? 'past' : 'upcoming';

$sql = "SELECT e.*,
          (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS attendees,
          (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.user_id = ?) AS joined
        FROM events e WHERE " . ($when === 'past' ? "e.event_date < NOW()" : "e.event_date >= NOW()");
$params = [$uid];
if ($cat !== 'all') { $sql .= " AND e.category = ?"; $params[] = $cat; }
$sql .= $when === 'past' ? " ORDER BY e.event_date DESC" : " ORDER BY e.event_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$pageTitle = 'Events';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/student_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>
    <div class="page-head"><h2>Events</h2></div>

    <div class="toolbar">
      <div class="filters">
        <a class="chip <?= $when === 'upcoming' ? 'active' : '' ?>" href="?when=upcoming&cat=<?= e($cat) ?>">Upcoming</a>
        <a class="chip <?= $when === 'past' ? 'active' : '' ?>" href="?when=past&cat=<?= e($cat) ?>">Past</a>
      </div>
      <div class="filters">
        <a class="chip <?= $cat === 'all' ? 'active' : '' ?>" href="?when=<?= $when ?>&cat=all">All</a>
        <?php foreach ($allowed as $c): ?>
          <a class="chip <?= $cat === $c ? 'active' : '' ?>" href="?when=<?= $when ?>&cat=<?= $c ?>"><?= ucfirst($c) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($events): ?>
    <div class="event-grid">
      <?php foreach ($events as $ev): $t = strtotime($ev['event_date']); ?>
        <article class="event-card">
          <div class="event-top">
            <div class="date-chip"><b><?= date('d', $t) ?></b><span><?= date('M', $t) ?></span></div>
            <div>
              <span class="badge badge-cat badge-<?= e($ev['category']) ?>"><?= e($ev['category']) ?></span>
              <h3><?= e($ev['title']) ?></h3>
            </div>
          </div>

          <p><?= e(mb_strimwidth($ev['description'], 0, 140, '...')) ?></p>

          <div class="event-info">
            <span><i class="fa-solid fa-location-dot"></i> <?= e($ev['venue']) ?></span>
            <span><i class="fa-regular fa-clock"></i> <?= date('D, d M Y \a\t g:i A', $t) ?></span>
          </div>

          <div class="event-foot">
            <small><i class="fa-solid fa-users"></i> <?= (int)$ev['attendees'] ?> going</small>

            <?php if ($when === 'past'): ?>
              <small>Event ended</small>
            <?php elseif ($ev['joined']): ?>
              <form method="POST" class="actions">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                <input type="hidden" name="action" value="cancel">
                <span class="joined-tag"><i class="fa-solid fa-check"></i> Registered</span>
                <button type="submit" class="btn btn-outline btn-sm">Cancel</button>
              </form>
            <?php else: ?>
              <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                <input type="hidden" name="action" value="register">
                <button type="submit" class="btn btn-primary btn-sm">Register</button>
              </form>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="panel"><div class="empty">No events found.</div></div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>