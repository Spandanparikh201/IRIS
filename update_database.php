<?php
include 'db_connect.php';

// Update students table
$sql1 = "ALTER TABLE students MODIFY COLUMN department ENUM('CE', 'IT', 'ME', 'EE', 'EC', 'CV', 'CSE', 'AI', 'DS') NOT NULL";

// Update attendance table
$sql2 = "ALTER TABLE attendance MODIFY COLUMN department ENUM('CE', 'IT', 'ME', 'EE', 'EC', 'CV', 'CSE', 'AI', 'DS') NOT NULL";

// Update attendance status
$sql3 = "ALTER TABLE attendance MODIFY COLUMN status ENUM('IN', 'OUT') NOT NULL";

echo "Updating database schema...<br>";

if ($conn->query($sql1) === TRUE) {
    echo "✓ Students table department column updated successfully<br>";
} else {
    echo "✗ Error updating students table: " . $conn->error . "<br>";
}

if ($conn->query($sql2) === TRUE) {
    echo "✓ Attendance table department column updated successfully<br>";
} else {
    echo "✗ Error updating attendance table: " . $conn->error . "<br>";
}

if ($conn->query($sql3) === TRUE) {
    echo "✓ Attendance table status column updated successfully<br>";
} else {
    echo "✗ Error updating attendance status: " . $conn->error . "<br>";
}

echo "<br>Database update completed!";
$conn->close();
?>