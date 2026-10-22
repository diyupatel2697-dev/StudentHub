# Practical 8: MySQL Schema Design, ER Model, PDO Connectivity, and Prepared Statements

## Overview

This practical demonstrates the complete database implementation for the StudentHub system, including:
- MySQL database schema design
- Entity-Relationship (ER) model
- PHP PDO (PHP Data Objects) database connectivity
- Prepared statements for secure database operations
- CRUD operations for Students, Events, and Registrations

## Files Included

### Database Files
- **studenthub_schema.sql** - Complete MySQL database schema with tables, relationships, and sample data
- **ER_Diagram.md** - Detailed Entity-Relationship model documentation

### PHP Files
- **config.php** - Database configuration and PDO connection setup
- **student_operations.php** - CRUD operations for students table
- **event_operations.php** - CRUD operations for events table
- **registration_operations.php** - CRUD operations for registrations table

## Database Schema

### Tables

#### 1. students
Stores student information and academic details.

**Columns:**
- `student_id` (PK) - Auto-increment primary key
- `student_number` - Unique student identification number
- `first_name`, `last_name` - Student name
- `email` - Unique email address
- `phone` - Contact phone number
- `date_of_birth` - Date of birth
- `gender` - Male, Female, or Other
- `address`, `city`, `state`, `postal_code` - Address information
- `enrollment_date` - Date of enrollment
- `semester` - Current semester (1-8)
- `department` - Academic department
- `cgpa` - Cumulative Grade Point Average
- `status` - Active, Inactive, Graduated, or Suspended
- `password` - Encrypted password
- `created_at`, `updated_at` - Timestamps

#### 2. events
Stores event information for campus activities.

**Columns:**
- `event_id` (PK) - Auto-increment primary key
- `event_name` - Name of the event
- `event_description` - Detailed description
- `event_type` - Academic, Cultural, Sports, Workshop, Seminar, or Other
- `event_date` - Date of the event
- `event_time` - Time of the event
- `venue` - Location
- `max_participants` - Maximum capacity
- `current_participants` - Current registered count
- `registration_deadline` - Last date to register
- `organizer_name`, `organizer_contact` - Organizer details
- `status` - Upcoming, Ongoing, Completed, or Cancelled
- `created_at`, `updated_at` - Timestamps

#### 3. registrations
Stores student event registrations.

**Columns:**
- `registration_id` (PK) - Auto-increment primary key
- `student_id` (FK) - Foreign key to students
- `event_id` (FK) - Foreign key to events
- `registration_date` - Registration timestamp
- `registration_status` - Registered, Confirmed, Cancelled, or Attended
- `payment_status` - Pending, Paid, or Free
- `payment_amount` - Amount paid
- `notes` - Additional notes

## Entity-Relationship Model

### Relationships

1. **STUDENTS - REGISTRATIONS** (One-to-Many)
   - One student can have multiple registrations
   - Foreign key: `student_id` in REGISTRATIONS
   - Delete rule: CASCADE

2. **EVENTS - REGISTRATIONS** (One-to-Many)
   - One event can have multiple registrations
   - Foreign key: `event_id` in REGISTRATIONS
   - Delete rule: CASCADE

### Constraints
- Unique constraint on (`student_id`, `event_id`) in REGISTRATIONS to prevent duplicate registrations
- Unique constraint on `student_number` and `email` in STUDENTS
- Foreign key constraints with CASCADE delete
- CHECK constraints on CGPA (0.00-4.00) and semester (1-8)

## PDO Database Connection

### Configuration (config.php)

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'studenthub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

### Connection Function

```php
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database connection failed.");
    }
}
```

## Prepared Statements

### Why Use Prepared Statements?

1. **Security** - Prevents SQL injection attacks
2. **Performance** - Query plan is compiled once, executed multiple times
3. **Flexibility** - Parameters can be bound dynamically

### Example: Create Student

```php
function createStudent($studentData) {
    try {
        $pdo = getDBConnection();
        
        $sql = "INSERT INTO students (
                    student_number, first_name, last_name, email, 
                    password
                ) VALUES (
                    :student_number, :first_name, :last_name, 
                    :email, :password
                )";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters with types
        $stmt->bindParam(':student_number', $studentData['student_number'], PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $studentData['first_name'], PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $studentData['last_name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $studentData['email'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        
        $stmt->execute();
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Create Student Error: " . $e->getMessage());
        return false;
    }
}
```

### Example: Read Student with Prepared Statement

```php
function getStudentById($studentId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT * FROM students WHERE student_id = :student_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get Student Error: " . $e->getMessage());
        return false;
    }
}
```

## CRUD Operations

### Student Operations (student_operations.php)

#### Create
```php
$studentData = [
    'student_number' => 'STU006',
    'first_name' => 'Alice',
    'last_name' => 'Williams',
    'email' => 'alice.williams@studenthub.edu',
    // ... other fields
    'password' => 'securePassword123'
];
$studentId = createStudent($studentData);
```

