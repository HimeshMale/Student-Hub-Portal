<?php
// Database connection (MySQLi, object-oriented)
mysqli_report(MYSQLI_REPORT_OFF);   // we handle errors ourselves

$conn = new mysqli('localhost', 'root', '', 'student_db');
if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error));
}
$conn->set_charset('utf8mb4');

session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function flash($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }