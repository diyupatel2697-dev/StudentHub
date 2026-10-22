# Practical 9: Secure User Registration with Database Insert, Duplicate Email Check, and Password Hashing

## Overview

This practical demonstrates secure user registration implementation using MySQLi with the following features:
- Frontend and backend input validation
- Duplicate email/username checking
- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Real-time email and student number availability checks via AJAX
- Password strength meter
- Appropriate success/error messages

## Files Created

### Frontend Files
1. **register.html** - Registration form with all required fields
2. **registration.css** - Styling for registration and login pages
3. **register_validation.js** - Frontend validation with real-time feedback

### Backend Files
4. **register.php** - Main registration processing script
5. **check_email.php** - AJAX endpoint for email availability check
6. **check_student_number.php** - AJAX endpoint for student number availability check
7. **login.php** - Secure login with password verification
8. **login_secure.html** - Secure login form

## Features Implemented

### 1. Frontend Validation

#### Field Validation Rules:
- **Student Number**: Format STU001, STU002, etc. (case-insensitive)
- **First Name**: Letters only, 2-30 characters
- **Last Name**: Letters only, 2-30 characters
- **Email**: Valid email format
- **Phone**: 10 digits (optional)
- **Date of Birth**: Valid date, age between 16-100 years
- **Gender**: Must select Male, Female, or Other
- **Password**: Minimum 8 characters with:
  - At least 1 uppercase letter
  - At least 1 lowercase letter
  - At least 1 number
  - At least 1 special character (@$!%*?&)
- **Confirm Password**: Must match password
- **Terms**: Must accept terms and conditions

#### Real-time Validation:
- Validation on field blur (when user leaves field)
- Validation on input (if field has error)
- Password strength meter with visual feedback
- AJAX checks for email and student number availability

### 2. Backend Validation

#### Input Sanitization:
```php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
```

#### Validation Functions:
- `validateEmail()` - Email format validation
- `validateStudentNumber()` - Student number format validation
- `validatePassword()` - Password strength validation

### 3. Duplicate Check

#### Email Duplicate Check:
```php
function emailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}
```

#### Student Number Duplicate Check:
```php
function studentNumberExists($conn, $studentNumber) {
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_number = ?");
    $stmt->bind_param("s", $studentNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}
```

### 4. Password Hashing

#### Using password_hash():
```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

- Uses bcrypt algorithm by default
- Automatically handles salt generation
- Future-proof (algorithm can be updated without changing code)

#### Password Verification (login.php):
```php
if (password_verify($password, $user['password'])) {
    // Password is correct
}
```

### 5. SQL Injection Prevention

All database queries use prepared statements:

```php
$stmt = $conn->prepare("INSERT INTO students (student_number, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $student_number, $email, $hashed_password);
$stmt->execute();
```

Benefits:
- User input is never directly concatenated into SQL queries
- Database engine handles proper escaping
- Protection against SQL injection attacks

### 6. AJAX Availability Checks

#### Email Check (check_email.php):
```javascript
fetch('check_email.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'email=' + encodeURIComponent(email)
})
.then(response => response.json())
.then(data => {
    if (data.exists) {
        // Show error
    }
});
```

#### Student Number Check (check_student_number.php):
Similar implementation for student number validation.

### 7. User Feedback

#### Success Messages:
- Green alert box on successful registration
- Redirect to login page with success indicator

#### Error Messages:
- Red alert box with specific error details
- Field-level error messages below each input
- Real-time feedback during typing

## Database Schema

Uses the existing `students` table from Practical 8:

```sql
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(10),
    enrollment_date DATE NOT NULL,
    semester INT NOT NULL,
    department VARCHAR(100),
    cgpa DECIMAL(3, 2),
    status ENUM('Active', 'Inactive', 'Graduated', 'Suspended') DEFAULT 'Active',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Setup Instructions

### 1. Database Setup
Ensure the `studenthub` database exists with the `students` table. Import the schema from Practical 8 if needed:
```bash
mysql -u root -p < ../practical 8/studenthub_schema.sql
```

