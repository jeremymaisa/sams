<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$message = ''; $isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?,?)")->execute([$_POST['student_id'], $_POST['subject_id']]);
        $message = "Student enrolled.";
    } catch (Exception $e) { $isError = true; $message = "This student is already enrolled in that subject."; }
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM enrollments WHERE id = ?")->execute([(int)$_GET['delete']]);
    $message = "Enrollment removed.";
}

$students = $pdo->query("SELECT s.id, u.full_name, s.student_number FROM students s JOIN users u ON u.id = s.user_id ORDER BY u.full_name")->fetchAll();
$subjects = $pdo->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_code")->fetchAll();
$enrollments = $pdo->query("
  SELECT e.id, u.full_name AS student_name, s.student_number, sub.subject_code, sub.subject_name
  FROM enrollments e JOIN students s ON s.id = e.student_id JOIN users u ON u.id = s.user_id
  JOIN subjects sub ON sub.id = e.subject_id ORDER BY u.full_name, sub.subject_code")->fetchAll();

$page_title = "Manage Enrollments";
include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Enrollments</h1>
<?php if ($message): ?><p class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= h($message) ?></p><?php endif; ?>
<div class="card">
  <h2>Enroll a Student in a Subject</h2>
  <form method="post">
    <div class="form-row">
      <div><label>Student</label>
        <select name="student_id" required>
          <?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>"><?= h($s['full_name']) ?> (<?= h($s['student_number']) ?>)</option><?php endforeach; ?>
        </select></div>
      <div><label>Subject</label>
        <select name="subject_id" required>
          <?php foreach ($subjects as $sub): ?><option value="<?= $sub['id'] ?>"><?= h($sub['subject_code']) ?> - <?= h($sub['subject_name']) ?></option><?php endforeach; ?>
        </select></div>
    </div>
    <button class="btn btn-primary" type="submit">Enroll</button>
  </form>
</div>
<div class="card">
  <h2>Current Enrollments</h2>
  <table>
    <tr><th>Student</th><th>Student #</th><th>Subject</th><th></th></tr>
    <?php foreach ($enrollments as $e): ?>
    <tr>
      <td><?= h($e['student_name']) ?></td><td><?= h($e['student_number']) ?></td>
      <td><?= h($e['subject_code']) ?> - <?= h($e['subject_name']) ?></td>
      <td><a class="btn btn-danger btn-sm" href="?delete=<?= $e['id'] ?>" onclick="return confirm('Remove this enrollment?')">Remove</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
