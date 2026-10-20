<?php
require 'db.php';
require_method('GET');

$q      = trim($_GET['q'] ?? '');
$course = trim($_GET['course'] ?? '');

$sql = 'SELECT student_id, name, course FROM students WHERE 1=1';
$types = ''; $params = [];

if ($q !== '') {
    $sql .= ' AND (student_id LIKE ? OR name LIKE ? OR course LIKE ?)';
    $like = '%' . $q . '%';
    $types .= 'sss'; array_push($params, $like, $like, $like);
}
if ($course !== '') {
    $sql .= ' AND course = ?';
    $types .= 's'; $params[] = $course;
}
$sql .= ' ORDER BY student_id ASC';

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
if (!$stmt->execute()) respond(500, false, 'Could not load students.');
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

respond(200, true, count($rows) . ' student(s) found.', $rows);