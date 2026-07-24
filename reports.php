<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
include 'rbac_helper.php';
$conn->query("SET time_zone = '+05:30'");

$activePage = 'reports';
$pageTitle = 'Reports & Analytics';
$pageSubtitle = 'Generate and download comprehensive reports';

$filterDept = getUserDept();

// Get departments from departments table
$deptQuery = "SELECT dept_code, dept_name FROM departments WHERE status = 'active' ORDER BY dept_name";
$deptResult = $conn->query($deptQuery);
$departments = [];
$deptNames = [];
while($row = $deptResult->fetch_assoc()) {
    $departments[] = $row['dept_code'];
    $deptNames[$row['dept_code']] = $row['dept_name'];
}

if (isset($_GET['report']) && isset($_GET['format'])) {
    // Parse report type - handle cases like "department&dept=CE"
    $reportParts = explode('&', $_GET['report']);
    $reportType = $reportParts[0];
    $format = $_GET['format'];
    
    // Parse additional parameters from report string
    if (count($reportParts) > 1) {
        for ($i = 1; $i < count($reportParts); $i++) {
            $param = explode('=', $reportParts[$i]);
            if (count($param) == 2) {
                $_GET[$param[0]] = urldecode($param[1]);
            }
        }
    }
    
    $result = null;
    switch($reportType) {
        case 'daily':
            $date = $_GET['date'] ?? date('Y-m-d');
            $deptWhere = $filterDept ? " AND a.department = ?" : "";
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a INNER JOIN students s ON a.rfid = s.rfid WHERE DATE(a.timestamp) = ?$deptWhere ORDER BY a.timestamp DESC";
            $stmt = $conn->prepare($sql);
            if ($filterDept) { $stmt->bind_param('ss', $date, $filterDept); } else { $stmt->bind_param('s', $date); }
            $stmt->execute();
            $result = $stmt->get_result();
            break;
        case 'weekly':
            $date = $_GET['date'] ?? date('Y-m-d');
            $deptWhere = $filterDept ? " AND a.department = ?" : "";
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a INNER JOIN students s ON a.rfid = s.rfid WHERE YEARWEEK(a.timestamp, 1) = YEARWEEK(?, 1)$deptWhere ORDER BY a.timestamp DESC";
            $stmt = $conn->prepare($sql);
            if ($filterDept) { $stmt->bind_param('ss', $date, $filterDept); } else { $stmt->bind_param('s', $date); }
            $stmt->execute();
            $result = $stmt->get_result();
            break;
        case 'monthly':
            $date = $_GET['date'] ?? date('Y-m-d');
            $deptWhere = $filterDept ? " AND a.department = ?" : "";
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a INNER JOIN students s ON a.rfid = s.rfid WHERE MONTH(a.timestamp) = MONTH(?) AND YEAR(a.timestamp) = YEAR(?) $deptWhere ORDER BY a.timestamp DESC";
            $stmt = $conn->prepare($sql);
            if ($filterDept) { $stmt->bind_param('sss', $date, $date, $filterDept); } else { $stmt->bind_param('ss', $date, $date); }
            $stmt->execute();
            $result = $stmt->get_result();
            break;
        case 'department':
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $dept = $_GET['dept'] ?? ($filterDept ?? '');
            
            $where = "";
            $params = [];
            $types = '';
            
            if ($dept || $filterDept) {
                $where .= " WHERE a.department = ?";
                $params[] = $dept ?: $filterDept;
                $types .= 's';
            }
            if ($dateFrom) {
                $where .= $where ? " AND DATE(a.timestamp) >= ?" : " WHERE DATE(a.timestamp) >= ?";
                $params[] = $dateFrom;
                $types .= 's';
            }
            if ($dateTo) {
                $where .= $where ? " AND DATE(a.timestamp) <= ?" : " WHERE DATE(a.timestamp) <= ?";
                $params[] = $dateTo;
                $types .= 's';
            }
            
            $sql = "SELECT a.name, s.roll_number, a.department, d.dept_name, a.status, a.timestamp 
                   FROM attendance a 
                   INNER JOIN students s ON a.rfid = s.rfid 
                   LEFT JOIN departments d ON a.department = d.dept_code 
                   $where 
                   ORDER BY a.timestamp DESC";
            $stmt = $conn->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            break;
        case 'student':
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $dept = $_GET['dept'] ?? ($filterDept ?? '');
            $student = $_GET['student'] ?? '';
            
            // Individual student attendance (detailed view)
            if ($student) {
                $where = "WHERE s.roll_number = ?";
                $params = [$student];
                $types = 's';
                
                if ($filterDept) {
                    $where .= " AND a.department = ?";
                    $params[] = $filterDept;
                    $types .= 's';
                }
                if ($dateFrom) {
                    $where .= " AND DATE(a.timestamp) >= ?";
                    $params[] = $dateFrom;
                    $types .= 's';
                }
                if ($dateTo) {
                    $where .= " AND DATE(a.timestamp) <= ?";
                    $params[] = $dateTo;
                    $types .= 's';
                }
                
                $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp 
                       FROM attendance a 
                       INNER JOIN students s ON a.rfid = s.rfid 
                       $where 
                       ORDER BY a.timestamp DESC";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            } 
            // Student summary (grouped by student)
            else {
                $where = "";
                $params = [];
                $types = '';
                
                if ($filterDept) {
                    $where .= " WHERE a.department = ?";
                    $params[] = $filterDept;
                    $types .= 's';
                }
                if ($dateFrom) {
                    $where .= $where ? " AND DATE(a.timestamp) >= ?" : " WHERE DATE(a.timestamp) >= ?";
                    $params[] = $dateFrom;
                    $types .= 's';
                }
                if ($dateTo) {
                    $where .= $where ? " AND DATE(a.timestamp) <= ?" : " WHERE DATE(a.timestamp) <= ?";
                    $params[] = $dateTo;
                    $types .= 's';
                }
                if ($dept && !$filterDept) {
                    $where .= $where ? " AND a.department = ?" : " WHERE a.department = ?";
                    $params[] = $dept;
                    $types .= 's';
                }
                
                $sql = "SELECT s.name, s.roll_number, a.department, COUNT(*) as total_attendance 
                       FROM attendance a 
                       INNER JOIN students s ON a.rfid = s.rfid 
                       $where 
                       GROUP BY s.roll_number, s.name, a.department 
                       ORDER BY s.name";
                
                if ($params) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $conn->query($sql);
                }
            }
            break;
        default:
            $deptWhere = $filterDept ? " WHERE a.department = ?" : "";
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a INNER JOIN students s ON a.rfid = s.rfid$deptWhere ORDER BY a.timestamp DESC LIMIT 100";
            $stmt = $conn->prepare($sql);
            if ($filterDept) $stmt->bind_param('s', $filterDept);
            $stmt->execute();
            $result = $stmt->get_result();
            break;
    }
    

    
    $data = [];
    if ($result) {
        while($row = $result->fetch_assoc()) { 
            $data[] = $row; 
        }
    } else {
        error_log("Query failed: " . $conn->error);
        die("<div style='padding: 20px; color: red; text-align: center;'><h3>Error: Database query failed. Please check the logs for details.</h3></div>");
    }
    
    if ($format == 'excel') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        if ($reportType == 'student' && !isset($_GET['student'])) {
            fputcsv($output, ['Name', 'Roll Number', 'Department', 'Total Attendance']);
            foreach($data as $row) {
                fputcsv($output, [$row['name'], $row['roll_number'], $row['department'], $row['total_attendance']]);
            }
        } else {
            fputcsv($output, ['Name', 'Roll Number', 'Department', 'Status', 'Timestamp']);
            foreach($data as $row) {
                fputcsv($output, [
                    $row['name'],
                    $row['roll_number'],
                    $row['department'],
                    $row['status'],
                    date('Y-m-d H:i:s', strtotime($row['timestamp'])) // IST
                ]);
            }
        }
        fclose($output);
        exit;
    } elseif ($format == 'pdf') {
        // Prepare chart data
        $chartData = [];
        if ($reportType == 'department') {
            $dept = $_GET['dept'] ?? ($filterDept ?? '');
            $chartDateFrom = $_GET['date_from'] ?? '';
            $chartDateTo = $_GET['date_to'] ?? '';
            $chartQuery = "SELECT a.status, COUNT(*) as count FROM attendance a INNER JOIN students s ON a.rfid = s.rfid";
            $chartWhere = [];
            if ($dept || $filterDept) {
                $chartWhere[] = "a.department = '" . $conn->real_escape_string($dept ?: $filterDept) . "'";
            }
            if ($chartDateFrom) {
                $chartWhere[] = "DATE(a.timestamp) >= '" . $conn->real_escape_string($chartDateFrom) . "'";
            }
            if ($chartDateTo) {
                $chartWhere[] = "DATE(a.timestamp) <= '" . $conn->real_escape_string($chartDateTo) . "'";
            }
            if ($chartWhere) {
                $chartQuery .= " WHERE " . implode(" AND ", $chartWhere);
            }
            $chartQuery .= " GROUP BY a.status";
            $chartResult = $conn->query($chartQuery);
            while($row = $chartResult->fetch_assoc()) {
                $chartData[] = $row;
            }
        } elseif (in_array($reportType, ['daily', 'weekly', 'monthly'])) {
            $chartQuery = "SELECT a.status, COUNT(*) as count FROM attendance a WHERE ";
            if ($reportType == 'daily') $chartQuery .= "DATE(a.timestamp) = CURDATE()";
            elseif ($reportType == 'weekly') $chartQuery .= "YEARWEEK(a.timestamp, 1) = YEARWEEK(CURDATE(), 1)";
            else $chartQuery .= "MONTH(a.timestamp) = MONTH(CURDATE())";
            if ($filterDept) {
                $chartQuery .= " AND a.department = '" . $conn->real_escape_string($filterDept) . "'";
            }
            $chartQuery .= " GROUP BY a.status";
            $chartResult = $conn->query($chartQuery);
            while($row = $chartResult->fetch_assoc()) {
                $chartData[] = $row;
            }
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?= ucfirst($reportType) ?> Report</title>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                h1 { color: #333; }
                .chart-container { width: 100%; max-width: 600px; margin: 20px auto; }
                .summary-stats { display: flex; gap: 20px; margin: 20px 0; }
                .stat-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; flex: 1; }
                .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; }
                .stat-label { color: #666; font-size: 0.9rem; }
            </style>
        </head>
        <body>
            <h1><?= ucfirst($reportType) ?> Attendance Report</h1>
            <p>Generated on: <?= date('d M Y h:i A') ?> IST</p>
            
            <?php if (!empty($chartData) && ($_GET['chart'] ?? '1') === '1'): ?>
            <div class="summary-stats">
                <?php 
                $totalRecords = array_sum(array_column($chartData, 'count'));
                if ($reportType == 'department') {
                    echo "<div class='stat-box'><div class='stat-number'>" . count($chartData) . "</div><div class='stat-label'>Departments</div></div>";
                    echo "<div class='stat-box'><div class='stat-number'>$totalRecords</div><div class='stat-label'>Total Records</div></div>";
                } else {
                    $inCount = 0; $outCount = 0;
                    foreach($chartData as $item) {
                        if($item['status'] == 'IN') $inCount = $item['count'];
                        if($item['status'] == 'OUT') $outCount = $item['count'];
                    }
                    echo "<div class='stat-box'><div class='stat-number'>$inCount</div><div class='stat-label'>IN Records</div></div>";
                    echo "<div class='stat-box'><div class='stat-number'>$outCount</div><div class='stat-label'>OUT Records</div></div>";
                    echo "<div class='stat-box'><div class='stat-number'>$totalRecords</div><div class='stat-label'>Total Records</div></div>";
                }
                ?>
            </div>
            
            <div class="chart-container">
                <canvas id="reportChart" width="400" height="200"></canvas>
            </div>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Roll Number</th>
                        <th>Department</th>
                        <?php if($reportType != 'student' || isset($_GET['student'])): ?>
                        <th>Status</th>
                        <th>Timestamp</th>
                        <?php else: ?>
                        <th>Total Attendance</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['roll_number']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <?php if($reportType != 'student' || isset($_GET['student'])): ?>
                        <td><?= $row['status'] ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($row['timestamp'])) ?> IST</td>
                        <?php else: ?>
                        <td><?= $row['total_attendance'] ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (!empty($chartData) && ($_GET['chart'] ?? '1') === '1'): ?>
            <script>
                const ctx = document.getElementById('reportChart').getContext('2d');
                const chartData = <?= json_encode($chartData) ?>;
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.map(item => item.dept_name || item.status + ' Status'),
                        datasets: [{
                            label: 'Records',
                            data: chartData.map(item => item.count),
                            backgroundColor: ['#667eea', '#764ba2', '#48bb78', '#38a169', '#ed8936', '#dd6b20', '#9f7aea', '#805ad5', '#38b2ac'],
                            borderColor: '#333',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: { display: true, text: '<?= ucfirst($reportType) ?> Report Chart' },
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Number of Records' } },
                            x: { title: { display: true, text: 'Categories' } }
                        }
                    }
                });
                
                // Auto-print after chart loads
                setTimeout(() => window.print(), 1000);
            </script>
            <?php else: ?>
            <script>window.print();</script>
            <?php endif; ?>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
