<?php
// admin/accommodation.php - review, approve, reject and remove room listings
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$statuses = ['pending', 'approved', 'rejected', 'taken'];
$status = $_GET['status'] ?? 'pending';
if (!in_array($status, $statuses, true)) { $status = 'pending'; }

$roomTypes = [
    'single' => 'Single room', 'bedsitter' => 'Bedsitter', 'one_bedroom' => 'One bedroom',
    'shared' => 'Shared room', 'hostel' => 'Hostel',
];

// ---------- Handle actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $back = $_POST['status'] ?? 'pending';
    if (!in_array($back, $statuses, true)) { $back = 'pending'; }

    if (!verify_csrf()) {
        set_flash('danger', 'Invalid form submission. Please try again.');
    } else {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        $stmt = $pdo->prepare('SELECT * FROM accommodations WHERE id = ?');
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if ($item) {
            $shortTitle = mb_strimwidth($item['title'], 0, 60, '...');

            if ($action === 'approve' || $action === 'reject') {
                $new = $action === 'approve' ? 'approved' : 'rejected';
                $pdo->prepare('UPDATE accommodations SET status = ? WHERE id = ?')->execute([$new, $id]);

                // Tell the student
                $msg = 'Your room listing "' . $shortTitle . '" was ' . $new . '.';
                $pdo->prepare('INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)')
                    ->execute([$item['user_id'], $msg, 'student/accommodation.php?view=mine']);

                set_flash('success', 'Listing ' . $new . '.');
            } elseif ($action === 'delete') {
                delete_image($item['image']);
                $pdo->prepare('DELETE FROM accommodations WHERE id = ?')->execute([$id]);
                set_flash('success', 'Listing deleted.');
            }
        }
    }
    redirect('admin/accommodation.php?status=' . $back);
}

// ---------- Counts for the tabs ----------
$counts = array_fill_keys($statuses, 0);
foreach ($pdo->query('SELECT status, COUNT(*) AS n FROM accommodations GROUP BY status')->fetchAll() as $c) {
    $counts[$c['status']] = (int)$c['n'];
}

// ---------- Listings for the chosen tab ----------
$stmt = $pdo->prepare(
    'SELECT a.*, u.full_name FROM accommodations a
     JOIN users u ON u.id = a.user_id
     WHERE a.status = ? ORDER BY a.created_at DESC'
);
$stmt->execute([$status]);
$rows = $stmt->fetchAll();

$pageTitle = 'Manage accommodation';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/admin_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>
    <div class="page-head"><h2>Manage accommodation</h2></div>

    <div class="toolbar">
      <div class="filters">
        <?php foreach ($statuses as $s): ?>
          <a class="chip <?= $status === $s ? 'active' : '' ?>" href="?status=<?= $s ?>">
            <?= ucfirst($s) ?> (<?= $counts[$s] ?>)
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Listing</th><th>Posted by</th><th>Price</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td>
                <div class="cell-title">
                  <?php if ($r['image']): ?>
                    <a href="<?= e(upload_url($r['image'])) ?>" target="_blank" rel="noopener">
                      <img class="thumb" src="<?= e(upload_url($r['image'])) ?>" alt="">
                    </a>
                  <?php endif; ?>
                  <div>
                    <?= e($r['title']) ?><br>
                    <small><?= e($roomTypes[$r['room_type']] ?? '') ?> &middot; <?= e($r['location']) ?></small><br>
                    <small><?= e(mb_strimwidth($r['description'], 0, 90, '...')) ?></small>
                  </div>
                </div>
              </td>
              <td><?= e($r['full_name']) ?><br><small><?= e($r['contact_phone']) ?></small></td>
              <td>KSh <?= number_format($r['price']) ?></td>
              <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
              <td>
                <div class="actions">
                  <?php if (in_array($r['status'], ['pending', 'rejected'], true)): ?>
                    <form method="POST">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="approve">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <input type="hidden" name="status" value="<?= e($status) ?>">
                      <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                  <?php endif; ?>
                  <?php if (in_array($r['status'], ['pending', 'approved'], true)): ?>
                    <form method="POST">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="reject">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <input type="hidden" name="status" value="<?= e($status) ?>">
                      <button type="submit" class="btn btn-outline btn-sm">Reject</button>
                    </form>
                  <?php endif; ?>
                  <form method="POST" onsubmit="return confirm('Delete this listing permanently?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="status" value="<?= e($status) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="empty">No <?= e($status) ?> listings.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>