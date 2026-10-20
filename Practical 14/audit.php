
<?php
require 'config.php';
requireAdmin();

$result = $conn->query(
    'SELECT audit_logs.action, audit_logs.details,
            audit_logs.created_at, users.name
     FROM audit_logs
     LEFT JOIN users ON audit_logs.user_id = users.id
     ORDER BY audit_logs.id DESC'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Audit Log</title>
<style>
body { font-family: Arial; background: #f4f6fa; padding: 20px; }
main { max-width: 900px; margin: auto; background: white; padding: 20px; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
.wrap { overflow-x: auto; }
</style>
</head>
<body>
<main>
    <h2>Admin Audit Log</h2>
    <p><a href="index.php">Back to Dashboard</a></p>
    <div class="wrap">
    <table>
        <tr>
            <th>User</th>
            <th>Action</th>
            <th>Details</th>
            <th>Date</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= e($row['name'] ?? 'Deleted user') ?></td>
            <td><?= e($row['action']) ?></td>
            <td><?= e($row['details'] ?? '') ?></td>
            <td><?= e($row['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
</main>
</body>
</html>
