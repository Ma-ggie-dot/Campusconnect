<?php
// student/accommodation.php - browse, post and manage room listings
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_student();
$uid = $_SESSION['user_id'];

$roomTypes = [
    'single'      => 'Single room',
    'bedsitter'   => 'Bedsitter',
    'one_bedroom' => 'One bedroom',
    'shared'      => 'Shared room',
    'hostel'      => 'Hostel',
];

// Default phone = the student's own number
$stmt = $pdo->prepare('SELECT phone FROM users WHERE id = ?');
$stmt->execute([$uid]);
$userPhone = (string)$stmt->fetchColumn();

$form = ['title' => '', 'room_type' => 'single', 'location' => '', 'price' => '',
         'contact_phone' => $userPhone, 'description' => ''];
$errors = [];

// ---------- Handle form submissions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        // Mark as taken / delete (own listings only)
        if ($action === 'mark_taken' || $action === 'delete') {
            $stmt = $pdo->prepare('SELECT * FROM accommodations WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $uid]);
            $own = $stmt->fetch();

            if ($own && $action === 'mark_taken') {
                $pdo->prepare("UPDATE accommodations SET status = 'taken' WHERE id = ? AND user_id = ? AND status = 'approved'")
                    ->execute([$id, $uid]);
                set_flash('success', 'Listing marked as taken.');
            } elseif ($own && $action === 'delete') {
                delete_image($own['image']);
                $pdo->prepare('DELETE FROM accommodations WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
                set_flash('success', 'Listing deleted.');
            }
            redirect('student/accommodation.php?view=mine');
        }

        // Create a new listing
        if ($action === 'create') {
            $form = [
                'title'         => trim($_POST['title'] ?? ''),
                'room_type'     => $_POST['room_type'] ?? '',
                'location'      => trim($_POST['location'] ?? ''),
                'price'         => trim($_POST['price'] ?? ''),
                'contact_phone' => trim($_POST['contact_phone'] ?? ''),
                'description'   => trim($_POST['description'] ?? ''),
            ];

            if (strlen($form['title']) < 5 || strlen($form['title']) > 200) { $errors[] = 'Title must be 5 to 200 characters.'; }
            if (!isset($roomTypes[$form['room_type']]))                      { $errors[] = 'Choose a room type.'; }
            if ($form['location'] === '' || strlen($form['location']) > 150) { $errors[] = 'Enter the location (for example "Thika Road, near the main gate").'; }
            if (!is_numeric($form['price']) || $form['price'] < 500 || $form['price'] > 500000) {
                $errors[] = 'Enter a monthly price between KSh 500 and KSh 500,000.';
            }
            if (!valid_ke_phone($form['contact_phone']))                     { $errors[] = 'Enter a valid Kenyan phone number, e.g. 0712345678.'; }
            if (strlen($form['description']) < 20)                           { $errors[] = 'Description must be at least 20 characters.'; }

            $imagePath = null;
            if (!$errors) {
                try {
                    $imagePath = save_image($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE], 'accommodation');
                } catch (RuntimeException $ex) {
                    $errors[] = $ex->getMessage();
                }
            }

            if (!$errors) {
                $pdo->prepare(
                    'INSERT INTO accommodations (user_id, title, description, room_type, location, price, contact_phone, image)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                )->execute([$uid, $form['title'], $form['description'], $form['room_type'],
                            $form['location'], $form['price'], $form['contact_phone'], $imagePath]);

                set_flash('success', 'Listing submitted! It will appear for other students once an administrator approves it.');
                redirect('student/accommodation.php?view=mine');
            }
        }
    }
}

// ---------- Decide which tab to show ----------
$view = $_GET['view'] ?? 'browse';
if (!in_array($view, ['browse', 'post', 'mine'], true)) { $view = 'browse'; }
if ($errors && ($_POST['action'] ?? '') === 'create') { $view = 'post'; }

// ---------- Browse data ----------
$rooms = [];
$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';
$max = (int)($_GET['max'] ?? 0);

if ($view === 'browse') {
    $sql = "SELECT * FROM accommodations WHERE status = 'approved'";
    $params = [];
    if ($q !== '') {
        $sql .= " AND (title LIKE ? OR location LIKE ? OR description LIKE ?)";
        array_push($params, "%$q%", "%$q%", "%$q%");
    }
    if (isset($roomTypes[$type])) { $sql .= " AND room_type = ?"; $params[] = $type; }
    if ($max > 0)                 { $sql .= " AND price <= ?";    $params[] = $max; }
    $sql .= " ORDER BY created_at DESC LIMIT 60";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
}

