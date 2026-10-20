<?php
session_start();
require_once __DIR__ . "/db.php";

function verifyUser(string $usr, string $passwd): bool
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
    $stmt->execute([$usr]);
    $hash = $stmt->fetchColumn();
    return $hash !== false && password_verify($passwd, $hash);
}

function requireLogin(): void
{
    if (empty($_SESSION["user"])) {
        header("Location: login.php");
        exit;
    }
}