<?php
require_once __DIR__ . "/db.php";

function validateRegister(string $usr, string $passwd, string $confirm): array
{
    $errors = [];

    if ($usr === "") {
        $errors["usr"] = "Username is required.";
    } elseif (!preg_match('/^[A-Z0-9_]{3,20}$/', $usr)) {
        $errors["usr"] = "3-20 characters: letters, numbers, underscore only.";
    } elseif (userExists($usr)) {
        $errors["usr"] = "Username already taken.";
    }

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

    if ($confirm === "") {
        $errors["confirm"] = "Please confirm your password.";
    } elseif ($passwd !== $confirm) {
        $errors["confirm"] = "Passwords do not match.";
    }

    return $errors;
}

function userExists(string $usr): bool
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ?");
    $stmt->execute([$usr]);
    return (bool) $stmt->fetchColumn();
}

function saveUser(string $usr, string $passwd): bool
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        return $stmt->execute([$usr, password_hash($passwd, PASSWORD_DEFAULT)]);
    } catch (PDOException $e) {
        return false;
    }
}