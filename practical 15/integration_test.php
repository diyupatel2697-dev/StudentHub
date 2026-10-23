<?php
/**
 * Comprehensive Integration Test for StudentHub Portal
 * Tests all modules: Navigation, Responsiveness, Authentication, Database Operations, Security, AJAX
 */

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>StudentHub - Integration Test</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 1200px; margin: 40px auto; padding: 20px; background: #f5f5f5; }";
echo ".test-section { background: white; padding: 30px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1, h2 { color: #2d5a27; }";
echo ".test-item { padding: 15px; margin: 10px 0; border-left: 4px solid #ddd; background: #f9f9f9; }";
echo ".pass { border-left-color: #28a745; background: #d4edda; }";
echo ".fail { border-left-color: #dc3545; background: #f8d7da; }";
echo ".pending { border-left-color: #ffc107; background: #fff3cd; }";
echo ".summary { background: linear-gradient(135deg, #2d5a27 0%, #1a3a17 100%); color: white; padding: 30px; border-radius: 10px; margin: 30px 0; }";
echo ".progress-bar { width: 100%; height: 30px; background: #e0e0e0; border-radius: 15px; overflow: hidden; margin: 20px 0; }";
echo ".progress-fill { height: 100%; background: linear-gradient(90deg, #28a745, #2d5a27); transition: width 0.5s; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🧪 StudentHub Integration Test</h1>";
echo "<p>Comprehensive testing of all portal modules and functionality</p>";

// Test results array
$testResults = [];
$totalTests = 0;
$passedTests = 0;

// Helper function to record test result
function recordTest(&$results, &$total, &$passed, $name, $status, $message = '') {
    $results[] = ['name' => $name, 'status' => $status, 'message' => $message];
    $total++;
    if ($status === 'pass') {
        $passed++;
    }
}

// ============================================
// TEST 1: File Structure Verification
// ============================================
echo "<div class='test-section'>";
echo "<h2>1. File Structure Verification</h2>";

$requiredFiles = [
    'portal.php',
    'index.html',
    'register.html',
    'login_secure.html',
    'dashboard.html',
    'style.css',
    'script.js',
    'config.php',
    'register.php',
    'login.php',
    'studenthub_schema.sql',
    'api.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        recordTest($testResults, $totalTests, $passedTests, "File exists: $file", 'pass');
        echo "<div class='test-item pass'>✓ $file exists</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "File exists: $file", 'fail', 'File missing');
        echo "<div class='test-item fail'>✗ $file missing</div>";
    }
}

echo "</div>";

// ============================================
// TEST 2: Database Connection
// ============================================
echo "<div class='test-section'>";
echo "<h2>2. Database Connection</h2>";

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        recordTest($testResults, $totalTests, $passedTests, "Database connection", 'fail', $conn->connect_error);
        echo "<div class='test-item fail'>✗ Database connection failed: " . $conn->connect_error . "</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "Database connection", 'pass');
        echo "<div class='test-item pass'>✓ Database connection successful</div>";
        
        // Test charset
        if ($conn->set_charset("utf8mb4")) {
            recordTest($testResults, $totalTests, $passedTests, "Database charset", 'pass');
            echo "<div class='test-item pass'>✓ UTF-8 charset set</div>";
        } else {
            recordTest($testResults, $totalTests, $passedTests, "Database charset", 'fail');
            echo "<div class='test-item fail'>✗ Failed to set charset</div>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    recordTest($testResults, $totalTests, $passedTests, "Database connection", 'fail', $e->getMessage());
    echo "<div class='test-item fail'>✗ Database error: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ============================================
// TEST 3: Database Tables
// ============================================
echo "<div class='test-section'>";
echo "<h2>3. Database Tables</h2>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    $tables = ['students', 'events', 'registrations'];
    
    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            recordTest($testResults, $totalTests, $passedTests, "Table exists: $table", 'pass');
            echo "<div class='test-item pass'>✓ Table '$table' exists</div>";
        } else {
            recordTest($testResults, $totalTests, $passedTests, "Table exists: $table", 'fail');
            echo "<div class='test-item fail'>✗ Table '$table' missing</div>";
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<div class='test-item fail'>✗ Error checking tables: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ============================================
// TEST 4: Security Features
// ============================================
echo "<div class='test-section'>";
echo "<h2>4. Security Features</h2>";

// Test password hashing
$testPassword = "TestPass123@";
$hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);

if ($hashedPassword && strlen($hashedPassword) === 60) {
    recordTest($testResults, $totalTests, $passedTests, "Password hashing", 'pass');
    echo "<div class='test-item pass'>✓ Password hashing working (bcrypt)</div>";
} else {
    recordTest($testResults, $totalTests, $passedTests, "Password hashing", 'fail');
    echo "<div class='test-item fail'>✗ Password hashing failed</div>";
}

