-- Create the users table with new structure
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    dept VARCHAR(50) NOT NULL,
    role ENUM('admin', 'teacher', 'staff') NOT NULL,
    password VARCHAR(20) NOT NULL,
    first_login BOOLEAN DEFAULT TRUE
);

-- Insert sample users with auto-generated passwords
INSERT INTO users (name, dept, role, password) VALUES
('Admin User', 'IT', 'admin', 'A7K9M2X5'),
('John Teacher', 'Computer Science', 'teacher', 'B3N8P1Q6'),
('Staff Member', 'HR', 'staff', 'C4R7T9W2');