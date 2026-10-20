
<?php
require 'config.php';

$students = [
    ['Rudra', 'rudra@example.com', 'Student@123'],
    ['Riya', 'riya@example.com', 'Student@123'],
    ['Neha', 'neha@example.com', 'Student@123'],
    ['Amit', 'amit@example.com', 'Student@123'],
    ['Karan', 'karan@example.com', 'Student@123'],
    ['Priya', 'priya@example.com', 'Student@123'],
    ['Jay', 'jay@example.com', 'Student@123'],
    ['Ananya', 'ananya@example.com', 'Student@123']
];

$stmt = $conn->prepare(
    'INSERT INTO users (name, email, password, role_id)
     VALUES (?, ?, ?, 3)'
);

foreach ($students as $student) {
    $name = $student[0];
    $email = $student[1];
    $password = password_hash($student[2], PASSWORD_DEFAULT);

    $stmt->bind_param('sss', $name, $email, $password);

    if ($stmt->execute()) {
        echo $name . ' account created.<br>';
    } else {
        echo $name . ' account already exists or could not be created.<br>';
    }
}

$stmt->close();

echo 'Student setup completed. Delete setup_student.php now.';
?>
