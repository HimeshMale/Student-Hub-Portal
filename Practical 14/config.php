
<?php
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli('localhost', 'root', '', 'student_db');

if ($conn->connect_error) {
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');

session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function logAction($conn, $action, $details = '') {
    $userId = $_SESSION['user_id'] ?? null;

    $stmt = $conn->prepare(
        'INSERT INTO audit_logs (user_id, action, details)
         VALUES (?, ?, ?)'
    );

    $stmt->bind_param('iss', $userId, $action, $details);
    $stmt->execute();
    $stmt->close();
}

function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();

    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. Admins only.');
    }
}
?>