### 2. Configure Database Credentials
Update the database credentials in all PHP files if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'studenthub');
```

### 3. Web Server Setup
Place files in your web server's document root (e.g., htdocs, www, or public_html).

### 4. Test Registration
Open your browser and navigate to:
```
http://localhost/practical%209/register.html
```

## Usage Examples

### Register a New User

1. Open `register.html`
2. Fill in all required fields:
   - Student Number: STU007
   - First Name: John
   - Last Name: Doe
   - Email: john.doe@example.com
   - Phone: 9876543210 (optional)
   - Date of Birth: Select from date picker
   - Gender: Select from dropdown
   - Password: SecurePass123@
   - Confirm Password: SecurePass123@
   - Accept terms and conditions
3. Click "Register" button
4. On success, you'll see a success message
5. Click "Login here" to proceed to login

### Login

1. Open `login_secure.html`
2. Enter your registered email and password
3. Click "Login"
4. On successful login, you'll be redirected to dashboard

## Security Features

### 1. SQL Injection Prevention
- All queries use prepared statements
- Parameter binding with type specification
- No direct string concatenation

### 2. Password Security
- Passwords hashed using bcrypt (PASSWORD_DEFAULT)
- Hash includes automatic salt generation
- Passwords never stored in plain text
- Verification using `password_verify()`

### 3. Input Validation
- Frontend validation for immediate feedback
- Backend validation as security layer
- Input sanitization to prevent XSS
- Email format validation
- Password strength requirements

### 4. Duplicate Prevention
- Email uniqueness check before insertion
- Student number uniqueness check before insertion
- Database-level unique constraints as backup

### 5. Session Management
- Secure session handling
- Session variables for user authentication
- Session destruction on logout

### 6. Error Handling
- Graceful error messages (no sensitive data exposed)
- Database error logging
- User-friendly error display

## Password Strength Requirements

To ensure strong passwords, the system requires:
- **Minimum length**: 8 characters
- **Uppercase**: At least 1 (A-Z)
- **Lowercase**: At least 1 (a-z)
- **Number**: At least 1 (0-9)
- **Special character**: At least 1 (@$!%*?&)

Example strong password: `SecurePass123@`

## Testing

### Test Valid Registration:
```
Student Number: STU007
First Name: Alice
Last Name: Johnson
Email: alice.johnson@test.com
Phone: 9876543210
Date of Birth: 2002-01-15
Gender: Female
Password: TestPass123@
Confirm Password: TestPass123@
```

### Test Duplicate Email:
Try registering with an email that already exists. Should show error: "This email is already registered."

### Test Duplicate Student Number:
Try registering with a student number that already exists. Should show error: "This student number is already registered."

### Test Weak Password:
Try with password: "password". Should show error: "Password must be at least 8 characters with uppercase, lowercase, number, and special character."

### Test Password Mismatch:
Enter different passwords in password and confirm password fields. Should show error: "Passwords do not match."

## Common Issues and Solutions

### Issue: Database connection failed
**Solution**: Check database credentials in PHP files. Ensure MySQL server is running.

### Issue: Email already registered error
**Solution**: Use a different email address or check if you already have an account.

### Issue: Student number format error
**Solution**: Ensure student number follows format STU001, STU002, etc.

### Issue: Password too weak
**Solution**: Use a stronger password with uppercase, lowercase, number, and special character.

### Issue: AJAX checks not working
**Solution**: Ensure check_email.php and check_student_number.php are in the same directory and accessible.

## Code Structure

### Frontend Flow:
1. User fills registration form
2. JavaScript validates each field on blur
3. AJAX checks email/student number availability
4. Password strength meter updates in real-time
5. Form submission validates all fields
6. If valid, form submits to register.php

### Backend Flow:
1. register.php receives POST data
2. Sanitizes all inputs
3. Validates each field
4. Checks for duplicate email
5. Checks for duplicate student number
6. Hashes password using password_hash()
7. Inserts user into database using prepared statement
8. Redirects with success/error message

## Best Practices Demonstrated

1. **Defense in Depth**: Validation on both frontend and backend
2. **Prepared Statements**: All database queries use parameter binding
3. **Password Hashing**: Using industry-standard bcrypt
4. **Input Sanitization**: Preventing XSS attacks
5. **User Feedback**: Clear, specific error messages
6. **Real-time Validation**: Immediate feedback during form entry
7. **AJAX Checks**: Asynchronous duplicate checking
8. **Session Security**: Proper session management
9. **Error Handling**: Graceful error messages
10. **Code Organization**: Separate files for different concerns

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- MySQLi extension enabled
- Web server (Apache/Nginx)
- Modern web browser with JavaScript enabled

## Notes

- All files are in practical 9 folder only
- No changes to files in other folders
- Uses existing database schema from Practical 8
- Passwords are never stored in plain text
- All user inputs are validated and sanitized
- Prepared statements prevent SQL injection
