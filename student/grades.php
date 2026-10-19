<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("
  SELECT sub.subject_code, sub.subject_name,
    MAX(CASE WHEN g.grading_period='Prelim'  THEN g.score END) AS prelim,
    MAX(CASE WHEN g.grading_period='Midterm' THEN g.score END) AS midterm,
    MAX(CASE WHEN g.grading_period='Final'   THEN g.score END) AS final,
    AVG(g.score) AS average
  FROM enrollments e JOIN subjects sub ON sub.id = e.subject_id
  LEFT JOIN grades g ON g.enrollment_id = e.id
  WHERE e.student_id = ? GROUP BY e.id, sub.subject_code, sub.subject_name");
$stmt->execute([$student['id'] ?? 0]);
$grades = $stmt->fetchAll();

$page_title = "My Grades";
include __DIR__ . '/../includes/header.php';
?>
<h1>My Grades</h1>
<div class="card">
  <table>
    <tr><th>Code</th><th>Subject</th><th>Prelim</th><th>Midterm</th><th>Final</th><th>Average</th></tr>
    <?php foreach ($grades as $g): ?>
    <tr>
      <td><?= h($g['subject_code']) ?></td><td><?= h($g['subject_name']) ?></td>
      <td><?= $g['prelim'] ?? '—' ?></td><td><?= $g['midterm'] ?? '—' ?></td><td><?= $g['final'] ?? '—' ?></td>
      <td><strong><?= $g['average'] !== null ? round($g['average'], 1) : '—' ?></strong></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$grades): ?><tr><td colspan="6">No grades recorded yet.</td></tr><?php endif; ?>
  </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
