<?php
require_once __DIR__ . '/../includes/layout.php';
$u = require_role('parent');
$kids = rows('SELECT * FROM children WHERE parent_id=? ORDER BY name', [$u['id']]);
$hospitals = rows('SELECT id,name,address,city FROM hospitals WHERE active=1 ORDER BY name');
$errors = [];

if (is_post()) {
    $cid = (int)($_POST['child'] ?? 0); $vid = (int)($_POST['vaccine'] ?? 0); $hid = (int)($_POST['hospital'] ?? 0); $date = post('date');
    $child = child_of_parent($cid, (int)$u['id']);                       // a parent can only book for their own child
    $v = row('SELECT * FROM vaccines WHERE id=?', [$vid]);
    $h = row('SELECT * FROM hospitals WHERE id=? AND active=1', [$hid]);
    if (!$child) $errors['child'] = 'Choose one of your children.';
    if (!$v) $errors['vaccine'] = 'Choose a vaccine.';
    elseif (!$v['available']) $errors['vaccine'] = $v['name'] . ' is unavailable right now. Choose another vaccine.';
    if (!$h) $errors['hospital'] = 'Choose a hospital.';
    if (!valid_date($date)) $errors['date'] = 'Enter a valid date.';
    elseif ($date < date('Y-m-d')) $errors['date'] = 'The date cannot be in the past.';
    elseif ($date > date('Y-m-d', strtotime('+2 years'))) $errors['date'] = 'Pick a date within the next 2 years.';
    if ($child && $v) {
        if (val("SELECT COUNT(*) FROM vaccination_records WHERE child_id=? AND vaccine_id=? AND status='vaccinated'", [$cid, $vid])) $errors['vaccine'] = 'This dose is already recorded as given.';
        elseif (val("SELECT COUNT(*) FROM appointments WHERE child_id=? AND vaccine_id=? AND (status='pending' OR (status='approved' AND result='pending'))", [$cid, $vid])) $errors['vaccine'] = 'This vaccine is already booked for this child.';
    }
    if (!$errors) {
        q('INSERT INTO appointments (child_id,vaccine_id,hospital_id,preferred_date) VALUES (?,?,?,?)', [$cid, $vid, $hid, $date]);
        flash('ok', 'Request sent. You will see the status under Bookings once the admin decides.');
        redirect('parent/bookings.php');
    }
}
$selChild = (int)($_POST['child'] ?? $_GET['child'] ?? ($kids[0]['id'] ?? 0));
$selVac = (int)($_POST['vaccine'] ?? $_GET['vaccine'] ?? 0);
$vaccines = rows('SELECT * FROM vaccines ORDER BY age_weeks, id');
$err = fn($k) => isset($errors[$k]) ? '<span class="help" style="color:var(--bad)">' . e($errors[$k]) . '</span>' : '';
page_header('Book a vaccination');
if (!$kids): ?><div class="card"><div class="empty">Add a child first.</div><p style="margin-top:14px"><a class="btn" href="<?= e(url('parent/children.php')) ?>">Add a child</a></p></div>
<?php elseif (!$hospitals): ?><div class="card"><div class="empty">No hospitals are active yet. Please check again later.</div></div>
<?php else: ?>
<div class="card" style="max-width:600px"><form method="post" class="stack"><?= csrf_field() ?>
  <label>Child<select name="child" required><?php foreach ($kids as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $selChild === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><?= $err('child') ?></label>
  <label>Vaccine<select name="vaccine" required><option value="">Choose</option><?php foreach ($vaccines as $v): ?><option value="<?= (int)$v['id'] ?>" <?= $selVac === (int)$v['id'] ? 'selected' : '' ?> <?= $v['available'] ? '' : 'disabled' ?>><?= e($v['name']) ?><?= $v['available'] ? '' : ' (unavailable)' ?></option><?php endforeach; ?></select><?= $err('vaccine') ?></label>
  <label>Hospital<select name="hospital" required><option value="">Choose</option><?php foreach ($hospitals as $h): ?><option value="<?= (int)$h['id'] ?>" <?= (int)($_POST['hospital'] ?? 0) === (int)$h['id'] ? 'selected' : '' ?>><?= e($h['name'] . ' · ' . $h['address'] . ', ' . $h['city']) ?></option><?php endforeach; ?></select><?= $err('hospital') ?></label>
  <label>Preferred date<input type="date" name="date" required min="<?= date('Y-m-d') ?>" value="<?= old('date', date('Y-m-d', strtotime('+1 day'))) ?>"><?= $err('date') ?></label>
  <button class="btn" type="submit">Send request</button>
</form></div>
<?php endif; page_footer();
