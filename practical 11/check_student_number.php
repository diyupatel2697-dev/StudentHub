<?php
/**
 * Check Student Number Availability via AJAX
 * Returns JSON response indicating if student number exists
 */

header('Content-Type: application/json');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');

// Get student number from POST request
$student_number = isset($_POST['student_number']) ? trim($_POST['student_number']) : '';

// Validate student number format
if (!preg_match('/^STU\d{3,6}$/i', $student_number)) {
    echo json_encode(['exists' => false, 'error' => 'Invalid student number format']);
    exit;
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['exists' => false, 'error' => 'Database connection failed']);
    exit;
}

$conn->set_charset("utf8mb4");

// Check if student number exists
$stmt = $conn->prepare("SELECT student_id FROM students WHERE student_number = ?");
$stmt->bind_param("s", $student_number);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result->num_rows > 0;
$stmt->close();
$conn->close();

echo json_encode(['exists' => $exists]);
?>
