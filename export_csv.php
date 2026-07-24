<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

include 'db_connect.php';
$conn->query("SET time_zone = '+05:30'");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ['Name', 'RFID', 'Department', 'Status', 'Timestamp']);

$result = $conn->query("SELECT * FROM attendance ORDER BY timestamp DESC");
while ($row = $result->fetch_assoc()) {
    $row['timestamp'] = date('Y-m-d H:i:s', strtotime($row['timestamp']));
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
