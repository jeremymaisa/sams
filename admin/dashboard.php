<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$counts = [
  'Students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
  'Teachers' => $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn(),
  'Subjects' => $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
  'Enrollments' => $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn(),
];
$avgs = $pdo->query("
  SELECT s.subject_name, AVG(g.score) AS avg_score
  FROM subjects s JOIN enrollments e ON e.subject_id = s.id JOIN grades g ON g.enrollment_id = e.id
  GROUP BY s.id, s.subject_name")->fetchAll();

$page_title = "Admin Dashboard";
include __DIR__ . '/../includes/header.php';
?>
<h1>Admin Dashboard</h1>
<div class="grid grid-3">
  <?php foreach ($counts as $label => $n): ?>
  <div class="card stat"><div class="num"><?= $n ?></div><div class="label"><?= $label ?></div></div>
  <?php endforeach; ?>
</div>
<div class="card">
  <h2>Average Score by Subject</h2>
  <div class="chart-wrap"><canvas id="subjectChart" height="120"></canvas></div>
</div>
<script>
new Chart(document.getElementById('subjectChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($avgs, 'subject_name')) ?>,
    datasets: [{ label: 'Average Score', data: <?= json_encode(array_map(fn($r) => round($r['avg_score'], 1), $avgs)) ?>, backgroundColor: '#2b5fce' }]
  },
  options: { scales: { y: { beginAtZero: true, max: 100 } } }
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
