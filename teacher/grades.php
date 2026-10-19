<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('teacher');

$stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$teacher = $stmt->fetch();
$teacherId = $teacher['id'] ?? 0;

$message = ''; $isError = false;
$subjectFilter = $_GET['subject_id'] ?? $_POST['subject_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $period = $_POST['grading_period'] ?? '';
    $score = $_POST['score'] ?? '';
    if (!in_array($period, ['Prelim', 'Midterm', 'Final']) || !is_numeric($score) || $score < 0 || $score > 100) {
        $isError = true; $message = "Invalid grade. Enter a score from 0 to 100.";
    } else {
        // Only allow editing enrollments that belong to this teacher's subjects
        $check = $pdo->prepare("SELECT e.id FROM enrollments e JOIN subjects s ON s.id = e.subject_id WHERE e.id = ? AND s.teacher_id = ?");
        $check->execute([(int)$_POST['enrollment_id'], $teacherId]);
        if ($check->fetch()) {
            $pdo->prepare("INSERT INTO grades (enrollment_id, grading_period, score) VALUES (?,?,?)
                           ON DUPLICATE KEY UPDATE score = VALUES(score)")
                ->execute([(int)$_POST['enrollment_id'], $period, $score]);
            $message = "$period grade saved.";
        } else { $isError = true; $message = "You are not authorized to edit that record."; }
    }
}

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE teacher_id = ? ORDER BY subject_code");
$stmt->execute([$teacherId]);
$subjects = $stmt->fetchAll();
$valid = array_column($subjects, 'id');
if (!in_array($subjectFilter, $valid) && $subjects) $subjectFilter = $subjects[0]['id'];

$roster = [];
if ($subjects) {
    $stmt = $pdo->prepare("
      SELECT e.id AS enrollment_id, u.full_name, st.student_number,
        MAX(CASE WHEN g.grading_period='Prelim'  THEN g.score END) AS prelim,
        MAX(CASE WHEN g.grading_period='Midterm' THEN g.score END) AS midterm,
        MAX(CASE WHEN g.grading_period='Final'   THEN g.score END) AS final
      FROM enrollments e JOIN students st ON st.id = e.student_id JOIN users u ON u.id = st.user_id
      LEFT JOIN grades g ON g.enrollment_id = e.id
      WHERE e.subject_id = ? GROUP BY e.id, u.full_name, st.student_number ORDER BY u.full_name");
    $stmt->execute([$subjectFilter]);
    $roster = $stmt->fetchAll();
}

$page_title = "Input Grades";
include __DIR__ . '/../includes/header.php';
?>
<h1>Input / Edit Grades</h1>
<?php if ($message): ?><p class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= h($message) ?></p><?php endif; ?>

<form method="get" class="form-row">
  <div><label>Subject</label>
    <select name="subject_id" onchange="this.form.submit()">
      <?php foreach ($subjects as $s): ?>
      <option value="<?= $s['id'] ?>" <?= $s['id'] == $subjectFilter ? 'selected' : '' ?>><?= h($s['subject_code']) ?> - <?= h($s['subject_name']) ?></option>
      <?php endforeach; ?>
    </select></div>
</form>

<div class="card">
  <h2>Class Roster &amp; Grades</h2>
  <table>
    <tr><th>Student</th><th>Student #</th><th>Prelim</th><th>Midterm</th><th>Final</th></tr>
    <?php foreach ($roster as $r): ?>
    <tr>
      <td><?= h($r['full_name']) ?></td><td><?= h($r['student_number']) ?></td>
      <?php foreach (['Prelim' => $r['prelim'], 'Midterm' => $r['midterm'], 'Final' => $r['final']] as $period => $val): ?>
      <td>
        <form method="post" style="display:flex;gap:4px;align-items:center">
          <input type="hidden" name="subject_id" value="<?= h($subjectFilter) ?>">
          <input type="hidden" name="enrollment_id" value="<?= $r['enrollment_id'] ?>">
          <input type="hidden" name="grading_period" value="<?= $period ?>">
          <input type="number" step="0.01" min="0" max="100" name="score" value="<?= h($val) ?>" style="width:80px" required>
          <button class="btn btn-secondary btn-sm" type="submit">Save</button>
        </form>
      </td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
    <?php if (!$roster): ?><tr><td colspan="5">No students enrolled in this subject yet.</td></tr><?php endif; ?>
  </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
