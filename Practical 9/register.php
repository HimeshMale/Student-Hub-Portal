<?php
require_once __DIR__ . "/includes/registration.php";

$errors  = [];
$success = "";
$usr     = "";
$posted  = $_SERVER["REQUEST_METHOD"] === "POST";

if ($posted) {
    // Sanitize
    $usr     = strtoupper(trim($_POST["usr"] ?? ""));
    $passwd  = $_POST["passwd"] ?? "";
    $confirm = $_POST["confirmpasswd"] ?? "";

    // Validate (backend)
    $errors = validateRegister($usr, $passwd, $confirm);

    // Insert only if everything is valid
    if (empty($errors)) {
        $result = saveUser($usr, $passwd);

        if ($result === "ok") {
            $success = "Registration successful.";
            $usr = "";
        } elseif ($result === "duplicate") {
            $errors["usr"] = "Username already taken.";
        } else {
            $errors["file"] = "Could not save data. Please try again.";
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
  <title>Register</title>
</head>
<body>
  <div class="login">
    <h1>Register</h1>
    <form method="POST" action="register.php" id="regForm" novalidate>
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

      <label for="confirmpasswd">Confirm Password:</label>
      <input type="password" name="confirmpasswd" id="confirmpasswd"
             class="<?= cls($errors, 'confirm', $posted) ?>"
             aria-describedby="confirmError" required>
      <span id="confirmError" class="error-msg" role="alert"><?= e($errors["confirm"] ?? "") ?></span>

      <span class="error-msg" role="alert"><?= e($errors["file"] ?? "") ?></span>

      <button type="submit">Register</button>
    </form>

    <?php if ($success): ?>
      <p class="success-msg" role="status"><?= e($success) ?></p>
    <?php endif; ?>
  </div>

  <script src="JS/register.js"></script>
</body>
</html>