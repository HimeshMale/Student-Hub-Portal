
<?php
require 'config.php';
requireLogin();

$result = $conn->query(
    'SELECT id, event_name, event_date
     FROM events ORDER BY event_date'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Events</title>
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
</style>
</head>
<body>
<main>
    <h2>College Events</h2>
    <p><a href="index.php">Back to Dashboard</a></p>

    <table>
        <tr>
            <th>ID</th>
            <th>Event Name</th>
            <th>Date</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= e($row['id']) ?></td>
            <td><?= e($row['event_name']) ?></td>
            <td><?= e($row['event_date'] ?? '') ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>
</body>
</html>
