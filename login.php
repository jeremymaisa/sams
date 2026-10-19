<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION['user_id'])) redirect('/' . current_role() . '/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([trim($_POST['username'] ?? '')]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        redirect('/' . $user['role'] . '/dashboard.php');
    }
    $error = "Invalid username or password.";
}

$page_title = "Login";
include __DIR__ . '/includes/header.php';
?>
<div class="login-box">
  <h1>Sign in to SAMS</h1>
  <?php if ($error): ?><p class="alert alert-error"><?= h($error) ?></p><?php endif; ?>
  <?php if (isset($_GET['error'])): ?><p class="alert alert-error">You are not authorized to view that page.</p><?php endif; ?>
  <form method="post">
    <label>Username</label><input type="text" name="username" required autofocus>
    <label>Password</label><input type="password" name="password" required>
    <button type="submit" class="btn btn-primary">Login</button>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
