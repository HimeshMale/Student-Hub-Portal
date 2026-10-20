<?php
require 'config.php';

if (!empty($_SESSION['admin'])) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('error', 'Invalid request token. Please try again.');
    } else {
        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        $stmt = $conn->prepare('SELECT password_hash FROM admins WHERE username = ?');
        $stmt->bind_param('s', $user);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($pass, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $user;
            $_SESSION['csrf']  = bin2hex(random_bytes(32));
            header('Location: index.php'); exit;
        }
        flash('error', 'Login failed: wrong username or password.');
    }
    header('Location: login.php'); exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login</title>
<style>
  body { margin:0; font-family:system-ui,Segoe UI,Arial,sans-serif; background:#f5f7fb; color:#1d2433;
         display:flex; min-height:100vh; align-items:center; justify-content:center; }
  .card { background:#fff; border:1px solid #d9dee7; border-radius:8px; padding:24px; width:320px; }
  h1 { font-size:1.3rem; margin:0 0 16px; }
  label { display:flex; flex-direction:column; font-size:.85rem; color:#667085; gap:4px; margin-bottom:12px; }
  input { padding:8px 10px; border:1px solid #d9dee7; border-radius:6px; font-size:.95rem; }
  input:focus, button:focus-visible { outline:2px solid #1f5eff; outline-offset:1px; }
  button { width:100%; padding:9px; border:0; border-radius:6px; background:#1f5eff; color:#fff; font-size:.95rem; cursor:pointer; }
  .msg { padding:10px 12px; border-radius:6px; margin-bottom:12px; font-size:.9rem; background:#fdecea; color:#b42318; border:1px solid #f4b8b2; }
</style>
</head>
<body>
<div class="card">
  <h1>Event admin login</h1>
  <?php if ($flash): ?><div class="msg" role="alert"><?= e($flash['msg']) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <label>Username <input name="username" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button type="submit">Log in</button>
  </form>
</div>
</body>
</html>