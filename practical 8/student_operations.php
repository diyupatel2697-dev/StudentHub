<?php
/**
 * StudentHub - Student CRUD Operations
 * Demonstrates PDO Prepared Statements for secure database operations
 */

require_once 'config.php';

/**
 * Create a new student
 * 
 * @param array $studentData Student information
 * @return int|false New student ID or false on failure
 */
function createStudent($studentData) {
    try {
        $pdo = getDBConnection();
        
        $sql = "INSERT INTO students (
                    student_number, 
                    first_name, 
                    last_name, 
                    email, 
                    phone, 
                    date_of_birth, 
                    gender, 
                    address, 
                    city, 
                    state, 
                    postal_code, 
                    enrollment_date, 
                    semester, 
                    department, 
                    cgpa, 
                    status, 
                    password
                ) VALUES (
                    :student_number, 
                    :first_name, 
                    :last_name, 
                    :email, 
                    :phone, 
                    :date_of_birth, 
                    :gender, 
                    :address, 
                    :city, 
                    :state, 
                    :postal_code, 
                    :enrollment_date, 
                    :semester, 
                    :department, 
                    :cgpa, 
                    :status, 
                    :password
                )";
        
        $stmt = $pdo->prepare($sql);
        
        // Hash the password
        $hashedPassword = hashPassword($studentData['password']);
        
        // Bind parameters
        $stmt->bindParam(':student_number', $studentData['student_number'], PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $studentData['first_name'], PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $studentData['last_name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $studentData['email'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $studentData['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':date_of_birth', $studentData['date_of_birth'], PDO::PARAM_STR);
        $stmt->bindParam(':gender', $studentData['gender'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $studentData['address'], PDO::PARAM_STR);
        $stmt->bindParam(':city', $studentData['city'], PDO::PARAM_STR);
        $stmt->bindParam(':state', $studentData['state'], PDO::PARAM_STR);
        $stmt->bindParam(':postal_code', $studentData['postal_code'], PDO::PARAM_STR);
        $stmt->bindParam(':enrollment_date', $studentData['enrollment_date'], PDO::PARAM_STR);
        $stmt->bindParam(':semester', $studentData['semester'], PDO::PARAM_INT);
        $stmt->bindParam(':department', $studentData['department'], PDO::PARAM_STR);
        $stmt->bindParam(':cgpa', $studentData['cgpa'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $studentData['status'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        
        $stmt->execute();
        
        $studentId = $pdo->lastInsertId();
        closeConnection($pdo);
        
        return $studentId;
    } catch (PDOException $e) {
        error_log("Create Student Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read a student by ID
 * 
 * @param int $studentId Student ID
 * @return array|false Student data or false if not found
 */
function getStudentById($studentId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT student_id, student_number, first_name, last_name, email, phone, 
                       date_of_birth, gender, address, city, state, postal_code, 
                       enrollment_date, semester, department, cgpa, status, created_at
                FROM students 
                WHERE student_id = :student_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        
        $student = $stmt->fetch();
        closeConnection($pdo);
        
        return $student ? $student : false;
    } catch (PDOException $e) {
        error_log("Get Student Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read a student by student number
 * 
 * @param string $studentNumber Student number
 * @return array|false Student data or false if not found
 */
function getStudentByNumber($studentNumber) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT student_id, student_number, first_name, last_name, email, phone, 
                       date_of_birth, gender, address, city, state, postal_code, 
                       enrollment_date, semester, department, cgpa, status, created_at
                FROM students 
                WHERE student_number = :student_number";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_number', $studentNumber, PDO::PARAM_STR);
        $stmt->execute();
        
        $student = $stmt->fetch();
        closeConnection($pdo);
        
        return $student ? $student : false;
    } catch (PDOException $e) {
        error_log("Get Student by Number Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Read all students
 * 
 * @param string $status Filter by status (optional)
 * @return array Array of students
 */
function getAllStudents($status = null) {
    try {
        $pdo = getDBConnection();
        
        if ($status) {
            $sql = "SELECT student_id, student_number, first_name, last_name, email, 
                           semester, department, cgpa, status
                    FROM students 
                    WHERE status = :status
                    ORDER BY last_name, first_name";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        } else {
            $sql = "SELECT student_id, student_number, first_name, last_name, email, 
                           semester, department, cgpa, status
                    FROM students 
                    ORDER BY last_name, first_name";
            $stmt = $pdo->query($sql);
        }
        
        $stmt->execute();
        $students = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $students;
    } catch (PDOException $e) {
        error_log("Get All Students Error: " . $e->getMessage());
        return	false;
    }
}

/**
 * Update student information
 * 
 * @param int $studentId Student ID
 * @param array $studentData Updated student information
 * @return bool True on success, false on failure
 */
function updateStudent($studentId, $studentData) {
    try {
        $pdo = getDBConnection();
        
        $sql = "UPDATE students SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    date_of_birth = :date_of_birth,
                    gender = :gender,
                    address = :address,
                    city = :city,
                    state = :state,
                    postal_code = :postal_code,
                    semester = :semester,
                    department = :department,
                    cgpa = :cgpa,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE student_id = :student_id";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':first_name', $studentData['first_name'], PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $studentData['last_name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $studentData['email'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $studentData['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':date_of_birth', $studentData['date_of_birth'], PDO::PARAM_STR);
        $stmt->bindParam(':gender', $studentData['gender'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $studentData['address'], PDO::PARAM_STR);
        $stmt->bindParam(':city', $studentData['city'], PDO::PARAM_STR);
        $stmt->bindParam(':state', $studentData['state'], PDO::PARAM_STR);
        $stmt->bindParam(':postal_code', $studentData['postal_code'], PDO::PARAM_STR);
        $stmt->bindParam(':semester', $studentData['semester'], PDO::PARAM_INT);
        $stmt->bindParam(':department', $studentData['department'], PDO::PARAM_STR);
        $stmt->bindParam(':cgpa', $studentData['cgpa'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $studentData['status'], PDO::PARAM_STR);
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Update Student Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update student password
 * 
 * @param int $studentId Student ID
 * @param string $newPassword New plain text password
 * @return bool True on success, false on failure
 */
function updateStudentPassword($studentId, $newPassword) {
    try {
        $pdo = getDBConnection();
        
        $sql = "UPDATE students SET 
                    password = :password,
                    updated_at = CURRENT_TIMESTAMP
                WHERE student_id = :student_id";
        
        $stmt = $pdo->prepare($sql);
        
        $hashedPassword = hashPassword($newPassword);
        
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Update Password Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a student
 * 
 * @param int $studentId Student ID
 * @return bool True on success, false on failure
 */
function deleteStudent($studentId) {
    try {
        $pdo = getDBConnection();
        
        $sql = "DELETE FROM students WHERE student_id = :student_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        closeConnection($pdo);
        
        return $result;
    } catch (PDOException $e) {
        error_log("Delete Student Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Search students by name or email
 * 
 * @param string $searchTerm Search term
 * @return array Array of matching students
 */
function searchStudents($searchTerm) {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT student_id, student_number, first_name, last_name, email, 
                       semester, department, cgpa, status
                FROM students 
                WHERE first_name LIKE :search_term 
                   OR last_name LIKE :search_term 
                   OR email LIKE :search_term
                   OR CONCAT(first_name, ' ', last_name) LIKE :search_term
                ORDER BY last_name, first_name";
        
        $searchPattern = '%' . $searchTerm . '%';
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':search_term', $searchPattern, PDO::PARAM_STR);
        $stmt->execute();
        
        $students = $stmt->fetchAll();
        closeConnection($pdo);
        
        return $students;
    } catch (PDOException $e) {
        error_log("Search Students Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get student statistics
 * 
 * @return array Statistics about students
 */
function getStudentStatistics() {
    try {
        $pdo = getDBConnection();
        
        $sql = "SELECT 
                    COUNT(*) as total_students,
                    COUNT(CASE WHEN status = 'Active' THEN 1 END) as active_students,
                    COUNT(CASE WHEN status = 'Graduated' THEN 1 END) as graduated_students,
                    COUNT(CASE WHEN status = 'Inactive' THEN 1 END) as inactive_students,
                    AVG(cgpa) as average_cgpa,
                    MAX(cgpa) as highest_cgpa,
                    MIN(cgpa) as lowest_cgpa
                FROM students";
        
        $stmt = $pdo->query($sql);
        $stats = $stmt->fetch();
        closeConnection($pdo);
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Get Statistics Error: " . $e->getMessage());
        return false;
    }
}

// Example usage (commented out for production)
/*
// Create a new student
$newStudent = [
    'student_number' => 'STU006',
    'first_name' => 'Alice',
    'last_name' => 'Williams',
    'email' => 'alice.williams@studenthub.edu',
    'phone' => '9876543215',
    'date_of_birth' => '2002-02-14',
    'gender' => 'Female',
    'address' => '987 Cedar Lane',
    'city' => 'Seattle',
    'state' => 'WA',
    'postal_code' => '98101',
    'enrollment_date' => '2021-08-01',
    'semester' => 5,
    'department' => 'Computer Science',
    'cgpa' => '3.95',
    'status' => 'Active',
    'password' => 'securePassword123'
];

$studentId = createStudent($newStudent);
echo "Created student with ID: " . $studentId;

// Read a student
$student = getStudentById(1);
print_r($student);

// Update a student
$updateData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@studenthub.edu',
    'phone' => '9876543210',
    'date_of_birth' => '2002-05-15',
    'gender' => 'Male',
    'address' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'postal_code' => '10001',
    'semester' => 6,
    'department' => 'Computer Science',
    'cgpa' => '3.80',
    'status' => 'Active'
];
updateStudent(1, $updateData);

// Delete a student
deleteStudent(6);
*/
?>
