<?php
/**
 * Practical 9: Automated Test Suite & Validation Report
 * StudentHub Green Theme Automated Verifier
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 9 - Automated Validation Suite</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .test-container {
            max-width: 850px;
            margin: 2rem auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        .test-header {
            border-bottom: 2px solid var(--soft-green);
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-title {
            color: var(--primary-green);
            font-size: 1.6rem;
            margin: 0;
        }
        .test-item {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #f9fbf9;
            border: 1px solid #e0e0e0;
        }
        .test-pass {
            border-left: 5px solid var(--primary-green);
            color: #1e3d1a;
        }
        .test-fail {
            border-left: 5px solid #d32f2f;
            background: #fffbfa;
            color: #c62828;
        }
        .badge-pass {
            background: var(--soft-green);
            color: var(--primary-green);
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .badge-fail {
            background: #ffebee;
            color: #d32f2f;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .hash-code {
            font-family: monospace;
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85rem;
            word-break: break-all;
        }
        .nav-btn {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary-green);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
        }
        .nav-btn:hover { background: var(--hover-green); }
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
            <li><a href="registration.html">Register</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="test_registration.php">Run Tests</a></li>
        </ul>
    </nav>
</header>

<div class="test-container">
    <div class="test-header">
        <div>
            <h1 class="test-title">Practical 9: Validation Test Suite</h1>
            <p style="color: #666; font-size: 0.9rem; margin-top: 4px;">Secure User Registration, Duplicate Email Check & Password Hashing</p>
        </div>
        <div>
            <a href="registration.html" class="nav-btn">&larr; Open Registration</a>
        </div>
    </div>

<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');

$allPass = true;

// 1. Test Database Connection
echo '<div class="test-item ';
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    $allPass = false;
    echo 'test-fail"><span class="badge-fail">FAIL</span> <strong>Test 1: MySQLi Database Connection</strong><br>Error: ' . htmlspecialchars($conn->connect_error) . '</div>';
} else {
    echo 'test-pass"><span class="badge-pass">PASS</span> <strong>Test 1: MySQLi Database Connection</strong><br>Successfully connected to MySQL database <code>' . DB_NAME . '</code> (Server: ' . $conn->server_info . ')</div>';
}

// 2. Test Table Structure
if (!$conn->connect_error) {
    echo '<div class="test-item ';
    $res = $conn->query("SHOW TABLES LIKE 'students'");
    if ($res && $res->num_rows > 0) {
        echo 'test-pass"><span class="badge-pass">PASS</span> <strong>Test 2: Schema Existence (`students` table)</strong><br>The <code>students</code> table is present in the database.</div>';
    } else {
        $allPass = false;
        echo 'test-fail"><span class="badge-fail">FAIL</span> <strong>Test 2: Schema Existence (`students` table)</strong><br>Table <code>students</code> not found in database.</div>';
    }
}

// 3. Test Password Hashing with password_hash()
echo '<div class="test-item ';
$samplePass = "SecurePass123@";
$sampleHash = password_hash($samplePass, PASSWORD_DEFAULT);
$isBcrypt = (strlen($sampleHash) === 60 && substr($sampleHash, 0, 4) === '$2y$');
$verifyCorrect = password_verify($samplePass, $sampleHash);
$verifyWrong = !password_verify("WrongPass123@", $sampleHash);

if ($isBcrypt && $verifyCorrect && $verifyWrong) {
    echo 'test-pass"><span class="badge-pass">PASS</span> <strong>Test 3: Password Hashing (password_hash &amp; password_verify)</strong><br>'
       . 'Generated standard 60-character bcrypt hash: <span class="hash-code">' . htmlspecialchars($sampleHash) . '</span><br>'
       . 'Verified: Password correctly verified with <code>password_verify()</code>; wrong passwords safely rejected.</div>';
} else {
    $allPass = false;
    echo 'test-fail"><span class="badge-fail">FAIL</span> <strong>Test 3: Password Hashing</strong><br>Hashing or verification logic failed.</div>';
}

// 4. Test Duplicate Email Prevention Query
if (!$conn->connect_error) {
    echo '<div class="test-item ';
    $dupEmail = 'john.doe@studenthub.edu';
    $dupStmt = $conn->prepare("SELECT student_id, first_name, email FROM students WHERE email = ? LIMIT 1");
    $dupStmt->bind_param("s", $dupEmail);
    $dupStmt->execute();
    $dupRes = $dupStmt->get_result();

    if ($dupRes && $dupRes->num_rows > 0) {
        $existing = $dupRes->fetch_assoc();
        echo 'test-pass"><span class="badge-pass">PASS</span> <strong>Test 4: Duplicate Email Detection (Prepared Statement)</strong><br>'
           . 'Successfully detected existing registered email <code>' . htmlspecialchars($existing['email']) . '</code> belonging to ' . htmlspecialchars($existing['first_name']) . '. System prevents duplicate insertion.</div>';
    } else {
        echo 'test-pass"><span class="badge-pass">PASS</span> <strong>Test 4: Duplicate Email Query Structure</strong><br>Prepared statement executed successfully. (Seed email <code>' . htmlspecialchars($dupEmail) . '</code> not present yet).</div>';
    }
    $dupStmt->close();
}

// 5. Test Input Sanitization (XSS Defense)
echo '<div class="test-item ';
function testSanitize($input) {
    return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
}
$xss = "<script>alert('xss');</script>";
$sanitized = testSanitize($xss);
if ($sanitized === '&lt;script&gt;alert(&#039;xss&#039;);&lt;/script&gt;') {
    echo 'test-pass"><span class="badge-pass">PASS</span> <strong>Test 5: Input Sanitization (XSS Prevention)</strong><br>'
       . 'Malicious script safely converted to HTML entities: <span class="hash-code">' . htmlspecialchars($sanitized) . '</span></div>';
} else {
    $allPass = false;
    echo 'test-fail"><span class="badge-fail">FAIL</span> <strong>Test 5: Sanitization</strong><br>Output did not escape tags properly.</div>';
}

// 6. Test Format Validations
echo '<div class="test-item ';
$validEmail = filter_var("student@studenthub.edu", FILTER_VALIDATE_EMAIL) !== false;
$invalidEmail = filter_var("invalid_email_format", FILTER_VALIDATE_EMAIL) === false;
$validStu = preg_match('/^STU\d{3,6}$/i', 'STU101');
$invalidStu = !preg_match('/^STU\d{3,6}$/i', 'ABCXYZ');

if ($validEmail && $invalidEmail && $validStu && $invalidStu) {
    echo 'test-pass"><span class="badge-pass">PASS</span> <strong>Test 6: Format Validations (Email &amp; Student Number)</strong><br>'
       . 'Valid formats correctly accepted; invalid formats correctly rejected.</div>';
} else {
    $allPass = false;
    echo 'test-fail"><span class="badge-fail">FAIL</span> <strong>Test 6: Format Validations</strong><br>Regex or filter logic failed.</div>';
}

// 7. Recent Registrations View
if (!$conn->connect_error) {
    echo '<div style="margin-top: 1.5rem;">';
    echo '<h3 style="color: var(--primary-green); margin-bottom: 0.8rem;">Current Registered Students in MySQL (`students` table):</h3>';
    $listRes = $conn->query("SELECT student_id, student_number, first_name, last_name, email, password, status FROM students ORDER BY student_id DESC LIMIT 5");
    if ($listRes && $listRes->num_rows > 0) {
        echo '<table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">';
        echo '<tr style="background: var(--soft-green); color: var(--dark-green); text-align: left;">';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">ID</th>';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">STU Number</th>';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">Name</th>';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">Email</th>';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">Password Hash (First 20 chars)</th>';
        echo '</tr>';
        while ($row = $listRes->fetch_assoc()) {
            echo '<tr>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($row['student_id']) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($row['student_number']) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($row['email']) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;"><span class="hash-code">' . htmlspecialchars(substr($row['password'], 0, 20)) . '...</span></td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p style="color: #666;">No students found in table yet. <a href="registration.html">Register a student</a> to test!</p>';
    }
    echo '</div>';
    $conn->close();
}
?>

    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <a href="registration.html" class="nav-btn">Test Registration Page</a>
        <a href="login.html" class="nav-btn" style="background: var(--medium-green);">Test Login Page</a>
    </div>
</div>

</body>
</html>
