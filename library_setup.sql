-- Library Management System Tables

-- Update users table to include librarian role
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'staff', 'librarian') DEFAULT 'staff';

-- Insert sample librarian user
INSERT INTO users (name, dept, role, password) VALUES 
('Librarian User', 'Library', 'librarian', 'L5M8N3P9');
-- Password is 'L5M8N3P9' (plain text as per your existing structure)

-- Books table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    publisher VARCHAR(255),
    publication_year YEAR,
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Book transactions table
CREATE TABLE book_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    student_name VARCHAR(255) NOT NULL,
    student_id VARCHAR(50),
    transaction_type ENUM('issue', 'return') NOT NULL,
    issue_date DATE,
    due_date DATE,
    return_date DATE,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Insert sample books
INSERT INTO books (isbn, title, author, category, publisher, publication_year, total_copies, available_copies, location) VALUES
('978-0134685991', 'Effective Java', 'Joshua Bloch', 'Programming', 'Addison-Wesley', 2017, 3, 3, 'A-101'),
('978-0596009205', 'Head First Design Patterns', 'Eric Freeman', 'Programming', 'O\'Reilly Media', 2004, 2, 2, 'A-102'),
('978-0321356680', 'Effective C++', 'Scott Meyers', 'Programming', 'Addison-Wesley', 2005, 2, 2, 'A-103'),
('978-0132350884', 'Clean Code', 'Robert Martin', 'Programming', 'Prentice Hall', 2008, 4, 4, 'A-104'),
('978-0201633610', 'Design Patterns', 'Gang of Four', 'Programming', 'Addison-Wesley', 1994, 2, 2, 'A-105');