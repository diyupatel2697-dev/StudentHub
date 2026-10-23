<?php
/**
 * Practical 10: Centralized Authentication & Session Security Manager
 * Features:
 * - Secure session management & fixation protection
 * - Session inactivity timeout (15 minutes)
 * - Role-Based Access Control (RBAC)
 * - Remember-Me cookie validation with token hashing
 */

// Configure session cookie parameters before starting session
if (session_status() === PHP_SESSION_NONE) {
    // 0 = until browser closes; path = '/'; secure = false for local HTTP; httponly = true; samesite = Lax
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

define('SESSION_TIMEOUT', 900); // 15 minutes in seconds

// Test timeout simulation trigger (for lab evaluation demonstration)
if (isset($_GET['simulate_timeout']) && $_GET['simulate_timeout'] == '1') {
    $_SESSION['last_activity'] = time() - (SESSION_TIMEOUT + 60);
}

// 1. Session Inactivity Timeout Check
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['last_activity'])) {
        $inactiveTime = time() - $_SESSION['last_activity'];
        if ($inactiveTime > SESSION_TIMEOUT) {
            // Session expired due to inactivity
            session_unset();
            session_destroy();
            // Clear remember-me cookie on timeout
            if (isset($_COOKIE['remember_me'])) {
                setcookie('remember_me', '', time() - 3600, '/');
            }
            header("Location: login.html?error=timeout&message=" . urlencode("Your session has expired due to inactivity ($inactiveTime seconds). Please log in again."));
            exit;
        }
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
} else {
    // 2. Remember-Me Token Auto-Login (if not logged in)
    if (isset($_COOKIE['remember_me']) && !empty($_COOKIE['remember_me'])) {
        checkRememberMeCookie();
    }
}

/**
 * Check if the user is currently authenticated
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user role ('Admin', 'Student', or 'Guest')
 */
function getUserRole() {
    return $_SESSION['role'] ?? 'Guest';
}

/**
 * Require authentication; redirects unauthorized users to login.html
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $redirectUrl = urlencode($_SERVER['REQUEST_URI'] ?? 'dashboard.php');
        header("Location: login.html?error=unauthorized&message=" . urlencode("Unauthorized access: Please log in to view this page.") . "&redirect=" . $redirectUrl);
        exit;
    }
}

/**
 * Require a specific role (e.g. 'Admin' or 'Student')
 */
function requireRole($requiredRole) {
    requireLogin();
    $currentRole = getUserRole();
    if ($currentRole !== $requiredRole) {
        // Forbidden: Student attempting to access Admin dashboard or vice-versa
        displayForbiddenPage($requiredRole, $currentRole);
        exit;
    }
}

/**
 * Display styled 403 Forbidden Page in StudentHub Green Theme
 */
function displayForbiddenPage($requiredRole, $currentRole) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>403 Access Denied - StudentHub</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .forbidden-card {
                max-width: 550px;
                margin: 4rem auto;
                background: #ffffff;
                padding: 2.5rem;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.15);
                border-top: 5px solid #d32f2f;
                text-align: center;
            }
            .forbidden-icon { font-size: 3rem; color: #d32f2f; margin-bottom: 1rem; }
            .badge-role { display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: bold; background: #ffebee; color: #c62828; }
        </style>
    </head>
    <body>
        <div class="forbidden-card">
            <div class="forbidden-icon">&#9888;</div>
            <h2 style="color: #c62828; margin-bottom: 0.5rem;">403 Access Denied</h2>
            <p style="color: #555; margin-bottom: 1.5rem;">
                This page is restricted to <strong><?php echo htmlspecialchars($requiredRole); ?></strong> users only.
                <br>Your current role is <span class="badge-role"><?php echo htmlspecialchars($currentRole); ?></span>.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <?php if ($currentRole === 'Student'): ?>
                    <a href="dashboard.php" class="btn btn-primary">Go to Student Dashboard</a>
                <?php else: ?>
                    <a href="admin_dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Verify and process Remember-Me Cookie using database selector & hashed token
 */
function checkRememberMeCookie() {
    $cookieParts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($cookieParts) !== 2) {
        return false;
    }
    list($selector, $validator) = $cookieParts;

    $conn = @new mysqli('localhost', 'root', '', 'studenthub');
    if ($conn->connect_error) return false;
    $conn->set_charset("utf8mb4");

    $stmt = $conn->prepare("SELECT user_id, token_hash, expires_at FROM remember_tokens WHERE selector = ? AND expires_at > NOW() LIMIT 1");
    if (!$stmt) { $conn->close(); return false; }

    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $tokenRow = $res->fetch_assoc();
        $calcHash = hash('sha256', $validator);

        if (hash_equals($tokenRow['token_hash'], $calcHash)) {
            // Valid token! Fetch user details
            $uStmt = $conn->prepare("SELECT student_id, student_number, first_name, last_name, email, role, status FROM students WHERE student_id = ? AND status = 'Active' LIMIT 1");
            $uStmt->bind_param("i", $tokenRow['user_id']);
            $uStmt->execute();
            $uRes = $uStmt->get_result();

            if ($uRes && $uRes->num_rows === 1) {
                $user = $uRes->fetch_assoc();
                session_regenerate_id(true); // Prevent fixation
                $_SESSION['user_id'] = $user['student_id'];
                $_SESSION['student_number'] = $user['student_number'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? 'Student';
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
            }
            $uStmt->close();
        }
    }
    $stmt->close();
    $conn->close();
}
?>
