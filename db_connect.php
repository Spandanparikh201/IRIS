<?php
$host = "localhost";
$username = "root";
$password = ""; // Use your MySQL root password if set
$database = "studentdb";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
