<?php
// admin/announcements.php - add, edit and delete announcements
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$categories = ['academic', 'event', 'deadline', 'general'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        if ($action === 'delete' && $id > 0) {
            $pdo->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
            set_flash('success', 'Announcement deleted.');
            redirect('admin/announcements.php');
        }

        if ($action === 'save') {
            $title    = trim($_POST['title'] ?? '');
            $body     = trim($_POST['body'] ?? '');
            $category = $_POST['category'] ?? '';

            if (strlen($title) < 5 || strlen($title) > 200) { $errors[] = 'Title must be 5 to 200 characters.'; }
            if (strlen($body) < 10)                          { $errors[] = 'Message must be at least 10 characters.'; }
            if (!in_array($category, $categories, true))     { $errors[] = 'Choose a valid category.'; }

            if (!$errors) {
                if ($id > 0) {
                    $pdo->prepare('UPDATE announcements SET title = ?, body = ?, category = ? WHERE id = ?')
                        ->execute([$title, $body, $category, $id]);
                    set_flash('success', 'Announcement updated.');
                } else {
                    $pdo->prepare('INSERT INTO announcements (title, body, category, posted_by) VALUES (?, ?, ?, ?)')
                        ->execute([$title, $body, $category, $_SESSION['user_id']]);
                    set_flash('success', 'Announcement published.');
                }
                redirect('admin/announcements.php');
            }
        }
    }
}

// Values shown in the form
$form = ['id' => 0, 'title' => '', 'body' => '', 'category' => 'general'];

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    if ($row = $stmt->fetch()) { $form = $row; }
}
if ($errors && ($_POST['action'] ?? '') === 'save') {
    $form = [
        'id'       => (int)($_POST['id'] ?? 0),
        'title'    => $_POST['title'] ?? '',
        'body'     => $_POST['body'] ?? '',
        'category' => $_POST['category'] ?? 'general',
    ];
}

$rows = $pdo->query('SELECT id, title, category, created_at FROM announcements ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Manage announcements';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container dash">
  <?php require_once __DIR__ . '/../includes/admin_nav.php'; ?>

  <div class="dash-main">
    <?php show_flash(); ?>
    <div class="page-head"><h2>Manage announcements</h2></div>

    <div class="form-card">
      <h3><?= $form['id'] ? 'Edit announcement' : 'New announcement' ?></h3>

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
          <label for="title">Title</label>
          <input type="text" id="title" name="title" class="form-control" value="<?= e($form['title']) ?>" required>
        </div>

        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category" class="form-control">
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c ?>" <?= $form['category'] === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="body">Message</label>
          <textarea id="body" name="body" class="form-control" required><?= e($form['body']) ?></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= $form['id'] ? 'Save changes' : 'Publish' ?></button>
          <?php if ($form['id']): ?>
            <a href="<?= BASE_URL ?>/admin/announcements.php" class="btn btn-outline">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Title</th><th>Category</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['title']) ?></td>
              <td><span class="badge badge-cat badge-<?= e($r['category']) ?>"><?= e($r['category']) ?></span></td>
              <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
              <td>
                <div class="actions">
                  <a href="?edit=<?= (int)$r['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                  <form method="POST" onsubmit="return confirm('Delete this announcement?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="4" class="empty">No announcements yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>