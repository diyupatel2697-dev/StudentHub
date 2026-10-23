<?php
/**
 * Practical 9: Secure User Registration with MySQLi
 * - Input validation & sanitization
 * - Duplicate email & student number checks
 * - Password hashing with password_hash() (bcrypt)
 * - Prepared statements to prevent SQL injection
 * - Integrated with StudentHub Green Theme
 */

session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$redirectPage = (strpos($referer, 'registration.html') !== false) ? 'registration.html' : 'register.html';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Retrieve input values supporting both registration form variants
    $student_number = isset($_POST['student_number']) ? sanitizeInput($_POST['student_number']) : '';
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $first_name = isset($_POST['first_name']) ? sanitizeInput($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitizeInput($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $mobile = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : (isset($_POST['mobile']) ? sanitizeInput($_POST['mobile']) : '');
    $date_of_birth = isset($_POST['date_of_birth']) ? sanitizeInput($_POST['date_of_birth']) : null;
    $course = isset($_POST['course']) ? sanitizeInput($_POST['course']) : (isset($_POST['department']) ? sanitizeInput($_POST['department']) : 'Computer Science');
    $year = isset($_POST['year']) ? sanitizeInput($_POST['year']) : (isset($_POST['semester']) ? sanitizeInput($_POST['semester']) : '1');
    $gender = isset($_POST['gender']) ? sanitizeInput($_POST['gender']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : (isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '');
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    // Handle full name vs first/last name
    if (!empty($name) && empty($first_name)) {
        $parts = explode(' ', $name, 2);
        $first_name = $parts[0];
        $last_name = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : $parts[0];
    } elseif (empty($name) && !empty($first_name)) {
        $name = trim($first_name . ' ' . $last_name);
    }

    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        $errorMsg = "Database connection failed: " . $conn->connect_error;
        header("Location: " . $redirectPage . "?status=error&message=" . urlencode($errorMsg));
        exit;
    }
    $conn->set_charset("utf8mb4");

    // Auto-generate student number if not provided
    if (empty($student_number)) {
        $resCount = $conn->query("SELECT COUNT(*) AS total FROM students");
        $rowCount = $resCount ? $resCount->fetch_assoc() : ['total' => 0];
        $student_number = "STU" . str_pad((int)$rowCount['total'] + 101, 3, "0", STR_PAD_LEFT);
    }

    // Validation
    if (empty($first_name) || !preg_match("/^[A-Za-z ]{2,50}$/", $name)) {
        $errors[] = "Please enter a valid name (letters only).";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($student_number) || !preg_match('/^STU\d{3,6}$/i', $student_number)) {
        $errors[] = "Student number must be in format STU001, STU101, etc.";
    }

    if (!empty($mobile) && !preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must contain at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if ($terms !== 'Accepted' && $terms !== 'on' && $terms !== '1' && $terms !== true) {
        $errors[] = "You must accept the terms and conditions.";
    }

    if (!empty($errors)) {
        $conn->close();
        header("Location: " . $redirectPage . "?status=error&message=" . urlencode(implode(" ", $errors)));
        exit;
    }

    // 1. DUPLICATE EMAIL CHECK using Prepared Statement
    $emailStmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $emailStmt->bind_param("s", $email);
    $emailStmt->execute();
    $emailRes = $emailStmt->get_result();
    if ($emailRes && $emailRes->num_rows > 0) {
        $emailStmt->close();
        $conn->close();
        header("Location: " . $redirectPage . "?status=error&message=" . urlencode("This email ($email) is already registered. Please login or use a different email."));
        exit;
    }
    $emailStmt->close();

    // 2. DUPLICATE STUDENT NUMBER CHECK using Prepared Statement
    $stuStmt = $conn->prepare("SELECT student_id FROM students WHERE UPPER(student_number) = UPPER(?) LIMIT 1");
    $stuStmt->bind_param("s", $student_number);
    $stuStmt->execute();
    $stuRes = $stuStmt->get_result();
    if ($stuRes && $stuRes->num_rows > 0) {
        $stuStmt->close();
        $conn->close();
        header("Location: " . $redirectPage . "?status=error&message=" . urlencode("Student number ($student_number) is already taken. Please use another."));
        exit;
    }
    $stuStmt->close();

    // 3. SECURE PASSWORD HASHING using password_hash() (bcrypt)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare fields for insertion
    $enrollmentDate = date('Y-m-d');
    $semester = is_numeric($year) ? (int)$year : 1;
    $status = 'Active';

    // 4. INSERT INTO DATABASE using Prepared Statement
    $insertStmt = $conn->prepare("INSERT INTO students (student_number, first_name, last_name, email, phone, date_of_birth, gender, enrollment_date, semester, department, status, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$insertStmt) {
        $errMsg = "Prepare failed: " . $conn->error;
        $conn->close();
        header("Location: " . $redirectPage . "?status=error&message=" . urlencode($errMsg));
        exit;
    }

    $insertStmt->bind_param(
        "ssssssssisss",
        $student_number,
        $first_name,
        $last_name,
        $email,
        $mobile,
        $date_of_birth,
        $gender,
        $enrollmentDate,
        $semester,
        $course,
        $status,
        $hashedPassword
    );

    $success = $insertStmt->execute();
    $insertStmt->close();
    $conn->close();

    if ($success) {
        // Also save to registrations.json if present
        $jsonFile = __DIR__ . "/registrations.json";
        if (file_exists($jsonFile)) {
            $jsonData = @file_get_contents($jsonFile);
            $regs = json_decode($jsonData, true) ?: [];
            $regs[] = [
                "name" => $name,
                "student_number" => $student_number,
                "email" => $email,
                "mobile" => $mobile,
                "course" => $course,
                "year" => $year,
                "gender" => $gender,
                "password" => $hashedPassword,
                "registered_at" => date('Y-m-d H:i:s')
            ];
            @file_put_contents($jsonFile, json_encode($regs, JSON_PRETTY_PRINT));
        }

        // Redirect with success flag
        header("Location: " . $redirectPage . "?status=success");
        exit;
    } else {
        header("Location: " . $redirectPage . "?status=error&message=" . urlencode("Registration failed during database insert. Please try again."));
        exit;
    }
} else {
    header("Location: " . $redirectPage);
    exit;
}
?>
