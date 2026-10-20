
<?php
require 'config.php';
requireLogin();

$result = $conn->query(
    'SELECT student_id, name, course
     FROM students ORDER BY student_id'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Students</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6fa;
    padding: 20px;
}
main {
    max-width: 800px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.wrap { overflow-x: auto; }
</style>
</head>
<body>
<main>
    <h2>Student Management</h2>
    <p><a href="index.php">Back to Dashboard</a></p>

    <div class="wrap">
    <table>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Course</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= e($row['student_id']) ?></td>
            <td><?= e($row['name']) ?></td>
            <td><?= e($row['course']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
</main>
</body>
</html>
