<?php
/**
 * Check Email Availability via AJAX
 * Returns JSON response indicating if email exists
 */

header('Content-Type: application/json');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');

// Get email from POST request
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false, 'error' => 'Invalid email format']);
    exit;
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['exists' => false, 'error' => 'Database connection failed']);
    exit;
}

$conn->set_charset("utf8mb4");

// Check if email exists
$stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result->num_rows > 0;
$stmt->close();
$conn->close();

echo json_encode(['exists' => $exists]);
?>
