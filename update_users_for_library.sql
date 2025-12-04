-- Update existing users table to support librarian role
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'staff', 'librarian') DEFAULT 'staff';

-- Insert sample librarian user
INSERT INTO users (name, dept, role, password) VALUES 
('Librarian User', 'Library', 'librarian', 'L5M8N3P9');
-- Default password is 'L5M8N3P9' - change after first login