<?php
/**
 * Practical 9: Secure Login Processor using MySQLi and password_verify()
 * StudentHub Green Theme Integration
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
$loginPage = (strpos($referer, 'login_secure.html') !== false) ? 'login_secure.html' : 'login.html';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = isset($_POST['email']) ? sanitizeInput($_POST['email']) : (isset($_POST['username']) ? sanitizeInput($_POST['username']) : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($identifier) || empty($password)) {
        header("Location: " . $loginPage . "?error=1&message=" . urlencode("Please enter both username/email and password."));
        exit;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        header("Location: " . $loginPage . "?error=1&message=" . urlencode("Database connection failed. Ensure MySQL is running."));
        exit;
    }
    $conn->set_charset("utf8mb4");

    // Prepared statement querying either email or student_number
    $stmt = $conn->prepare("SELECT student_id, student_number, first_name, last_name, email, password, status FROM students WHERE email = ? OR UPPER(student_number) = UPPER(?) LIMIT 1");
    if (!$stmt) {
        $conn->close();
        header("Location: " . $loginPage . "?error=1&message=" . urlencode("Server query error."));
        exit;
    }

    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify hashed password using password_verify()
        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'Active') {
                $_SESSION['user_id'] = $user['student_id'];
                $_SESSION['student_number'] = $user['student_number'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;

                $stmt->close();
                $conn->close();
                header("Location: dashboard.html");
                exit;
            } else {
                $stmt->close();
                $conn->close();
                header("Location: " . $loginPage . "?error=1&message=" . urlencode("Your account is inactive. Please contact admin."));
                exit;
            }
        } else {
            $stmt->close();
            $conn->close();
            header("Location: " . $loginPage . "?error=1&message=" . urlencode("Invalid email/username or password."));
            exit;
        }
    } else {
        $stmt->close();
        $conn->close();
        header("Location: " . $loginPage . "?error=1&message=" . urlencode("Invalid email/username or password."));
        exit;
    }
} else {
    header("Location: " . $loginPage);
    exit;
}
?>
