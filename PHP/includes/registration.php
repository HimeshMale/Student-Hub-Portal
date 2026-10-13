<?php
// Adjust this path if your login CSV lives somewhere else.
const USERS_CSV = __DIR__ . "/../data/login_data.csv";

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
    } elseif (strlen($passwd) < 6) {
        $errors["passwd"] = "Password must be at least 6 characters.";
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
    if (!is_file(USERS_CSV)) return false;
    $fh = fopen(USERS_CSV, "r");
    if (!$fh) return false;
    while (($row = fgetcsv($fh)) !== false) {
        if (isset($row[0]) && $row[0] === $usr) {
            fclose($fh);
            return true;
        }
    }
    fclose($fh);
    return false;
}

function saveUserToCsv(string $usr, string $passwd): bool
{
    $dir = dirname(USERS_CSV);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) return false;

    $fh = fopen(USERS_CSV, "a");
    if (!$fh) return false;

    flock($fh, LOCK_EX);
    $ok = fputcsv($fh, [$usr, password_hash($passwd, PASSWORD_DEFAULT)]) !== false;
    flock($fh, LOCK_UN);
    fclose($fh);
    return $ok;
}