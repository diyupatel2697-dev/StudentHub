<?php
/**
 * StudentHub Portal - Main Entry Point
 * Integrates all modules into a single deployable portal
 */

session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $isLoggedIn ? ($_SESSION['first_name'] ?? 'User') : 'Guest';

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');

// Get portal statistics
function getPortalStats() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            return null;
        }
        
        $stats = [];
        
        // Student count
        $result = $conn->query("SELECT COUNT(*) as count FROM students");
        $stats['students'] = $result->fetch_assoc()['count'];
        
        // Event count
        $result = $conn->query("SELECT COUNT(*) as count FROM events");
        $stats['events'] = $result->fetch_assoc()['count'];
        
        // Registration count
        $result = $conn->query("SELECT COUNT(*) as count FROM registrations");
        $stats['registrations'] = $result->fetch_assoc()['count'];
        
        // Upcoming events
        $result = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'Upcoming'");
        $stats['upcoming_events'] = $result->fetch_assoc()['count'];
        
        $conn->close();
        return $stats;
    } catch (Exception $e) {
        return null;
    }
}

$portalStats = getPortalStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .portal-header {
            background: linear-gradient(135deg, #2d5a27 0%, #1a3a17 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .portal-header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        
        .portal-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        
        .portal-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #2d5a27;
            font-size: 3em;
            margin: 0;
        }
        
        .stat-card p {
            color: #666;
            margin: 10px 0 0;
            font-size: 1.1em;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .module-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .module-card h3 {
            color: #2d5a27;
            margin-top: 0;
        }
        
        .module-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .module-card .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #2d5a27;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .module-card .btn:hover {
            background: #1a3a17;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: center;
        }
        
        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 30px 0;
        }
        
        .quick-links a {
            padding: 12px 25px;
            background: #2d5a27;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: background 0.3s;
        }
        
        .quick-links a:hover {
            background: #1a3a17;
        }
        
        @media (max-width: 768px) {
            .portal-header h1 {
                font-size: 2em;
            }
            
            .stats-grid,
            .modules-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-links {
                flex-direction: column;
            }
            
            .quick-links a {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="portal-header">
        <h1>🎓 StudentHub Portal</h1>
        <p>Your Complete Academic Management System</p>
        <p>Welcome, <?php echo htmlspecialchars($userName); ?>! <?php if ($isLoggedIn): ?><a href="logout.php" style="color: white; margin-left: 20px;">Logout</a><?php else: ?><a href="login_secure.html" style="color: white; margin-left: 20px;">Login</a><?php endif; ?></p>
    </header>
    
    <div class="portal-container">
        <?php if ($portalStats): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $portalStats['students']; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $portalStats['events']; ?></h3>
                <p>Total Events</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $portalStats['registrations']; ?></h3>
                <p>Registrations</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $portalStats['upcoming_events']; ?></h3>
                <p>Upcoming Events</p>
            </div>
        </div>
        <?php else: ?>
        <div class="welcome-banner">
            <h3>⚠️ Database Connection Required</h3>
            <p>Please ensure the database is configured and running to view statistics.</p>
        </div>
        <?php endif; ?>
        
        <div class="welcome-banner">
            <h2>🚀 Welcome to StudentHub</h2>
            <p>A comprehensive student management system with registration, events, attendance, and more.</p>
        </div>
        
        <h2>Quick Links</h2>
        <div class="quick-links">
            <a href="index.html">Home</a>
            <a href="register.html">Register</a>
            <a href="login_secure.html">Login</a>
            <a href="dashboard.html">Dashboard</a>
            <a href="about.html">About</a>
            <a href="contact.html">Contact</a>
            <a href="faqs.html">FAQs</a>
        </div>
        
        <h2>Available Modules</h2>
        <div class="modules-grid">
            <div class="module-card">
                <h3>📝 Registration System</h3>
                <p>Secure user registration with email validation, duplicate checks, and password hashing using MySQLi.</p>
                <a href="register.html" class="btn">Register Now</a>
            </div>
            
            <div class="module-card">
                <h3>🔐 Authentication</h3>
                <p>Secure login system with session management, password verification, and account status checks.</p>
                <a href="login_secure.html" class="btn">Login</a>
            </div>
            
            <div class="module-card">
                <h3>📊 Dashboard</h3>
                <p>Interactive dashboard with academic overview, today's classes, and recent announcements.</p>
                <a href="dashboard.html" class="btn">View Dashboard</a>
            </div>
            
            <div class="module-card">
                <h3>📅 Events & Registrations</h3>
                <p>Event management system with registration tracking, payment status, and participant management.</p>
                <a href="index.php" class="btn">Manage Events</a>
            </div>
            
            <div class="module-card">
                <h3>📈 AJAX CRUD Operations</h3>
                <p>AJAX-powered student management with real-time updates, search, and seamless user experience.</p>
                <a href="index.php" class="btn">AJAX CRUD</a>
            </div>
            
            <div class="module-card">
                <h3>📚 Academic Records</h3>
                <p>Complete academic tracking including attendance, subjects, results, and study materials.</p>
                <a href="dashboard.html" class="btn">Academic Portal</a>
            </div>
        </div>
        
        <h2>System Features</h2>
        <div class="modules-grid">
            <div class="module-card">
                <h3>🔒 Security</h3>
                <p>Password hashing, SQL injection prevention, input sanitization, and session security.</p>
            </div>
            
            <div class="module-card">
                <h3>📱 Responsive Design</h3>
                <p>Mobile-friendly interface with hamburger menu and adaptive layouts.</p>
            </div>
            
            <div class="module-card">
                <h3>🎨 Theme Support</h3>
                <p>Light/dark mode toggle with smooth transitions and persistent preferences.</p>
            </div>
            
            <div class="module-card">
                <h3>⚡ Real-time Updates</h3>
                <p>AJAX-powered features for instant feedback and seamless user experience.</p>
            </div>
            
            <div class="module-card">
                <h3>✅ Input Validation</h3>
                <p>Frontend and backend validation with real-time feedback and error handling.</p>
            </div>
            
            <div class="module-card">
                <h3>📊 Database Integration</h3>
                <p>MySQL database with prepared statements, transactions, and data integrity.</p>
            </div>
        </div>
        
        <div class="welcome-banner" style="background: linear-gradient(135deg, #2d5a27 0%, #1a3a17 100%);">
            <h2>🧪 Testing & Integration</h2>
            <p>Run comprehensive tests to verify all functionality:</p>
            <div class="quick-links" style="justify-content: center;">
                <a href="test_connection.php">Database Test</a>
                <a href="test_registration.php">Registration Test</a>
                <a href="test_practical10.php">AJAX Test</a>
            </div>
        </div>
        
        <footer style="text-align: center; padding: 40px 0; color: #666; border-top: 1px solid #ddd; margin-top: 60px;">
            <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
            <p>Integrated Student Management System - Practical 15</p>
        </footer>
    </div>
</body>
</html>
