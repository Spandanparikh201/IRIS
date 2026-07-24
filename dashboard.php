<?php
session_start();
header('Cache-Control: no-cache, must-revalidate');
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}

include 'db_connect.php';
include 'rbac_helper.php';
$conn->query("SET time_zone = '+05:30'");

$filterDept = getUserDept();

$activePage = 'dashboard';
$prefix = (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin') ? 'Prof. ' : '';
$pageTitle = 'Welcome back, ' . $prefix . htmlspecialchars($_SESSION['user']) . '!';
$pageSubtitle = 'Intelligent RFID Identification System';
$pageStyles = '.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 20px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.3); }
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
.stat-icon { width: 50px; height: 50px; margin: 0 auto 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; }
.stat-number { font-size: 1.6rem; font-weight: bold; color: #333; margin-bottom: 10px; }
.stat-label { color: #666; font-weight: 500; }
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
';
include 'header.php';
?>
        
        <?php
        $deptClause = $filterDept ? " WHERE department = ?" : "";

        $sql = "SELECT COUNT(*) FROM students$deptClause";
        $stmt = $conn->prepare($sql);
        if ($filterDept) { $stmt->bind_param("s", $filterDept); }
        $stmt->execute();
        $totalStudents = $stmt->get_result()->fetch_row()[0];
        
        $sql = "SELECT COUNT(DISTINCT department) FROM students$deptClause";
        $stmt = $conn->prepare($sql);
        if ($filterDept) { $stmt->bind_param("s", $filterDept); }
        $stmt->execute();
        $totalDepartments = $stmt->get_result()->fetch_row()[0];
        
        $attDeptClause = $filterDept ? " AND a.department = ?" : "";
        $sql = "SELECT COUNT(DISTINCT a.rfid) FROM attendance a JOIN students s ON a.rfid = s.rfid WHERE DATE(a.timestamp) = CURDATE() AND a.status = 'IN'$attDeptClause";
        $stmt = $conn->prepare($sql);
        if ($filterDept) { $stmt->bind_param("s", $filterDept); }
        $stmt->execute();
        $todayPresent = $stmt->get_result()->fetch_row()[0];
        
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
            
            <?php if (!$filterDept): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-number"><?= $totalDepartments ?></div>
                <div class="stat-label">Departments</div>
            </div>
            <?php endif; ?>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number"><?= $todayPresent ?></div>
                <div class="stat-label">Present Today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number"><?= $attendanceRate ?>%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
            
            <?php if (!$filterDept): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #805ad5 0%, #6b46c1 100%);">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            
            <div class="quick-actions">
                <a href="view_students.php" class="action-btn">
                    <i class="fas fa-list"></i>
                    View Students
                </a>
                <a href="add_student.php" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    Add Student
                </a>
                <a href="mark_attendance.php" class="action-btn">
                    <i class="fas fa-pen"></i>
                    Mark Attendance
                </a>
                <a href="attendance.php" class="action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    Analytics
                </a>
                <a href="reports.php" class="action-btn">
                    <i class="fas fa-file-export"></i>
                    Generate Report
                </a>
                <a href="settings.php" class="action-btn">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </div>
        </div>
        

        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Recent Attendance</h3>
            </div>
            
            <div id="activityFeed">
                <?php
                $attDeptWhere = $filterDept ? " WHERE s.department = ?" : "";
                $stmt = $conn->prepare("
                    SELECT a.id, s.name, s.department, s.roll_number, a.status, a.timestamp
                    FROM attendance a
                    JOIN students s ON a.rfid = s.rfid
                    $attDeptWhere
                    ORDER BY a.timestamp DESC
                    LIMIT 15
                ");
                if ($filterDept) $stmt->bind_param("s", $filterDept);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $isIn = $row['status'] === 'IN';
                        $iconColor = $isIn ? '#48bb78' : '#f56565';
                        $icon = $isIn ? 'sign-in-alt' : 'sign-out-alt';
                        $label = $isIn ? 'Checked In' : 'Checked Out';
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="background: <?= $iconColor ?>;">
                                <i class="fas fa-<?= $icon ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-user"><?= htmlspecialchars($row['name']) ?></div>
                                <div><?= $label ?> &middot; <?= htmlspecialchars($row['department']) ?> &middot; <?= htmlspecialchars($row['roll_number'] ?? '—') ?></div>
                                <div class="activity-time"><?= date('M d, Y h:i A', strtotime($row['timestamp'])) ?></div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p style="text-align: center; color: #999; padding: 20px;">No attendance records found</p>';
                }
                ?>
            </div>
        </div>
    </div>
<?php include 'footer.php'; ?>
