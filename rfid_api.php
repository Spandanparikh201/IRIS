<?php
session_start();
header("Content-Type: application/json");
date_default_timezone_set('Asia/Kolkata'); // Ensure correct server timezone
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$rfid = $_POST['rfid'] ?? '';
$action = $_POST['action'] ?? 'TOGGLE'; // Future-proofing

if (empty($rfid)) {
    echo json_encode(["success" => false, "message" => "Missing RFID"]);
    exit;
}

// --------- Config ----------
$DUPLICATE_INTERVAL = 5;  // 5 seconds minimum before toggle

// -------- Fetch Student Info ----------
$stmt = $conn->prepare("SELECT name, department FROM students WHERE rfid = ?");
$stmt->bind_param("s", $rfid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Student not found"]);
    exit;
}

$student = $res->fetch_assoc();
$name = $student['name'];
$department = $student['department'];

// -------- Fetch Last Attendance ----------
$check = $conn->prepare("SELECT status, timestamp FROM attendance WHERE rfid = ? ORDER BY id DESC LIMIT 1");
$check->bind_param("s", $rfid);
$check->execute();
$last = $check->get_result()->fetch_assoc();

$currentTime = time();
$status = "IN"; // default first scan

if ($last) {
    $lastStatus = $last['status'];
    $lastTime = strtotime($last['timestamp']);
    $timeDiff = max(0, $currentTime - $lastTime); // Protect negative difference

    // If within duplicate interval → ignore toggle
    if ($timeDiff < $DUPLICATE_INTERVAL) {
        echo json_encode([
            "success" => false,
            "message" => "Duplicate scan ignored",
            "debug" => "TimeDiff=$timeDiff < $DUPLICATE_INTERVAL"
        ]);
        exit;
    }

    // Toggle status after allowed interval
    $status = ($lastStatus === "IN") ? "OUT" : "IN";
}

// -------- Insert Attendance ----------
$insert = $conn->prepare("INSERT INTO attendance (rfid, name, department, status) VALUES (?, ?, ?, ?)");
$insert->bind_param("ssss", $rfid, $name, $department, $status);

if (!$insert->execute()) {
    echo json_encode(["success" => false, "message" => "DB insert failed"]);
    exit;
}

// -------- API Response ----------
echo json_encode([
    "success" => true,
    "message" => "$status recorded",
    "rfid" => $rfid,
    "name" => $name,
    "department" => $department,
    "status" => $status
]);
?>
