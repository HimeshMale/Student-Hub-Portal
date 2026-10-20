
<?php
require 'config.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(
        $_SESSION['csrf'],
        $_POST['csrf'] ?? ''
    )) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare(
            'SELECT users.id, users.name, users.password,
                    roles.role_name
             FROM users
             JOIN roles ON users.role_id = roles.id
             WHERE users.email = ?'
        );

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify(
            $password,
            $user['password']
        )) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role_name'];

            logAction($conn, 'Login', 'User logged in');

            header('Location: index.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    margin: 0;
    padding: 50px 15px;
}
.card {
    max-width: 350px;
    margin: auto;
    padding: 25px;
    background: white;
    border-radius: 8px;
}
input, button {
    box-sizing: border-box;
    width: 100%;
    padding: 11px;
    margin: 8px 0;
}
button {
    background: #2563eb;
    color: white;
    border: 0;
    cursor: pointer;
}
.error { color: #b91c1c; }
</style>
</head>
<body>
<div class="card">
    <h2>Admin Dashboard Login</h2>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf"
               value="<?= e($_SESSION['csrf']) ?>">

        <input type="email" name="email"
               placeholder="Email" required>

        <input type="password" name="password"
               placeholder="Password" required>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
