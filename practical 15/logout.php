<?php
session_start();
require "db.php";

if (!empty($_SESSION["user_id"]) && !empty($_SESSION["username"])) {
    $userId = (int)$_SESSION["user_id"];
    $username = $_SESSION["username"];
    $action = "Logged out";

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, username, action) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iss", $userId, $username, $action);
    $stmt->execute();
    $stmt->close();
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.php");
exit;
?>