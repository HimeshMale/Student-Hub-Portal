<?php
require_once __DIR__ . "/auth.php";

const SESSION_TIMEOUT = 300;   // seconds of inactivity (5 min). Use 30 to test quickly.

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    ini_set("session.use_strict_mode", "1");   // reject unknown session IDs
    ini_set("session.use_only_cookies", "1");  // no session ID in URLs
    session_set_cookie_params([
        "lifetime" => 0,                          // ends when browser closes
        "path"     => "/",
        "httponly" => true,
        "secure"   => !empty($_SERVER["HTTPS"]),
        "samesite" => "Lax",
    ]);
    session_start();
}

function loginUser(array $user): void
{
    session_regenerate_id(true);               // prevents session fixation
    $_SESSION["user_id"]       = (int)$user["id"];
    $_SESSION["username"]      = $user["username"];
    $_SESSION["role"]          = $user["role"];
    $_SESSION["last_activity"] = time();
}

function destroySession(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), "", [
            "expires"  => time() - 42000,
            "path"     => $p["path"],
            "domain"   => $p["domain"],
            "secure"   => $p["secure"],
            "httponly" => $p["httponly"],
            "samesite" => "Lax",
        ]);
    }
    session_destroy();
}

function enforceTimeout(): void
{
    if (isset($_SESSION["user_id"])) {
        if (time() - ($_SESSION["last_activity"] ?? 0) > SESSION_TIMEOUT) {
            destroySession();
            header("Location: login.php?timeout=1");
            exit;
        }
        $_SESSION["last_activity"] = time();   // refresh on every request
    }
}

function requireLogin(): void
{
    enforceTimeout();
    if (empty($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();
    if (($_SESSION["role"] ?? "") !== $role) {
        http_response_code(403);
        $home = dashboardFor($_SESSION["role"]);
        echo "<h1>403 - Access denied</h1><p>You do not have permission to view this page.</p>";
        echo '<a href="' . htmlspecialchars($home) . '">Go to your dashboard</a>';
        exit;
    }
}

startSecureSession();