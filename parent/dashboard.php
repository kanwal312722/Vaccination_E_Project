<?php
require_once __DIR__ . '/../includes/layout.php';
$u = require_role('parent');
$kids = rows('SELECT * FROM children WHERE parent_id=? ORDER BY dob DESC', [$u['id']]);
$soon = date('Y-m-d', strtotime('+7 days'));
page_header('Welcome, ' . explode(' ', $u['name'])[0]);
if (!$kids): ?>
<div class="card"><div class="empty">Add your child to see the vaccination schedule.</div><p style="margin-top:14px"><a class="btn" href="<?= e(url('parent/children.php')) ?>">Add a child</a></p></div>
<?php else:
foreach ($kids as $c):
    $sch = child_schedule((int)$c['id']);
    $doneN = count(array_filter($sch, fn($s) => $s['state'] === 'done'));
    $alerts = array_filter($sch, fn($s) => $s['state'] === 'overdue' || ($s['state'] === 'upcoming' && $s['due'] <= $soon));
?>
<div class="card">
  <h2><?= e($c['name']) ?> <span class="muted small">· <?= e(age_text($c['dob'])) ?> · <?= $doneN ?> of <?= count($sch) ?> done</span></h2>
  <?php foreach ($alerts as $a): ?>
    <div class="alert <?= $a['state'] === 'overdue' ? 'bad' : '' ?>"><?= $a['state'] === 'overdue'
        ? e($a['vaccine']['name']) . ' was due on ' . e(fmt_date($a['due'])) . '. Book a hospital now.'
        : e($a['vaccine']['name']) . ' is due on ' . e(fmt_date($a['due'])) . '.' ?></div>
  <?php endforeach; ?>
  <div class="tl">
  <?php foreach ($sch as $s): ?>
    <div class="r"><span class="n"><?= e($s['vaccine']['name']) ?></span>
      <span class="muted small"><?= $s['state'] === 'done' ? 'Given' : 'Due ' . e(fmt_date($s['due'])) ?></span>
      <?= pill($s['state'] === 'booked' ? 'booked' : $s['state']) ?>
      <?php if ($s['state'] === 'overdue' || $s['state'] === 'upcoming'): ?>
        <?php if ($s['vaccine']['available']): ?><a class="btn sm ghost" href="<?= e(url('parent/book.php?child=' . (int)$c['id'] . '&vaccine=' . (int)$s['vaccine']['id'])) ?>">Book</a>
        <?php else: ?><span class="muted small">Unavailable now</span><?php endif; ?>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
</div>
<?php endforeach; endif;
page_footer();
