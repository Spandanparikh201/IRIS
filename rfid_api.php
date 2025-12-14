<?php
session_start();
header("Content-Type: application/json");
date_default_timezone_set('Asia/Kolkata');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$rfid = $_POST['rfid'] ?? '';
if (empty($rfid)) {
    echo json_encode(["success" => false, "message" => "Missing RFID"]);
    exit;
}

// Duplicate interval in seconds
$DUPLICATE_INTERVAL = 20;

// ---------------- Fetch Student ----------------
$stmt = $conn->prepare("SELECT name, department FROM students WHERE rfid = ?");
$stmt->bind_param("s", $rfid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Student not found",
        "rfid" => $rfid
    ]);
    exit;
}

$student = $res->fetch_assoc();
$name = $student['name'];
$department = $student['department'];

// ---------------- Get Last Scan ----------------
$check = $conn->prepare("
    SELECT status, timestamp 
    FROM attendance 
    WHERE rfid = ? 
    ORDER BY id DESC LIMIT 1
");
$check->bind_param("s", $rfid);
$check->execute();
$last = $check->get_result()->fetch_assoc();

$currentTime = time();
$status = "IN"; // default first scan

if ($last) {
    $lastStatus = $last['status'];
    $lastTime = strtotime($last['timestamp']);
    $timeDiff = $currentTime - $lastTime;

    // ✔ Duplicate Scan Protection
    if ($timeDiff < $DUPLICATE_INTERVAL) {
        echo json_encode([
            "success" => false,
            "message" => "Duplicate scan ignored",
            "rfid" => $rfid,
            "name" => $name,
            "department" => $department,
            "status" => $lastStatus
        ]);
        exit;
    }

    // ✔ Toggle IN/OUT
    $status = ($lastStatus === "IN") ? "OUT" : "IN";
}

// ---------------- Insert New Attendance Entry ----------------
$insert = $conn->prepare("
    INSERT INTO attendance (rfid, name, department, status) 
    VALUES (?, ?, ?, ?)
");
$insert->bind_param("ssss", $rfid, $name, $department, $status);

if (!$insert->execute()) {
    echo json_encode(["success" => false, "message" => "Insert failed"]);
    exit;
}

// ---------------- Calculate Current IN Students ----------------
$countSQL = "
    SELECT COUNT(*) AS total_in
    FROM (
        SELECT rfid,
               (SELECT status 
                FROM attendance a2 
                WHERE a2.rfid = a1.rfid 
                ORDER BY id DESC LIMIT 1) AS last_status
        FROM attendance a1
        GROUP BY rfid
    ) AS t
    WHERE last_status = 'IN'
";

$countResult = $conn->query($countSQL);
$currentIn = $countResult->fetch_assoc()['total_in'] ?? 0;

// ---------------- Final API Response ----------------
echo json_encode([
    "success" => true,
    "message" => "$status recorded",
    "rfid" => $rfid,
    "name" => $name,
    "department" => $department,
    "status" => $status,
    "current_in_count" => $currentIn
]);

?>
