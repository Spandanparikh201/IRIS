-- Optimized Department Master for existing IRIS database

-- Create departments master table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_code ENUM('CE','IT','ME','EE','EC','CV','CSE','AI','DS') UNIQUE NOT NULL,
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

-- Insert departments matching your existing ENUM values
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

-- Update student count based on existing data
UPDATE departments d 
SET total_students = (
    SELECT COUNT(*) 
    FROM students s 
    WHERE s.department = d.dept_code
);

-- Create triggers for auto-updating student count
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

-- Useful queries for your existing data:

-- 1. Department overview with current students
SELECT 
    d.dept_code,
    d.dept_name,
    d.dept_head,
    d.total_students,
    COUNT(DISTINCT s.id) as actual_students,
    d.contact_email,
    d.status
FROM departments d
LEFT JOIN students s ON d.dept_code = s.department
GROUP BY d.id
ORDER BY d.dept_name;

-- 2. Attendance summary by department (today)
SELECT 
    d.dept_name,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(CASE WHEN a.status = 'IN' AND DATE(a.timestamp) = CURDATE() THEN 1 END) as present_today,
    COUNT(CASE WHEN DATE(a.timestamp) = CURDATE() THEN 1 END) as total_records_today
FROM departments d
LEFT JOIN students s ON d.dept_code = s.department
LEFT JOIN attendance a ON s.rfid = a.rfid
WHERE d.status = 'active'
GROUP BY d.id
ORDER BY d.dept_name;

-- 3. Department-wise student list
SELECT 
    d.dept_name,
    s.name,
    s.roll_number,
    s.email,
    s.rfid
FROM departments d
JOIN students s ON d.dept_code = s.department
WHERE d.status = 'active'
ORDER BY d.dept_name, s.name;