<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$message = ''; $isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, units, teacher_id, school_year, semester) VALUES (?,?,?,?,?,?)")
            ->execute([trim($_POST['subject_code']), trim($_POST['subject_name']), $_POST['units'],
                       $_POST['teacher_id'] !== '' ? $_POST['teacher_id'] : null, trim($_POST['school_year']), trim($_POST['semester'])]);
        $message = "Subject added.";
    } catch (Exception $e) { $isError = true; $message = "Could not add subject. The subject code may already exist."; }
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM subjects WHERE id = ?")->execute([(int)$_GET['delete']]);
    $message = "Subject deleted.";
}

$teachers = $pdo->query("SELECT t.id, u.full_name FROM teachers t JOIN users u ON u.id = t.user_id ORDER BY u.full_name")->fetchAll();
$subjects = $pdo->query("
  SELECT s.*, u.full_name AS teacher_name FROM subjects s
  LEFT JOIN teachers t ON t.id = s.teacher_id LEFT JOIN users u ON u.id = t.user_id
  ORDER BY s.subject_code")->fetchAll();

$page_title = "Manage Subjects";
include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Subjects</h1>
<?php if ($message): ?><p class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= h($message) ?></p><?php endif; ?>
<div class="card">
  <h2>Add Subject</h2>
  <form method="post">
    <div class="form-row">
      <div><label>Subject Code</label><input name="subject_code" required></div>
      <div><label>Subject Name</label><input name="subject_name" required></div>
      <div><label>Units</label><input type="number" step="0.5" name="units" value="3.0"></div>
      <div><label>Teacher</label>
        <select name="teacher_id"><option value="">-- Unassigned --</option>
          <?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= h($t['full_name']) ?></option><?php endforeach; ?>
        </select></div>
      <div><label>School Year</label><input name="school_year" value="2026-2027"></div>
      <div><label>Semester</label><input name="semester" value="1st Semester"></div>
    </div>
    <button class="btn btn-primary" type="submit">Add Subject</button>
  </form>
</div>
<div class="card">
  <h2>All Subjects</h2>
  <table>
    <tr><th>Code</th><th>Name</th><th>Units</th><th>Teacher</th><th>Year</th><th>Semester</th><th></th></tr>
    <?php foreach ($subjects as $s): ?>
    <tr>
      <td><?= h($s['subject_code']) ?></td><td><?= h($s['subject_name']) ?></td><td><?= h($s['units']) ?></td>
      <td><?= h($s['teacher_name'] ?? 'Unassigned') ?></td><td><?= h($s['school_year']) ?></td><td><?= h($s['semester']) ?></td>
      <td><a class="btn btn-danger btn-sm" href="?delete=<?= $s['id'] ?>" onclick="return confirm('Delete this subject?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
