<?php
require_once __DIR__ . '/includes/layout.php';
$u = user();
if ($u) { redirect(home_for($u['role'])); }
page_header('');
?>
<section class="hero">
  <div>
    <h1>Every child. Every dose. <em>On time.</em></h1>
    <p>Register your child, see which vaccines are due, book a hospital and keep the vaccination record in one place.</p>
    <p style="display:flex;gap:10px;flex-wrap:wrap"><a class="btn" href="<?= e(url('register.php')) ?>">Create an account</a><a class="btn ghost" href="<?= e(url('login.php')) ?>">Log in</a></p>
  </div>
  <img src="<?= e(url('assets/baby.jpg')) ?>" alt="Smiling baby">
</section>
<div class="grid" style="margin-top:24px">
  <div class="card"><h3>Parents</h3><p class="muted">Add children, get due-date alerts, book a hospital and view past vaccinations.</p></div>
  <div class="card"><h3>Hospitals</h3><p class="muted">Receive approved appointments and mark each dose as vaccinated or not.</p></div>
  <div class="card"><h3>Admin</h3><p class="muted">Approve requests, manage vaccines and hospitals, and export reports.</p></div>
</div>
<?php page_footer();
