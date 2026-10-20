<?php
require_once __DIR__ . "/db.php";

const REMEMBER_COOKIE = "remember_me";
const REMEMBER_DAYS   = 30;

function dashboardFor(string $role): string
{
    return $role === "admin" ? "admin_dashboard.php" : "student_dashboard.php";
}

function findUser(string $usr): ?array
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $usr);
    $stmt->execute();
    $stmt->bind_result($id, $username, $hash, $role);
    $user = $stmt->fetch()
        ? ["id" => $id, "username" => $username, "password_hash" => $hash, "role" => $role]
        : null;
    $stmt->close();
    return $user;
}

/* ---------- Remember-me (selector + hashed validator) ---------- */

function setRememberCookie(string $value, int $expires): void
{
    setcookie(REMEMBER_COOKIE, $value, [
        "expires"  => $expires,
        "path"     => "/",
        "httponly" => true,                       // not readable by JavaScript
        "secure"   => !empty($_SERVER["HTTPS"]),  // HTTPS only when available
        "samesite" => "Lax",
    ]);
}

function issueRememberToken(int $userId): void
{
    global $conn;
    $selector  = bin2hex(random_bytes(9));      // 18 chars, public lookup key
    $validator = bin2hex(random_bytes(32));     // secret, only the hash is stored
    $hash      = hash("sha256", $validator);
    $expires   = time() + REMEMBER_DAYS * 86400;
    $expiresDb = date("Y-m-d H:i:s", $expires);

    $stmt = $conn->prepare("INSERT INTO auth_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $selector, $hash, $expiresDb);
    $stmt->execute();
    $stmt->close();

    setRememberCookie($selector . ":" . $validator, $expires);
}

function deleteTokenBySelector(string $selector): void
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE selector = ?");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $stmt->close();
}

/** Returns the user array if the cookie is valid (and rotates the token), else null. */
function userFromRememberCookie(): ?array
{
    global $conn;
    if (empty($_COOKIE[REMEMBER_COOKIE])) return null;

    $parts = explode(":", $_COOKIE[REMEMBER_COOKIE]);
    if (count($parts) !== 2) { setRememberCookie("", time() - 3600); return null; }
    [$selector, $validator] = $parts;

    $stmt = $conn->prepare(
        "SELECT t.token_hash, u.id, u.username, u.role
         FROM auth_tokens t JOIN users u ON u.id = t.user_id
         WHERE t.selector = ? AND t.expires_at > NOW() LIMIT 1"
    );
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $stmt->bind_result($tokenHash, $id, $username, $role);
    $found = $stmt->fetch();
    $stmt->close();

    if (!$found || !hash_equals($tokenHash, hash("sha256", $validator))) {
        deleteTokenBySelector($selector);
        setRememberCookie("", time() - 3600);
        return null;
    }

    deleteTokenBySelector($selector);   // rotate: old token is used once
    issueRememberToken((int)$id);
    return ["id" => $id, "username" => $username, "role" => $role];
}

function clearRememberToken(): void
{
    if (!empty($_COOKIE[REMEMBER_COOKIE])) {
        $parts = explode(":", $_COOKIE[REMEMBER_COOKIE]);
        if (!empty($parts[0])) deleteTokenBySelector($parts[0]);
    }
    setRememberCookie("", time() - 3600);
}