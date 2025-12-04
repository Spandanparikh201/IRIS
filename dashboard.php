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
    <title>I.R.I.S Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 30px 0;
            box-shadow: 5px 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-220px);
            width: 60px;
        }
        
        .sidebar.collapsed .logo h1,
        .sidebar.collapsed .logo p,
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 15px;
        }
        
        .logo {
            text-align: center;
            padding: 0 30px 30px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0 20px;
        }
        
        .nav-item {
            margin-bottom: 10px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #555;
            text-decoration: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link span {
            transition: opacity 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link i {
            margin-right: 12px;
            width: 20px;
            min-width: 20px;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 60px;
        }
        
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s ease;
        }
        
        .toggle-btn:hover {
            transform: scale(1.1);
        }
        
        .header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 25px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .header-title p {
            color: #666;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.4);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-weight: 500;
        }
        
        .card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.5rem;
            color: #333;
            font-weight: 600;
        }
        
        .filters {
            display: flex;
            gap: 20px;
            align-items: end;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }
        
        input, select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102,126,234,0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(72,187,120,0.3);
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            color: white;
            padding: 20px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s ease;
        }
        
        tr:hover td {
            background-color: #f7fafc;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-in {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-out {
            background: #fed7d7;
            color: #742a2a;
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <button class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <h1>I.R.I.S</h1>
            <p>Dashboard</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="add_student.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="attendance.php" class="nav-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Attendance</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link">
                    <i class="fas fa-chart-pie"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="library.php" class="nav-link">
                    <i class="fas fa-book"></i>
                    <span>Library</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <li class="nav-item">
                <a href="manage_users.php" class="nav-link">
                    <i class="fas fa-users-cog"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2>Welcome back, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
                <p><?= isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'User' ?> - <?= isset($_SESSION['user_dept']) ? htmlspecialchars($_SESSION['user_dept']) : 'General' ?> Department</p>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user'], 0, 1)) ?>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <?php
        // Get statistics
        $totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
        $todayPresent = $conn->query("SELECT COUNT(DISTINCT rfid) FROM attendance WHERE DATE(timestamp) = CURDATE() AND status = 'IN'")->fetch_row()[0];
        $todayTotal = $conn->query("SELECT COUNT(*) FROM attendance WHERE DATE(timestamp) = CURDATE()")->fetch_row()[0];
        $attendanceRate = $totalStudents > 0 ? round(($todayPresent / $totalStudents) * 100, 1) : 0;
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= $totalStudents ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number"><?= $todayPresent ?></div>
                <div class="stat-label">Present Today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number"><?= $attendanceRate ?>%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?= $todayTotal ?></div>
                <div class="stat-label">Today's Records</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filter Attendance
                </h3>
            </div>
            
            <form method="GET">
                <div class="filters">
                    <div class="form-group">
                        <label for="date"><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" name="date" value="<?= $_GET['date'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="department"><i class="fas fa-building"></i> Department</label>
                        <select name="department">
                            <option value="">All Departments</option>
                            <option value="CE" <?= ($_GET['department'] ?? '') == 'CE' ? 'selected' : '' ?>>Computer Engineering</option>
                            <option value="IT" <?= ($_GET['department'] ?? '') == 'IT' ? 'selected' : '' ?>>Information Technology</option>
                            <option value="ME" <?= ($_GET['department'] ?? '') == 'ME' ? 'selected' : '' ?>>Mechanical Engineering</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
        
        <?php
        $where = "1";
        $params = [];
        if (!empty($_GET['date'])) {
            $where .= " AND DATE(timestamp) = ?";
            $params[] = $_GET['date'];
        }
        if (!empty($_GET['department'])) {
            $where .= " AND a.department = ?";
            $params[] = $_GET['department'];
        }
        
        $sql = "SELECT a.name, a.rfid, a.status, a.timestamp, a.department, s.roll_number FROM attendance a LEFT JOIN students s ON a.rfid = s.rfid WHERE $where ORDER BY a.timestamp DESC LIMIT 50";
        $stmt = $conn->prepare($sql);
        if ($params) {
            $types = str_repeat("s", count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table"></i> Recent Attendance Records
                </h3>
                <div style="display: flex; gap: 10px;">
                    <a href="send_email.php" class="btn btn-secondary">
                        <i class="fas fa-envelope"></i> Send Report
                    </a>
                    <button onclick="exportTableToCSV('attendance.csv')" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>
            
            <div class="table-container">
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Name</th>
                            <th><i class="fas fa-id-badge"></i> Roll No</th>
                            <th><i class="fas fa-id-card"></i> RFID</th>
                            <th><i class="fas fa-check-circle"></i> Status</th>
                            <th><i class="fas fa-clock"></i> Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['roll_number']) ?></td>
                            <td><?= htmlspecialchars($row['rfid']) ?></td>
                            <td><span class="status-badge status-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
                            <td><?= date('M d, Y h:i A', strtotime($row['timestamp'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function exportTableToCSV(filename) {
            let csv = [];
            const rows = document.querySelectorAll("table tr");
            for (let row of rows) {
                const cols = row.querySelectorAll("td, th");
                const rowData = Array.from(cols).map(col => `"${col.innerText}"`);
                csv.push(rowData.join(","));
            }
            const blob = new Blob([csv.join("\n")], { type: 'text/csv' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.click();
        }
    </script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            if (window.innerWidth <= 1024) {
                sidebar.classList.toggle('mobile-open');
            }
        }
    </script>
</body>
</html>