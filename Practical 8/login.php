<?php
require_once __DIR__ . "/includes/validation.php";
require_once __DIR__ . "/includes/auth.php";

$errors = [];
$usr    = "";
$posted = $_SERVER["REQUEST_METHOD"] === "POST";

if ($posted) {
    // Sanitize
    $usr    = strtoupper(trim($_POST["usr"] ?? ""));
    $passwd = $_POST["passwd"] ?? "";

    // Validate
    $errors = validateLogin($usr, $passwd);

    // Check credentials only if both fields are valid
    if (empty($errors)) {
        if (verifyUser($usr, $passwd)) {
            session_regenerate_id(true);
            $_SESSION["user"] = $usr;
            header("Location: home.php");
            exit;
        } else {
            $errors["file"] = "Invalid username or password.";
        }
    }
}

function cls(array $errors, string $field, bool $posted): string
{
    if (!$posted) return "";
    return isset($errors[$field]) ? "invalid" : "valid";
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
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
    <form method="POST" action="login.php" novalidate>
      <label for="usr">Username:</label>
      <input type="text" name="usr" id="usr" value="<?= e($usr) ?>"
             class="<?= cls($errors, 'usr', $posted) ?>"
             aria-describedby="usrError" required>
      <span id="usrError" class="error-msg" role="alert"><?= e($errors["usr"] ?? "") ?></span>

      <label for="passwd">Password:</label>
      <input type="password" name="passwd" id="passwd"
             class="<?= cls($errors, 'passwd', $posted) ?>"
             aria-describedby="passwdError" required>
      <span id="passwdError" class="error-msg" role="alert"><?= e($errors["passwd"] ?? "") ?></span>

      <span class="error-msg" role="alert"><?= e($errors["file"] ?? "") ?></span>

      <button type="submit">Login</button>
    </form>

    <a href="register.php">New User?</a>
  </div>
</body>
</html>