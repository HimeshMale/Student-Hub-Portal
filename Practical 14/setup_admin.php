
<?php
require 'config.php';

$name = 'Admin';
$email = 'admin@example.com';
$password = password_hash('Admin@123', PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    'INSERT INTO users (name, email, password, role_id)
     VALUES (?, ?, ?, 1)'
);

$stmt->bind_param('sss', $name, $email, $password);

if ($stmt->execute()) {
    echo 'Admin created. Delete setup_admin.php now.';
} else {
    echo 'Admin already exists or could not be created.';
}

$stmt->close();
?>
