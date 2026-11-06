<?php
require_once __DIR__ . '/../includes/layout.php';
$u = require_role('parent');
$errors = [];
if (is_post()) {
    if (post('action') === 'profile') {
        $name = clean(post('name')); $phone = post('phone');
        if (!valid_name($name)) $errors['name'] = 'Enter your full name (2 to 100 characters).';
        if (!valid_phone($phone)) $errors['phone'] = 'Phone can use digits, + and - (7 to 20 characters).';
        if (!$errors) { q('UPDATE users SET name=?, phone=? WHERE id=?', [$name, $phone ?: null, $u['id']]); flash('ok', 'Profile updated.'); redirect('parent/profile.php'); }
    }
    if (post('action') === 'password') {
        $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['password'] ?? '');
        $hash = val('SELECT password_hash FROM users WHERE id=?', [$u['id']]);
        if (!password_verify($cur, $hash)) $errors['current'] = 'Current password is incorrect.';
        elseif ($e = password_error($new)) $errors['password'] = $e;
        elseif (!hash_equals($new, (string)($_POST['confirm'] ?? ''))) $errors['confirm'] = 'Passwords do not match.';
        else { q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($new, PASSWORD_DEFAULT), $u['id']]); flash('ok', 'Password changed.'); redirect('parent/profile.php'); }
    }
}
$err = fn($k) => isset($errors[$k]) ? '<span class="help" style="color:var(--bad)">' . e($errors[$k]) . '</span>' : '';
page_header('My profile');
?>
<div class="grid">
<div class="card"><h3>Details</h3><form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="profile">
  <label>Full name<input name="name" required maxlength="100" value="<?= old('name', $u['name']) ?>"><?= $err('name') ?></label>
  <label>Email<input value="<?= e($u['email']) ?>" disabled></label>
  <label>Phone<input name="phone" maxlength="20" value="<?= old('phone', $u['phone'] ?? '') ?>"><?= $err('phone') ?></label>
  <button class="btn" type="submit">Save</button></form></div>
<div class="card"><h3>Change password</h3><form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="password">
  <label>Current password<input type="password" name="current" required autocomplete="current-password"><?= $err('current') ?></label>
  <label>New password<input type="password" name="password" required minlength="8" autocomplete="new-password"><?= $err('password') ?></label>
  <label>Confirm new password<input type="password" name="confirm" required autocomplete="new-password"><?= $err('confirm') ?></label>
  <button class="btn" type="submit">Change password</button></form></div>
</div>
<?php page_footer();
