<?php
session_start();
require "db.php";

if (empty($_SESSION["user_id"]) || empty($_SESSION["role"])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION["username"];
$role = $_SESSION["role"];

function logAction($conn, $userId, $username, $action) {
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (user_id, username, action) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iss", $userId, $username, $action);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["log_action"])) {
    $action = "Visited " . ($role === "admin" ? "admin" : "student") . " dashboard";
    logAction($conn, $_SESSION["user_id"], $username, $action);
    header("Location: dashboard.php");
    exit;
}

if ($role === "admin") {
    $result = $conn->query("SELECT COUNT(*) AS total FROM student_records");
    $totalStudents = $result ? $result->fetch_assoc()["total"] : 0;

    $result = $conn->query("SELECT COUNT(*) AS total FROM event_management");
    $totalEvents = $result ? $result->fetch_assoc()["total"] : 0;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f1;
            margin: 0;
        }
        header {
            background: #2d5a27;
            color: white;
            padding: 20px;
        }
        nav {
            background: white;
            padding: 15px;
        }
        nav a {
            color: #2d5a27;
            margin-right: 18px;
            text-decoration: none;
        }
        main {
            max-width: 1000px;
            margin: 25px auto;
            padding: 20px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
        }
        h2, h3 {
            color: #2d5a27;
        }
        button {
            padding: 10px 15px;
            background: #2d5a27;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
    <h1>StudentHub Dashboard</h1>
    <p>Welcome, <?= h($username) ?>!</p>
    <p>Your role: <?= h(ucfirst($role)) ?></p>
</header>

<nav>
    <a href="dashboard.php">Dashboard</a>

    <?php if ($role === "admin"): ?>
        <a href="audit_logs.php">Audit Logs</a>
        <a href="../practical%2011/index.php">Student Management</a>
        <a href="../practical%2012/index.php">Event Management</a>
    <?php else: ?>
        <a href="../practical%2011/index.php">Student Records</a>
    <?php endif; ?>

    <a href="logout.php">Logout</a>
</nav>

<main>
    <?php if ($role === "admin"): ?>
        <h2>Admin Dashboard</h2>

        <div class="cards">
            <div class="card">
                <h3>Total Students</h3>
                <p><?= (int)$totalStudents ?></p>
            </div>

            <div class="card">
                <h3>Total Events</h3>
                <p><?= (int)$totalEvents ?></p>
            </div>

            <div class="card">
                <h3>Admin Controls</h3>
                <p>Manage records and review system activity.</p>
            </div>
        </div>
    <?php else: ?>
        <h2>Student Dashboard</h2>
        <div class="card">
            <h3>Welcome to StudentHub</h3>
            <p>You are logged in as a student.</p>
            <p>Use the available menu to view student records.</p>
        </div>
    <?php endif; ?>

    <form method="POST" style="margin-top:20px;">
        <button type="submit" name="log_action" value="1">
            Record Dashboard Visit
        </button>
    </form>
</main>
</body>
</html>