<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin');
$n = [
    'Children' => val('SELECT COUNT(*) FROM children'),
    'Hospitals' => val('SELECT COUNT(*) FROM hospitals WHERE active=1'),
    'Pending requests' => val("SELECT COUNT(*) FROM appointments WHERE status='pending'"),
    'Doses given' => val("SELECT COUNT(*) FROM vaccination_records WHERE status='vaccinated'"),
];
$pending = rows("SELECT a.id, a.preferred_date, c.name child, v.name vaccine, h.name hospital
                 FROM appointments a JOIN children c ON c.id=a.child_id JOIN vaccines v ON v.id=a.vaccine_id JOIN hospitals h ON h.id=a.hospital_id
                 WHERE a.status='pending' ORDER BY a.preferred_date LIMIT 5");
$upcoming = rows("SELECT a.preferred_date, c.name child, v.name vaccine, h.name hospital
                  FROM appointments a JOIN children c ON c.id=a.child_id JOIN vaccines v ON v.id=a.vaccine_id JOIN hospitals h ON h.id=a.hospital_id
                  WHERE a.status='approved' AND a.result='pending' AND a.preferred_date>=? ORDER BY a.preferred_date LIMIT 5", [date('Y-m-d')]);
page_header('Admin dashboard');
?>
<div class="stats"><?php foreach ($n as $label => $v): ?><div class="stat"><b><?= (int)$v ?></b><span><?= e($label) ?></span></div><?php endforeach; ?></div>
<div class="grid">
  <div class="card"><h3>Waiting for approval</h3>
  <?php if ($pending): ?><div class="tw"><table><thead><tr><th>Child</th><th>Vaccine</th><th>Hospital</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($pending as $p): ?><tr><td><?= e($p['child']) ?></td><td><?= e($p['vaccine']) ?></td><td><?= e($p['hospital']) ?></td><td><?= e(fmt_date($p['preferred_date'])) ?></td></tr><?php endforeach; ?>
  </tbody></table></div><p style="margin-top:12px"><a class="btn sm" href="<?= e(url('admin/requests.php')) ?>">Review requests</a></p>
  <?php else: ?><div class="empty">No pending requests.</div><?php endif; ?></div>
  <div class="card"><h3>Upcoming vaccinations</h3>
  <?php if ($upcoming): ?><div class="tw"><table><thead><tr><th>Date</th><th>Child</th><th>Vaccine</th><th>Hospital</th></tr></thead><tbody>
    <?php foreach ($upcoming as $p): ?><tr><td><?= e(fmt_date($p['preferred_date'])) ?></td><td><?= e($p['child']) ?></td><td><?= e($p['vaccine']) ?></td><td><?= e($p['hospital']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <?php else: ?><div class="empty">Nothing scheduled.</div><?php endif; ?></div>
</div>
<?php page_footer();
