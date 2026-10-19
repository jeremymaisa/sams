<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$sid = $stmt->fetch()['id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT sub.subject_name,
    MAX(CASE WHEN g.grading_period='Prelim'  THEN g.score END) AS prelim,
    MAX(CASE WHEN g.grading_period='Midterm' THEN g.score END) AS midterm,
    MAX(CASE WHEN g.grading_period='Final'   THEN g.score END) AS final
  FROM enrollments e JOIN subjects sub ON sub.id = e.subject_id
  LEFT JOIN grades g ON g.enrollment_id = e.id
  WHERE e.student_id = ? GROUP BY e.id, sub.subject_name");
$stmt->execute([$sid]);
$gradeData = $stmt->fetchAll();

$stmt = $pdo->prepare("
  SELECT a.status, COUNT(*) AS total FROM enrollments e JOIN attendance a ON a.enrollment_id = e.id
  WHERE e.student_id = ? GROUP BY a.status");
$stmt->execute([$sid]);
$attData = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT AVG(g.score) FROM enrollments e JOIN grades g ON g.enrollment_id = e.id WHERE e.student_id = ?");
$stmt->execute([$sid]);
$overall = $stmt->fetchColumn();

$colors = ['Present' => '#1f9d55', 'Absent' => '#d64545', 'Late' => '#c98a12', 'Excused' => '#2b5fce'];
$attColors = array_map(fn($r) => $colors[$r['status']], $attData);

$page_title = "Performance Report";
include __DIR__ . '/../includes/header.php';
?>
<h1>Performance Report</h1>
<div class="grid grid-3">
  <div class="card stat"><div class="num"><?= $overall !== null && $overall !== false ? round($overall, 1) : '—' ?></div><div class="label">Overall Average</div></div>
</div>
<div class="card">
  <h2>Grades by Grading Period</h2>
  <div class="chart-wrap"><canvas id="gradeChart" height="120"></canvas></div>
</div>
<div class="card">
  <h2>Attendance Breakdown</h2>
  <?php if ($attData): ?>
  <div class="chart-wrap" style="max-width:360px"><canvas id="attChart"></canvas></div>
  <?php else: ?><p>No attendance records yet.</p><?php endif; ?>
</div>
<script>
new Chart(document.getElementById('gradeChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($gradeData, 'subject_name')) ?>,
    datasets: [
      { label: 'Prelim',  data: <?= json_encode(array_map(fn($r) => $r['prelim']  !== null ? (float)$r['prelim']  : null, $gradeData)) ?>, backgroundColor: '#2b5fce' },
      { label: 'Midterm', data: <?= json_encode(array_map(fn($r) => $r['midterm'] !== null ? (float)$r['midterm'] : null, $gradeData)) ?>, backgroundColor: '#5c8ae6' },
      { label: 'Final',   data: <?= json_encode(array_map(fn($r) => $r['final']   !== null ? (float)$r['final']   : null, $gradeData)) ?>, backgroundColor: '#9fc0f5' }
    ]
  },
  options: { scales: { y: { beginAtZero: true, max: 100 } } }
});
<?php if ($attData): ?>
new Chart(document.getElementById('attChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_column($attData, 'status')) ?>,
    datasets: [{ data: <?= json_encode(array_map('intval', array_column($attData, 'total'))) ?>, backgroundColor: <?= json_encode($attColors) ?> }]
  }
});
<?php endif; ?>
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
