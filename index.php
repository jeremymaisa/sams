<?php
require_once __DIR__ . '/includes/auth.php';
isset($_SESSION['user_id']) ? redirect('/' . current_role() . '/dashboard.php') : redirect('/login.php');
