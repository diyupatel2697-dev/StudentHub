<?php
/**
 * Practical 10: Admin Dashboard (Protected by RBAC)
 * Guarded strictly by requireRole('Admin')
 */
require_once __DIR__ . '/auth.php';
requireRole('Admin');

$adminName = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
$adminEmail = htmlspecialchars($_SESSION['email'] ?? 'N/A');
$adminNumber = htmlspecialchars($_SESSION['student_number'] ?? 'ADM001');

// Fetch statistics and last login from MySQL
$totalStudents = 0;
$totalAdmins = 0;
$totalEvents = 0;
$totalRegistrations = 0;
$lastLoginFormatted = "N/A";
$usersList = [];

$conn = @new mysqli('localhost', 'root', '', 'studenthub');
if (!$conn->connect_error) {
    // 1. Fetch counts
    $stuRes = $conn->query("SELECT COUNT(*) AS c FROM students WHERE role = 'Student'");
    if ($stuRes) $totalStudents = (int)$stuRes->fetch_assoc()['c'];

    $admRes = $conn->query("SELECT COUNT(*) AS c FROM students WHERE role = 'Admin'");
    if ($admRes) $totalAdmins = (int)$admRes->fetch_assoc()['c'];

    $eveRes = $conn->query("SELECT COUNT(*) AS c FROM events");
    if ($eveRes) $totalEvents = (int)$eveRes->fetch_assoc()['c'];

    $regRes = $conn->query("SELECT COUNT(*) AS c FROM registrations");
    if ($regRes) $totalRegistrations = (int)$regRes->fetch_assoc()['c'];

    // 2. Fetch admin last login
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

    // 3. Fetch recent users with roles
    $listRes = $conn->query("SELECT student_id, student_number, first_name, last_name, email, role, status, last_login, created_at FROM students ORDER BY student_id DESC LIMIT 8");
    if ($listRes) {
        while ($r = $listRes->fetch_assoc()) {
            $usersList[] = $r;
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentHub (RBAC)</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-badge {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
        }
        .student-badge {
            background: var(--soft-green);
            color: var(--primary-green);
            border: 1px solid var(--light-green);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
        }
        .admin-header-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 25px;
            border-left: 5px solid #c62828;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            font-size: 2rem;
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
        .btn-logout:hover { background: #b71c1c; }
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
        .btn-timeout-demo:hover { background: #e65100; }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <div class="logo-icon"></div>
            <div class="site-title">STUDENT HUB <span style="font-size: 0.8rem; background: #c62828; color: white; padding: 2px 8px; border-radius: 4px; margin-left: 8px;">ADMIN CONSOLE</span></div>
        </div>
        <nav>
            <ul id="nav-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="admin_dashboard.php" style="color: var(--primary-green); font-weight: bold;">Admin Dashboard</a></li>
                <li><a href="test_practical10.php">Test Suite</a></li>
                <li><a href="logout.php" class="btn-logout" style="color: white; margin-left: 10px;">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="dashboard-container">
        <aside class="sidebar">
            <ul>
                <li><a href="admin_dashboard.php"><span>???</span> <span>Admin Overview</span></a></li>
                <li><a href="#users-section"><span>??</span> <span>User Management</span></a></li>
                <li><a href="dashboard.php"><span>??</span> <span>View Student Page</span></a></li>
                <li><a href="test_practical10.php"><span>??</span> <span>Practical 10 Tests</span></a></li>
                <li><a href="logout.php" style="color: #d32f2f;"><span>??</span> <span>Logout</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="admin-header-card">
                <div>
                    <h2 style="color: #c62828; margin: 0 0 5px 0;">Administrator Control Center</h2>
                    <p style="margin: 0; color: #555; font-size: 0.95rem;">
                        Logged in as: <strong><?php echo $adminName; ?></strong> (<?php echo $adminEmail; ?>) | Role: <span class="admin-badge">ADMIN</span>
                    </p>
                    <p style="margin: 5px 0 0 0; color: #777; font-size: 0.85rem;">
                        <strong>Last Login:</strong> <?php echo htmlspecialchars($lastLoginFormatted); ?> (Recorded in database)
                    </p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="admin_dashboard.php?simulate_timeout=1" class="btn-timeout-demo" title="Sets session last_activity back 15 minutes to demo timeout redirection">Simulate Timeout</a>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <!-- RBAC Statistics Grid -->
            <div class="stat-grid">
                <div class="stat-card">
                    <h4>Total Students</h4>
                    <div class="stat-number"><?php echo $totalStudents; ?></div>
                    <p style="color: #666; font-size: 0.85rem;">Role = 'Student'</p>
                </div>
                <div class="stat-card">
                    <h4>System Admins</h4>
                    <div class="stat-number" style="color: #c62828;"><?php echo $totalAdmins; ?></div>
                    <p style="color: #666; font-size: 0.85rem;">Role = 'Admin' (RBAC)</p>
                </div>
                <div class="stat-card">
                    <h4>Active Events</h4>
                    <div class="stat-number"><?php echo $totalEvents; ?></div>
                    <p style="color: #666; font-size: 0.85rem;">Campus Events</p>
                </div>
                <div class="stat-card">
                    <h4>Event Registrations</h4>
                    <div class="stat-number"><?php echo $totalRegistrations; ?></div>
                    <p style="color: #666; font-size: 0.85rem;">Student Enrollments</p>
                </div>
            </div>

            <!-- RBAC User Management Table -->
            <div id="users-section" class="card" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="color: var(--primary-green); margin: 0;">User Accounts &amp; RBAC Roles</h3>
                    <span style="font-size: 0.85rem; color: #666;">Loaded directly from MySQL <code>students</code> table</span>
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: var(--soft-green); color: var(--dark-green); text-align: left;">
                            <th style="padding: 10px; border: 1px solid #ddd;">ID</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">User / STU #</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Full Name</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Email</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Assigned Role</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Last Login Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($usersList)): ?>
                            <?php foreach ($usersList as $u): ?>
                                <tr>
                                    <td style="padding: 10px; border: 1px solid #ddd;">#<?php echo htmlspecialchars($u['student_id']); ?></td>
                                    <td style="padding: 10px; border: 1px solid #ddd;"><strong><?php echo htmlspecialchars($u['student_number']); ?></strong></td>
                                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">
                                        <?php if ($u['role'] === 'Admin'): ?>
                                            <span class="admin-badge">Admin</span>
                                        <?php else: ?>
                                            <span class="student-badge">Student</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($u['status']); ?></td>
                                    <td style="padding: 10px; border: 1px solid #ddd; font-size: 0.85rem; color: #555;">
                                        <?php echo !empty($u['last_login']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($u['last_login']))) : '<span style="color:#aaa;">Never logged in</span>'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="padding: 15px; text-align: center; color: #888;">No users found in database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>
