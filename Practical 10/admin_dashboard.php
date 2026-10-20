<?php
require_once __DIR__ . "/includes/session.php";
requireRole("admin");   // only admins get past this line

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

// Summary numbers (no user input, so plain queries are safe)
$row = $conn->query(
    "SELECT COUNT(*), SUM(role = 'admin'), SUM(role = 'student') FROM users"
)->fetch_row();
$totalUsers    = (int)$row[0];
$totalAdmins   = (int)$row[1];
$totalStudents = (int)$row[2];

$activeTokens = (int)$conn->query(
    "SELECT COUNT(*) FROM auth_tokens WHERE expires_at > NOW()"
)->fetch_row()[0];

// All registered users
$users = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: #f2f4f8; color: #222; }

    header {
      background: #fff;
      border-bottom: 1px solid #ddd;
      padding: 14px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }
    header h1 { font-size: 1.4em; }
    .who { display: flex; align-items: center; gap: 14px; font-size: 0.95em; }
    .badge {
      background: #4CAF50; color: #fff;
      padding: 2px 10px; border-radius: 12px; font-size: 0.8em;
    }
    .logout {
      background: #4CAF50; color: #fff; text-decoration: none;
      padding: 8px 16px; border-radius: 4px;
    }

    main { max-width: 900px; margin: 24px auto; padding: 0 16px; }

    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; }
    .card {
      background: #fff; border: 1px solid #ddd; border-radius: 6px;
      padding: 18px; text-align: center;
    }
    .card .num { font-size: 2em; font-weight: bold; color: #4CAF50; }
    .card .label { margin-top: 4px; font-size: 0.9em; color: #555; }

    section {
      background: #fff; border: 1px solid #ddd; border-radius: 6px;
      margin-top: 24px; padding: 20px;
    }
    section h2 { font-size: 1.1em; margin-bottom: 12px; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #eee; }
    th { background: #f7f7f7; font-size: 0.9em; }
    .role-admin   { color: #b26a00; font-weight: bold; }
    .role-student { color: #2e7d32; }
    .note { margin-top: 10px; font-size: 0.85em; color: #666; }
  </style>
</head>
<body>
  <header>
    <h1>Admin Dashboard</h1>
    <div class="who">
      <span>Signed in as <strong><?= e($_SESSION["username"]) ?></strong></span>
      <span class="badge"><?= e($_SESSION["role"]) ?></span>
      <a class="logout" href="logout.php">Logout</a>
    </div>
  </header>

  <main>
    <div class="cards">
      <div class="card"><div class="num"><?= $totalUsers ?></div><div class="label">Total users</div></div>
      <div class="card"><div class="num"><?= $totalStudents ?></div><div class="label">Students</div></div>
      <div class="card"><div class="num"><?= $totalAdmins ?></div><div class="label">Admins</div></div>
      <div class="card"><div class="num"><?= $activeTokens ?></div><div class="label">Remember-me logins</div></div>
    </div>

    <section>
      <h2>Registered users</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>ID</th><th>Username</th><th>Role</th><th>Registered</th></tr>
          </thead>
          <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
              <tr>
                <td><?= (int)$u["id"] ?></td>
                <td>
                  <?= e($u["username"]) ?>
                  <?php if ((int)$u["id"] === (int)$_SESSION["user_id"]): ?>(you)<?php endif; ?>
                </td>
                <td class="role-<?= e($u["role"]) ?>"><?= e($u["role"]) ?></td>
                <td><?= e($u["created_at"]) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <p class="note">
        Session ends after <?= SESSION_TIMEOUT / 60 ?> minutes of inactivity.
      </p>
    </section>
  </main>
</body>
</html>