<?php
$pageStyles = '
.reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
.report-card { background: rgba(255,255,255,0.95); padding: 30px; border-radius: 20px; text-align: center; transition: all 0.3s ease; cursor: pointer; display: flex; flex-direction: column; }
.report-card .btn { margin-top: auto; align-self: center; }
.report-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
.report-icon { font-size: 3rem; margin-bottom: 20px; color: #667eea; }
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.modal-content { background: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 30px; border-radius: 20px; width: 400px; text-align: center; max-height: 90vh; overflow-y: auto; }
.modal-buttons { display: flex; gap: 15px; justify-content: center; margin-top: 20px; }
.form-group { margin-bottom: 15px; text-align: left; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
.form-group input, .form-group select { width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; }
.searchable-select { position: relative; }
.search-input { width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; margin-bottom: 5px; }
.select-dropdown { max-height: 200px; overflow-y: auto; border: 2px solid #e2e8f0; border-radius: 8px; background: white; }
.select-option { padding: 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
.select-option:hover { background: #f7fafc; }
.select-option.selected { background: #667eea; color: white; }
.status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
.status-in { background: #c6f6d5; color: #22543d; }
.status-out { background: #fed7d7; color: #742a2a; }
@media (max-width: 768px) { .reports-grid { grid-template-columns: 1fr; } }
';

include 'header.php';
?>

<div class="reports-grid">
    <div class="report-card">
        <div class="report-icon"><i class="fas fa-calendar-day"></i></div>
        <h3>Daily Report</h3>
        <p>Generate today's attendance report</p>
        <br>
        <button onclick="showFormatDialog('daily')" class="btn btn-primary"><i class="fas fa-download"></i> Generate</button>
    </div>
    <div class="report-card">
        <div class="report-icon"><i class="fas fa-calendar-week"></i></div>
        <h3>Weekly Report</h3>
        <p>Generate this week's attendance summary</p>
        <br>
        <button onclick="showFormatDialog('weekly')" class="btn btn-primary"><i class="fas fa-download"></i> Generate</button>
    </div>
    <div class="report-card">
        <div class="report-icon"><i class="fas fa-calendar-alt"></i></div>
        <h3>Monthly Report</h3>
        <p>Generate monthly attendance analysis</p>
        <br>
        <button onclick="showFormatDialog('monthly')" class="btn btn-primary"><i class="fas fa-download"></i> Generate</button>
    </div>
    <div class="report-card">
        <div class="report-icon"><i class="fas fa-building"></i></div>
        <h3>Department Report</h3>
        <p>Department-wise attendance breakdown</p>
        <br>
        <button onclick="showDeptDialog('department')" class="btn btn-primary"><i class="fas fa-download"></i> Generate</button>
    </div>
    <div class="report-card">
        <div class="report-icon"><i class="fas fa-users"></i></div>
        <h3>Student Report</h3>
        <p>Individual student attendance records</p>
        <br>
        <button onclick="showStudentDialog('student')" class="btn btn-primary"><i class="fas fa-download"></i> Generate</button>
    </div>
    <div class="report-card">
        <div class="report-icon"><i class="fas fa-envelope"></i></div>
        <h3>Email Report</h3>
        <p>Send attendance report via email</p>
        <br>
        <a href="send_email.php" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Email</a>
    </div>
</div>

<div id="formatModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-file-download"></i> Choose Download Format</h3>
        <p>Select the format for your report:</p>
        <div class="form-group">
            <label><input type="checkbox" id="includeChart" checked> Include Chart in PDF</label>
        </div>
        <div class="modal-buttons">
            <button onclick="generateReport('excel')" class="btn btn-primary"><i class="fas fa-file-excel"></i> Excel</button>
            <button onclick="generateReport('pdf')" class="btn btn-primary"><i class="fas fa-file-pdf"></i> PDF</button>
            <button onclick="closeModal()" class="btn" style="background: #e2e8f0; color: #555;">Cancel</button>
        </div>
    </div>
</div>

<div id="deptModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-building"></i> Department Report</h3>
        <div class="form-group">
            <label>Select Department:</label>
            <select id="deptSelect">
                <?php if (!$filterDept): ?>
                <option value="">All Departments</option>
                <?php endif; ?>
                <?php foreach($departments as $dept): ?>
                <?php if (!$filterDept || $dept === $filterDept): ?>
                <option value="<?= $dept ?>" <?= $filterDept === $dept ? 'selected' : '' ?>><?= htmlspecialchars($deptNames[$dept] ?? '') ?></option>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>From Date:</label>
            <input type="date" id="deptDateFrom">
        </div>
        <div class="form-group">
            <label>To Date:</label>
            <input type="date" id="deptDateTo">
        </div>
        <div class="modal-buttons">
            <button onclick="proceedWithDept()" class="btn btn-primary">Continue</button>
            <button onclick="closeModal()" class="btn" style="background: #e2e8f0; color: #555;">Cancel</button>
        </div>
    </div>
</div>

<div id="studentModal" class="modal">
    <div class="modal-content" style="width: 500px;">
        <h3><i class="fas fa-user"></i> Student Report</h3>
        <div class="form-group">
            <label>From Date:</label>
            <input type="date" id="studentDateFrom">
        </div>
        <div class="form-group">
            <label>To Date:</label>
            <input type="date" id="studentDateTo">
        </div>
        <div class="form-group">
            <label>Department:</label>
            <select id="studentDept">
                <?php if (!$filterDept): ?>
                <option value="">All Departments</option>
                <?php endif; ?>
                <?php foreach($departments as $dept): ?>
                <?php if (!$filterDept || $dept === $filterDept): ?>
                <option value="<?= $dept ?>" <?= $filterDept === $dept ? 'selected' : '' ?>><?= htmlspecialchars($deptNames[$dept] ?? '') ?></option>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Select Student:</label>
            <div class="searchable-select">
                <input type="text" id="studentSearch" class="search-input" placeholder="Search student by name..." onkeyup="searchStudents()">
                <select id="studentSelect" style="width: 100%;">
                    <option value="">All Students Summary</option>
                    <?php
                    $studentSql = "SELECT roll_number, name, department FROM students";
                    if ($filterDept) $studentSql .= " WHERE department = ?";
                    $studentSql .= " ORDER BY name";
                    $studentStmt = $conn->prepare($studentSql);
                    if ($filterDept) $studentStmt->bind_param("s", $filterDept);
                    $studentStmt->execute();
                    $students = $studentStmt->get_result();
                    while($student = $students->fetch_assoc()) {
                        $name = htmlspecialchars($student['name']);
                        $rn = htmlspecialchars($student['roll_number']);
                        $dept = htmlspecialchars($student['department']);
                        echo "<option value='{$rn}' data-dept='{$dept}'>{$name} ({$rn}) - {$dept}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="modal-buttons">
            <button onclick="proceedWithStudent()" class="btn btn-primary">Continue</button>
            <button onclick="closeModal()" class="btn" style="background: #e2e8f0; color: #555;">Cancel</button>
        </div>
    </div>
</div>

<?php
$pageScripts = '
let selectedReport = "";

function showFormatDialog(reportType) {
    selectedReport = reportType;
    document.getElementById("formatModal").style.display = "block";
}
function closeModal() {
    document.getElementById("formatModal").style.display = "none";
    document.getElementById("deptModal").style.display = "none";
    document.getElementById("studentModal").style.display = "none";
}
function showDeptDialog(reportType) {
    selectedReport = reportType;
    document.getElementById("deptModal").style.display = "block";
}
function showStudentDialog(reportType) {
    selectedReport = reportType;
    document.getElementById("studentModal").style.display = "block";
}
function proceedWithDept() {
    const dept = document.getElementById("deptSelect").value;
    const dateFrom = document.getElementById("deptDateFrom").value;
    const dateTo = document.getElementById("deptDateTo").value;
    let params = [];
    if (dateFrom) params.push(`date_from=${encodeURIComponent(dateFrom)}`);
    if (dateTo) params.push(`date_to=${encodeURIComponent(dateTo)}`);
    if (dept) params.push(`dept=${encodeURIComponent(dept)}`);
    selectedReport = params.length ? `department&${params.join("&")}` : "department";
    closeModal();
    showFormatDialog(selectedReport);
}
function proceedWithStudent() {
    const dateFrom = document.getElementById("studentDateFrom").value;
    const dateTo = document.getElementById("studentDateTo").value;
    const dept = document.getElementById("studentDept").value;
    const student = document.getElementById("studentSelect").value;
    let params = [];
    if (dateFrom) params.push(`date_from=${encodeURIComponent(dateFrom)}`);
    if (dateTo) params.push(`date_to=${encodeURIComponent(dateTo)}`);
    if (dept) params.push(`dept=${encodeURIComponent(dept)}`);
    if (student) params.push(`student=${encodeURIComponent(student)}`);
    selectedReport = params.length ? `student&${params.join("&")}` : "student";
    closeModal();
    showFormatDialog(selectedReport);
}
function filterStudents() {
    const deptFilter = document.getElementById("studentDept").value;
    const sel = document.getElementById("studentSelect");
    for (let i = 1; i < sel.options.length; i++) {
        sel.options[i].style.display = !deptFilter || sel.options[i].getAttribute("data-dept") === deptFilter ? "block" : "none";
    }
}
function searchStudents() {
    const term = document.getElementById("studentSearch").value.toLowerCase();
    const sel = document.getElementById("studentSelect");
    for (let i = 1; i < sel.options.length; i++) {
        sel.options[i].style.display = sel.options[i].textContent.toLowerCase().includes(term) ? "block" : "none";
    }
}
function generateReport(format) {
    let url = `?report=${encodeURIComponent(selectedReport)}&format=${format}`;
    if (format === "pdf") url += `&chart=${document.getElementById("includeChart").checked ? "1" : "0"}`;
    if (format === "excel") { window.location.href = url; } else { window.open(url, "_blank").focus(); }
    closeModal();
}
document.addEventListener("DOMContentLoaded", function() {
    const ds = document.getElementById("studentDept");
    if (ds) ds.addEventListener("change", function() { filterStudents(); document.getElementById("studentSearch").value = ""; searchStudents(); });
    const sm = document.getElementById("studentModal");
    if (sm) new MutationObserver(function(m) { m.forEach(function(mut) { if (mut.type === "attributes" && mut.attributeName === "style" && sm.style.display === "block") document.getElementById("studentSearch").value = ""; }); }).observe(sm, { attributes: true });
});
window.onclick = function(e) { ["formatModal","deptModal","studentModal"].forEach(function(id) { if (e.target == document.getElementById(id)) closeModal(); }); };
';

include 'footer.php';
