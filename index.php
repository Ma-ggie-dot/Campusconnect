<?php
// index.php - public landing page
$pageTitle = 'Home';
require_once 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="eyebrow" style="color: var(--accent);">Student Life &amp; Services Hub</span>
      <h1>Everything students need, <span>in one place.</span></h1>
      <p class="lead">
        Find accommodation, report lost items, buy and sell, discover events and
        request campus services, without searching through endless WhatsApp groups.
      </p>
      <div class="hero-actions">
        <a href="auth/register.php" class="btn btn-accent">Create free account</a>
        <a href="auth/login.php" class="btn btn-light">Login</a>
      </div>
    </div>

    <div class="hero-card">
      <h3>What you can do on CampusConnect</h3>
      <ul class="hero-list">
        <li><i class="fa-solid fa-bullhorn"></i> Read verified campus announcements</li>
        <li><i class="fa-solid fa-calendar-days"></i> Discover and join events</li>
        <li><i class="fa-solid fa-house"></i> Find rooms near campus</li>
        <li><i class="fa-solid fa-magnifying-glass"></i> Report or find lost items</li>
        <li><i class="fa-solid fa-screwdriver-wrench"></i> Track your service requests</li>
      </ul>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section" id="features">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Features</span>
      <h2>One platform, six campus services</h2>
      <p>Everything is reviewed by administrators so the information you see is reliable.</p>
    </div>

    <div class="grid grid-3">
      <article class="card">
        <div class="card-icon"><i class="fa-solid fa-bullhorn"></i></div>
        <h3>Announcements</h3>
        <p>Academic notices, deadlines and general updates from the university, all in one feed.</p>
      </article>

      <article class="card">
        <div class="card-icon"><i class="fa-solid fa-calendar-days"></i></div>
        <h3>Events</h3>
        <p>Club activities, sports, workshops and academic events. Register in one click.</p>
      </article>

      <article class="card">
        <div class="card-icon"><i class="fa-solid fa-house"></i></div>
        <h3>Accommodation</h3>
        <p>Search rooms and houses by location, price and room type, with contact details.</p>
      </article>

      <article class="card">
        <div class="card-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <h3>Lost &amp; Found</h3>
        <p>Report a lost item or hand in something you found, and mark it recovered.</p>
      </article>

      <article class="card">
        <div class="card-icon"><i class="fa-solid fa-cart-shopping"></i></div>
        <h3>Marketplace</h3>
        <p>Buy and sell textbooks, electronics, furniture and clothes with other students.</p>
      </article>

      <article class="card">
        <div class="card-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <h3>Service Requests</h3>
        <p>Report a problem to ICT or maintenance and follow it from pending to resolved.</p>
      </article>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section section-alt" id="how">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">How it works</span>
      <h2>Get started in three steps</h2>
    </div>

    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <h3>Create an account</h3>
        <p>Register with your student email and registration number.</p>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <h3>Explore services</h3>
        <p>Browse announcements, events, rooms, items and more from your dashboard.</p>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <h3>Post and request</h3>
        <p>Submit listings or requests. Admins approve them and you get notified.</p>
      </div>
    </div>
  </div>
</section>

<!-- CALL TO ACTION -->
<section class="section">
  <div class="container">
    <div class="cta">
      <h2>Ready to make campus life easier?</h2>
      <p>Join CampusConnect and stop missing the information that matters.</p>
      <a href="auth/register.php" class="btn btn-accent">Join CampusConnect</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>