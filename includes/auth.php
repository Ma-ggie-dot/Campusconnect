<?php
// includes/auth.php - login and role checks
require_once __DIR__ . '/functions.php';

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

// Any logged-in user
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('info', 'Please log in to continue.');
        redirect('auth/login.php');
    }
}

// Students only (admins are sent to their own panel)
function require_student(): void {
    require_login();
    if ($_SESSION['role'] !== 'student') {
        redirect('admin/dashboard.php');
    }
}

// Admins only
function require_admin(): void {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        set_flash('danger', 'Access denied. Administrators only.');
        redirect('student/dashboard.php');
    }
}