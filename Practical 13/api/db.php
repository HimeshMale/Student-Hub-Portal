<?php
// Shared by every endpoint: JSON headers, DB connection, helpers.
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');          // PHP warnings must never break the JSON
mysqli_report(MYSQLI_REPORT_OFF);

const COURSES = [
    'DCE' => 'Computer Engineering',
    'DCS' => 'Computer Science',
    'DIT' => 'Information Technology',
];

/* Send one JSON response and stop. */
function respond(int $code, bool $success, string $message, $data = null): void {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Allow only one HTTP method per endpoint. */
function require_method(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        respond(405, false, "Method not allowed. Use $method for this endpoint.");
    }
}

/* Read and decode the JSON request body. */
function json_input(): array {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) respond(400, false, 'Request body must be valid JSON.');
    return $data;
}

/* Validate student fields BEFORE touching the database. Returns [id, name, course]. */
function validate_student(array $d): array {
    $sid    = strtoupper(trim((string)($d['student_id'] ?? '')));
    $name   = trim((string)($d['name'] ?? ''));
    $course = trim((string)($d['course'] ?? ''));
    $errors = [];

    if (!preg_match('/^\d{2}(DCE|DCS|DIT)\d{3}$/', $sid, $m)) {
        $errors[] = 'Student ID must look like 25DCE011 (year + DCE/DCS/DIT + 3 digits).';
    } elseif ($course !== COURSES[$m[1]]) {
        $errors[] = "ID $sid belongs to " . COURSES[$m[1]] . ', so the course must match.';
    }
    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Name is required (max 100 characters).';

    if ($errors) respond(400, false, implode(' ', $errors), ['errors' => $errors]);
    return [$sid, $name, $course];
}

$conn = new mysqli('localhost', 'root', '', 'student_db');
if ($conn->connect_error) respond(500, false, 'Database connection failed.');
$conn->set_charset('utf8mb4');