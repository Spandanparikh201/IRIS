<?php
session_start();
// 🔧 TIMEZONE FIX (PHP)
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if user has access to reports (admin and teacher only)
if (!in_array($_SESSION['user_role'], ['admin', 'teacher'])) {
    echo "<script>alert('Access denied. You do not have permission to view reports.'); window.location.href='dashboard.php';</script>";
    exit();
}

include 'db_connect.php';
// 🔧 TIMEZONE FIX (MySQL session)
$conn->query("SET time_zone = '+05:30'");

// Handle AJAX preview requests
if (isset($_GET['preview'])) {
    $reportType = $_GET['report'] ?? 'daily';
    $chartData = [];
    
    switch($reportType) {
        case 'daily':
            $result = $conn->query("SELECT status, COUNT(*) as count FROM attendance WHERE DATE(timestamp) = CURDATE() GROUP BY status");
            break;
        case 'weekly':
            $result = $conn->query("SELECT status, COUNT(*) as count FROM attendance WHERE YEARWEEK(timestamp, 1) = YEARWEEK(CURDATE(), 1) GROUP BY status");
            break;
        case 'monthly':
            $result = $conn->query("SELECT status, COUNT(*) as count FROM attendance WHERE MONTH(timestamp) = MONTH(CURDATE()) GROUP BY status");
            break;
        case 'department':
            $result = $conn->query("SELECT d.dept_name, COUNT(a.id) as count FROM departments d LEFT JOIN attendance a ON d.dept_code = a.department GROUP BY d.id ORDER BY d.dept_name");
            break;
        default:
            $result = $conn->query("SELECT status, COUNT(*) as count FROM attendance GROUP BY status LIMIT 10");
    }
    
    while($row = $result->fetch_assoc()) {
        $chartData[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($chartData);
    exit;
}

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
    $reportType = $_GET['report'];
    $format = $_GET['format'];
    
    switch($reportType) {
        case 'daily':
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a LEFT JOIN students s ON a.rfid = s.rfid WHERE DATE(a.timestamp) = CURDATE() ORDER BY a.timestamp DESC";
            break;
        case 'weekly':
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a LEFT JOIN students s ON a.rfid = s.rfid WHERE YEARWEEK(a.timestamp, 1) = YEARWEEK(CURDATE(), 1) ORDER BY a.timestamp DESC";
            break;
        case 'monthly':
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a LEFT JOIN students s ON a.rfid = s.rfid WHERE MONTH(a.timestamp) = MONTH(CURDATE()) ORDER BY a.timestamp DESC";
            break;
        case 'department':
            $dept = $_GET['dept'] ?? '';
            if ($dept) {
                $sql = "SELECT a.name, s.roll_number, a.department, d.dept_name, a.status, a.timestamp 
                       FROM attendance a 
                       LEFT JOIN students s ON a.rfid = s.rfid 
                       LEFT JOIN departments d ON a.department = d.dept_code 
                       WHERE a.department = ? 
                       ORDER BY a.timestamp DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $dept);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $sql = "SELECT a.name, s.roll_number, a.department, d.dept_name, a.status, a.timestamp 
                       FROM attendance a 
                       LEFT JOIN students s ON a.rfid = s.rfid 
                       LEFT JOIN departments d ON a.department = d.dept_code 
                       ORDER BY d.dept_name, a.timestamp DESC";
                $result = $conn->query($sql);
            }
            break;
        case 'student':
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $dept = $_GET['dept'] ?? '';
            $student = $_GET['student'] ?? '';
            $where = [];
            $params = [];
            $types = '';
            
            if ($dateFrom) {
                $where[] = "DATE(a.timestamp) >= ?";
                $params[] = $dateFrom;
                $types .= 's';
            }
            if ($dateTo) {
                $where[] = "DATE(a.timestamp) <= ?";
                $params[] = $dateTo;
                $types .= 's';
            }
            if ($dept) {
                $where[] = "a.department = ?";
                $params[] = $dept;
                $types .= 's';
            }
            
            if ($student) {
                $where[] = "s.roll_number = ?";
                $params[] = $student;
                $types .= 's';
                $whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
                $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a LEFT JOIN students s ON a.rfid = s.rfid$whereClause ORDER BY a.timestamp DESC";
            } else {
                $whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
                $sql = "SELECT a.name, s.roll_number, a.department, COUNT(*) as total_attendance FROM attendance a LEFT JOIN students s ON a.rfid = s.rfid$whereClause GROUP BY a.rfid ORDER BY a.name";
            }
            
            if ($params) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql);
            }
            break;
        case 'custom':
        default:
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $dept = $_GET['dept'] ?? '';
            $student = $_GET['student'] ?? '';
            $status = $_GET['status'] ?? '';
            $where = [];
            $params = [];
            $types = '';
            
            if ($dateFrom) {
                $where[] = "DATE(a.timestamp) >= ?";
                $params[] = $dateFrom;
                $types .= 's';
            }
            if ($dateTo) {
                $where[] = "DATE(a.timestamp) <= ?";
                $params[] = $dateTo;
                $types .= 's';
            }
            if ($dept) {
                $where[] = "a.department = ?";
                $params[] = $dept;
                $types .= 's';
            }
            if ($student) {
                $where[] = "s.roll_number = ?";
                $params[] = $student;
                $types .= 's';
            }
            if ($status) {
                $where[] = "a.status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            $whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp FROM attendance a LEFT JOIN students s ON a.rfid = s.rfid$whereClause ORDER BY a.timestamp DESC LIMIT 100";
            
            if ($params) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql);
            }
            break;
    }
    
    // Handle result based on report type
    if (!isset($result)) {
        $result = $conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $conn->error . "<br>Query: " . $sql);
        }
    }
    
    $data = [];
    while($row = $result->fetch_assoc()) { $data[] = $row; }
    
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
            $chartQuery = "SELECT d.dept_name, COUNT(a.id) as count FROM departments d LEFT JOIN attendance a ON d.dept_code = a.department GROUP BY d.id ORDER BY d.dept_name";
            $chartResult = $conn->query($chartQuery);
            while($row = $chartResult->fetch_assoc()) {
                $chartData[] = $row;
            }
        } elseif (in_array($reportType, ['daily', 'weekly', 'monthly'])) {
            $chartQuery = "SELECT a.status, COUNT(*) as count FROM attendance a WHERE ";
            if ($reportType == 'daily') $chartQuery .= "DATE(a.timestamp) = CURDATE()";
            elseif ($reportType == 'weekly') $chartQuery .= "YEARWEEK(a.timestamp, 1) = YEARWEEK(CURDATE(), 1)";
            else $chartQuery .= "MONTH(a.timestamp) = MONTH(CURDATE())";
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
            
            <?php if (!empty($chartData)): ?>
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
            
            <?php if (!empty($chartData)): ?>
            <script>
                const ctx = document.getElementById('reportChart').getContext('2d');
                const chartData = <?= json_encode($chartData) ?>;
                
                <?php if ($reportType == 'department'): ?>
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.map(item => item.dept_name),
                        datasets: [{
                            label: 'Attendance Records',
                            data: chartData.map(item => item.count),
                            backgroundColor: ['#667eea', '#764ba2', '#48bb78', '#38a169', '#ed8936', '#dd6b20', '#9f7aea', '#805ad5', '#38b2ac'],
                            borderColor: '#333',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: { display: true, text: 'Department-wise Attendance' },
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Number of Records' } },
                            x: { title: { display: true, text: 'Departments' } }
                        }
                    }
                });
                <?php else: ?>
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.map(item => item.status + ' Status'),
                        datasets: [{
                            data: chartData.map(item => item.count),
                            backgroundColor: ['#48bb78', '#ed8936'],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: { display: true, text: '<?= ucfirst($reportType) ?> Attendance Distribution' },
                            legend: { position: 'bottom' }
                        }
                    }
                });
                <?php endif; ?>
                
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - I.R.I.S</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100vh; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 30px 0; box-shadow: 5px 0 20px rgba(0,0,0,0.1); z-index: 1000; transition: transform 0.3s ease; }
        .sidebar.collapsed { transform: translateX(-220px); width: 60px; }
        .sidebar.collapsed .logo h1, .sidebar.collapsed .logo p, .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 15px; }
        .logo { text-align: center; padding: 0 30px 30px; border-bottom: 1px solid rgba(0,0,0,0.1); margin-bottom: 30px; }
        .logo h1 { font-size: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 5px; }
        .logo p { color: #666; font-size: 0.9rem; }
        .nav-menu { list-style: none; padding: 0 20px; }
        .nav-item { margin-bottom: 10px; }
        .nav-link { display: flex; align-items: center; padding: 15px 20px; color: #555; text-decoration: none; border-radius: 15px; transition: all 0.3s ease; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateX(5px); }
        .nav-link i { margin-right: 12px; width: 20px; min-width: 20px; }
        .nav-link span { transition: opacity 0.3s ease; }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 60px; }
        .toggle-btn { position: fixed; top: 20px; left: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 1001; transition: all 0.3s ease; }
        .toggle-btn:hover { transform: scale(1.1); }
        .header { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 25px 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header-title h2 { color: #333; font-size: 2rem; margin-bottom: 5px; }
        .header-title p { color: #666; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem; }
        .logout-btn { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 500; transition: all 0.3s ease; text-decoration: none; }
        .logout-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,107,107,0.4); }
        .card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.3); }
        .reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .report-card { background: rgba(255,255,255,0.95); padding: 30px; border-radius: 20px; text-align: center; transition: all 0.3s ease; cursor: pointer; }
        .report-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
        .report-icon { font-size: 3rem; margin-bottom: 20px; color: #667eea; }
        .btn { padding: 12px 24px; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.3); }
        .table-container { overflow-x: auto; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: white; }
        th { background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%); color: white; padding: 15px 12px; text-align: left; font-weight: 600; font-size: 0.9rem; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        tr:hover td { background-color: #f7fafc; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-in { background: #c6f6d5; color: #22543d; }
        .status-out { background: #fed7d7; color: #742a2a; }
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
        @media (max-width: 1024px) { .sidebar { transform: translateX(-100%); } .sidebar.mobile-open { transform: translateX(0); } .main-content { margin-left: 0; } .header { flex-direction: column; gap: 20px; text-align: center; } .reports-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    
    <div class="sidebar" id="sidebar">
        <div class="logo"><h1>I.R.I.S</h1><p>Dashboard</p></div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="add_student.php" class="nav-link"><i class="fas fa-users"></i><span>Students</span></a></li>
            <li class="nav-item"><a href="attendance.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link active"><i class="fas fa-chart-pie"></i><span>Reports</span></a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        </ul>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2>Reports & Analytics</h2>
                <p>Generate and download comprehensive reports</p>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user'], 0, 1)) ?></div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
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
                <div class="report-icon"><i class="fas fa-file-excel"></i></div>
                <h3>Custom Report</h3>
                <p>Generate custom date range reports</p>
                <br>
<button onclick="showCustomDialog('custom')" class="btn btn-primary"><i class="fas fa-download"></i> Generate</button>
            </div>
        </div>
        
        <!-- Chart Preview Section -->
        <div class="card" id="chartPreview" style="display: none;">
            <h3>📊 Report Preview</h3>
            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <canvas id="previewChart" width="400" height="200"></canvas>
                </div>
                <div style="flex: 1;">
                    <div id="chartStats"></div>
                </div>
            </div>
        </div>

    </div>
    
    <div id="formatModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-file-download"></i> Choose Download Format</h3>
            <p>Select the format for your report:</p>
            <div class="modal-buttons">
                <button onclick="generateReport('excel')" class="btn btn-primary">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button onclick="generateReport('pdf')" class="btn btn-primary">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button onclick="closeModal()" class="btn" style="background: #e2e8f0; color: #555;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    
    <div id="deptModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-building"></i> Department Report</h3>
            <div class="form-group">
                <label>Select Department:</label>
                <select id="deptSelect">
                    <option value="">All Departments</option>
                    <?php foreach($departments as $dept): ?>
                    <option value="<?= $dept ?>"><?= $deptNames[$dept] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-buttons">
                <button onclick="proceedWithDept()" class="btn btn-primary">Continue</button>
                <button onclick="closeModal()" class="btn" style="background: #e2e8f0; color: #555;">Cancel</button>
            </div>
        </div>
    </div>
    
    <div id="customModal" class="modal">
        <div class="modal-content" style="width: 500px;">
            <h3><i class="fas fa-calendar-alt"></i> Custom Report</h3>
            <div class="form-group">
                <label>From Date:</label>
                <input type="date" id="dateFrom">
            </div>
            <div class="form-group">
                <label>To Date:</label>
                <input type="date" id="dateTo">
            </div>
            <div class="form-group">
                <label>Department:</label>
                <select id="customDept">
                    <option value="">All Departments</option>
                    <?php foreach($departments as $dept): ?>
                    <option value="<?= $dept ?>"><?= $deptNames[$dept] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Student:</label>
                <div class="searchable-select">
                    <input type="text" id="customStudentSearch" class="search-input" placeholder="Search student by name..." onkeyup="searchCustomStudents()">
                    <select id="customStudent">
                        <option value="">All Students</option>
                        <?php
                        $students = $conn->query("SELECT roll_number, name, department FROM students ORDER BY name");
                        while($student = $students->fetch_assoc()) {
                            echo "<option value='{$student['roll_number']}' data-dept='{$student['department']}'>{$student['name']} ({$student['roll_number']}) - {$student['department']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Status:</label>
                <select id="customStatus">
                    <option value="">All Status</option>
                    <option value="IN">IN</option>
                    <option value="OUT">OUT</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button onclick="proceedWithCustom()" class="btn btn-primary">Continue</button>
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
                    <option value="">All Departments</option>
                    <?php foreach($departments as $dept): ?>
                    <option value="<?= $dept ?>"><?= $deptNames[$dept] ?></option>
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
                        $students = $conn->query("SELECT roll_number, name, department FROM students ORDER BY name");
                        while($student = $students->fetch_assoc()) {
                            echo "<option value='{$student['roll_number']}' data-dept='{$student['department']}'>{$student['name']} ({$student['roll_number']}) - {$student['department']}</option>";
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
    
    <script>
        let selectedReport = '';
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            if (window.innerWidth <= 1024) sidebar.classList.toggle('mobile-open');
        }
        
        function showFormatDialog(reportType) {
            selectedReport = reportType;
            loadChartPreview(reportType);
            document.getElementById('formatModal').style.display = 'block';
        }
        
        function loadChartPreview(reportType) {
            fetch(`?preview=1&report=${reportType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        showChart(data, reportType);
                        document.getElementById('chartPreview').style.display = 'block';
                    }
                })
                .catch(error => console.log('Preview not available'));
        }
        
        function showChart(data, reportType) {
            const ctx = document.getElementById('previewChart').getContext('2d');
            
            if (window.previewChartInstance) {
                window.previewChartInstance.destroy();
            }
            
            if (reportType === 'department') {
                window.previewChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(item => item.dept_name || item.department),
                        datasets: [{
                            label: 'Records',
                            data: data.map(item => item.count),
                            backgroundColor: '#667eea'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { title: { display: true, text: 'Department-wise Data' } }
                    }
                });
            } else {
                window.previewChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.map(item => item.status + ' Status'),
                        datasets: [{
                            data: data.map(item => item.count),
                            backgroundColor: ['#48bb78', '#ed8936']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { title: { display: true, text: `${reportType} Distribution` } }
                    }
                });
            }
            
            // Update stats
            const total = data.reduce((sum, item) => sum + parseInt(item.count), 0);
            document.getElementById('chartStats').innerHTML = `
                <h4>📊 Statistics</h4>
                <p><strong>Total Records:</strong> ${total}</p>
                <p><strong>Categories:</strong> ${data.length}</p>
            `;
        }
        
        function closeModal() {
            document.getElementById('formatModal').style.display = 'none';
            document.getElementById('deptModal').style.display = 'none';
            document.getElementById('customModal').style.display = 'none';
            document.getElementById('studentModal').style.display = 'none';
        }
        
        function showDeptDialog(reportType) {
            selectedReport = reportType;
            document.getElementById('deptModal').style.display = 'block';
        }
        
        function showCustomDialog(reportType) {
            selectedReport = reportType;
            document.getElementById('customModal').style.display = 'block';
        }
        
        function showStudentDialog(reportType) {
            selectedReport = reportType;
            document.getElementById('studentModal').style.display = 'block';
        }
        
        function proceedWithDept() {
            const dept = document.getElementById('deptSelect').value;
            selectedReport = dept ? `department&dept=${encodeURIComponent(dept)}` : 'department';
            closeModal();
            showFormatDialog('');
        }
        
        function proceedWithCustom() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const dept = document.getElementById('customDept').value;
            const student = document.getElementById('customStudent').value;
            const status = document.getElementById('customStatus').value;
            let params = [];
            if (dateFrom) params.push(`date_from=${encodeURIComponent(dateFrom)}`);
            if (dateTo) params.push(`date_to=${encodeURIComponent(dateTo)}`);
            if (dept) params.push(`dept=${encodeURIComponent(dept)}`);
            if (student) params.push(`student=${encodeURIComponent(student)}`);
            if (status) params.push(`status=${encodeURIComponent(status)}`);
            selectedReport = params.length ? `custom&${params.join('&')}` : 'custom';
            closeModal();
            showFormatDialog('');
        }
        
        function proceedWithStudent() {
            const dateFrom = document.getElementById('studentDateFrom').value;
            const dateTo = document.getElementById('studentDateTo').value;
            const dept = document.getElementById('studentDept').value;
            const student = document.getElementById('studentSelect').value;
            let params = [];
            if (dateFrom) params.push(`date_from=${encodeURIComponent(dateFrom)}`);
            if (dateTo) params.push(`date_to=${encodeURIComponent(dateTo)}`);
            if (dept) params.push(`dept=${encodeURIComponent(dept)}`);
            if (student) params.push(`student=${encodeURIComponent(student)}`);
            selectedReport = params.length ? `student&${params.join('&')}` : 'student';
            closeModal();
            showFormatDialog('');
        }
        
        function filterStudents() {
            const deptFilter = document.getElementById('studentDept').value;
            const studentSelect = document.getElementById('studentSelect');
            const options = studentSelect.getElementsByTagName('option');
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                const optionDept = option.getAttribute('data-dept');
                
                if (!deptFilter || optionDept === deptFilter) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        }
        
        function searchStudents() {
            const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
            const studentSelect = document.getElementById('studentSelect');
            const options = studentSelect.getElementsByTagName('option');
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                const optionText = option.textContent.toLowerCase();
                
                if (optionText.includes(searchTerm)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        }
        
        function searchCustomStudents() {
            const searchTerm = document.getElementById('customStudentSearch').value.toLowerCase();
            const studentSelect = document.getElementById('customStudent');
            const options = studentSelect.getElementsByTagName('option');
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                const optionText = option.textContent.toLowerCase();
                
                if (optionText.includes(searchTerm)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            }
        }
        
        function generateReport(format) {
            let url = `?report=${selectedReport}&format=${format}`;
            if (format === 'excel') {
                window.location.href = url;
            } else {
                const printWindow = window.open(url, '_blank');
                printWindow.focus();
            }
            closeModal();
        }
        
        // Add event listeners for department filters and search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const studentDeptSelect = document.getElementById('studentDept');
            if (studentDeptSelect) {
                studentDeptSelect.addEventListener('change', function() {
                    filterStudents();
                    // Clear search when department changes
                    document.getElementById('studentSearch').value = '';
                    searchStudents();
                });
            }
            
            // Clear search when student modal opens
            const studentModal = document.getElementById('studentModal');
            if (studentModal) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            if (studentModal.style.display === 'block') {
                                document.getElementById('studentSearch').value = '';
                                document.getElementById('customStudentSearch').value = '';
                            }
                        }
                    });
                });
                observer.observe(studentModal, { attributes: true });
            }
        });
        
        window.onclick = function(event) {
            const modals = ['formatModal', 'deptModal', 'customModal', 'studentModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    closeModal();
                }
            });
        }
    </script>
</body>
</html>