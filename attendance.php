<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}
include 'db_connect.php';
include 'rbac_helper.php';

$activePage = 'attendance';
$pageTitle = 'Attendance Analytics';
$pageSubtitle = 'Visual representation of attendance data';
$pageStyles = '.filter-bar { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; background: rgba(255,255,255,0.95); padding: 20px 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
.filter-bar label { font-weight: 600; color: #333; }
.filter-bar input[type="date"] { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; }
.filter-bar .btn { padding: 8px 20px; }
.chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; }
.chart-container { height: 400px; }';

$selectedDate = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
    $selectedDate = date('Y-m-d');
}

$filterDept = getUserDept();
$showDeptChart = !$filterDept;

include 'header.php';
?>
        
        <div class="filter-bar">
            <label for="date"><i class="fas fa-calendar-alt"></i> Select Date:</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()" form="dateForm">
            <form method="GET" id="dateForm" style="display:inline;">
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;"><i class="fas fa-redo"></i> Today</button>
            </form>
        </div>
        
        <div class="chart-grid">
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-pie"></i> Attendance on <?= htmlspecialchars(date('M d, Y', strtotime($selectedDate))) ?></h3>
                <div class="chart-container"><canvas id="todayChart"></canvas></div>
            </div>
            
            <?php if ($showDeptChart): ?>
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-bar"></i> Department Wise</h3>
                <div class="chart-container"><canvas id="departmentChart"></canvas></div>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-line"></i> Weekly Trend</h3>
                <div class="chart-container"><canvas id="weeklyChart"></canvas></div>
            </div>
            
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-clock"></i> Hourly Distribution</h3>
                <div class="chart-container"><canvas id="hourlyChart"></canvas></div>
            </div>
    
    <?php
    $deptClause = $filterDept ? " AND department = ?" : "";

    $sql = "SELECT COUNT(DISTINCT rfid) FROM attendance WHERE DATE(timestamp) = ? AND status = 'IN'$deptClause";
    $stmt = $conn->prepare($sql);
    if ($filterDept) { $stmt->bind_param("ss", $selectedDate, $filterDept); } else { $stmt->bind_param("s", $selectedDate); }
    $stmt->execute();
    $todayIn = $stmt->get_result()->fetch_row()[0];
    
    $sql = "SELECT COUNT(DISTINCT rfid) FROM attendance WHERE DATE(timestamp) = ? AND status = 'OUT'$deptClause";
    $stmt = $conn->prepare($sql);
    if ($filterDept) { $stmt->bind_param("ss", $selectedDate, $filterDept); } else { $stmt->bind_param("s", $selectedDate); }
    $stmt->execute();
    $todayOut = $stmt->get_result()->fetch_row()[0];

    if ($showDeptChart) {
        $stmt = $conn->prepare("SELECT department, COUNT(*) as count FROM attendance WHERE DATE(timestamp) = ? GROUP BY department");
        $stmt->bind_param("s", $selectedDate);
        $stmt->execute();
        $deptData = $stmt->get_result();
        $deptLabels = $deptCounts = [];
        while ($row = $deptData->fetch_assoc()) { $deptLabels[] = $row['department']; $deptCounts[] = $row['count']; }
    }
    
    $sql = "SELECT DATE(timestamp) as date, COUNT(*) as count FROM attendance WHERE timestamp >= DATE_SUB(?, INTERVAL 6 DAY) AND timestamp < DATE_ADD(?, INTERVAL 1 DAY)$deptClause GROUP BY DATE(timestamp) ORDER BY date";
    $stmt = $conn->prepare($sql);
    if ($filterDept) { $stmt->bind_param("sss", $selectedDate, $selectedDate, $filterDept); } else { $stmt->bind_param("ss", $selectedDate, $selectedDate); }
    $stmt->execute();
    $weeklyData = $stmt->get_result();
    $weekLabels = $weekCounts = [];
    while ($row = $weeklyData->fetch_assoc()) { $weekLabels[] = date('M d', strtotime($row['date'])); $weekCounts[] = $row['count']; }
    
    $sql = "SELECT HOUR(timestamp) as hour, COUNT(*) as count FROM attendance WHERE DATE(timestamp) = ?$deptClause GROUP BY HOUR(timestamp) ORDER BY hour";
    $stmt = $conn->prepare($sql);
    if ($filterDept) { $stmt->bind_param("ss", $selectedDate, $filterDept); } else { $stmt->bind_param("s", $selectedDate); }
    $stmt->execute();
    $hourlyData = $stmt->get_result();
    $hourLabels = $hourCounts = [];
    while ($row = $hourlyData->fetch_assoc()) { $hourLabels[] = $row['hour'] . ':00'; $hourCounts[] = $row['count']; }
    ?>
    
<?php $pageScripts = '
        new Chart(document.getElementById("todayChart"), {
            type: "pie",
            data: {
                labels: ["IN", "OUT"],
                datasets: [{ data: [' . $todayIn . ', ' . $todayOut . '], backgroundColor: ["#48bb78", "#f56565"] }]
            }
        });
        ' . ($showDeptChart ? '
        new Chart(document.getElementById("departmentChart"), {
            type: "bar",
            data: {
                labels: ' . json_encode($deptLabels) . ',
                datasets: [{ label: "Attendance Count", data: ' . json_encode($deptCounts) . ', backgroundColor: "#667eea" }]
            }
        });
        ' : '') . '
        new Chart(document.getElementById("weeklyChart"), {
            type: "line",
            data: {
                labels: ' . json_encode($weekLabels) . ',
                datasets: [{ label: "Daily Attendance", data: ' . json_encode($weekCounts) . ', borderColor: "#764ba2", fill: false }]
            }
        });
        new Chart(document.getElementById("hourlyChart"), {
            type: "bar",
            data: {
                labels: ' . json_encode($hourLabels) . ',
                datasets: [{ label: "Hourly Count", data: ' . json_encode($hourCounts) . ', backgroundColor: "#38a169" }]
            }
        });
'; ?>
<?php include 'footer.php'; ?>
