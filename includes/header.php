<?php $role = current_role(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($page_title ?? 'SAMS') ?> - Student Academic Monitoring System</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>
<body>
<header class="topbar">
  <div class="brand">📊 SAMS</div>
  <?php if ($role): ?>
  <nav>
    <?php if ($role === 'admin'): ?>
      <a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
      <a href="<?= BASE_URL ?>/admin/manage_users.php">Users</a>
      <a href="<?= BASE_URL ?>/admin/manage_subjects.php">Subjects</a>
      <a href="<?= BASE_URL ?>/admin/manage_enrollments.php">Enrollments</a>
    <?php elseif ($role === 'teacher'): ?>
      <a href="<?= BASE_URL ?>/teacher/dashboard.php">Dashboard</a>
      <a href="<?= BASE_URL ?>/teacher/grades.php">Grades</a>
      <a href="<?= BASE_URL ?>/teacher/attendance.php">Attendance</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/student/dashboard.php">Dashboard</a>
      <a href="<?= BASE_URL ?>/student/grades.php">My Grades</a>
      <a href="<?= BASE_URL ?>/student/attendance.php">My Attendance</a>
      <a href="<?= BASE_URL ?>/student/reports.php">Performance Report</a>
    <?php endif; ?>
  </nav>
  <div class="user-info">
    <span><?= h(current_name()) ?> (<?= h($role) ?>)</span>
    <a href="<?= BASE_URL ?>/logout.php" class="btn-logout">Logout</a>
  </div>
  <?php endif; ?>
</header>
<main class="container">
