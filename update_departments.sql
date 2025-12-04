-- Update students table to use ENUM for department
ALTER TABLE students MODIFY COLUMN department ENUM('CE', 'IT', 'ME', 'EE', 'EC', 'CV', 'CSE', 'AI', 'DS') NOT NULL;

-- Update attendance table to use ENUM for department
ALTER TABLE attendance MODIFY COLUMN department ENUM('CE', 'IT', 'ME', 'EE', 'EC', 'CV', 'CSE', 'AI', 'DS') NOT NULL;

-- Update attendance table to use ENUM for status
ALTER TABLE attendance MODIFY COLUMN status ENUM('IN', 'OUT') NOT NULL;