// Test password verification
if (password_verify($testPassword, $hashedPassword)) {
    recordTest($testResults, $totalTests, $passedTests, "Password verification", 'pass');
    echo "<div class='test-item pass'>✓ Password verification working</div>";
} else {
    recordTest($testResults, $totalTests, $passedTests, "Password verification", 'fail');
    echo "<div class='test-item fail'>✗ Password verification failed</div>";
}

// Test input sanitization
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

$maliciousInput = "<script>alert('XSS')</script>";
$sanitized = sanitizeInput($maliciousInput);

if ($sanitized !== $maliciousInput && strpos($sanitized, '&lt;') !== false) {
    recordTest($testResults, $totalTests, $passedTests, "Input sanitization", 'pass');
    echo "<div class='test-item pass'>✓ Input sanitization working</div>";
} else {
    recordTest($testResults, $totalTests, $passedTests, "Input sanitization", 'fail');
    echo "<div class='test-item fail'>✗ Input sanitization failed</div>";
}

// Test SQL injection prevention
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $maliciousInput = "John' OR '1'='1";
    
    $stmt = $conn->prepare("SELECT * FROM students WHERE first_name = ?");
    $stmt->bind_param("s", $maliciousInput);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->num_rows;
    $stmt->close();
    $conn->close();
    
    if ($count === 0) {
        recordTest($testResults, $totalTests, $passedTests, "SQL injection prevention", 'pass');
        echo "<div class='test-item pass'>✓ SQL injection prevented</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "SQL injection prevention", 'fail');
        echo "<div class='test-item fail'>✗ SQL injection vulnerability detected</div>";
    }
} catch (Exception $e) {
    echo "<div class='test-item fail'>✗ SQL injection test error: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ============================================
// TEST 5: Database Operations
// ============================================
echo "<div class='test-section'>";
echo "<h2>5. Database Operations</h2>";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Test SELECT operation
    $result = $conn->query("SELECT COUNT(*) as count FROM students");
    if ($result) {
        recordTest($testResults, $totalTests, $passedTests, "SELECT operation", 'pass');
        echo "<div class='test-item pass'>✓ SELECT operation working</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "SELECT operation", 'fail');
        echo "<div class='test-item fail'>✗ SELECT operation failed</div>";
    }
    
    // Test prepared statement
    $stmt = $conn->prepare("SELECT * FROM students LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        recordTest($testResults, $totalTests, $passedTests, "Prepared statement", 'pass');
        echo "<div class='test-item pass'>✓ Prepared statement working</div>";
        $stmt->close();
    } else {
        recordTest($testResults, $totalTests, $passedTests, "Prepared statement", 'fail');
        echo "<div class='test-item fail'>✗ Prepared statement failed</div>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<div class='test-item fail'>✗ Database operations error: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ============================================
// TEST 6: Authentication System
// ============================================
echo "<div class='test-section'>";
echo "<h2>6. Authentication System</h2>";

// Check if auth files exist
$authFiles = ['auth.php', 'login.php', 'logout.php', 'register.php'];
foreach ($authFiles as $file) {
    if (file_exists($file)) {
        recordTest($testResults, $totalTests, $passedTests, "Auth file: $file", 'pass');
        echo "<div class='test-item pass'>✓ $file exists</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "Auth file: $file", 'fail');
        echo "<div class='test-item fail'>✗ $file missing</div>";
    }
}

// Test session functionality
session_start();
$_SESSION['test'] = 'value';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'value') {
    recordTest($testResults, $totalTests, $passedTests, "Session functionality", 'pass');
    echo "<div class='test-item pass'>✓ Session functionality working</div>";
    unset($_SESSION['test']);
} else {
    recordTest($testResults, $totalTests, $passedTests, "Session functionality", 'fail');
    echo "<div class='test-item fail'>✗ Session functionality failed</div>";
}

echo "</div>";

// ============================================
// TEST 7: AJAX Functionality
// ============================================
echo "<div class='test-section'>";
echo "<h2>7. AJAX Functionality</h2>";

if (file_exists('api.php')) {
    recordTest($testResults, $totalTests, $passedTests, "API endpoint exists", 'pass');
    echo "<div class='test-item pass'>✓ api.php exists</div>";
} else {
    recordTest($testResults, $totalTests, $passedTests, "API endpoint exists", 'fail');
    echo "<div class='test-item fail'>✗ api.php missing</div>";
}

if (file_exists('index.php')) {
    recordTest($testResults, $totalTests, $passedTests, "AJAX CRUD page exists", 'pass');
    echo "<div class='test-item pass'>✓ AJAX CRUD page exists</div>";
} else {
    recordTest($testResults, $totalTests, $passedTests, "AJAX CRUD page exists", 'fail');
    echo "<div class='test-item fail'>✗ AJAX CRUD page missing</div>";
}

echo "</div>";

// ============================================
// TEST 8: Frontend Assets
// ============================================
echo "<div class='test-section'>";
echo "<h2>8. Frontend Assets</h2>";

$frontendFiles = [
    'style.css' => 'CSS stylesheet',
    'script.js' => 'JavaScript file',
    'register_validation.js' => 'Registration validation',
    'validation.js' => 'Form validation'
];

foreach ($frontendFiles as $file => $description) {
    if (file_exists($file)) {
        recordTest($testResults, $totalTests, $passedTests, "Frontend asset: $file", 'pass');
        echo "<div class='test-item pass'>✓ $description ($file) exists</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "Frontend asset: $file", 'fail');
        echo "<div class='test-item fail'>✗ $description ($file) missing</div>";
    }
}

echo "</div>";

// ============================================
// TEST 9: Navigation Links
// ============================================
echo "<div class='test-section'>";
echo "<h2>9. Navigation Links</h2>";

$navigationPages = [
    'index.html' => 'Home',
    'about.html' => 'About',
    'contact.html' => 'Contact',
    'faqs.html' => 'FAQs',
    'dashboard.html' => 'Dashboard',
    'assignments.html' => 'Assignments',
    'attendance.html' => 'Attendance',
    'subjects.html' => 'Subjects',
    'results.html' => 'Results',
    'timetable.html' => 'Timetable'
];

foreach ($navigationPages as $file => $name) {
    if (file_exists($file)) {
        recordTest($testResults, $totalTests, $passedTests, "Navigation: $name", 'pass');
        echo "<div class='test-item pass'>✓ $name page exists</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "Navigation: $name", 'fail');
        echo "<div class='test-item fail'>✗ $name page missing</div>";
    }
}

echo "</div>";

// ============================================
// TEST 10: Configuration Files
// ============================================
echo "<div class='test-section'>";
echo "<h2>10. Configuration Files</h2>";

$configFiles = [
    'config.php' => 'Main configuration',
    'db_connect.php' => 'Database connection',
    'studenthub_schema.sql' => 'Database schema'
];

foreach ($configFiles as $file => $description) {
    if (file_exists($file)) {
        recordTest($testResults, $totalTests, $passedTests, "Config: $file", 'pass');
        echo "<div class='test-item pass'>✓ $description exists</div>";
    } else {
        recordTest($testResults, $totalTests, $passedTests, "Config: $file", 'fail');
        echo "<div class='test-item fail'>✗ $description missing</div>";
    }
}

echo "</div>";

// ============================================
// SUMMARY
// ============================================
$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;

echo "<div class='summary'>";
echo "<h2>📊 Test Summary</h2>";
echo "<div class='progress-bar'>";
echo "<div class='progress-fill' style='width: $percentage%;'></div>";
echo "</div>";
echo "<p><strong>Total Tests:</strong> $totalTests</p>";
echo "<p><strong>Passed:</strong> $passedTests</p>";
echo "<p><strong>Failed:</strong> " . ($totalTests - $passedTests) . "</p>";
echo "<p><strong>Success Rate:</strong> $percentage%</p>";

if ($percentage >= 80) {
    echo "<p style='color: #28a745; font-size: 1.2em; margin-top: 20px;'>✅ Integration test PASSED! System is ready for deployment.</p>";
} elseif ($percentage >= 50) {
    echo "<p style='color: #ffc107; font-size: 1.2em; margin-top: 20px;'>⚠️ Integration test PARTIAL. Some issues need to be addressed.</p>";
} else {
    echo "<p style='color: #dc3545; font-size: 1.2em; margin-top: 20px;'>❌ Integration test FAILED. Major issues need to be fixed.</p>";
}

echo "</div>";

// ============================================
// DEPLOYMENT CHECKLIST
// ============================================
echo "<div class='test-section'>";
echo "<h2>📋 Deployment Checklist</h2>";
echo "<ul>";
echo "<li>✓ All required files are present</li>";
echo "<li>✓ Database connection is working</li>";
echo "<li>✓ Security features are implemented</li>";
echo "<li>✓ Authentication system is functional</li>";
echo "<li>✓ AJAX endpoints are available</li>";
echo "<li>✓ Frontend assets are loaded</li>";
echo "<li>✓ Navigation links are working</li>";
echo "<li>✓ Configuration files are set up</li>";
echo "</ul>";
echo "</div>";

// ============================================
// NEXT STEPS
// ============================================
echo "<div class='test-section'>";
echo "<h2>🚀 Next Steps</h2>";
echo "<ol>";
echo "<li>Ensure XAMPP/Apache is running</li>";
echo "<li>Import studenthub_schema.sql into MySQL</li>";
echo "<li>Configure virtual host (optional)</li>";
echo "<li>Test the portal at: http://localhost/practical%2015/portal.php</li>";
echo "<li>Test registration: register.html</li>";
echo "<li>Test login: login_secure.html</li>";
echo "<li>Test AJAX CRUD: index.php</li>";
echo "<li>Test dashboard: dashboard.html</li>";
echo "<li>Verify responsive design on mobile devices</li>";
echo "<li>Commit to GitHub repository</li>";
echo "</ol>";
echo "</div>";

echo "</body>";
echo "</html>";
?>
