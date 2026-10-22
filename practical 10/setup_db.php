<?php
/**
 * Practical 10: Database Setup & Migration Script
 * Ensures role column, last_login column, remember_tokens table, and seed users exist.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');

function runSetup() {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        return ["success" => false, "message" => "Database connection failed: " . $conn->connect_error];
    }
    $conn->set_charset("utf8mb4");

    // 1. Add 'role' column if not exists
    $checkRole = $conn->query("SHOW COLUMNS FROM students LIKE 'role'");
    if ($checkRole && $checkRole->num_rows === 0) {
        $conn->query("ALTER TABLE students ADD COLUMN role ENUM('Student', 'Admin') NOT NULL DEFAULT 'Student' AFTER status");
    }

    // 2. Add 'last_login' column if not exists
    $checkLogin = $conn->query("SHOW COLUMNS FROM students LIKE 'last_login'");
    if ($checkLogin && $checkLogin->num_rows === 0) {
        $conn->query("ALTER TABLE students ADD COLUMN last_login DATETIME NULL AFTER updated_at");
    }

    // 3. Create 'remember_tokens' table if not exists
    $createTokensTable = "CREATE TABLE IF NOT EXISTS remember_tokens (
        token_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        selector VARCHAR(32) NOT NULL UNIQUE,
        token_hash VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_selector (selector),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->query($createTokensTable);

    // 4. Ensure Admin account exists (admin@studenthub.edu / AdminPass123@)
    $adminEmail = 'admin@studenthub.edu';
    $adminPass = password_hash('AdminPass123@', PASSWORD_DEFAULT);
    $checkAdmin = $conn->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $checkAdmin->bind_param("s", $adminEmail);
    $checkAdmin->execute();
    $adminRes = $checkAdmin->get_result();

    if ($adminRes && $adminRes->num_rows > 0) {
        // Update to Admin role and password
        $upStmt = $conn->prepare("UPDATE students SET role = 'Admin', password = ? WHERE email = ?");
        $upStmt->bind_param("ss", $adminPass, $adminEmail);
        $upStmt->execute();
        $upStmt->close();
    } else {
        // Insert Admin user
        $insAdmin = $conn->prepare("INSERT INTO students (student_number, first_name, last_name, email, phone, enrollment_date, semester, department, status, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $admNum = 'ADM001';
        $admFirst = 'System';
        $admLast = 'Administrator';
        $admPhone = '9999999999';
        $admDate = date('Y-m-d');
        $admSem = 1;
        $admDept = 'Administration';
        $admStatus = 'Active';
        $admRole = 'Admin';
        $insAdmin->bind_param("ssssssissss", $admNum, $admFirst, $admLast, $adminEmail, $admPhone, $admDate, $admSem, $admDept, $admStatus, $admRole, $adminPass);
        $insAdmin->execute();
        $insAdmin->close();
    }
    $checkAdmin->close();

    // 5. Ensure Demo Student account exists (john.doe@studenthub.edu / StudentPass123@)
    $stuEmail = 'john.doe@studenthub.edu';
    $stuPass = password_hash('StudentPass123@', PASSWORD_DEFAULT);
    $checkStu = $conn->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $checkStu->bind_param("s", $stuEmail);
    $checkStu->execute();
    $stuRes = $checkStu->get_result();

    if ($stuRes && $stuRes->num_rows > 0) {
        $upStu = $conn->prepare("UPDATE students SET role = 'Student', password = ? WHERE email = ?");
        $upStu->bind_param("ss", $stuPass, $stuEmail);
        $upStu->execute();
        $upStu->close();
    } else {
        $insStu = $conn->prepare("INSERT INTO students (student_number, first_name, last_name, email, phone, enrollment_date, semester, department, status, role, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $sNum = 'STU001';
        $sFirst = 'John';
        $sLast = 'Doe';
        $sPhone = '9876543210';
        $sDate = date('Y-m-d');
        $sSem = 5;
        $sDept = 'Computer Science';
        $sStatus = 'Active';
        $sRole = 'Student';
        $insStu->bind_param("ssssssissss", $sNum, $sFirst, $sLast, $stuEmail, $sPhone, $sDate, $sSem, $sDept, $sStatus, $sRole, $stuPass);
        $insStu->execute();
        $insStu->close();
    }
    $checkStu->close();

    $conn->close();
    return ["success" => true, "message" => "Database setup and migrations completed successfully."];
}

// Run if called directly
if (php_sapi_name() === 'cli' || !empty($_GET['run'])) {
    $res = runSetup();
    echo $res['message'] . "\n";
}
?>
