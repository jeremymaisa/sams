<?php
session_start();

// Folder name of the project under htdocs. If you rename the folder, change this.
// Use '' (empty) if the project is placed directly in the web root.
define('BASE_URL', '/sams');

function redirect($path) { header("Location: " . BASE_URL . $path); exit; }

function require_login() {
    if (!isset($_SESSION['user_id'])) redirect('/login.php');
}
function require_role($roles) {
    require_login();
    if (!in_array($_SESSION['role'], (array)$roles)) redirect('/login.php?error=unauthorized');
}
function current_user_id() { return $_SESSION['user_id'] ?? null; }
function current_role()    { return $_SESSION['role'] ?? null; }
function current_name()    { return $_SESSION['full_name'] ?? ''; }
function h($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
