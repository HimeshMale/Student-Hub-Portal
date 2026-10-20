<?php
require_once __DIR__ . "/includes/session.php";

clearRememberToken();   // remove remember-me token from DB and browser
destroySession();       // clear data, expire session cookie, destroy session

header("Location: login.php?logout=1");
exit;