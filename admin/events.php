<?php
// admin/events.php - add, edit and delete events
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$categories = ['academic', 'club', 'sports', 'workshop', 'other'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        if ($action === 'delete' && $id > 0) {
            $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
            set_flash('success', 'Event deleted.');
            redirect('admin/events.php');
        }

        if ($action === 'save') {
            $title    = trim($_POST['title'] ?? '');
            $desc     = trim($_POST['description'] ?? '');
            $category = $_POST['category'] ?? '';
            $venue    = trim($_POST['venue'] ?? '');
            $dateRaw  = $_POST['event_date'] ?? '';
            $ts       = strtotime($dateRaw);

            if (strlen($title) < 5 || strlen($title) > 200) { $errors[] = 'Title must be 5 to 200 characters.'; }
            if (strlen($desc) < 10)                          { $errors[] = 'Description must be at least 10 characters.'; }
            if (!in_array($category, $categories, true))     { $errors[] = 'Choose a valid category.'; }
            if ($venue === '' || strlen($venue) > 150)       { $errors[] = 'Enter the venue.'; }
            if (!$ts)                                        { $errors[] = 'Choose a valid date and time.'; }

            if (!$errors) {
                $dt = date('Y-m-d H:i:s', $ts);
                if ($id > 0) {
                    $pdo->prepare('UPDATE events SET title = ?, description = ?, category = ?, venue = ?, event_date = ? WHERE id = ?')
                        ->execute([$title, $desc, $category, $venue, $dt, $id]);
                    set_flash('success', 'Event updated.');
                } else {
                    $pdo->prepare('INSERT INTO events (title, description, category, venue, event_date, created_by) VALUES (?, ?, ?, ?, ?, ?)')
                        ->execute([$title, $desc, $category, $venue, $dt, $_SESSION['user_id']]);
                    set_flash('success', 'Event created.');
                }
                redirect('admin/events.php');
            }
        }
    }
}

// Values shown in the form
$form = ['id' => 0, 'title' => '', 'description' => '', 'category' => 'academic', 'venue' => '', 'event_date' => ''];

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    if ($row = $stmt->fetch()) {
        $form = $row;
        $form['event_date'] = date('Y-m-d\TH:i', strtotime($row['event_date']));
    }
}
if ($errors && ($_POST['action'] ?? '') === 'save') {
    $form = [
        'id'          => (int)($_POST['id'] ?? 0),
        'title'       => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'category'    => $_POST['category'] ?? 'academic',
        'venue'       => $_POST['venue'] ?? '',
        'event_date'  => $_POST['event_date'] ?? '',
    ];
}

$rows = $pdo->query(
    'SELECT e.id, e.title, e.category, e.venue, e.event_date,
            (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id) AS attendees
     FROM events e ORDER BY e.event_date DESC'
)->fetchAll();

$pageTitle = 'Manage events';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/admin_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>
    <div class="page-head"><h2>Manage events</h2></div>

    <div class="form-card">
      <h3><?= $form['id'] ? 'Edit event' : 'New event' ?></h3>

      <?php if ($errors): ?>
        <div class="alert alert-danger alert-persist">
          <?php foreach ($errors as $err): ?><div>&bull; <?= e($err) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)$form['id'] ?>">

        <div class="form-group">
          <label for="title">Event title</label>
          <input type="text" id="title" name="title" class="form-control" value="<?= e($form['title']) ?>" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" class="form-control">
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c ?>" <?= $form['category'] === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="event_date">Date and time</label>
            <input type="datetime-local" id="event_date" name="event_date" class="form-control" value="<?= e($form['event_date']) ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label for="venue">Venue</label>
          <input type="text" id="venue" name="venue" class="form-control" value="<?= e($form['venue']) ?>" required>
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" class="form-control" required><?= e($form['description']) ?></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= $form['id'] ? 'Save changes' : 'Create event' ?></button>
          <?php if ($form['id']): ?>
            <a href="<?= BASE_URL ?>/admin/events.php" class="btn btn-outline">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Event</th><th>Date</th><th>Venue</th><th>Going</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['title']) ?><br><span class="badge badge-cat badge-<?= e($r['category']) ?>"><?= e($r['category']) ?></span></td>
              <td><?= date('d M Y, g:i A', strtotime($r['event_date'])) ?></td>
              <td><?= e($r['venue']) ?></td>
              <td><?= (int)$r['attendees'] ?></td>
              <td>
                <div class="actions">
                  <a href="?edit=<?= (int)$r['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                  <form method="POST" onsubmit="return confirm('Delete this event?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="empty">No events yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>