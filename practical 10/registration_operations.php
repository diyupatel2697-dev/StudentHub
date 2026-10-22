<?php
/**
 * StudentHub - Registration CRUD Operations
 * Demonstrates PDO Prepared Statements for secure database operations
 */

require_once 'config.php';

/**
 * Register a student for an event
 * 
 * @param int $studentId Student ID
 * @param int $eventId Event ID
 * @param string $paymentStatus Payment status (Pending, Paid, Free)
 * @param float $paymentAmount Payment amount
 * @param string $notes Additional notes
 * @return int|false New registration ID or false on failure
 */
function registerForEvent($studentId, $eventId, $paymentStatus = 'Pending', $paymentAmount = 0.00, $notes = null) {
    try {
        $pdo = getDBConnection();
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Check if event has capacity
        $eventSql = "SELECT max_participants, current_participants, registration_deadline 
                     FROM events WHERE event_id = :event_id FOR UPDATE";
        $eventStmt = $pdo->prepare($eventSql);
        $eventStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $eventStmt->execute();
        $event = $eventStmt->fetch();
        
        if (!$event) {
            throw new Exception("Event not found");
        }
        
        if ($event['current_participants'] >= $event['max_participants']) {
            throw new Exception("Event is full");
        }
        
        if (strtotime($event['registration_deadline']) < time()) {
            throw new Exception("Registration deadline has passed");
        }
        
        // Check if student already registered
        $checkSql = "SELECT registration_id FROM registrations 
                     WHERE student_id = :student_id AND event_id = :event_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $checkStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            throw new Exception("Student already registered for this event");
        }
        
        // Insert registration
        $sql = "INSERT INTO registrations (
                    student_id, 
                    event_id, 
                    registration_status, 
                    payment_status, 
                    payment_amount, 
                    notes
                ) VALUES (
                    :student_id, 
                    :event_id, 
                    :registration_status, 
                    :payment_status, 
                    :payment_amount, 
                    :notes
                )";
        
        $stmt = $pdo->prepare($sql);
        
        $registrationStatus = 'Registered';
        
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':registration_status', $registrationStatus, PDO::PARAM_STR);
        $stmt->bindParam(':payment_status', $paymentStatus, PDO::PARAM_STR);
        $stmt->bindParam(':payment_amount', $paymentAmount, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        
        $stmt->execute();
        
        $registrationId = $pdo->lastInsertId();
        
        // Update event participant count
        $updateEventSql = "UPDATE events SET current_participants = current_participants + 1 
                           WHERE event_id = :event_id";
        $updateEventStmt = $pdo->prepare($updateEventSql);
        $updateEventStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $updateEventStmt->execute();
        
        // Commit transaction
        $pdo->commit();
        closeConnection($pdo);
        
        return $registrationId;
    } catch (Exception $e) {
        // Rollback on error
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Registration Error: " . $e->getMessage());
        return false;
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Registration PDO Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read a registration by ID
 * 
 * @param int $registrationId Registration ID
 * @return array|false Registration data or false if not found
 */
function getRegistrationById($registrationId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT r.registration_id, r.student_id, r.event_id, r.registration_date, 
                       r.registration_status, r.payment_status, r.payment_amount, r.notes,
                       s.student_number, s.first_name, s.last_name, s.email,
                       e.event_name, e.event_type, e.event_date, e.event_time, e.venue
                FROM registrations r
                JOIN students s ON r.student_id = s.student_id
                JOIN events e ON r.event_id = e.event_id
                WHERE r.registration_id = :registration_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
        $stmt->execute();
        
        $registration = $stmt->fetch();
        closeConnection($pdo);
        
        return $registration ? $registration : false;
    } catch (PDOException $e) {
        error_log("Get Registration Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all registrations for a student
 * 
 * @param int $studentId Student ID
 * @return array Array of registrations
 */
function getStudentRegistrations($studentId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT r.registration_id, r.registration_date, r.registration_status, 
                       r.payment_status, r.payment_amount, r.notes,
                       e.event_id, e.event_name, e.event_type, e.event_date, e.event_time, e.venue
                FROM registrations r
                JOIN events e ON r.event_id = e.event_id
                WHERE r.student_id = :student_id
                ORDER BY e.event_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        
        $registrations = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $registrations;
    } catch (PDOException $e) {
        error_log("Get Student Registrations Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all registrations for an event
 * 
 * @param int $eventId Event ID
 * @return array Array of registrations
 */
function getEventRegistrations($eventId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT r.registration_id, r.registration_date, r.registration_status, 
                       r.payment_status, r.payment_amount, r.notes,
                       s.student_id, s.student_number, s.first_name, s.last_name, s.email, s.phone
                FROM registrations r
                JOIN students s ON r.student_id = s.student_id
                WHERE r.event_id = :event_id
                ORDER BY r.registration_date ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        
        $registrations = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $registrations;
    } catch (PDOException $e) {
        error_log("Get Event Registrations Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all registrations
 * 
 * @param string $status Filter by registration status (optional)
 * @return array Array of registrations
 */
function getAllRegistrations($status = null) {
    try {
        $pdo = getDBConnection();
        
        if ($status) {
            $sql = "SELECT r.registration_id, r.registration_date, r.registration_status, 
                           r.payment_status, r.payment_amount,
                           s.student_number, s.first_name, s.last_name,
                           e.event_name, e.event_type, e.event_date
                    FROM registrations r
                    JOIN students s ON r.student_id = s.student_id
                    JOIN events e ON r.event_id = e.event_id
                    WHERE r.registration_status = :status
                    ORDER BY r.registration_date DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        } else {
            $sql = "SELECT r.registration_id, r.registration_date, r.registration_status, 
                           r.payment_status, r.payment_amount,
                           s.student_number, s.first_name, s.last_name,
                           e.event_name, e.event_type, e.event_date
                    FROM registrations r
                    JOIN students s ON r.student_id = s.student_id
                    JOIN events e ON r.event_id = e.event_id
                    ORDER BY r.registration_date DESC";
            $stmt = $pdo->query($sql);
        }
        
        $stmt->execute();
        $registrations = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $registrations;
    } catch (PDOException $e) {
        error_log("Get All Registrations Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update registration status
 * 
 * @param int $registrationId Registration ID
 * @param string $newStatus New registration status
 * @return bool True on success, false on failure
 */
function updateRegistrationStatus($registrationId, $newStatus) {
    try {
        $pdo = getDBConnection();
        
        $sql = "UPDATE registrations SET 
                    registration_status = :registration_status
                WHERE registration_id = :registration_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
        $stmt->bindParam(':registration_status', $newStatus, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Update Registration Status Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update payment status
 * 
 * @param int $registrationId Registration ID
 * @param string $paymentStatus New payment status
 * @param float $paymentAmount Payment amount
 * @return bool True on success, false on failure
 */
function updatePaymentStatus($registrationId, $paymentStatus, $paymentAmount = null) {
    try {
        $pdo = getDBConnection();
        
        if ($paymentAmount !== null) {
            $sql = "UPDATE registrations SET 
                        payment_status = :payment_status,
                        payment_amount = :payment_amount
                    WHERE registration_id = :registration_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
            $stmt->bindParam(':payment_status', $paymentStatus, PDO::PARAM_STR);
            $stmt->bindParam(':payment_amount', $paymentAmount, PDO::PARAM_STR);
        } else {
            $sql = "UPDATE registrations SET 
                        payment_status = :payment_status
                    WHERE registration_id = :registration_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
            $stmt->bindParam(':payment_status', $paymentStatus, PDO::PARAM_STR);
        }
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Update Payment Status Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Cancel a registration
 * 
 * @param int $registrationId Registration ID
 * @return bool True on success, false on failure
 */
function cancelRegistration($registrationId) {
    try {
        $pdo = getDBConnection();
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Get registration details
        $getSql = "SELECT event_id FROM registrations WHERE registration_id = :registration_id";
        $getStmt = $pdo->prepare($getSql);
        $getStmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
        $getStmt->execute();
        $registration = $getStmt->fetch();
        
        if (!$registration) {
            throw new Exception("Registration not found");
        }
        
        // Update registration status
        $updateSql = "UPDATE registrations SET 
                         registration_status = 'Cancelled'
                      WHERE registration_id = :registration_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
        $updateStmt->execute();
        
        // Decrease event participant count
        $updateEventSql = "UPDATE events SET current_participants = current_participants - 1 
                           WHERE event_id = :event_id";
        $updateEventStmt = $pdo->prepare($updateEventSql);
        $updateEventStmt->bindParam(':event_id', $registration['event_id'], PDO::PARAM_INT);
        $updateEventStmt->execute();
        
        // Commit transaction
        $pdo->commit();
        closeConnection($pdo);
        
        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Cancel Registration Error: " . $e->getMessage());
        return false;
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Cancel Registration PDO Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a registration
 * 
 * @param int $registrationId Registration ID
 * @return bool True on success, false on failure
 */
function deleteRegistration($registrationId) {
    try {
        $pdo = getDBConnection();
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Get registration details
        $getSql = "SELECT event_id FROM registrations WHERE registration_id = :registration_id";
        $getStmt = $pdo->prepare($getSql);
        $getStmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
        $getStmt->execute();
        $registration = $getStmt->fetch();
        
        if (!$registration) {
            throw new Exception("Registration not found");
        }
        
        // Delete registration
        $sql = "DELETE FROM registrations WHERE registration_id = :registration_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Decrease event participant count
        $updateEventSql = "UPDATE events SET current_participants = current_participants - 1 
                           WHERE event_id = :event_id";
        $updateEventStmt = $pdo->prepare($updateEventSql);
        $updateEventStmt->bindParam(':event_id', $registration['event_id'], PDO::PARAM_INT);
        $updateEventStmt->execute();
        
        // Commit transaction
        $pdo->commit();
        closeConnection($pdo);
        
        return true;
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Delete Registration Error: " . $e->getMessage());
        return false;
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Delete Registration PDO Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get registration statistics
 * 
 * @return array Statistics about registrations
 */
function getRegistrationStatistics() {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT 
                    COUNT(*) as total_registrations,
                    COUNT(CASE WHEN registration_status = 'Registered' THEN 1 END) as registered,
                    COUNT(CASE WHEN registration_status = 'Confirmed' THEN 1 END) as confirmed,
                    COUNT(CASE WHEN registration_status = 'Cancelled' THEN 1 END) as cancelled,
                    COUNT(CASE WHEN registration_status = 'Attended' THEN 1 END) as attended,
                    COUNT(CASE WHEN payment_status = 'Paid' THEN 1 END) as paid_registrations,
                    SUM(payment_amount) as total_revenue
                FROM registrations";
        
        $stmt = $pdo->query($sql);
        $stats = $stmt->fetch();
        closeConnection($pdo);
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Get Registration Statistics Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if student is registered for event
 * 
 * @param int $studentId Student ID
 * @param int $eventId Event ID
 * @return bool True if registered, false otherwise
 */
function isStudentRegistered($studentId, $eventId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT COUNT(*) as count FROM registrations 
                WHERE student_id = :student_id AND event_id = :event_id 
                AND registration_status != 'Cancelled'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        closeConnection($pdo);
        
        return $result['count'] > 0;
    } catch (PDOException $e) {
        error_log("Check Registration Error: " . $e->getMessage());
        return false;
    }
}

// Example usage (commented out for production)
/*
// Register a student for an event
$registrationId = registerForEvent(1, 1, 'Paid', 50.00, 'Looking forward to it!');
echo "Created registration with ID: " . $registrationId;

// Get student registrations
$registrations = getStudentRegistrations(1);
print_r($registrations);

// Get event registrations
$registrations = getEventRegistrations(1);
print_r($registrations);

// Update registration status
updateRegistrationStatus(1, 'Confirmed');

// Update payment status
updatePaymentStatus(1, 'Paid', 50.00);

// Cancel registration
cancelRegistration(1);

// Delete registration
deleteRegistration(1);
*/
?>