// ---------- My listings data ----------
$mine = [];
if ($view === 'mine') {
    $stmt = $pdo->prepare('SELECT * FROM accommodations WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$uid]);
    $mine = $stmt->fetchAll();
}

$pageTitle = 'Accommodation';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/student_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>
    <div class="page-head"><h2>Accommodation</h2></div>

    <div class="toolbar">
      <div class="filters">
        <a class="chip <?= $view === 'browse' ? 'active' : '' ?>" href="?view=browse">Browse rooms</a>
        <a class="chip <?= $view === 'post' ? 'active' : '' ?>" href="?view=post">Post a room</a>
        <a class="chip <?= $view === 'mine' ? 'active' : '' ?>" href="?view=mine">My listings</a>
      </div>
    </div>

<?php if ($view === 'browse'): ?>

    <form class="filter-form" method="GET">
      <input type="hidden" name="view" value="browse">
      <div class="form-group">
        <label for="q">Search</label>
        <input type="search" id="q" name="q" class="form-control" placeholder="Location or keyword" value="<?= e($q) ?>">
      </div>
      <div class="form-group">
        <label for="type">Room type</label>
        <select id="type" name="type" class="form-control">
          <option value="">All types</option>
          <?php foreach ($roomTypes as $k => $label): ?>
            <option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="max">Max price (KSh)</label>
        <input type="number" id="max" name="max" class="form-control" min="0" step="500" placeholder="e.g. 8000" value="<?= $max > 0 ? $max : '' ?>">
      </div>
      <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if ($rooms): ?>
    <div class="event-grid">
      <?php foreach ($rooms as $r): ?>
        <article class="listing-card">
          <div class="listing-img">
            <?php if ($r['image']): ?>
              <img src="<?= e(upload_url($r['image'])) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
            <?php else: ?>
              <i class="fa-solid fa-house"></i>
            <?php endif; ?>
          </div>
          <div class="listing-body">
            <span class="badge badge-cat"><?= e($roomTypes[$r['room_type']] ?? $r['room_type']) ?></span>
            <h3><?= e($r['title']) ?></h3>
            <div class="price-tag">KSh <?= number_format($r['price']) ?> <small>/ month</small></div>
            <div class="event-info"><span><i class="fa-solid fa-location-dot"></i> <?= e($r['location']) ?></span></div>
            <p><?= e(mb_strimwidth($r['description'], 0, 110, '...')) ?></p>
            <div class="listing-actions">
              <a class="btn btn-primary btn-sm" href="tel:<?= e(preg_replace('/\s+/', '', $r['contact_phone'])) ?>">
                <i class="fa-solid fa-phone"></i> Call</a>
              <a class="btn btn-whatsapp btn-sm" target="_blank" rel="noopener"
                 href="https://wa.me/<?= e(wa_number($r['contact_phone'])) ?>?text=<?= rawurlencode('Hi, I saw your room listing "' . $r['title'] . '" on CampusConnect.') ?>">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="panel"><div class="empty">No rooms found. Try changing your search or filters.</div></div>
    <?php endif; ?>

<?php elseif ($view === 'post'): ?>

    <div class="form-card">
      <h3>Post a room</h3>
      <p class="text-muted" style="margin-bottom:1rem;">An administrator will review your listing before it is shown to other students.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger alert-persist">
          <?php foreach ($errors as $err): ?><div>&bull; <?= e($err) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">

        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Self-contained bedsitter near main gate" value="<?= e($form['title']) ?>" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="room_type">Room type</label>
            <select id="room_type" name="room_type" class="form-control">
              <?php foreach ($roomTypes as $k => $label): ?>
                <option value="<?= $k ?>" <?= $form['room_type'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="price">Price per month (KSh)</label>
            <input type="number" id="price" name="price" class="form-control" min="500" step="100" value="<?= e($form['price']) ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" class="form-control" placeholder="Area and landmark" value="<?= e($form['location']) ?>" required>
          </div>
          <div class="form-group">
            <label for="contact_phone">Contact phone</label>
            <input type="tel" id="contact_phone" name="contact_phone" class="form-control" value="<?= e($form['contact_phone']) ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" class="form-control" placeholder="Water, security, distance to campus, deposit, rules..." required><?= e($form['description']) ?></textarea>
        </div>

        <div class="form-group">
          <label for="image">Photo (optional)</label>
          <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
          <div class="hint">JPG, PNG or WEBP, up to 2 MB.</div>
        </div>

        <button type="submit" class="btn btn-primary">Submit for approval</button>
      </form>
    </div>

<?php else: ?>

    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Listing</th><th>Price</th><th>Status</th><th>Posted</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if ($mine): foreach ($mine as $m): ?>
            <tr>
              <td>
                <div class="cell-title">
                  <?php if ($m['image']): ?>
                    <img class="thumb" src="<?= e(upload_url($m['image'])) ?>" alt="">
                  <?php endif; ?>
                  <div><?= e($m['title']) ?><br><small><?= e($roomTypes[$m['room_type']] ?? '') ?> &middot; <?= e($m['location']) ?></small></div>
                </div>
              </td>
              <td>KSh <?= number_format($m['price']) ?></td>
              <td><span class="badge badge-<?= e($m['status']) ?>"><?= e($m['status']) ?></span></td>
              <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
              <td>
                <div class="actions">
                  <?php if ($m['status'] === 'approved'): ?>
                    <form method="POST">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="mark_taken">
                      <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                      <button type="submit" class="btn btn-outline btn-sm">Mark taken</button>
                    </form>
                  <?php endif; ?>
                  <form method="POST" onsubmit="return confirm('Delete this listing?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="empty">You have not posted any rooms yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>