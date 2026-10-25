<?php
// includes/functions.php - helpers used across the whole site
require_once __DIR__ . '/../config/config.php';

// Escape output so user input can never run as HTML or JavaScript (prevents XSS)
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Redirect to a page inside the site, e.g. redirect('auth/login.php')
function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

// Flash messages: shown once after a redirect
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
}

// CSRF protection: stops other websites from submitting forms on behalf of a user
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

// Re-fill a form field after a validation error
function old(string $key): string {
    return e($_POST[$key] ?? '');
}
// ---------- Image upload helpers ----------

// Saves an uploaded image safely. Returns e.g. "accommodation/ab12cd.jpg",
// or null if no file was chosen. Throws RuntimeException with a friendly message on failure.
function save_image(array $file, string $folder): ?string {
    $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_NO_FILE) { return null; }
    if ($err !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image could not be uploaded. Please try again.');
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('The image is too large. Maximum size is 2 MB.');
    }

    // Check the real file type, not just the name the user gave it
    $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime])) {
        throw new RuntimeException('Only JPG, PNG or WEBP images are allowed.');
    }

    $dir = UPLOAD_DIR . $folder . '/';
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }

    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        throw new RuntimeException('The image could not be saved. Please try again.');
    }
    return $folder . '/' . $name;
}

// Deletes an image file that was saved with save_image()
function delete_image(?string $path): void {
    if ($path && strpos($path, '..') === false) {
        $full = UPLOAD_DIR . $path;
        if (is_file($full)) { unlink($full); }
    }
}

// Public web address of an uploaded image
function upload_url(string $path): string {
    return BASE_URL . '/uploads/' . $path;
}

// ---------- Phone helpers ----------

function valid_ke_phone(string $phone): bool {
    return (bool)preg_match('/^(\+?254|0)[17]\d{8}$/', $phone);
}

// 0712345678 -> 254712345678 (for WhatsApp links)
function wa_number(string $phone): string {
    $p = preg_replace('/\D/', '', $phone);
    if (substr($p, 0, 1) === '0') { $p = '254' . substr($p, 1); }
    return $p;
}