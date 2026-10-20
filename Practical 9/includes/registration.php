<?php
require_once __DIR__ . "/db.php";

function validateRegister(string $usr, string $passwd, string $confirm): array
{
    $errors = [];

    // Username
    if ($usr === "") {
        $errors["usr"] = "Username is required.";
    } elseif (!preg_match('/^[A-Z0-9_]{3,20}$/', $usr)) {
        $errors["usr"] = "3-20 characters: letters, numbers, underscore only.";
    } elseif (usernameExists($usr)) {            // duplicate check #1 (before insert)
        $errors["usr"] = "Username already taken.";
    }

    // Password
    if ($passwd === "") {
        $errors["passwd"] = "Password is required.";
    } elseif (strlen($passwd) < 8) {
        $errors["passwd"] = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[A-Z]/', $passwd)) {
        $errors["passwd"] = "Password needs at least one uppercase letter.";
    } elseif (!preg_match('/[a-z]/', $passwd)) {
        $errors["passwd"] = "Password needs at least one lowercase letter.";
    } elseif (!preg_match('/[0-9]/', $passwd)) {
        $errors["passwd"] = "Password needs at least one number.";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $passwd)) {
        $errors["passwd"] = "Password needs at least one special character.";
    }

    // Confirm password
    if ($confirm === "") {
        $errors["confirm"] = "Please confirm your password.";
    } elseif ($passwd !== $confirm) {
        $errors["confirm"] = "Passwords do not match.";
    }

    return $errors;
}

function usernameExists(string $usr): bool
{
    global $conn;
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $usr);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

/**
 * Returns "ok", "duplicate" or "error".
 */
function saveUser(string $usr, string $passwd): string
{
    global $conn;
    $hash = password_hash($passwd, PASSWORD_DEFAULT);   // hash BEFORE storing

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $usr, $hash);
        $stmt->execute();
        $stmt->close();
        return "ok";
    } catch (mysqli_sql_exception $e) {
        // duplicate check #2: the UNIQUE key rejects it (error 1062)
        return $e->getCode() === 1062 ? "duplicate" : "error";
    }
}