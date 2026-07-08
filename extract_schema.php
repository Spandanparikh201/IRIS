<?php
$host = $argv[1] ?? 'localhost';
$user = $argv[2] ?? 'root';
$pass = $argv[3] ?? '';

$conn = new mysqli($host, $user, $pass, 'studentdb');
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$tables = ['students', 'attendance', 'users', 'departments', 'library_books', 'library_transactions', 'library_fine'];
$schema = "-- IRIS Production Schema\n-- Extracted from studentdb on " . date('Y-m-d') . "\n\n";

foreach ($tables as $table) {
    $result = $conn->query("SHOW CREATE TABLE `$table`");
    if ($result && $row = $result->fetch_assoc()) {
        $schema .= $row['Create Table'] . ";\n\n";
    }
}

file_put_contents(__DIR__ . '/schema.sql', $schema);
echo "Schema extracted to schema.sql\n";
$conn->close();
