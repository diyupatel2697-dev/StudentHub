<?php
require "db.php";

$conn->query("
    CREATE TABLE IF NOT EXISTS app_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student') NOT NULL DEFAULT 'student'
    )
");

$conn->query("
    CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(50) NOT NULL,
        action VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$accounts = [
    ["admin", "Admin@123", "admin"],
    ["student", "Student@123", "student"]
];

$stmt = $conn->prepare(
    "INSERT IGNORE INTO app_users (username, password_hash, role) VALUES (?, ?, ?)"
);

foreach ($accounts as $account) {
    $username = $account[0];
    $passwordHash = password_hash($account[1], PASSWORD_DEFAULT);
    $role = $account[2];

    $stmt->bind_param("sss", $username, $passwordHash, $role);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo "Setup completed successfully. Delete setup.php after running it.";
?>