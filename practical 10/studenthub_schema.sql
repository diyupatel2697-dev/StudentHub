-- StudentHub Database Schema
-- MySQL Schema Design for Student Management System

-- Create Database
CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;

-- ============================================
-- Table: students
-- Stores student information
-- ============================================
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_number (student_number),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: events
-- Stores event information
-- ============================================
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(200) NOT NULL,
    event_description TEXT,
    event_type ENUM('Academic', 'Cultural', 'Sports', 'Workshop', 'Seminar', 'Other') NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(200),
    max_participants INT,
    current_participants INT DEFAULT 0,
    registration_deadline DATE NOT NULL,
    organizer_name VARCHAR(100),
    organizer_contact VARCHAR(20),
    status ENUM('Upcoming', 'Ongoing', 'Completed', 'Cancelled') DEFAULT 'Upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_event_type (event_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: registrations
-- Stores student event registrations
-- ============================================
CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    registration_status ENUM('Registered', 'Confirmed', 'Cancelled', 'Attended') DEFAULT 'Registered',
    payment_status ENUM('Pending', 'Paid', 'Free') DEFAULT 'Pending',
    payment_amount DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (student_id, event_id),
    INDEX idx_student_id (student_id),
    INDEX idx_event_id (event_id),
    INDEX idx_registration_status (registration_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample Data Insertion
-- ============================================

-- Insert sample students
INSERT INTO students (student_number, first_name, last_name, email, phone, date_of_birth, gender, address, city, state, postal_code, enrollment_date, semester, department, cgpa, status, password) VALUES
('STU001', 'John', 'Doe', 'john.doe@studenthub.edu', '9876543210', '2002-05-15', 'Male', '123 Main Street', 'New York', 'NY', '10001', '2021-08-01', 5, 'Computer Science', 3.75, 'Active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('STU002', 'Jane', 'Smith', 'jane.smith@studenthub.edu', '9876543211', '2002-08-20', 'Female', '456 Oak Avenue', 'Los Angeles', 'CA', '90001', '2021-08-01', 5, 'Computer Science', 3.85, 'Active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('STU003', 'Michael', 'Johnson', 'michael.johnson@studenthub.edu', '9876543212', '2002-03-10', 'Male', '789 Pine Road', 'Chicago', 'IL', '60601', '2021-08-01', 5, 'Information Technology', 3.65, 'Active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('STU004', 'Emily', 'Brown', 'emily.brown@studenthub.edu', '9876543213', '2002-11-25', 'Female', '321 Elm Street', 'Houston', 'TX', '77001', '2021-08-01', 5, 'Computer Science', 3.90, 'Active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('STU005', 'David', 'Wilson', 'david.wilson@studenthub.edu', '9876543214', '2002-07-08', 'Male', '654 Maple Drive', 'Phoenix', 'AZ', '85001', '2021-08-01', 5, 'Information Technology', 3.45, 'Active', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample events
INSERT INTO events (event_name, event_description, event_type, event_date, event_time, venue, max_participants, registration_deadline, organizer_name, organizer_contact, status) VALUES
('Annual Tech Symposium 2026', 'A technical symposium featuring guest lectures and workshops on emerging technologies', 'Academic', '2026-10-15', '09:00:00', 'Main Auditorium', 200, '2026-10-10', 'Dr. Sarah Johnson', '9876543220', 'Upcoming'),
('Cultural Fest 2026', 'Annual cultural festival showcasing music, dance, and arts', 'Cultural', '2026-11-20', '10:00:00', 'Campus Grounds', 500, '2026-11-15', 'Prof. Michael Chen', '9876543221', 'Upcoming'),
('Inter-College Sports Meet', 'Annual sports competition with various athletic events', 'Sports', '2026-12-05', '08:00:00', 'Sports Complex', 300, '2026-11-30', 'Coach Robert Taylor', '9876543222', 'Upcoming'),
('Web Development Workshop', 'Hands-on workshop on modern web development technologies', 'Workshop', '2026-09-25', '14:00:00', 'Lab 101', 50, '2026-09-20', 'Dr. Emily Davis', '9876543223', 'Upcoming'),
('AI/ML Seminar', 'Seminar on Artificial Intelligence and Machine Learning applications', 'Seminar', '2026-10-05', '11:00:00', 'Conference Hall', 150, '2026-10-01', 'Dr. Richard Feynman', '9876543224', 'Upcoming');

-- Insert sample registrations
INSERT INTO registrations (student_id, event_id, registration_status, payment_status, payment_amount) VALUES
(1, 1, 'Registered', 'Paid', 50.00),
(1, 4, 'Registered', 'Paid', 25.00),
(2, 1, 'Registered', 'Paid', 50.00),
(2, 2, 'Registered', 'Free', 0.00),
(3, 1, 'Registered', 'Paid', 50.00),
(3, 3, 'Registered', 'Paid', 30.00),
(4, 1, 'Registered', 'Paid', 50.00),
(4, 4, 'Registered', 'Paid', 25.00),
(5, 2, 'Registered', 'Free', 0.00),
(5, 5, 'Registered', 'Paid', 20.00);

-- ============================================
-- Useful Queries
-- ============================================

-- View all students with their registrations
-- SELECT s.student_number, s.first_name, s.last_name, s.email, COUNT(r.registration_id) as total_registrations
-- FROM students s
-- LEFT JOIN registrations r ON s.student_id = r.student_id
-- GROUP BY s.student_id;

-- View all events with participant count
-- SELECT e.event_name, e.event_type, e.event_date, e.venue, e.max_participants, COUNT(r.registration_id) as registered_count
-- FROM events e
-- LEFT JOIN registrations r ON e.event_id = r.event_id AND r.registration_status = 'Registered'
-- GROUP BY e.event_id;

-- View upcoming events
-- SELECT * FROM events WHERE event_date >= CURDATE() AND status = 'Upcoming' ORDER BY event_date ASC;

-- View student registrations for a specific event
-- SELECT s.student_number, s.first_name, s.last_name, s.email, r.registration_date, r.registration_status
-- FROM registrations r
-- JOIN students s ON r.student_id = s.student_id
-- WHERE r.event_id = 1;
