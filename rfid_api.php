<?php
session_start();
header("Content-Type: application/json");
include 'db_connect.php';

// Check if user is authenticated (optional for API)
// Uncomment below lines if you want to restrict API access
/*
if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit;
}
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rfid = $_POST['rfid'] ?? '';

    if (empty($rfid)) {
        echo json_encode(["success" => false, "message" => "Missing RFID"]);
        exit;
    }

    // Fetch student info
    $stmt = $conn->prepare("SELECT name, department FROM students WHERE rfid = ?");
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Student not found"]);
        exit;
    }

    $student = $result->fetch_assoc();
    $name = $student['name'];
    $department = $student['department'];

    // Check last scan
    $check = $conn->prepare("SELECT status, timestamp FROM attendance WHERE rfid = ? ORDER BY id DESC LIMIT 1");
    $check->bind_param("s", $rfid);
    $check->execute();
    $last = $check->get_result()->fetch_assoc();

    $currentTime = time();
    $status = "IN";

    if ($last) {
        $lastStatus = $last['status'];
        $lastTime = strtotime($last['timestamp']);
        if (($currentTime - $lastTime) < 5) {
            echo json_encode(["success" => false, "message" => "Duplicate scan ignored"]);
            exit;
        }
        $status = ($lastStatus == "IN") ? "OUT" : "IN";
    }

    // Insert attendance
    $insert = $conn->prepare("INSERT INTO attendance (rfid, name, department, status) VALUES (?, ?, ?, ?)");
    $insert->bind_param("ssss", $rfid, $name, $department, $status);
    $insert->execute();

    echo json_encode([
        "success" => true,
        "message" => "$status recorded",
        "name" => $name,
        "department" => $department
    ]);
}
?>
