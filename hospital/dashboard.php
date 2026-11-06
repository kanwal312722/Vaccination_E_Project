<?php
require_once __DIR__ . '/../includes/layout.php';
$u = require_role('hospital');
$h = row('SELECT * FROM hospitals WHERE user_id=?', [$u['id']]);
if (!$h) { http_response_code(403); exit('No hospital is linked to this account.'); }

if (is_post() && post('action') === 'result') {
    $id = (int)($_POST['id'] ?? 0);
    $res = post('result');
    if (!in_array($res, ['vaccinated', 'not_vaccinated'], true)) { flash('error', 'Choose Vaccinated or Not vaccinated.'); redirect('hospital/dashboard.php'); }
    $pdo = db(); $pdo->beginTransaction();
    try {
        // Only this hospital's approved, not-yet-updated appointments can be changed.
        $n = q("UPDATE appointments SET result=?, updated_at=CURRENT_TIMESTAMP WHERE id=? AND hospital_id=? AND status='approved' AND result='pending'", [$res, $id, $h['id']])->rowCount();
        if ($n) {
            $a = row('SELECT child_id, vaccine_id FROM appointments WHERE id=?', [$id]);
            q('INSERT INTO vaccination_records (child_id,vaccine_id,hospital_id,appointment_id,given_on,status) VALUES (?,?,?,?,?,?)',
              [$a['child_id'], $a['vaccine_id'], $h['id'], $id, date('Y-m-d'), $res]);
        }
        $pdo->commit();
    } catch (Throwable $ex) { $pdo->rollBack(); throw $ex; }
    flash($n ? 'ok' : 'error', $n ? 'Status updated. The parent and admin reports now show it.' : 'This appointment cannot be updated.');
    redirect('hospital/dashboard.php');
}
$inbox = rows("SELECT a.id, a.preferred_date, c.name child, c.dob, c.gender, v.name vaccine, p.name parent, p.phone
               FROM appointments a JOIN children c ON c.id=a.child_id JOIN users p ON p.id=c.parent_id JOIN vaccines v ON v.id=a.vaccine_id
               WHERE a.hospital_id=? AND a.status='approved' AND a.result='pending' ORDER BY a.preferred_date, a.id", [$h['id']]);
$hist = rows("SELECT a.preferred_date, a.result, a.updated_at, c.name child, v.name vaccine FROM appointments a JOIN children c ON c.id=a.child_id JOIN vaccines v ON v.id=a.vaccine_id
              WHERE a.hospital_id=? AND a.result<>'pending' ORDER BY a.updated_at DESC, a.id DESC LIMIT 20", [$h['id']]);
page_header($h['name']);
?>
<div class="card"><h3>Approved appointments</h3>
<?php if ($inbox): ?><div class="tw"><table><thead><tr><th>Date</th><th>Child</th><th>Vaccine</th><th>Parent</th><th>Update status</th></tr></thead><tbody>
<?php foreach ($inbox as $a): ?><tr><td><?= e(fmt_date($a['preferred_date'])) ?></td><td><?= e($a['child']) ?><div class="muted small"><?= e($a['gender']) ?> · <?= e(age_text($a['dob'])) ?></div></td><td><?= e($a['vaccine']) ?></td><td><?= e($a['parent']) ?><div class="muted small"><?= e($a['phone']) ?></div></td>
<td><form method="post" class="acts"><?= csrf_field() ?><input type="hidden" name="action" value="result"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
<button class="btn sm ok" name="result" value="vaccinated">Vaccinated</button><button class="btn sm no" name="result" value="not_vaccinated">Not vaccinated</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty">Nothing waiting. Approved appointments appear here.</div><?php endif; ?></div>
<div class="card"><h3>Completed</h3>
<?php if ($hist): ?><div class="tw"><table><thead><tr><th>Date</th><th>Child</th><th>Vaccine</th><th>Result</th></tr></thead><tbody>
<?php foreach ($hist as $a): ?><tr><td><?= e(fmt_date($a['preferred_date'])) ?></td><td><?= e($a['child']) ?></td><td><?= e($a['vaccine']) ?></td><td><?= pill($a['result']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php else: ?><div class="empty">No updates yet.</div><?php endif; ?></div>
<?php page_footer();
