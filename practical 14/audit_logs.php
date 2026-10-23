<?php
session_start();
require "db.php";

if (
    empty($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    http_response_code(403);
    exit("Access denied. Admin only.");
}

$result = $conn->query(
    "SELECT username, action, created_at
     FROM audit_logs
     ORDER BY id DESC
     LIMIT 100"
);

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f1;
            padding: 25px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }
        h1 {
            color: #2d5a27;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #2d5a27;
            color: white;
        }
        a {
            color: #2d5a27;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>System Audit Logs</h1>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <table>
        <tr>
            <th>Username</th>
            <th>Action</th>
            <th>Date and Time</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= h($row["username"]) ?></td>
                    <td><?= h($row["action"]) ?></td>
                    <td><?= h($row["created_at"]) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No audit records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>