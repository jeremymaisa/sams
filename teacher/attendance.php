<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('teacher');

$stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$teacher = $stmt->fetch();
$teacherId = $teacher['id'] ?? 0;

$message = ''; $isError = false;
$date = $_POST['attendance_date'] ?? $_GET['date'] ?? date('Y-m-d');
$subjectFilter = $_POST['subject_id'] ?? $_GET['subject_id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE teacher_id = ? ORDER BY subject_code");
$stmt->execute([$teacherId]);
$subjects = $stmt->fetchAll();
$valid = array_column($subjects, 'id');
if (!in_array($subjectFilter, $valid) && $subjects) $subjectFilter = $subjects[0]['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($subjectFilter, $valid)) {
    $allowed = ['Present', 'Absent', 'Late', 'Excused'];
    $own = $pdo->prepare("SELECT id FROM enrollments WHERE id = ? AND subject_id = ?");
    $save = $pdo->prepare("INSERT INTO attendance (enrollment_id, attendance_date, status) VALUES (?,?,?)
                           ON DUPLICATE KEY UPDATE status = VALUES(status)");
    foreach (($_POST['status'] ?? []) as $enrollmentId => $status) {
        $own->execute([(int)$enrollmentId, $subjectFilter]);
        if ($own->fetch() && in_array($status, $allowed)) $save->execute([(int)$enrollmentId, $date, $status]);
    }
    $message = "Attendance saved for " . $date . ".";
}

$roster = [];
if ($subjects) {
    $stmt = $pdo->prepare("
      SELECT e.id AS enrollment_id, u.full_name, st.student_number,
        (SELECT status FROM attendance a WHERE a.enrollment_id = e.id AND a.attendance_date = ?) AS status
      FROM enrollments e JOIN students st ON st.id = e.student_id JOIN users u ON u.id = st.user_id
      WHERE e.subject_id = ? ORDER BY u.full_name");
    $stmt->execute([$date, $subjectFilter]);
    $roster = $stmt->fetchAll();
}

$page_title = "Record Attendance";
include __DIR__ . '/../includes/header.php';
?>
<h1>Record Attendance</h1>
<?php if ($message): ?><p class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= h($message) ?></p><?php endif; ?>

<form method="get" class="form-row">
  <div><label>Subject</label>
    <select name="subject_id" onchange="this.form.submit()">
      <?php foreach ($subjects as $s): ?>
      <option value="<?= $s['id'] ?>" <?= $s['id'] == $subjectFilter ? 'selected' : '' ?>><?= h($s['subject_code']) ?> - <?= h($s['subject_name']) ?></option>
      <?php endforeach; ?>
    </select></div>
  <div><label>Date</label><input type="date" name="date" value="<?= h($date) ?>" onchange="this.form.submit()"></div>
</form>

<form method="post">
  <input type="hidden" name="subject_id" value="<?= h($subjectFilter) ?>">
  <input type="hidden" name="attendance_date" value="<?= h($date) ?>">
  <div class="card">
    <h2>Class Roster - <?= h($date) ?></h2>
    <table>
      <tr><th>Student</th><th>Student #</th><th>Status</th></tr>
      <?php foreach ($roster as $r): ?>
      <tr>
        <td><?= h($r['full_name']) ?></td><td><?= h($r['student_number']) ?></td>
        <td>
          <select name="status[<?= $r['enrollment_id'] ?>]">
            <?php foreach (['Present', 'Absent', 'Late', 'Excused'] as $opt): ?>
            <option value="<?= $opt ?>" <?= $r['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$roster): ?><tr><td colspan="3">No students enrolled in this subject yet.</td></tr><?php endif; ?>
    </table>
    <?php if ($roster): ?><button class="btn btn-primary" type="submit">Save Attendance</button><?php endif; ?>
  </div>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
