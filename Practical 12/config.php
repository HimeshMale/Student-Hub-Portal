<?php
// MySQLi connection + session + helpers
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli('localhost', 'root', '', 'event_db');
if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}
$conn->set_charset('utf8mb4');

session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Poster upload rules
const UPLOAD_DIR     = __DIR__ . '/uploads/';
const MAX_POSTER_MB  = 2;
const ALLOWED_TYPES  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const STATUSES       = ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'];

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function flash($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function csrf_ok() { return hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? ''); }