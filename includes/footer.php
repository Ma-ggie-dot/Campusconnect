<?php // includes/footer.php - bottom of every page ?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?= BASE_URL ?>/index.php" class="logo">
          <i class="fa-solid fa-graduation-cap"></i>
          <span>Campus<b>Connect</b></span>
        </a>
        <p><?= SITE_TAGLINE ?> A student life and services platform built for Mount Kenya University.</p>
      </div>

      <div>
        <h4>Quick links</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
          <li><a href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
          <li><a href="<?= BASE_URL ?>/auth/register.php">Create account</a></li>
        </ul>
      </div>

      <div>
        <h4>Services</h4>
        <ul>
          <li>Accommodation</li>
          <li>Lost &amp; Found</li>
          <li>Marketplace</li>
          <li>Events &amp; Announcements</li>
          <li>Service Requests</li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
    </div>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>