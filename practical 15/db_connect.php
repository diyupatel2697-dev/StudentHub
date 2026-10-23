<?php
/**
 * Practical 9: Database Connection using MySQLi
 * Subject: Web Development Frameworks (ITUE203)
 * StudentHub Portal - MySQLi Database Handler
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');
define('DB_PORT', 3306);

/**
 * Get MySQLi Database Connection
 * 
 * @return mysqli Database connection object
 */
function getMySQLiConnection() {
    // Enable mysqli error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log("MySQLi Connection Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Test MySQLi Database Connection
 * 
 * @return array Status and message
 */
function testMySQLiConnection() {
    $conn = getMySQLiConnection();
    if ($conn) {
        $serverInfo = $conn->server_info;
        $conn->close();
        return [
            'success' => true,
            'message' => 'Connected successfully to database "' . DB_NAME . '" (MySQL ' . $serverInfo . ')'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to connect to MySQL database "' . DB_NAME . '". Ensure XAMPP MySQL is running.'
        ];
    }
}
?>