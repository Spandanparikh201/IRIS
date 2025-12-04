<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

if ($_POST) {
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        $stmt = $conn->prepare("SELECT password FROM users WHERE name = ?");
        $stmt->bind_param("s", $_SESSION['user']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($current === $result['password'] && $new === $confirm) {
            $stmt = $conn->prepare("UPDATE users SET password = ?, first_login = FALSE WHERE name = ?");
            $stmt->bind_param("ss", $new, $_SESSION['user']);
            if ($stmt->execute()) $success = "Password updated successfully!";
        } else {
            $error = "Invalid current password or passwords don't match!";
        }
    }
    
    if (isset($_POST['add_user'])) {
        $name = $_POST['name'];
        $dept = $_POST['dept'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("INSERT INTO users (name, dept, password, role, first_login) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->bind_param("ssss", $name, $dept, $password, $role);
        if ($stmt->execute()) $success = "User added successfully!";
        else $error = "Error adding user!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - I.R.I.S</title>
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
        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
        .form-group label { font-weight: 600; color: #555; font-size: 0.9rem; }
        input, select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; }
        input:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn { padding: 12px 24px; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.3); }
        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 1024px) { .sidebar { transform: translateX(-100%); } .sidebar.mobile-open { transform: translateX(0); } .main-content { margin-left: 0; } .header { flex-direction: column; gap: 20px; text-align: center; } .settings-grid { grid-template-columns: 1fr; } }
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
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Reports</span></a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link active"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            <?php if ($_SESSION['user_role'] == 'admin'): ?>
            <li class="nav-item"><a href="manage_users.php" class="nav-link"><i class="fas fa-users-cog"></i><span>Manage Users</span></a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2>System Settings</h2>
                <p>Manage system configuration and user accounts</p>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user'], 0, 1)) ?></div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <div class="settings-grid">
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-key"></i> Change Password</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                </form>
            </div>
            
            <?php if ($_SESSION['user_role'] == 'admin'): ?>
            <div class="card">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-user-plus"></i> Add New User</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="dept" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-database"></i> System Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <strong>Total Students:</strong><br>
                    <?= $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?>
                </div>
                <div>
                    <strong>Total Users:</strong><br>
                    <?= $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?>
                </div>
                <div>
                    <strong>Total Records:</strong><br>
                    <?= $conn->query("SELECT COUNT(*) FROM attendance")->fetch_row()[0] ?>
                </div>
                <div>
                    <strong>System Version:</strong><br>
                    I.R.I.S v1.0
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            if (window.innerWidth <= 1024) sidebar.classList.toggle('mobile-open');
        }
    </script>
</body>
</html>