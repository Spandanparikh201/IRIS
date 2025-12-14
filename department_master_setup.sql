-- Department Master Table Setup

-- Create departments master table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_code VARCHAR(10) UNIQUE NOT NULL,
    dept_name VARCHAR(100) NOT NULL,
    dept_head VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    building VARCHAR(50),
    floor_number INT,
    total_students INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert existing departments
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

-- Add foreign key constraint to students table (if not exists)
-- First, let's check if the constraint already exists
-- ALTER TABLE students ADD CONSTRAINT fk_student_department 
-- FOREIGN KEY (department) REFERENCES departments(dept_code) 
-- ON UPDATE CASCADE ON DELETE RESTRICT;

-- Update student count in departments table
UPDATE departments d 
SET total_students = (
    SELECT COUNT(*) 
    FROM students s 
    WHERE s.department = d.dept_code
);

-- Create trigger to auto-update student count
DELIMITER //
CREATE TRIGGER update_dept_student_count_insert
AFTER INSERT ON students
FOR EACH ROW
BEGIN
    UPDATE departments 
    SET total_students = (
        SELECT COUNT(*) 
        FROM students 
        WHERE department = NEW.department
    ) 
    WHERE dept_code = NEW.department;
END//

CREATE TRIGGER update_dept_student_count_update
AFTER UPDATE ON students
FOR EACH ROW
BEGIN
    -- Update old department count
    IF OLD.department != NEW.department THEN
        UPDATE departments 
        SET total_students = (
            SELECT COUNT(*) 
            FROM students 
            WHERE department = OLD.department
        ) 
        WHERE dept_code = OLD.department;
    END IF;
    
    -- Update new department count
    UPDATE departments 
    SET total_students = (
        SELECT COUNT(*) 
        FROM students 
        WHERE department = NEW.department
    ) 
    WHERE dept_code = NEW.department;
END//

CREATE TRIGGER update_dept_student_count_delete
AFTER DELETE ON students
FOR EACH ROW
BEGIN
    UPDATE departments 
    SET total_students = (
        SELECT COUNT(*) 
        FROM students 
        WHERE department = OLD.department
    ) 
    WHERE dept_code = OLD.department;
END//
DELIMITER ;

-- Useful queries for department management

-- 1. Get all departments with student count
-- SELECT d.*, d.total_students as current_count 
-- FROM departments d 
-- ORDER BY d.dept_name;

-- 2. Get department details with active students
-- SELECT d.dept_code, d.dept_name, d.dept_head, 
--        COUNT(s.id) as active_students,
--        d.contact_email, d.building, d.floor_number
-- FROM departments d 
-- LEFT JOIN students s ON d.dept_code = s.department 
-- WHERE d.status = 'active'
-- GROUP BY d.id 
-- ORDER BY d.dept_name;

-- 3. Get attendance summary by department
-- SELECT d.dept_name, 
--        COUNT(DISTINCT s.id) as total_students,
--        COUNT(a.id) as total_attendance_records,
--        COUNT(CASE WHEN a.status = 'IN' THEN 1 END) as total_in_records
-- FROM departments d
-- LEFT JOIN students s ON d.dept_code = s.department
-- LEFT JOIN attendance a ON s.rfid = a.rfid
-- WHERE d.status = 'active'
-- GROUP BY d.id
-- ORDER BY d.dept_name;

-- 4. Update department information
-- UPDATE departments 
-- SET dept_head = 'New Head Name', 
--     contact_email = 'new.email@college.edu',
--     updated_at = CURRENT_TIMESTAMP
-- WHERE dept_code = 'CE';

-- 5. Add new department
-- INSERT INTO departments (dept_code, dept_name, dept_head, contact_email, status) 
-- VALUES ('NEW', 'New Department', 'Dr. New Head', 'new@college.edu', 'active');