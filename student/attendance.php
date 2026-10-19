<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("
  SELECT sub.subject_code, sub.subject_name, a.attendance_date, a.status
  FROM enrollments e JOIN subjects sub ON sub.id = e.subject_id JOIN attendance a ON a.enrollment_id = e.id
  WHERE e.student_id = ? ORDER BY a.attendance_date DESC");
$stmt->execute([$student['id'] ?? 0]);
$records = $stmt->fetchAll();
$badge = ['Present' => 'badge-present', 'Absent' => 'badge-absent', 'Late' => 'badge-late', 'Excused' => 'badge-excused'];

$page_title = "My Attendance";
include __DIR__ . '/../includes/header.php';
?>
<h1>My Attendance</h1>
<div class="card">
  <table>
    <tr><th>Date</th><th>Subject</th><th>Status</th></tr>
    <?php foreach ($records as $r): ?>
    <tr>
      <td><?= h($r['attendance_date']) ?></td>
      <td><?= h($r['subject_code']) ?> - <?= h($r['subject_name']) ?></td>
      <td><span class="badge <?= $badge[$r['status']] ?>"><?= h($r['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$records): ?><tr><td colspan="3">No attendance records yet.</td></tr><?php endif; ?>
  </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
