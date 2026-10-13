<?php
// Regular expressions (same as the JavaScript version)
const USR_REGEX = '/^(D2[1-6]DCE(00[1-9]|0[1-9][0-9]|1[0-9][0-9]|200)|2[1-5]DCE(00[1-9]|0[1-9][0-9]|1[0-9][0-9]|200))$/';
const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';

// Returns an array of errors; empty array means both fields are valid
function validateLogin(string $usr, string $passwd): array
{
    $errors = [];

    if ($usr === "") {
        $errors["usr"] = "Username is required.";
    } elseif (!preg_match(USR_REGEX, $usr)) {
        $errors["usr"] = "Invalid username. Example: 23DCE045 or D23DCE045.";
    }

    if ($passwd === "") {
        $errors["passwd"] = "Password is required.";
    } elseif (!preg_match(PASSWORD_REGEX, $passwd)) {
        $errors["passwd"] = "Use 8+ characters with an uppercase letter, a number and a special character.";
    }

    return $errors;
}