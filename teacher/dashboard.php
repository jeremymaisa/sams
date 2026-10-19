<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('teacher');

$stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$teacher = $stmt->fetch();

$subjects = [];
if ($teacher) {
    $stmt = $pdo->prepare("
      SELECT sub.*, COUNT(e.id) AS student_count FROM subjects sub
      LEFT JOIN enrollments e ON e.subject_id = sub.id
      WHERE sub.teacher_id = ? GROUP BY sub.id");
    $stmt->execute([$teacher['id']]);
    $subjects = $stmt->fetchAll();
}

$page_title = "Teacher Dashboard";
include __DIR__ . '/../includes/header.php';
?>
<h1>Welcome, <?= h(current_name()) ?></h1>
<div class="card">
  <h2>My Subjects</h2>
  <table>
    <tr><th>Code</th><th>Name</th><th>Units</th><th>Students Enrolled</th></tr>
    <?php foreach ($subjects as $s): ?>
    <tr><td><?= h($s['subject_code']) ?></td><td><?= h($s['subject_name']) ?></td><td><?= h($s['units']) ?></td><td><?= $s['student_count'] ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$subjects): ?><tr><td colspan="4">No subjects assigned yet. Ask the admin to assign you a subject.</td></tr><?php endif; ?>
  </table>
</div>
<div class="card">
  <h2>Quick Links</h2>
  <p><a href="<?= BASE_URL ?>/teacher/grades.php">➡ Input / Edit Grades</a></p>
  <p><a href="<?= BASE_URL ?>/teacher/attendance.php">➡ Record Attendance</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
