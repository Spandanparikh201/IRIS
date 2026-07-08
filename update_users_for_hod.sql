-- Update users table to add HOD role
-- First, modify the role column to include 'hod' as an option

-- Drop the existing users table if it exists (backup first if needed)
-- DROP TABLE IF EXISTS users_backup;
-- CREATE TABLE users_backup AS SELECT * FROM users;

-- Modify the role column to include 'hod'
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'hod', 'teacher', 'staff') NOT NULL;

-- Update existing admin users to have 'admin' role
-- (This should already be the case, but just to be sure)
UPDATE users SET role = 'admin' WHERE role = 'admin';

-- Add sample HOD users for each department
INSERT INTO users (name, dept, role, password) VALUES
('HOD Computer Science', 'Computer Science', 'hod', 'H1O2D3P4'),
('HOD Information Technology', 'Information Technology', 'hod', 'H1O2D5P6'),
('HOD Mechanical Engineering', 'Mechanical Engineering', 'hod', 'H1O2D7P8'),
('HOD Electrical Engineering', 'Electrical Engineering', 'hod', 'H1O2D9P0'),
('HOD Electronics & Communication', 'Electronics & Communication', 'hod', 'H1O2D1P2'),
('HOD Civil Engineering', 'Civil Engineering', 'hod', 'H1O2D3P4'),
('HOD Computer Science & Engineering', 'Computer Science & Engineering', 'hod', 'H1O2D5P6'),
('HOD Artificial Intelligence', 'Artificial Intelligence', 'hod', 'H1O2D7P8'),
('HOD Data Science', 'Data Science', 'hod', 'H1O2D9P0');

-- Update existing teacher/staff users to have 'teacher' role
UPDATE users SET role = 'teacher' WHERE role = 'teacher';
UPDATE users SET role = 'staff' WHERE role = 'staff';

-- Display the updated users table
SELECT * FROM users;
