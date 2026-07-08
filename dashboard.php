<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}

include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - I.R.I.S</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .stat-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.3); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
        .stat-icon { width: 70px; height: 70px; margin: 0 auto 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #333; margin-bottom: 10px; }
        .stat-label { color: #666; font-weight: 500; }
        .card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.3); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid rgba(0,0,0,0.1); }
        .card-title { font-size: 1.5rem; color: #333; font-weight: 600; }
        .activity-item { display: flex; align-items: flex-start; gap: 15px; padding: 15px; border-bottom: 1px solid #e2e8f0; }
        .activity-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; }
        .activity-content { flex: 1; }
        .activity-user { font-weight: 600; color: #333; }
        .activity-time { color: #999; font-size: 0.85rem; }
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
        .action-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 20px; border-radius: 15px; cursor: pointer; transition: all 0.3s ease; text-align: center; text-decoration: none; display: block; }
        .action-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(102,126,234,0.3); }
        .action-btn i { display: block; font-size: 2rem; margin-bottom: 10px; }
        @media (max-width: 1024px) { .sidebar { transform: translateX(-100%); } .sidebar.mobile-open { transform: translateX(0); } .main-content { margin-left: 0; } .header { flex-direction: column; gap: 20px; text-align: center; } }
    </style>
</head>
<body>
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    
    <div class="sidebar" id="sidebar">
        <div class="logo"><h1>I.R.I.S</h1><p>Dashboard</p></div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="add_student.php" class="nav-link"><i class="fas fa-users"></i><span>Students</span></a></li>
            <li class="nav-item"><a href="attendance.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Reports</span></a></li>
            <li class="nav-item"><a href="library.php" class="nav-link"><i class="fas fa-book"></i><span>Library</span></a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <li class="nav-item"><a href="manage_users.php" class="nav-link"><i class="fas fa-users-cog"></i><span>Manage Users</span></a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2>Welcome back, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
                <p>Intelligent RFID Identification System</p>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user'], 0, 1)) ?></div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <?php
        // Get statistics - ALL data using prepared statements
        $stmt = $conn->prepare("SELECT COUNT(*) FROM students");
        $stmt->execute();
        $totalStudents = $stmt->get_result()->fetch_row()[0];
        
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT department) FROM students");
        $stmt->execute();
        $totalDepartments = $stmt->get_result()->fetch_row()[0];
        
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT rfid) FROM attendance WHERE DATE(timestamp) = CURDATE() AND status = 'IN'");
        $stmt->execute();
        $todayPresent = $stmt->get_result()->fetch_row()[0];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE DATE(timestamp) = CURDATE()");
        $stmt->execute();
        $todayTotal = $stmt->get_result()->fetch_row()[0];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM books");
        $stmt->execute();
        $totalBooks = $stmt->get_result()->fetch_row()[0];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM book_transactions WHERE issue_date IS NOT NULL AND return_date IS NULL");
        $stmt->execute();
        $issuedBooks = $stmt->get_result()->fetch_row()[0];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $totalUsers = $stmt->get_result()->fetch_row()[0];
        
        $attendanceRate = $totalStudents > 0 ? round(($todayPresent / $totalStudents) * 100, 1) : 0;
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= $totalStudents ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-number"><?= $totalDepartments ?></div>
                <div class="stat-label">Departments</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number"><?= $todayPresent ?></div>
                <div class="stat-label">Present Today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-number"><?= $issuedBooks ?></div>
                <div class="stat-label">Books Issued</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number"><?= $attendanceRate ?>%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #805ad5 0%, #6b46c1 100%);">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            
            <div class="quick-actions">
                <a href="add_student.php" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    Add Student
                </a>
                <a href="attendance.php" class="action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    Mark Attendance
                </a>
                <a href="reports.php" class="action-btn">
                    <i class="fas fa-file-export"></i>
                    Generate Report
                </a>
                <a href="library.php" class="action-btn">
                    <i class="fas fa-book-plus"></i>
                    Issue Book
                </a>
                <a href="settings.php" class="action-btn">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Recent Activity</h3>
            </div>
            
            <div id="activityFeed">
                <?php
                // Get recent activity - using direct queries for static data
                $studentsQuery = "SELECT 'student' as type, name, CONCAT('Added new student: ', name) as action, NOW() as timestamp FROM students";
                $attendanceQuery = "SELECT 'attendance' as type, name, CONCAT('Marked attendance: ', status) as action, timestamp FROM attendance WHERE DATE(timestamp) = CURDATE()";
                
                $studentsResult = $conn->query($studentsQuery);
                $attendanceResult = $conn->query($attendanceQuery);
                
                // Combine results manually
                $allRows = [];
                if ($studentsResult && $studentsResult->num_rows > 0) {
                    while ($row = $studentsResult->fetch_assoc()) {
                        $allRows[] = $row;
                    }
                }
                if ($attendanceResult && $attendanceResult->num_rows > 0) {
                    while ($row = $attendanceResult->fetch_assoc()) {
                        $allRows[] = $row;
                    }
                }
                
                // Sort by timestamp descending and limit to 10
                usort($allRows, function($a, $b) {
                    return strcmp($b['timestamp'], $a['timestamp']);
                });
                $allRows = array_slice($allRows, 0, 10);
                
                if (!empty($allRows)) {
                    foreach ($allRows as $row) {
                        $iconColor = $row['type'] == 'student' ? '#667eea' : '#48bb78';
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: <?= $iconColor ?>;">
                                <i class="fas fa-<?= $row['type'] == 'student' ? 'user' : 'check-circle' ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-user"><?= htmlspecialchars($row['name']) ?></div>
                                <div><?= htmlspecialchars($row['action']) ?></div>
                                <div class="activity-time"><?= date('M d, Y h:i A', strtotime($row['timestamp'])) ?></div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p style="text-align: center; color: #999; padding: 20px;">No recent activity</p>';
                }
                ?>
            </div>
        </div>
    </div>
    
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
