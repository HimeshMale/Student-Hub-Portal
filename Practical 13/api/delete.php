<?php
require 'db.php';
require_method('POST');

$d   = json_input();
$sid = strtoupper(trim((string)($d['student_id'] ?? '')));
if ($sid === '') respond(400, false, 'student_id is required.');

$stmt = $conn->prepare('DELETE FROM students WHERE student_id = ?');
$stmt->bind_param('s', $sid);

if (!$stmt->execute())        respond(500, false, 'Database error while deleting the student.');
if ($stmt->affected_rows < 1) respond(404, false, 'Student not found.');
respond(200, true, 'Student deleted successfully.', ['student_id' => $sid]);