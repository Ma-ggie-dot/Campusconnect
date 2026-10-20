<?php
// config/config.php - global site settings

define('SITE_NAME', 'CampusConnect');
define('SITE_TAGLINE', 'Everything students need, in one place.');

// Change this to your live URL when you deploy (no trailing slash)
define('BASE_URL', 'http://localhost/campusconnect');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB

// Show errors while developing. Set to false on the live server.
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}

date_default_timezone_set('Africa/Nairobi');

// Start a secure session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}