<?php
/**
 * StudentHub - Event CRUD Operations
 * Demonstrates PDO Prepared Statements for secure database operations
 */

require_once 'config.php';

/**
 * Create a new event
 * 
 * @param array $eventData Event information
 * @return int|false New event ID or false on failure
 */
function createEvent($eventData) {
    try {
        $pdo = getDBConnection();
        
        $sql = "INSERT INTO events (
                    event_name, 
                    event_description, 
                    event_type, 
                    event_date, 
                    event_time, 
                    venue, 
                    max_participants, 
                    registration_deadline, 
                    organizer_name, 
                    organizer_contact, 
                    status
                ) VALUES (
                    :event_name, 
                    :event_description, 
                    :event_type, 
                    :event_date, 
                    :event_time, 
                    :venue, 
                    :max_participants, 
                    :registration_deadline, 
                    :organizer_name, 
                    :organizer_contact, 
                    :status
                )";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':event_name', $eventData['event_name'], PDO::PARAM_STR);
        $stmt->bindParam(':event_description', $eventData['event_description'], PDO::PARAM_STR);
        $stmt->bindParam(':event_type', $eventData['event_type'], PDO::PARAM_STR);
        $stmt->bindParam(':event_date', $eventData['event_date'], PDO::PARAM_STR);
        $stmt->bindParam(':event_time', $eventData['event_time'], PDO::PARAM_STR);
        $stmt->bindParam(':venue', $eventData['venue'], PDO::PARAM_STR);
        $stmt->bindParam(':max_participants', $eventData['max_participants'], PDO::PARAM_INT);
        $stmt->bindParam(':registration_deadline', $eventData['registration_deadline'], PDO::PARAM_STR);
        $stmt->bindParam(':organizer_name', $eventData['organizer_name'], PDO::PARAM_STR);
        $stmt->bindParam(':organizer_contact', $eventData['organizer_contact'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $eventData['status'], PDO::PARAM_STR);
        
        $stmt->execute();
        
        $eventId = $pdo->lastInsertId();
        closeConnection($pdo);
        
        return $eventId;
    } catch (PDOException $e) {
        error_log("Create Event Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read an event by ID
 * 
 * @param int $eventId Event ID
 * @return array|false Event data or false if not found
 */
function getEventById($eventId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT event_id, event_name, event_description, event_type, event_date, 
                       event_time, venue, max_participants, current_participants, 
                       registration_deadline, organizer_name, organizer_contact, status, created_at
                FROM events 
                WHERE event_id = :event_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        
        $event = $stmt->fetch();
        closeConnection($pdo);
        
        return $event ? $event : false;
    } catch (PDOException $e) {
        error_log("Get Event Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read all events
 * 
 * @param string $status Filter by status (optional)
 * @param string $type Filter by type (optional)
 * @return array Array of events
 */
function getAllEvents($status = null, $type = null) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT event_id, event_name, event_description, event_type, event_date, 
                       event_time, venue, max_participants, current_participants, 
                       registration_deadline, organizer_name, status
                FROM events";
        
        $conditions = [];
        $params = [];
        
        if ($status) {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }
        
        if ($type) {
            $conditions[] = "event_type = :event_type";
            $params[':event_type'] = $type;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY event_date ASC";
        
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $events = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $events;
    } catch (PDOException $e) {
        error_log("Get All Events Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get upcoming events
 * 
 * @return array Array of upcoming events
 */
function getUpcomingEvents() {
    return getAllEvents('Upcoming');
}

/**
 * Get events by type
 * 
 * @param string $eventType Event type
 * @return array Array of events
 */
function getEventsByType($eventType) {
    return getAllEvents(null, $eventType);
}

/**
 * Update event information
 * 
 * @param int $eventId Event ID
 * @param array $eventData Updated event information
 * @return bool True on success, false on failure
 */
function updateEvent($eventId, $eventData) {
    try {
        $pdo = getDBConnection();
        
        $sql = "UPDATE events SET 
                    event_name = :event_name,
                    event_description = :event_description,
                    event_type = :event_type,
                    event_date = :event_date,
                    event_time = :event_time,
                    venue = :venue,
                    max_participants = :max_participants,
                    registration_deadline = :registration_deadline,
                    organizer_name = :organizer_name,
                    organizer_contact = :organizer_contact,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE event_id = :event_id";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':event_name', $eventData['event_name'], PDO::PARAM_STR);
        $stmt->bindParam(':event_description', $eventData['event_description'], PDO::PARAM_STR);
        $stmt->bindParam(':event_type', $eventData['event_type'], PDO::PARAM_STR);
        $stmt->bindParam(':event_date', $eventData['event_date'], PDO::PARAM_STR);
        $stmt->bindParam(':event_time', $eventData['event_time'], PDO::PARAM_STR);
        $stmt->bindParam(':venue', $eventData['venue'], PDO::PARAM_STR);
        $stmt->bindParam(':max_participants', $eventData['max_participants'], PDO::PARAM_INT);
        $stmt->bindParam(':registration_deadline', $eventData['registration_deadline'], PDO::PARAM_STR);
        $stmt->bindParam(':organizer_name', $eventData['organizer_name'], PDO::PARAM_STR);
        $stmt->bindParam(':organizer_contact', $eventData['organizer_contact'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $eventData['status'], PDO::PARAM_STR);
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Update Event Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update event participant count
 * 
 * @param int $eventId Event ID
 * @param int $change Change in participant count (+1 or -1)
 * @return bool True on success, false on failure
 */
function updateEventParticipantCount($eventId, $change) {
    try {
        $pdo = getDBConnection();
        
        $sql = "UPDATE events SET 
                    current_participants = current_participants + :change,
                    updated_at = CURRENT_TIMESTAMP
                WHERE event_id = :event_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindParam(':change', $change, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Update Participant Count Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete an event
 * 
 * @param int $eventId Event ID
 * @return bool True on success, false on failure
 */
function deleteEvent($eventId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "DELETE FROM events WHERE event_id = :event_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Delete Event Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Search events by name or description
 * 
 * @param string $searchTerm Search term
 * @return array Array of matching events
 */
function searchEvents($searchTerm) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT event_id, event_name, event_description, event_type, event_date, 
                       event_time, venue, max_participants, current_participants, 
                       registration_deadline, organizer_name, status
                FROM events 
                WHERE event_name LIKE :search_term 
                   OR event_description LIKE :search_term
                ORDER BY event_date ASC";
        
        $searchPattern = '%' . $searchTerm . '%';
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':search_term', $searchPattern, PDO::PARAM_STR);
        $stmt->execute();
        
        $events = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $events;
    } catch (PDOException $e) {
        error_log("Search Events Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get available events (not full and registration open)
 * 
 * @return array Array of available events
 */
function getAvailableEvents() {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT event_id, event_name, event_description, event_type, event_date, 
                       event_time, venue, max_participants, current_participants, 
                       registration_deadline, organizer_name, status
                FROM events 
                WHERE status = 'Upcoming'
                  AND current_participants < max_participants
                  AND registration_deadline >= CURDATE()
                ORDER BY event_date ASC";
        
        $stmt = $pdo->query($sql);
        $events = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $events;
    } catch (PDOException $e) {
        error_log("Get Available Events Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get event statistics
 * 
 * @return array Statistics about events
 */
function getEventStatistics() {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT 
                    COUNT(*) as total_events,
                    COUNT(CASE WHEN status = 'Upcoming' THEN 1 END) as upcoming_events,
                    COUNT(CASE WHEN status = 'Ongoing' THEN 1 END) as ongoing_events,
                    COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_events,
                    COUNT(CASE WHEN status = 'Cancelled' THEN 1 END) as cancelled_events,
                    SUM(current_participants) as total_registrations,
                    SUM(max_participants) as total_capacity
                FROM events";
        
        $stmt = $pdo->query($sql);
        $stats = $stmt->fetch();
        closeConnection($pdo);
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Get Event Statistics Error: " . $e->getMessage());
        return false;
    }
}

// Example usage (commented out for production)
/*
// Create a new event
$newEvent = [
    'event_name' => 'Hackathon 2026',
    'event_description' => '24-hour coding competition',
    'event_type' => 'Academic',
    'event_date' => '2026-12-15',
    'event_time' => '09:00:00',
    'venue' => 'Tech Hub',
    'max_participants' => 100,
    'registration_deadline' => '2026-12-10',
    'organizer_name' => 'Tech Club',
    'organizer_contact' => '9876543250',
    'status' => 'Upcoming'
];

$eventId = createEvent($newEvent);
echo "Created event with ID: " . $eventId;

// Read an event
$event = getEventById(1);
print_r($event);

// Update an event
$updateData = [
    'event_name' => 'Annual Tech Symposium 2026',
    'event_description' => 'A technical symposium featuring guest lectures',
    'event_type' => 'Academic',
    'event_date' => '2026-10-15',
    'event_time' => '09:00:00',
    'venue' => 'Main Auditorium',
    'max_participants' => 250,
    'registration_deadline' => '2026-10-10',
    'organizer_name' => 'Dr. Sarah Johnson',
    'organizer_contact' => '9876543220',
    'status' => 'Upcoming'
];
updateEvent(1, $updateData);

// Delete an event
deleteEvent(6);
*/
?>
