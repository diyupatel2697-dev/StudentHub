<?php
session_start();
require "db.php";

if (!empty($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password_hash, role FROM app_users WHERE username=?"
    );
    $stmt = $conn->prepare(
    "SELECT id, username, password_hash, role FROM app_users WHERE username=?"
);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $username);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user["password_hash"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = (int)$user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        $log = $conn->prepare(
            "INSERT INTO audit_logs (user_id, username, action) VALUES (?, ?, ?)"
        );

        $action = "Logged in";
        $log->bind_param("iss", $_SESSION["user_id"], $_SESSION["username"], $action);
        $log->execute();
        $log->close();

        header("Location: dashboard.php");
        exit;
    } else {
        $message = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f1;
            padding: 30px;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }
        h1 {
            color: #2d5a27;
        }
        input, button {
            width: 100%;
            padding: 11px;
            margin: 8px 0;
            box-sizing: border-box;
        }
        input {
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #2d5a27;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .message {
            color: #b3261e;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>StudentHub Login</h1>

    <p class="message">
        <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
    </p>

    <form method="POST">
        <input type="text" name="username" placeholder="Username"
               maxlength="50" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>