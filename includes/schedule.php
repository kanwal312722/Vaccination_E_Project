<?php
// Vaccination schedule logic. All dates are Y-m-d strings.

function due_date(string $dob, int $weeks): string {
    return (new DateTimeImmutable($dob))->modify('+' . $weeks . ' weeks')->format('Y-m-d');
}

function age_text(string $dob): string {
    $d = (new DateTimeImmutable($dob))->diff(new DateTimeImmutable('today'));
    if ($d->invert) return 'not born yet';
    if ($d->y >= 1) return $d->y . ' yr ' . $d->m . ' mo';
    $weeks = (int)floor($d->days / 7);
    return $weeks < 12 ? $weeks . ' weeks' : $d->m . ' months';
}

// One entry per vaccine: done | booked | overdue | upcoming
function child_schedule(int $childId): array {
    $c = row('SELECT * FROM children WHERE id=?', [$childId]);
    if (!$c) return [];
    $done = array_map('intval', array_column(rows("SELECT vaccine_id FROM vaccination_records WHERE child_id=? AND status='vaccinated'", [$childId]), 'vaccine_id'));
    $open = [];
    foreach (rows("SELECT a.*, h.name AS hospital FROM appointments a JOIN hospitals h ON h.id=a.hospital_id
                   WHERE a.child_id=? AND (a.status='pending' OR (a.status='approved' AND a.result='pending'))", [$childId]) as $a) {
        $open[(int)$a['vaccine_id']] = $a;
    }
    $today = date('Y-m-d');
    $out = [];
    foreach (rows('SELECT * FROM vaccines ORDER BY age_weeks, id') as $v) {
        $due = due_date($c['dob'], (int)$v['age_weeks']);
        $id = (int)$v['id'];
        if (in_array($id, $done, true))      $state = 'done';
        elseif (isset($open[$id]))            $state = 'booked';
        else                                  $state = $due < $today ? 'overdue' : 'upcoming';
        $out[] = ['vaccine' => $v, 'state' => $state, 'due' => $due, 'appt' => $open[$id] ?? null];
    }
    return $out;
}

function next_due(array $schedule): ?array {
    foreach ($schedule as $s) { if ($s['state'] === 'overdue' || $s['state'] === 'upcoming') return $s; }
    return null;
}

function fmt_date(?string $d): string { return $d ? date('j M Y', strtotime($d)) : ''; }

function pill(string $state): string {
    $label = ucwords(str_replace('_', ' ', $state));
    return '<span class="pill ' . e($state) . '">' . e($label) . '</span>';
}

function child_of_parent(int $childId, int $parentId): ?array {
    return row('SELECT * FROM children WHERE id=? AND parent_id=?', [$childId, $parentId]);
}
