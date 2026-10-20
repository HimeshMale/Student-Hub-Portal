
<?php
require 'config.php';

if (!empty($_SESSION['user_id'])) {
    logAction($conn, 'Logout', 'User logged out');
}

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
?>
