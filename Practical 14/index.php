
<?php
require 'config.php';
requireAdmin();

$roleId = 1;

// Load menu items permitted for this role
$stmt = $conn->prepare(
    'SELECT menus.menu_name, menus.menu_url
     FROM menus
     JOIN role_menus ON menus.id = role_menus.menu_id
     WHERE role_menus.role_id = ?
     ORDER BY menus.id'
);
$stmt->bind_param('i', $roleId);
$stmt->execute();
$menus = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Dashboard statistics
$totalUsers = $conn->query(
    'SELECT COUNT(*) AS total FROM users'
)->fetch_assoc()['total'];

$totalStudents = $conn->query(
    'SELECT COUNT(*) AS total FROM students'
)->fetch_assoc()['total'];

$totalEvents = $conn->query(
    'SELECT COUNT(*) AS total FROM events'
)->fetch_assoc()['total'];

$newUsers = $conn->query(
    'SELECT COUNT(*) AS total FROM users
     WHERE created_at >= CURDATE()'
)->fetch_assoc()['total'];

// Latest registrations
$latest = $conn->query(
    'SELECT name, email, created_at
     FROM users ORDER BY created_at DESC LIMIT 5'
)->fetch_all(MYSQLI_ASSOC);

// Recent audit records
$logs = $conn->query(
    'SELECT audit_logs.action, audit_logs.details,
            audit_logs.created_at, users.name
     FROM audit_logs
     LEFT JOIN users ON audit_logs.user_id = users.id
     ORDER BY audit_logs.id DESC LIMIT 5'
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard</title>
<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6fa;
    color: #222;
}

header {
    background: #243b64;
    color: white;
    padding: 20px;
}

header h2 { margin: 0 0 6px; }

.layout {
    display: flex;
    min-height: calc(100vh - 76px);
}

nav {
    width: 200px;
    background: white;
    padding: 20px 12px;
}

nav a {
    display: block;
    padding: 12px;
    margin-bottom: 6px;
    color: #333;
    text-decoration: none;
    border-radius: 5px;
}

nav a:hover, nav a.active {
    background: #e7edff;
    color: #1d4ed8;
}

main {
    flex: 1;
    min-width: 0;
    padding: 24px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 24px;
}

.card, .panel {
    background: white;
    padding: 18px;
    border-radius: 8px;
    border: 1px solid #e1e5ed;
}

.card p { color: #666; margin: 0 0 10px; }
.card h2 { margin: 0; color: #244b94; }

.panel { margin-bottom: 20px; }

.table-wrap { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 8px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

th { color: #555; }

.logout {
    color: #b91c1c;
}

@media (max-width: 750px) {
    .layout { flex-direction: column; }
    nav { width: 100%; }
    nav a { display: inline-block; }
    .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    main { padding: 14px; }
}
</style>
</head>
<body>

<header>
    <h2>Admin Dashboard</h2>
    Welcome, <?= e($_SESSION['name']) ?>
    (<?= e($_SESSION['role']) ?>)
</header>

<div class="layout">
    <nav>
        <?php foreach ($menus as $menu): ?>
            <a class="<?= $menu['menu_url'] === 'index.php'
                ? 'active' : '' ?>"
               href="<?= e($menu['menu_url']) ?>">
                <?= e($menu['menu_name']) ?>
            </a>
        <?php endforeach; ?>

        <a class="logout" href="logout.php">Logout</a>
    </nav>

    <main>
        <h2>Overview</h2>

        <div class="cards">
            <div class="card">
                <p>Total Users</p>
                <h2><?= (int)$totalUsers ?></h2>
            </div>

            <div class="card">
                <p>Students</p>
                <h2><?= (int)$totalStudents ?></h2>
            </div>

            <div class="card">
                <p>Total Events</p>
                <h2><?= (int)$totalEvents ?></h2>
            </div>

            <div class="card">
                <p>Today's Registrations</p>
                <h2><?= (int)$newUsers ?></h2>
            </div>
        </div>

        <div class="panel">
            <h3>Latest Registrations</h3>
            <div class="table-wrap">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered</th>
                </tr>

                <?php foreach ($latest as $user): ?>
                <tr>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e($user['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$latest): ?>
                <tr><td colspan="3">No registrations yet.</td></tr>
                <?php endif; ?>
            </table>
            </div>
        </div>

        <div class="panel">
            <h3>Recent Audit Log</h3>
            <div class="table-wrap">
            <table>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>Date</th>
                </tr>

                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['name'] ?? 'Deleted user') ?></td>
                    <td><?= e($log['action']) ?></td>
                    <td><?= e($log['details'] ?? '') ?></td>
                    <td><?= e($log['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$logs): ?>
                <tr><td colspan="4">No activity recorded.</td></tr>
                <?php endif; ?>
            </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
