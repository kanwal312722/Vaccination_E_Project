<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/schedule.php';

function nav_links(?string $role): array {
    return match ($role) {
        'admin' => ['admin/dashboard.php' => 'Dashboard', 'admin/requests.php' => 'Requests', 'admin/children.php' => 'Children',
                    'admin/vaccines.php' => 'Vaccines', 'admin/hospitals.php' => 'Hospitals', 'admin/bookings.php' => 'Bookings', 'admin/reports.php' => 'Reports'],
        'parent' => ['parent/dashboard.php' => 'Dashboard', 'parent/children.php' => 'My children', 'parent/book.php' => 'Book',
                     'parent/bookings.php' => 'Bookings', 'parent/report.php' => 'Reports', 'parent/profile.php' => 'Profile'],
        'hospital' => ['hospital/dashboard.php' => 'Appointments', 'hospital/profile.php' => 'Profile'],
        default => [],
    };
}

function page_header(string $title, ?string $role = null): void {
    $u = user();
    $self = trim(str_replace('\\', '/', substr($_SERVER['SCRIPT_NAME'], strlen(BASE_URL))), '/');
    ?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head><body>
<header class="bar"><div class="wrap bar-in">
  <a class="brand" href="<?= e(url($u ? home_for($u['role']) : 'index.php')) ?>"><svg width="26" height="26" viewBox="0 0 32 32" aria-hidden="true"><path d="M16 2 28 6v9c0 7.500-4.800 12.600-12 15C8.800 27.600 4 22.500 4 15V6z" fill="var(--primary)"/><path d="M16 9v12M10 15h12" stroke="var(--accent)" stroke-width="3.400" stroke-linecap="round" fill="none"/></svg><?= e(APP_NAME) ?></a>
  <nav aria-label="Main">
  <?php foreach (nav_links($u['role'] ?? null) as $href => $label): ?>
    <a href="<?= e(url($href)) ?>"<?= $self === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
  <?php if ($u): ?>
    <form method="post" action="<?= e(url('logout.php')) ?>" class="inline"><?= csrf_field() ?><button class="link" type="submit">Log out (<?= e(explode(' ', $u['name'])[0]) ?>)</button></form>
  <?php else: ?>
    <a href="<?= e(url('login.php')) ?>">Log in</a><a class="btn sm" href="<?= e(url('register.php')) ?>">Register</a>
  <?php endif; ?>
  </nav>
</div></header>
<main class="wrap">
<?php foreach (flashes() as [$type, $msg]): ?><div class="flash <?= e($type) ?>" role="status"><?= e($msg) ?></div><?php endforeach; ?>
<?php if ($title !== ''): ?><h1><?= e($title) ?></h1><?php endif;
}

function page_footer(): void { ?>
</main>
<footer class="wrap foot"><?= e(APP_NAME) ?> · Pakistan EPI schedule</footer>
</body></html>
<?php }
