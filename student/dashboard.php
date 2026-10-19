<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("
  SELECT sub.subject_code, sub.subject_name, AVG(g.score) AS average
  FROM enrollments e JOIN subjects sub ON sub.id = e.subject_id
  LEFT JOIN grades g ON g.enrollment_id = e.id
  WHERE e.student_id = ? GROUP BY e.id, sub.subject_code, sub.subject_name");
$stmt->execute([$student['id'] ?? 0]);
$subjects = $stmt->fetchAll();

$page_title = "Student Dashboard";
include __DIR__ . '/../includes/header.php';
?>
<h1>Welcome, <?= h(current_name()) ?></h1>
<p class="hint">Student #: <?= h($student['student_number'] ?? '') ?> | <?= h($student['year_level'] ?? '') ?> - <?= h($student['section'] ?? '') ?></p>
<div class="card">
  <h2>My Subjects</h2>
  <table>
    <tr><th>Code</th><th>Subject</th><th>Current Average</th></tr>
    <?php foreach ($subjects as $s): ?>
    <tr><td><?= h($s['subject_code']) ?></td><td><?= h($s['subject_name']) ?></td><td><?= $s['average'] !== null ? round($s['average'], 1) : '—' ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$subjects): ?><tr><td colspan="3">You are not enrolled in any subject yet.</td></tr><?php endif; ?>
  </table>
</div>
<div class="card">
  <h2>Quick Links</h2>
  <p><a href="<?= BASE_URL ?>/student/grades.php">➡ View My Grades</a></p>
  <p><a href="<?= BASE_URL ?>/student/attendance.php">➡ View My Attendance</a></p>
  <p><a href="<?= BASE_URL ?>/student/reports.php">➡ Performance Report &amp; Charts</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
