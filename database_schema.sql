-- ============================================================
-- IRIS (Intelligent RFID Identification System) - Full Schema
-- Database: studentdb
-- Generated: 2026-07-08
-- ============================================================

CREATE DATABASE IF NOT EXISTS studentdb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE studentdb;

-- ============================================================
-- 1. users - System user accounts
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    dept VARCHAR(50) NOT NULL,
    role ENUM('admin', 'teacher', 'staff') DEFAULT 'staff',
    password VARCHAR(20) NOT NULL,
    first_login BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 2. students - Student records
-- ============================================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
    department ENUM('CE','IT','ME','EE','EC','CV','CSE','AI','DS') NOT NULL,
    email VARCHAR(100) NOT NULL,
    rfid VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 3. attendance - RFID scan logs
-- ============================================================
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rfid VARCHAR(50) NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    department ENUM('CE','IT','ME','EE','EC','CV','CSE','AI','DS') NOT NULL,
    status ENUM('IN', 'OUT') NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 4. departments - Department master data
-- ============================================================
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_code ENUM('CE','IT','ME','EE','EC','CV','CSE','AI','DS') NOT NULL UNIQUE,
    dept_name VARCHAR(100) NOT NULL,
    dept_head VARCHAR(100) DEFAULT NULL,
    contact_email VARCHAR(100) DEFAULT NULL,
    contact_phone VARCHAR(20) DEFAULT NULL,
    building VARCHAR(50) DEFAULT NULL,
    floor_number INT DEFAULT NULL,
    total_students INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 5. books - Library catalog
-- ============================================================
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    publisher VARCHAR(255) DEFAULT NULL,
    publication_year YEAR DEFAULT NULL,
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    location VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 6. book_transactions - Book issue/return records
-- ============================================================
CREATE TABLE book_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT DEFAULT NULL,
    student_name VARCHAR(255) NOT NULL,
    student_id VARCHAR(50) DEFAULT NULL,
    transaction_type ENUM('issue', 'return') NOT NULL,
    issue_date DATE DEFAULT NULL,
    due_date DATE DEFAULT NULL,
    return_date DATE DEFAULT NULL,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Triggers: Auto-update department student counts
-- ============================================================
DELIMITER //

CREATE TRIGGER update_dept_count_insert
AFTER INSERT ON students
FOR EACH ROW
BEGIN
    UPDATE departments
    SET total_students = (SELECT COUNT(*) FROM students WHERE department = NEW.department)
    WHERE dept_code = NEW.department;
END//

CREATE TRIGGER update_dept_count_update
AFTER UPDATE ON students
FOR EACH ROW
BEGIN
    IF OLD.department != NEW.department THEN
        UPDATE departments
        SET total_students = (SELECT COUNT(*) FROM students WHERE department = OLD.department)
        WHERE dept_code = OLD.department;

        UPDATE departments
        SET total_students = (SELECT COUNT(*) FROM students WHERE department = NEW.department)
        WHERE dept_code = NEW.department;
    END IF;
END//

CREATE TRIGGER update_dept_count_delete
AFTER DELETE ON students
FOR EACH ROW
BEGIN
    UPDATE departments
    SET total_students = (SELECT COUNT(*) FROM students WHERE department = OLD.department)
    WHERE dept_code = OLD.department;
END//

DELIMITER ;

-- ============================================================
-- Seed Data
-- ============================================================

-- Default departments
INSERT INTO departments (dept_code, dept_name, dept_head, contact_email, status) VALUES
('CE', 'Computer Engineering', 'Dr. John Smith', 'ce.head@college.edu', 'active'),
('IT', 'Information Technology', 'Dr. Jane Doe', 'it.head@college.edu', 'active'),
('ME', 'Mechanical Engineering', 'Dr. Mike Johnson', 'me.head@college.edu', 'active'),
('EE', 'Electrical Engineering', 'Dr. Sarah Wilson', 'ee.head@college.edu', 'active'),
('EC', 'Electronics & Communication', 'Dr. David Brown', 'ec.head@college.edu', 'active'),
('CV', 'Civil Engineering', 'Dr. Lisa Davis', 'cv.head@college.edu', 'active'),
('CSE', 'Computer Science Engineering', 'Dr. Robert Miller', 'cse.head@college.edu', 'active'),
('AI', 'Artificial Intelligence', 'Dr. Emily Taylor', 'ai.head@college.edu', 'active'),
('DS', 'Data Science', 'Dr. Chris Anderson', 'ds.head@college.edu', 'active');

-- Default users (passwords are plain text - update to hashed in production)
INSERT INTO users (name, dept, role, password, first_login) VALUES
('Admin User', 'IT', 'admin', 'A7K9M2X5', TRUE),
('John Teacher', 'Computer Science', 'teacher', 'B3N8P1Q6', TRUE),
('Staff Member', 'HR', 'staff', 'C4R7T9W2', TRUE),
('Staff User', 'General', 'staff', 'S1T2U3V4', TRUE);

-- Sample books
INSERT INTO books (isbn, title, author, category, publisher, publication_year, total_copies, available_copies, location) VALUES
('978-0134685991', 'Effective Java', 'Joshua Bloch', 'Programming', 'Addison-Wesley', 2017, 3, 3, 'A-101'),
('978-0596009205', 'Head First Design Patterns', 'Eric Freeman', 'Programming', 'O''Reilly Media', 2004, 2, 2, 'A-102'),
('978-0321356680', 'Effective C++', 'Scott Meyers', 'Programming', 'Addison-Wesley', 2005, 2, 2, 'A-103'),
('978-0132350884', 'Clean Code', 'Robert Martin', 'Programming', 'Prentice Hall', 2008, 4, 4, 'A-104'),
('978-0201633610', 'Design Patterns', 'Gang of Four', 'Programming', 'Addison-Wesley', 1994, 2, 2, 'A-105');
