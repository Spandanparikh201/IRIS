-- IRIS Production Schema
-- Extracted from studentdb on 2026-07-09

-- Tables: users, students, attendance, departments, books, book_transactions

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `dept` varchar(50) NOT NULL,
  `role` enum('admin','teacher','staff','librarian') DEFAULT 'staff',
  `password` varchar(255) NOT NULL,
  `first_login` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `roll_number` varchar(20) NOT NULL,
  `department` enum('CE','IT','ME','EE','EC','CV','CSE','AI','DS') NOT NULL,
  `email` varchar(100) NOT NULL,
  `rfid` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roll_number` (`roll_number`),
  UNIQUE KEY `rfid` (`rfid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rfid` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `department` enum('CE','IT','ME','EE','EC','CV','CSE','AI','DS') NOT NULL,
  `status` enum('IN','OUT') NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_code` enum('CE','IT','ME','EE','EC','CV','CSE','AI','DS') NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `dept_head` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `building` varchar(50) DEFAULT NULL,
  `floor_number` int(11) DEFAULT NULL,
  `total_students` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dept_code` (`dept_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `total_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 1,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `book_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) DEFAULT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `transaction_type` enum('issue','return') NOT NULL,
  `issue_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('issued','returned','overdue') DEFAULT 'issued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `book_transactions_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

