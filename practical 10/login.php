<?php
/**
 * Practical 10: Secure Login Processing with Sessions, RBAC & Remember-Me
 * Features:
 * - password_verify() validation
 * - session_regenerate_id(true) protection against session fixation
 * - Role-Based Redirection (Admin -> admin_dashboard.php, Student -> dashboard.php)
 * - last_login timestamp recording
 * - Secure random token generation & hashing for Remember-Me
 */

require_once __DIR__ . '/auth.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = isset($_POST['email']) ? sanitizeInput($_POST['email']) : (isset($_POST['username']) ? sanitizeInput($_POST['username']) : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($identifier) || empty($password)) {
        header("Location: login.html?error=1&message=" . urlencode("Please enter both username/email and password."));
        exit;
    }

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        header("Location: login.html?error=1&message=" . urlencode("Database connection failed. Ensure MySQL is running."));
        exit;
    }
    $conn->set_charset("utf8mb4");

    // Fetch user with role
    $stmt = $conn->prepare("SELECT student_id, student_number, first_name, last_name, email, password, role, status FROM students WHERE email = ? OR UPPER(student_number) = UPPER(?) LIMIT 1");
    if (!$stmt) {
        $conn->close();
        header("Location: login.html?error=1&message=" . urlencode("Query preparation failed."));
        exit;
    }

    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 1. Password Verification using password_verify()
        if (password_verify($password, $user['password'])) {
            if ($user['status'] !== 'Active') {
                $stmt->close();
                $conn->close();
                header("Location: login.html?error=1&message=" . urlencode("Your account is not active. Please contact administrator."));
                exit;
            }

            // 2. Prevent Session Fixation by regenerating Session ID
            session_regenerate_id(true);

            // 3. Establish Session Variables
            $_SESSION['user_id'] = $user['student_id'];
            $_SESSION['student_number'] = $user['student_number'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = !empty($user['role']) ? $user['role'] : 'Student';
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();

            // 4. Update Last Login Timestamp (Intermediate Extension)
            $updateLogin = $conn->prepare("UPDATE students SET last_login = NOW() WHERE student_id = ?");
            if ($updateLogin) {
                $updateLogin->bind_param("i", $user['student_id']);
                $updateLogin->execute();
                $updateLogin->close();
            }

            // 5. Secure Remember-Me Token Generation (Advanced Extension)
            if ($remember) {
                $selector = bin2hex(random_bytes(16)); // 32 hex chars
                $validator = bin2hex(random_bytes(32)); // 64 hex chars
                $tokenHash = hash('sha256', $validator);
                $expiryTime = time() + (30 * 24 * 60 * 60); // 30 days
                $expiryDate = date('Y-m-d H:i:s', $expiryTime);

                // Store hashed token in database
                $tokStmt = $conn->prepare("INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)");
                if ($tokStmt) {
                    $tokStmt->bind_param("isss", $user['student_id'], $selector, $tokenHash, $expiryDate);
                    $tokStmt->execute();
                    $tokStmt->close();
                }

                // Set secure HTTP-only cookie with selector and raw validator
                setcookie('remember_me', $selector . ':' . $validator, [
                    'expires' => $expiryTime,
                    'path' => '/',
                    'domain' => '',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            $stmt->close();
            $conn->close();

            // 6. Role-Based Redirection (RBAC)
            if ($_SESSION['role'] === 'Admin') {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: dashboard.php");
                exit;
            }

        } else {
            // Password mismatch
            $stmt->close();
            $conn->close();
            header("Location: login.html?error=1&message=" . urlencode("Invalid email/username or password."));
            exit;
        }
    } else {
        // User not found
        $stmt->close();
        $conn->close();
        header("Location: login.html?error=1&message=" . urlencode("Invalid email/username or password."));
        exit;
    }

} else {
    // If already logged in, redirect to appropriate dashboard based on role
    if (isLoggedIn()) {
        if (getUserRole() === 'Admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
    header("Location: login.html");
    exit;
}
?>
