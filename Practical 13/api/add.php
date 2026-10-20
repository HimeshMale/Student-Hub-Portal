<?php
require 'db.php';
require_method('POST');

[$sid, $name, $course] = validate_student(json_input());

$stmt = $conn->prepare('INSERT INTO students (student_id, name, course) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $sid, $name, $course);

if ($stmt->execute()) {
    respond(201, true, 'Student added successfully.', ['student_id' => $sid, 'name' => $name, 'course' => $course]);
}
if ($stmt->errno === 1062) respond(409, false, 'A student with this ID already exists.');
respond(500, false, 'Database error while adding the student.');