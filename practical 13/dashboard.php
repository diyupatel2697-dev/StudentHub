<?php
/**
 * Practical 10: Student Dashboard (Protected by Session & RBAC)
 * Guarded by requireLogin()
 */
require_once __DIR__ . '/auth.php';
requireLogin();

$userName = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
$studentNumber = htmlspecialchars($_SESSION['student_number'] ?? 'N/A');
$email = htmlspecialchars($_SESSION['email'] ?? 'N/A');
$role = htmlspecialchars($_SESSION['role'] ?? 'Student');

// Fetch last login from database
$lastLoginFormatted = "First login";
$conn = @new mysqli('localhost', 'root', '', 'studenthub');
if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT last_login FROM students WHERE student_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (!empty($row['last_login'])) {
                $lastLoginFormatted = date('M d, Y h:i A', strtotime($row['last_login']));
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - StudentHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .role-badge {
            background: var(--soft-green);
            color: var(--primary-green);
            border: 1px solid var(--light-green);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .session-info-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid var(--primary-green);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-top: 4px solid var(--primary-green);
        }
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-green);
            margin: 5px 0;
        }
        .btn-logout {
            background: #d32f2f;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: 0.25s;
        }
        .btn-logout:hover {
            background: #b71c1c;
        }
        .btn-timeout-demo {
            background: #f57c00;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .btn-timeout-demo:hover {
            background: #e65100;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <div class="logo-icon"></div>
            <div class="site-title">STUDENT HUB</div>
        </div>
        <nav>
            <ul id="nav-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="dashboard.php" style="color: var(--primary-green); font-weight: bold;">Dashboard</a></li>
                <li><a href="profile.html">Profile</a></li>
                <li><a href="logout.php" class="btn-logout" style="color: white; margin-left: 10px;">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="dashboard-container">
        <aside class="sidebar">
            <ul>
                <li><a href="dashboard.php"><span>??</span> <span>Dashboard</span></a></li>
                <li><a href="profile.html"><span>??</span> <span>Profile</span></a></li>
                <li><a href="attendance.html"><span>??</span> <span>Attendance</span></a></li>
                <li><a href="subjects.html"><span>??</span> <span>Subjects</span></a></li>
                <li><a href="assignments.html"><span>??</span> <span>Assignments</span></a></li>
                <li><a href="results.html"><span>??</span> <span>Results</span></a></li>
                <li><a href="test_practical10.php"><span>??</span> <span>Test Practical 10</span></a></li>
                <li><a href="logout.php" style="color: #d32f2f;"><span>??</span> <span>Logout</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="session-info-card">
                <div>
                    <h2 style="color: var(--primary-green); margin: 0 0 5px 0;">Welcome, <?php echo $userName; ?>!</h2>
                    <p style="margin: 0; color: #555; font-size: 0.95rem;">
                        Student ID: <strong><?php echo $studentNumber; ?></strong> | Email: <strong><?php echo $email; ?></strong> | Role: <span class="role-badge"><?php echo $role; ?></span>
                    </p>
                    <p style="margin: 5px 0 0 0; color: #777; font-size: 0.85rem;">
                        <strong>Last Login:</strong> <?php echo htmlspecialchars($lastLoginFormatted); ?> (Recorded in database)
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="dashboard.php?simulate_timeout=1" class="btn-timeout-demo" title="Sets session last_activity back 15 minutes to demo timeout redirection">Simulate Inactivity Timeout</a>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <h4>Session Status</h4>
                    <div class="stat-number" style="color: var(--primary-green); font-size: 1.3rem;">Active &amp; Secure</div>
                    <p style="color: #666; font-size: 0.85rem; margin-top: 5px;">Session ID regenerated; protected against fixation.</p>
                </div>
                <div class="stat-card">
                    <h4>Session Timeout Limit</h4>
                    <div class="stat-number">15 Mins</div>
                    <p style="color: #666; font-size: 0.85rem; margin-top: 5px;">Auto-terminates upon inactivity.</p>
                </div>
                <div class="stat-card">
                    <h4>Role-Based Access</h4>
                    <div class="stat-number" style="font-size: 1.3rem;"><?php echo $role; ?> View</div>
                    <p style="color: #666; font-size: 0.85rem; margin-top: 5px;">Authorized student dashboard view.</p>
                </div>
            </div>

            <div class="card" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                <h3 style="color: var(--primary-green); margin-bottom: 15px;">Academic Overview</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: var(--soft-green); color: var(--dark-green); text-align: left;">
                            <th style="padding: 10px; border: 1px solid #ddd;">Course Code</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Course Title</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Credits</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;">ITUE203</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">Web Development Frameworks</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">4</td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><span class="role-badge">Enrolled</span></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;">ITUE204</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">Database Management Systems</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">4</td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><span class="role-badge">Enrolled</span></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;">ITUE205</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">Data Structures &amp; Algorithms</td>
                            <td style="padding: 10px; border: 1px solid #ddd;">4</td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><span class="role-badge">Enrolled</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>
