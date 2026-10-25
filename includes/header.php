<?php
// includes/header.php - top of every page
require_once __DIR__ . '/../config/config.php';

$pageTitle   = $pageTitle ?? 'Home';
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn  = isset($_SESSION['user_id']);
$userRole    = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="CampusConnect - accommodation, events, lost and found, marketplace and student services in one place.">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= SITE_NAME ?></title>

  <!-- Fonts: Poppins (headings) and Inter (body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Site styles -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="container nav">
    <a href="<?= BASE_URL ?>/index.php" class="logo">
      <i class="fa-solid fa-graduation-cap"></i>
      <span>Campus<b>Connect</b></span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
      <i class="fa-solid fa-bars"></i>
    </button>

    <nav class="nav-links" id="navLinks">
      <a href="<?= BASE_URL ?>/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>

      <?php if (!$isLoggedIn): ?>
        <a href="<?= BASE_URL ?>/index.php#features">Features</a>
        <a href="<?= BASE_URL ?>/index.php#how">How it works</a>
        <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-accent btn-sm">Get started</a>

      <?php elseif ($userRole === 'admin'): ?>
        <a href="<?= BASE_URL ?>/admin/dashboard.php">Admin Panel</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline btn-sm">Logout</a>

      <?php else: ?>
        <a href="<?= BASE_URL ?>/student/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline btn-sm">Logout</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>