<?php
require 'db.php';
require_method('POST');

[$sid, $name, $course] = validate_student(json_input());

// Make sure the student exists first (affected_rows is 0 when nothing changed)
$chk = $conn->prepare('SELECT 1 FROM students WHERE student_id = ?');
$chk->bind_param('s', $sid);
$chk->execute();
if ($chk->get_result()->num_rows === 0) respond(404, false, 'Student not found.');

$stmt = $conn->prepare('UPDATE students SET name = ?, course = ? WHERE student_id = ?');
$stmt->bind_param('sss', $name, $course, $sid);

if ($stmt->execute()) {
    respond(200, true, 'Student updated successfully.', ['student_id' => $sid, 'name' => $name, 'course' => $course]);
}
respond(500, false, 'Database error while updating the student.');