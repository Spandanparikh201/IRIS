<?php
include 'db_connect.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ['Name', 'RFID', 'Department', 'Status', 'Timestamp']);

$result = mysqli_query($conn, "SELECT * FROM attendance ORDER BY timestamp DESC");
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