#### Read
```php
$student = getStudentById(1);
$students = getAllStudents();
$student = getStudentByNumber('STU001');
$students = searchStudents('John');
```

#### Update
```php
$updateData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@studenthub.edu',
    // ... other fields
];
updateStudent(1, $updateData);
```

#### Delete
```php
deleteStudent(1);
```

### Event Operations (event_operations.php)

#### Create
```php
$eventData = [
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
$eventId = createEvent($eventData);
```

#### Read
```php
$event = getEventById(1);
$events = getAllEvents();
$events = getUpcomingEvents();
$events = getEventsByType('Academic');
$events = getAvailableEvents();
```

#### Update
```php
updateEvent(1, $eventData);
updateEventParticipantCount(1, 1); // Increment by 1
```

#### Delete
```php
deleteEvent(1);
```

### Registration Operations (registration_operations.php)

#### Create (Register)
```php
$registrationId = registerForEvent(
    $studentId = 1, 
    $eventId = 1, 
    $paymentStatus = 'Paid', 
    $paymentAmount = 50.00, 
    $notes = 'Looking forward to it!'
);
```

#### Read
```php
$registration = getRegistrationById(1);
$registrations = getStudentRegistrations(1);
$registrations = getEventRegistrations(1);
$registrations = getAllRegistrations();
```

#### Update
```php
updateRegistrationStatus(1, 'Confirmed');
updatePaymentStatus(1, 'Paid', 50.00);
```

#### Delete
```php
cancelRegistration(1); // Soft delete with status update
deleteRegistration(1);  // Hard delete
```

## Security Features

### 1. SQL Injection Prevention
All database operations use prepared statements with parameter binding.

### 2. Password Hashing
Passwords are hashed using PHP's `password_hash()` function with the default algorithm (bcrypt).

```php
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
```

### 3. Input Sanitization
All user inputs are sanitized before processing.

```php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
```

### 4. Error Handling
All database operations are wrapped in try-catch blocks with proper error logging.

## Transactions

Registration operations use transactions to ensure data integrity:

```php
$pdo->beginTransaction();
try {
    // Insert registration
    // Update event participant count
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}
```

## Setup Instructions

### 1. Create Database
```bash
mysql -u root -p < studenthub_schema.sql
```

Or run the SQL commands in phpMyAdmin or MySQL Workbench.

### 2. Configure Database Connection
Edit `config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'studenthub');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Test Connection
Create a test file `test_connection.php`:
```php
<?php
require_once 'config.php';
if (testConnection()) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}
?>
```

### 4. Run Test Operations
Uncomment the example usage sections in the operation files to test CRUD operations.

## Sample Queries

### Get all students with registration count
```sql
SELECT s.student_number, s.first_name, s.last_name, s.email, 
       COUNT(r.registration_id) as total_registrations
FROM students s
LEFT JOIN registrations r ON s.student_id = r.student_id
GROUP BY s.student_id;
```

### Get upcoming events with available slots
```sql
SELECT e.event_name, e.event_type, e.event_date, e.venue,
       e.max_participants, 
       (e.max_participants - e.current_participants) as available_slots
FROM events e
WHERE e.event_date >= CURDATE() 
    AND e.status = 'Upcoming'
    AND e.current_participants < e.max_participants
ORDER BY e.event_date ASC;
```

### Get student's event history
```sql
SELECT e.event_name, e.event_type, e.event_date, 
       r.registration_date, r.registration_status, r.payment_status
FROM registrations r
JOIN events e ON r.event_id = e.event_id
WHERE r.student_id = 1
ORDER BY e.event_date DESC;
```

## Best Practices Demonstrated

1. **Prepared Statements** - All queries use parameter binding
2. **Error Handling** - Try-catch blocks with error logging
3. **Input Validation** - Email validation, data sanitization
4. **Password Security** - Hashing with bcrypt
5. **Transactions** - For multi-step operations
6. **Foreign Keys** - Data integrity with CASCADE delete
7. **Indexes** - Optimized queries with proper indexes
8. **Normalization** - 3NF compliant schema
9. **Separation of Concerns** - Separate files for each entity
10. **Documentation** - Comprehensive inline comments

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- PDO MySQL extension enabled
- Web server (Apache/Nginx) or PHP built-in server

## Testing

To test the implementation:

1. Import the schema into MySQL
2. Configure database credentials in config.php
3. Create a test script:
```php
<?php
require_once 'config.php';
require_once 'student_operations.php';
require_once 'event_operations.php';
require_once 'registration_operations.php';

// Test student operations
$students = getAllStudents();
print_r($students);

// Test event operations
$events = getUpcomingEvents();
print_r($events);

// Test registration operations
$registrationId = registerForEvent(1, 1, 'Paid', 50.00);
echo "Registration ID: " . $registrationId;
?>
```

## Notes

- All original files in other folders remain unchanged
- Only new files added to practical 8 folder
- Database uses InnoDB engine for transaction support
- UTF-8 encoding for international character support
- All timestamps are in UTC
- Passwords should never be stored in plain text
