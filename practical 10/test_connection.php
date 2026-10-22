
<?php
echo "<h1>Practical 8 - Database Connection Test</h1>";
echo "<hr>";

try {
    require_once 'config.php';
    $pdo = getDBConnection();
    echo "<h2>Test 1: Database Connection</h2>";
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    echo "<p>Connected to database: " . DB_NAME . "</p>";
    closeConnection($pdo);
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}


echo "<h2>Test 2: Check Database Tables</h2>";

try {
    $pdo = getDBConnection();

    $tables = ['students', 'events', 'registrations'];

    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (in_array($table, $existingTables, true)) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
        }
    }

    closeConnection($pdo);
} catch (Throwable $e) {
    echo "<p style='color: red;'>✗ Error checking tables: " .
        htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";


echo "<h2>Test 3: Check Sample Data</h2>";

try {
    $pdo = getDBConnection();

    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $studentCount = (int)$stmt->fetchColumn();
    echo "<p>Students in database: <strong>$studentCount</strong></p>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $eventCount = (int)$stmt->fetchColumn();
    echo "<p>Events in database: <strong>$eventCount</strong></p>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM registrations");
    $registrationCount = (int)$stmt->fetchColumn();
    echo "<p>Registrations in database: <strong>$registrationCount</strong></p>";

    if ($studentCount > 0 && $eventCount > 0 && $registrationCount > 0) {
        echo "<p style='color: green;'>✓ Sample data exists in all tables</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Some tables have no sample data</p>";
    }

    closeConnection($pdo);
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking sample data: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

echo "<h2>Test 4: Test Student CRUD Operations</h2>";

try {
    require_once 'student_operations.php';

    $student = getStudentById(1);

    if ($student) {
        echo "<p style='color: green;'>✓ Read student successful: " .
            htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to read student</p>";
    }

    $students = getAllStudents();

    if ($students !== false && $students !== null) {
        echo "<p style='color: green;'>✓ Get all students successful: " . count($students) . " students found</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get all students</p>";
    }

    $searchResults = searchStudents('John');

    if ($searchResults !== false && $searchResults !== null && count($searchResults) > 0) {
        echo "<p style='color: green;'>✓ Search students successful: " . count($searchResults) . " results found</p>";
    } elseif (is_array($searchResults) && count($searchResults) === 0) {
        echo "<p style='color: orange;'>⚠ Search ran but returned no results for John</p>";
    } else {
        echo "<p style='color: red;'>✗ Student search failed</p>";
    }

    $stats = getStudentStatistics();

    if ($stats) {
        echo "<p style='color: green;'>✓ Get student statistics successful</p>";
        echo "<ul>";
        echo "<li>Total students: " . htmlspecialchars((string)$stats['total_students']) . "</li>";
        echo "<li>Active students: " . htmlspecialchars((string)$stats['active_students']) . "</li>";
        echo "<li>Average CGPA: " . htmlspecialchars((string)$stats['average_cgpa']) . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get student statistics</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color: red;'>✗ Student operations error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

echo "<h2>Test 5: Test Event CRUD Operations</h2>";

try {
    require_once 'event_operations.php';

    $event = getEventById(1);

    if ($event) {
        echo "<p style='color: green;'>✓ Read event successful: " . htmlspecialchars($event['event_name']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to read event</p>";
    }

    $events = getAllEvents();

    if ($events !== false && $events !== null) {
        echo "<p style='color: green;'>✓ Get all events successful: " . count($events) . " events found</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get all events</p>";
    }

    $upcomingEvents = getUpcomingEvents();

    if ($upcomingEvents !== false && $upcomingEvents !== null) {
        echo "<p style='color: green;'>✓ Get upcoming events successful: " . count($upcomingEvents) . " upcoming events</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get upcoming events</p>";
    }

    $availableEvents = getAvailableEvents();

    if ($availableEvents !== false && $availableEvents !== null) {
        echo "<p style='color: green;'>✓ Get available events successful: " . count($availableEvents) . " available events</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get available events</p>";
    }

    $stats = getEventStatistics();

    if ($stats) {
        echo "<p style='color: green;'>✓ Get event statistics successful</p>";
        echo "<ul>";
        echo "<li>Total events: " . htmlspecialchars((string)$stats['total_events']) . "</li>";
        echo "<li>Upcoming events: " . htmlspecialchars((string)$stats['upcoming_events']) . "</li>";
        echo "<li>Total registrations: " . htmlspecialchars((string)$stats['total_registrations']) . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get event statistics</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color: red;'>✗ Event operations error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

echo "<h2>Test 6: Test Registration CRUD Operations</h2>";

try {
    require_once 'registration_operations.php';

    $registration = getRegistrationById(1);

    if ($registration) {
        echo "<p style='color: green;'>✓ Read registration successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to read registration</p>";
    }

    $studentRegs = getStudentRegistrations(1);

    if ($studentRegs !== false && $studentRegs !== null) {
        echo "<p style='color: green;'>✓ Get student registrations successful: " . count($studentRegs) . " registrations found</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get student registrations</p>";
    }

    $eventRegs = getEventRegistrations(1);

    if ($eventRegs !== false && $eventRegs !== null) {
        echo "<p style='color: green;'>✓ Get event registrations successful: " . count($eventRegs) . " registrations found</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get event registrations</p>";
    }

    $isRegistered = isStudentRegistered(1, 1);
    echo "<p style='color: green;'>✓ Check registration status successful: " .
        ($isRegistered ? "Registered" : "Not registered") . "</p>";

    $stats = getRegistrationStatistics();

    if ($stats) {
        echo "<p style='color: green;'>✓ Get registration statistics successful</p>";
        echo "<ul>";
        echo "<li>Total registrations: " . htmlspecialchars((string)$stats['total_registrations']) . "</li>";
        echo "<li>Confirmed registrations: " . htmlspecialchars((string)$stats['confirmed']) . "</li>";
        echo "<li>Paid registrations: " . htmlspecialchars((string)$stats['paid_registrations']) . "</li>";
        echo "<li>Total revenue: $" . number_format((float)$stats['total_revenue'], 2) . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Failed to get registration statistics</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color: red;'>✗ Registration operations error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

echo "<h2>Test 7: Test Prepared Statement Security</h2>";

try {
    $pdo = getDBConnection();

    $maliciousInput = "John' OR '1'='1";
    $sql = "SELECT * FROM students WHERE first_name = :first_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':first_name', $maliciousInput, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll();

    if (count($results) === 0) {
        echo "<p style='color: green;'>✓ SQL injection prevented - no results returned for malicious input</p>";
    } else {
        echo "<p style='color: red;'>✗ Unexpected results returned for malicious input</p>";
    }

    closeConnection($pdo);
} catch (Throwable $e) {
    echo "<p style='color: red;'>✗ Security test error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

echo "<h2>Test Summary</h2>";
echo "<p>Review the results above. Green checkmarks indicate successful tests; red crosses indicate problems that need attention.</p>";
echo "<p>Database: " . htmlspecialchars(DB_NAME) . "</p>";
?>
