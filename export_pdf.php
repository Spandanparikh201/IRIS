<?php
// 🔧 TIMEZONE FIX (PHP)
date_default_timezone_set('Asia/Kolkata');

require 'vendor/autoload.php';
use Dompdf\Dompdf;

include 'db_connect.php';
// 🔧 TIMEZONE FIX (MySQL session)
$conn->query("SET time_zone = '+05:30'");

$html = '
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .header { text-align: center; margin-bottom: 30px; }
    .header h1 { color: #2d3748; margin-bottom: 5px; }
    .header p { color: #718096; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th { background: #4a5568; color: white; padding: 12px 8px; text-align: left; }
    td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; }
    tr:nth-child(even) { background: #f7fafc; }
    .status-present { color: #22543d; font-weight: bold; }
    .status-absent { color: #742a2a; font-weight: bold; }
</style>
<div class="header">
    <h1>📊 Attendance Report</h1>
    <p>Generated on ' . date('d M Y h:i A') . ' IST</p>
</div>
<table>
    <tr><th>Name</th><th>RFID</th><th>Department</th><th>Status</th><th>Timestamp</th></tr>';

$result = mysqli_query($conn, "SELECT * FROM attendance ORDER BY timestamp DESC");
while ($row = mysqli_fetch_assoc($result)) {
  $statusClass = strtolower($row['status']) == 'present' ? 'status-present' : 'status-absent';
  $html .= "<tr>
    <td>{$row['name']}</td>
    <td>{$row['rfid']}</td>
    <td>{$row['department']}</td>
    <td class='{$statusClass}'>{$row['status']}</td>
    <td>" . date('M d, Y h:i A', strtotime($row['timestamp'])) . " IST</td>
  </tr>";
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("Attendance_Report.pdf");
?>
