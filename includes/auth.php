<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

/* ---------- output and routing ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $p = ''): string { return BASE_URL . '/' . ltrim($p, '/'); }
function redirect(string $p): void { header('Location: ' . url($p)); exit; }
function post(string $k): string { return trim((string)($_POST[$k] ?? '')); }
function old(string $k, string $default = ''): string { return e($_POST[$k] ?? $default); }
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    if (is_post() && !hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

/* ---------- flash messages ---------- */
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

/* ---------- login state ---------- */
function user(): ?array {
    static $u = false;
    if ($u === false) { $u = empty($_SESSION['uid']) ? null : row('SELECT id,name,email,phone,role FROM users WHERE id=?', [$_SESSION['uid']]); }
    return $u;
}
function login_user(int $id): void { session_regenerate_id(true); $_SESSION['uid'] = $id; }
function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $c = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $c['path'], $c['domain'], $c['secure'], $c['httponly']);
    }
    session_destroy();
}
// Call at the top of every protected page. Checks the role and the CSRF token on POST.
function require_role(string $role): array {
    $u = user();
    if (!$u) { redirect('login.php'); }
    if ($u['role'] !== $role) { http_response_code(403); exit('You do not have access to this page.'); }
    csrf_check();
    return $u;
}
function home_for(string $role): string { return $role . '/dashboard.php'; }

/* ---------- validation ---------- */
function clean(string $s): string { return trim(preg_replace('/\s+/u', ' ', $s)); }
function valid_name(string $s, int $min = 2, int $max = 100): bool {
    $l = mb_strlen($s);
    return $l >= $min && $l <= $max && preg_match('/\p{L}/u', $s) === 1 && preg_match('/^[\p{L}\p{N} .,\'&()\/-]+$/u', $s) === 1;
}
function valid_phone(string $s): bool { return $s === '' || preg_match('/^[0-9+\- ]{7,20}$/', $s) === 1; }
function valid_date(string $s): bool { $d = DateTime::createFromFormat('Y-m-d', $s); return $d && $d->format('Y-m-d') === $s; }
function password_error(string $p): ?string {
    if (strlen($p) < 8) return 'Password needs at least 8 characters.';
    if (!preg_match('/[A-Za-z]/', $p) || !preg_match('/\d/', $p)) return 'Password needs letters and numbers.';
    return null;
}

/* ---------- pagination ---------- */
function paginate(int $total, int $per = 10): array {
    $pages = max(1, (int)ceil($total / $per));
    $page = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
    return [$page, $pages, ($page - 1) * $per, $per];
}
function pager(int $page, int $pages): string {
    if ($pages < 2) return '';
    $h = '<nav class="pager" aria-label="Pages">';
    for ($i = 1; $i <= $pages; $i++) {
        $qs = http_build_query(array_merge($_GET, ['page' => $i]));
        $h .= '<a href="?' . e($qs) . '"' . ($i === $page ? ' aria-current="page"' : '') . '>' . $i . '</a>';
    }
    return $h . '</nav>';
}
