<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$message = ''; $isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    if (!in_array($role, ['admin', 'teacher', 'student'])) $role = 'student';
    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?,?,?,?,?)")
            ->execute([trim($_POST['username']), password_hash($_POST['password'], PASSWORD_DEFAULT), $role, trim($_POST['full_name']), trim($_POST['email'])]);
        $uid = $pdo->lastInsertId();
        if ($role === 'student') {
            $pdo->prepare("INSERT INTO students (user_id, student_number, year_level, section) VALUES (?,?,?,?)")
                ->execute([$uid, trim($_POST['student_number']), trim($_POST['year_level']), trim($_POST['section'])]);
        } elseif ($role === 'teacher') {
            $pdo->prepare("INSERT INTO teachers (user_id, employee_number, department) VALUES (?,?,?)")
                ->execute([$uid, trim($_POST['employee_number']), trim($_POST['department'])]);
        }
        $pdo->commit();
        $message = "User created successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $isError = true;
        $message = "Could not create user. The username, student number, or employee number may already exist.";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id === (int)current_user_id()) { $isError = true; $message = "You cannot delete your own account."; }
    else { $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]); $message = "User deleted."; }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll();
$page_title = "Manage Users";
include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Users</h1>
<?php if ($message): ?><p class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>"><?= h($message) ?></p><?php endif; ?>

<div class="card">
  <h2>Add New User</h2>
  <form method="post">
    <div class="form-row">
      <div><label>Username</label><input name="username" required></div>
      <div><label>Full Name</label><input name="full_name" required></div>
      <div><label>Email</label><input type="email" name="email"></div>
      <div><label>Password</label><input type="password" name="password" required></div>
    </div>
    <div class="form-row">
      <div><label>Role</label>
        <select name="role" id="roleSelect" onchange="toggleFields()">
          <option value="student">Student</option><option value="teacher">Teacher</option><option value="admin">Admin</option>
        </select></div>
      <div class="f-student"><label>Student Number</label><input name="student_number"></div>
      <div class="f-student"><label>Year Level</label><input name="year_level" placeholder="e.g. 3rd Year"></div>
      <div class="f-student"><label>Section</label><input name="section"></div>
      <div class="f-teacher" style="display:none"><label>Employee Number</label><input name="employee_number"></div>
      <div class="f-teacher" style="display:none"><label>Department</label><input name="department"></div>
    </div>
    <button class="btn btn-primary" type="submit">Create User</button>
  </form>
</div>

<div class="card">
  <h2>All Users</h2>
  <table>
    <tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Email</th><th></th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td><td><?= h($u['username']) ?></td><td><?= h($u['full_name']) ?></td>
      <td><?= h(ucfirst($u['role'])) ?></td><td><?= h($u['email']) ?></td>
      <td><a class="btn btn-danger btn-sm" href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<script>
function toggleFields() {
  const role = document.getElementById('roleSelect').value;
  document.querySelectorAll('.f-student').forEach(e => e.style.display = role === 'student' ? 'block' : 'none');
  document.querySelectorAll('.f-teacher').forEach(e => e.style.display = role === 'teacher' ? 'block' : 'none');
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
