<?php
require_once __DIR__ . '/includes/auth.php';
$members = [
  'Maisa, Jeremy O.', 'Malicay, Jayson Mark T.', 'Dacles, Rayver E.', 'Garbo, Johnpaul B.',
  'Balderamos, Gerald D.', 'Villar, Emmalyn D.', 'Morfe, Riczhie Kyreme M.', 'Bayongan, Keth Lee Mark B.',
];
$page_title = "Project Team";
include __DIR__ . '/includes/header.php';
?>
<h1>About the Project</h1>
<div class="card">
  <h2>Web-Based Student Academic Performance Monitoring System</h2>
  <p>Monitors student grades, attendance, and overall academic performance, with separate access for administrators, teachers, and students.</p>
</div>
<div class="card">
  <h2>Project Team</h2>
  <table>
    <tr><th style="width:60px">#</th><th>Member</th></tr>
    <?php foreach ($members as $i => $m): ?><tr><td><?= $i + 1 ?></td><td><?= h($m) ?></td></tr><?php endforeach; ?>
  </table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
