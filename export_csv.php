<?php
// 🔧 TIMEZONE FIX (PHP)
date_default_timezone_set('Asia/Kolkata');

include 'db_connect.php';
// 🔧 TIMEZONE FIX (MySQL session)
$conn->query("SET time_zone = '+05:30'");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ['Name', 'RFID', 'Department', 'Status', 'Timestamp']);

$result = mysqli_query($conn, "SELECT * FROM attendance ORDER BY timestamp DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $row['timestamp'] = date('Y-m-d H:i:s', strtotime($row['timestamp'])); // IST
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
