<?php
if (!isset($pageTitle)) $pageTitle = 'I.R.I.S';
if (!isset($pageSubtitle)) $pageSubtitle = '';
$active = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - I.R.I.S</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100vh; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 30px 0; box-shadow: 5px 0 20px rgba(0,0,0,0.1); z-index: 1000; transition: width 0.3s ease; overflow-y: auto; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar.collapsed { width: 72px; }
        .sidebar.collapsed .logo { padding: 0 0 30px; }
        .sidebar.collapsed .logo h1, .sidebar.collapsed .logo p { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 15px 0; }
        .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .nav-link i { margin-right: 0; }
        .sidebar.collapsed .nav-menu { padding: 0 10px; }
        .logo { text-align: center; padding: 0 30px 30px; border-bottom: 1px solid rgba(0,0,0,0.1); margin-bottom: 30px; white-space: nowrap; overflow: hidden; }
        .logo h1 { font-size: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 5px; }
        .logo p { color: #666; font-size: 0.9rem; }
        .nav-menu { list-style: none; padding: 0 20px; }
        .nav-item { margin-bottom: 10px; }
        .nav-link { display: flex; align-items: center; justify-content: flex-start; padding: 15px 20px; color: #555; text-decoration: none; border-radius: 15px; transition: all 0.3s ease; font-weight: 500; white-space: nowrap; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateX(5px); }
        .sidebar.collapsed .nav-link:hover { transform: none; }
        .nav-link i { margin-right: 12px; width: 20px; min-width: 20px; text-align: center; }
        .nav-link span { transition: opacity 0.3s ease; }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 72px; }
        .toggle-btn { position: fixed; top: 20px; left: 262px; width: 36px; height: 36px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: 2px solid rgba(255,255,255,0.8); border-radius: 50%; cursor: pointer; z-index: 1001; display: flex; align-items: center; justify-content: center; transition: left 0.3s ease, transform 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .toggle-btn i { transition: transform 0.3s ease; font-size: 0.85rem; }
        .toggle-btn:hover { transform: scale(1.1); }
        .header { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 25px 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header-title h2 { color: #333; font-size: 2rem; margin-bottom: 5px; }
        .header-title p { color: #666; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem; }
        .logout-btn { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 500; transition: all 0.3s ease; text-decoration: none; }
        .logout-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,107,107,0.4); }
        .card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.3); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .form-control { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        input, select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; }
        input:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn { padding: 12px 24px; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.3); }
        .btn-success { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(72,187,120,0.3); }
        .btn-danger { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(255,107,107,0.4); }
        .btn-secondary { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(72,187,120,0.3); }
        .table-container { overflow-x: auto; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: white; }
        th { background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%); color: white; padding: 20px 16px; text-align: left; font-weight: 600; font-size: 0.9rem; }
        td { padding: 16px; border-bottom: 1px solid #e2e8f0; }
        tr:hover td { background-color: #f7fafc; }
        .row { display: flex; gap: 20px; margin-bottom: 20px; }
        .col-md-6 { flex: 1; }
        .col-md-4 { flex: 1; }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { color: #22543d; background: #c6f6d5; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .error { color: #742a2a; background: #fed7d7; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        @media (max-width: 1024px) { .sidebar { width: 280px; transform: translateX(-100%); } .sidebar.mobile-open { transform: translateX(0); } .sidebar.collapsed { width: 280px; } .main-content { margin-left: 0; } .main-content.expanded { margin-left: 0; } .header { flex-direction: column; gap: 20px; text-align: center; } .row { flex-direction: column; } }
        <?= $pageStyles ?? '' ?>
    </style>
</head>
<body>
    <button class="toggle-btn" onclick="toggleSidebar()" id="toggleBtn"><i class="fas fa-chevron-left"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="logo"><h1>I.R.I.S</h1><p>Dashboard</p></div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link<?= $active === 'dashboard' ? ' active' : '' ?>"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="add_student.php" class="nav-link<?= $active === 'add_student' ? ' active' : '' ?>"><i class="fas fa-user-plus"></i><span>Add Student</span></a></li>
            <li class="nav-item"><a href="view_students.php" class="nav-link<?= $active === 'view_students' ? ' active' : '' ?>"><i class="fas fa-list"></i><span>View Students</span></a></li>
            <li class="nav-item"><a href="attendance.php" class="nav-link<?= $active === 'attendance' ? ' active' : '' ?>"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
            <li class="nav-item"><a href="mark_attendance.php" class="nav-link<?= $active === 'mark_attendance' ? ' active' : '' ?>"><i class="fas fa-pen"></i><span>Mark Attendance</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link<?= $active === 'reports' ? ' active' : '' ?>"><i class="fas fa-chart-pie"></i><span>Reports</span></a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link<?= $active === 'settings' ? ' active' : '' ?>"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item"><a href="manage_users.php" class="nav-link<?= $active === 'manage_users' ? ' active' : '' ?>"><i class="fas fa-users-cog"></i><span>Manage Users</span></a></li>
            <li class="nav-item"><a href="manage_departments.php" class="nav-link<?= $active === 'manage_departments' ? ' active' : '' ?>"><i class="fas fa-building"></i><span>Departments</span></a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2><?= htmlspecialchars($pageTitle) ?></h2>
                <p><?= htmlspecialchars($pageSubtitle) ?></p>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user'] ?? '?', 0, 1)) ?></div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
