<?php
/**
 * Practical 10: Secure Logout Processor
 * - Destroys PHP session
 * - Clears session cookie
 * - Removes Remember-Me token from database and invalidates remember-me cookie
 * - Redirects to login with confirmation
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Delete Remember-Me token from database if present
if (isset($_COOKIE['remember_me'])) {
    $cookieParts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($cookieParts) === 2) {
        $selector = $cookieParts[0];
        $conn = @new mysqli('localhost', 'root', '', 'studenthub');
        if (!$conn->connect_error) {
            $delStmt = $conn->prepare("DELETE FROM remember_tokens WHERE selector = ?");
            if ($delStmt) {
                $delStmt->bind_param("s", $selector);
                $delStmt->execute();
                $delStmt->close();
            }
            $conn->close();
        }
    }
    // Delete cookie from browser
    setcookie('remember_me', '', time() - 3600, '/');
}

// 2. Clear all session variables
$_SESSION = [];

// 3. Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destroy the session
session_destroy();

// 5. Redirect to login page with logout notification
header("Location: login.html?status=logout&message=" . urlencode("You have been securely logged out."));
exit;
?>
