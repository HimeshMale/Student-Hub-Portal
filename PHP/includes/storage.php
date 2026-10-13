<?php
// Appends one login record (username, password) to data/login_data.csv.
// Returns true on success.
function saveLoginToCsv(string $usr, string $passwd): bool
{
    $dir  = dirname(__DIR__) . "/data";
    $file = $dir . "/login_data.csv";

    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return false;
    }

    $isNew  = !file_exists($file);
    $handle = fopen($file, "a");

    if (!$handle || !flock($handle, LOCK_EX)) {
        return false;
    }

    if ($isNew) {
        fputcsv($handle, ["username", "password"]);
    }
    fputcsv($handle, [$usr, password_hash($passwd, PASSWORD_DEFAULT)]);

    flock($handle, LOCK_UN);
    fclose($handle);
    return true;
}