<?php
require_once __DIR__ . '/../includes/layout.php';
$u = require_role('hospital');
$h = row('SELECT * FROM hospitals WHERE user_id=?', [$u['id']]);
if (!$h) { http_response_code(403); exit('No hospital is linked to this account.'); }
$errors = [];
if (is_post()) {
    $name = clean(post('name')); $addr = clean(post('address')); $city = clean(post('city')); $phone = post('phone');
    if (!valid_name($name, 3, 120)) $errors['name'] = 'Enter the hospital name (3 to 120 characters).';
    elseif (val('SELECT id FROM hospitals WHERE LOWER(name)=? AND id<>?', [mb_strtolower($name), $h['id']])) $errors['name'] = 'A hospital with this name already exists.';
    if (mb_strlen($addr) < 5 || mb_strlen($addr) > 200) $errors['address'] = 'Enter the full address (5 to 200 characters).';
    if (!valid_name($city, 2, 80)) $errors['city'] = 'Enter the city.';
    if (!valid_phone($phone)) $errors['phone'] = 'Phone can use digits, + and - (7 to 20 characters).';
    if (!$errors) { q('UPDATE hospitals SET name=?, address=?, city=?, phone=? WHERE id=?', [$name, $addr, $city, $phone ?: null, $h['id']]); flash('ok', 'Hospital details updated.'); redirect('hospital/profile.php'); }
}
$err = fn($k) => isset($errors[$k]) ? '<span class="help" style="color:var(--bad)">' . e($errors[$k]) . '</span>' : '';
page_header('Hospital profile');
?>
<div class="card" style="max-width:600px"><form method="post" class="stack"><?= csrf_field() ?>
  <label>Hospital name<input name="name" required maxlength="120" value="<?= old('name', $h['name']) ?>"><?= $err('name') ?></label>
  <label>Address<input name="address" required maxlength="200" value="<?= old('address', $h['address']) ?>"><?= $err('address') ?></label>
  <div class="row2"><label>City<input name="city" required maxlength="80" value="<?= old('city', $h['city']) ?>"><?= $err('city') ?></label>
  <label>Phone<input name="phone" maxlength="20" value="<?= old('phone', $h['phone'] ?? '') ?>"><?= $err('phone') ?></label></div>
  <button class="btn" type="submit">Save</button></form></div>
<?php page_footer();
