<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Analytics - I.R.I.S</title>
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
        .chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; }
        .chart-container { height: 400px; }
        @media (max-width: 1024px) { .sidebar { transform: translateX(-100%); } .sidebar.mobile-open { transform: translateX(0); } .main-content { margin-left: 0; } .header { flex-direction: column; gap: 20px; text-align: center; } .chart-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    
    <div class="sidebar" id="sidebar">
        <div class="logo"><h1>I.R.I.S</h1><p>Dashboard</p></div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="add_student.php" class="nav-link"><i class="fas fa-users"></i><span>Students</span></a></li>
            <li class="nav-item"><a href="attendance.php" class="nav-link active"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Reports</span></a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        </ul>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2>Attendance Analytics</h2>
                <p>Visual representation of attendance data</p>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user'], 0, 1)) ?></div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="chart-grid">
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-pie"></i> Today's Attendance</h3>
                <div class="chart-container"><canvas id="todayChart"></canvas></div>
            </div>
            
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-bar"></i> Department Wise</h3>
                <div class="chart-container"><canvas id="departmentChart"></canvas></div>
            </div>
            
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-line"></i> Weekly Trend</h3>
                <div class="chart-container"><canvas id="weeklyChart"></canvas></div>
            </div>
            
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-clock"></i> Hourly Distribution</h3>
                <div class="chart-container"><canvas id="hourlyChart"></canvas></div>
            </div>
        </div>
    </div>
    
    <?php
    // Get chart data
    $todayIn = $conn->query("SELECT COUNT(DISTINCT rfid) FROM attendance WHERE DATE(timestamp) = CURDATE() AND status = 'IN'")->fetch_row()[0];
    $todayOut = $conn->query("SELECT COUNT(DISTINCT rfid) FROM attendance WHERE DATE(timestamp) = CURDATE() AND status = 'OUT'")->fetch_row()[0];
    
    $deptData = $conn->query("SELECT department, COUNT(*) as count FROM attendance WHERE DATE(timestamp) = CURDATE() GROUP BY department");
    $deptLabels = $deptCounts = [];
    while ($row = $deptData->fetch_assoc()) { $deptLabels[] = $row['department']; $deptCounts[] = $row['count']; }
    
    $weeklyData = $conn->query("SELECT DATE(timestamp) as date, COUNT(*) as count FROM attendance WHERE timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(timestamp) ORDER BY date");
    $weekLabels = $weekCounts = [];
    while ($row = $weeklyData->fetch_assoc()) { $weekLabels[] = date('M d', strtotime($row['date'])); $weekCounts[] = $row['count']; }
    
    $hourlyData = $conn->query("SELECT HOUR(timestamp) as hour, COUNT(*) as count FROM attendance WHERE DATE(timestamp) = CURDATE() GROUP BY HOUR(timestamp) ORDER BY hour");
    $hourLabels = $hourCounts = [];
    while ($row = $hourlyData->fetch_assoc()) { $hourLabels[] = $row['hour'] . ':00'; $hourCounts[] = $row['count']; }
    ?>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            if (window.innerWidth <= 1024) sidebar.classList.toggle('mobile-open');
        }
        
        // Today's Attendance Pie Chart
        new Chart(document.getElementById('todayChart'), {
            type: 'pie',
            data: {
                labels: ['IN', 'OUT'],
                datasets: [{
                    data: [<?= $todayIn ?>, <?= $todayOut ?>],
                    backgroundColor: ['#48bb78', '#f56565']
                }]
            }
        });
        
        // Department Bar Chart
        new Chart(document.getElementById('departmentChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($deptLabels) ?>,
                datasets: [{
                    label: 'Attendance Count',
                    data: <?= json_encode($deptCounts) ?>,
                    backgroundColor: '#667eea'
                }]
            }
        });
        
        // Weekly Line Chart
        new Chart(document.getElementById('weeklyChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($weekLabels) ?>,
                datasets: [{
                    label: 'Daily Attendance',
                    data: <?= json_encode($weekCounts) ?>,
                    borderColor: '#764ba2',
                    fill: false
                }]
            }
        });
        
        // Hourly Bar Chart
        new Chart(document.getElementById('hourlyChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($hourLabels) ?>,
                datasets: [{
                    label: 'Hourly Count',
                    data: <?= json_encode($hourCounts) ?>,
                    backgroundColor: '#38a169'
                }]
            }
        });
    </script>
</body>
</html>