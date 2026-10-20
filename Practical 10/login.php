<?php
require_once __DIR__ . "/includes/session.php";

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

// Already logged in -> go to the right dashboard
if (!empty($_SESSION["user_id"])) {
    header("Location: " . dashboardFor($_SESSION["role"]));
    exit;
}

// Valid remember-me cookie -> log in automatically
if ($user = userFromRememberCookie()) {
    loginUser($user);
    header("Location: " . dashboardFor($user["role"]));
    exit;
}

$errors = [];
$info   = "";
$usr    = "";
$posted = $_SERVER["REQUEST_METHOD"] === "POST";

if (isset($_GET["timeout"])) $info = "Session expired due to inactivity. Please log in again.";
if (isset($_GET["logout"]))  $info = "You have been logged out.";

if ($posted) {
    $usr      = strtoupper(trim($_POST["usr"] ?? ""));
    $passwd   = $_POST["passwd"] ?? "";
    $remember = isset($_POST["remember"]);

    if ($usr === "")    $errors["usr"]    = "Username is required.";
    if ($passwd === "") $errors["passwd"] = "Password is required.";

    if (empty($errors)) {
        $user = findUser($usr);

        // password_verify compares the typed password with the stored hash
        if ($user && password_verify($passwd, $user["password_hash"])) {
            loginUser($user);                       // regenerates session ID
            if ($remember) issueRememberToken((int)$user["id"]);
            header("Location: " . dashboardFor($user["role"]));   // role-based redirect
            exit;
        }
        $errors["login"] = "Invalid username or password.";   // same message for both cases
    }
}

function cls(array $errors, string $field, bool $posted): string
{
    if (!$posted) return "";
    return isset($errors[$field]) ? "invalid" : "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="CSS/login.css">
  <title>Login</title>
</head>
<body>
  <div class="login">
    <h1>Login</h1>

    <?php if ($info): ?>
      <p class="success-msg" role="status"><?= e($info) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <label for="usr">Username:</label>
      <input type="text" name="usr" id="usr" value="<?= e($usr) ?>"
             class="<?= cls($errors, 'usr', $posted) ?>" required>
      <span class="error-msg" role="alert"><?= e($errors["usr"] ?? "") ?></span>

      <label for="passwd">Password:</label>
      <input type="password" name="passwd" id="passwd"
             class="<?= cls($errors, 'passwd', $posted) ?>" required>
      <span class="error-msg" role="alert"><?= e($errors["passwd"] ?? "") ?></span>

      <label style="display:flex;align-items:center;gap:8px;font-size:0.9em;">
        <input type="checkbox" name="remember" style="width:auto;margin:0;"> Remember me
      </label>

      <span class="error-msg" role="alert"><?= e($errors["login"] ?? "") ?></span>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>