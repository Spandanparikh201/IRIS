<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if ($_POST) {
    if (isset($_POST['single_add'])) {
        $name = $_POST['name'];
        $roll_number = $_POST['roll_number'];
        $department = $_POST['department'];
        $email = $_POST['email'];
        $rfid = $_POST['rfid'];
        
        $stmt = $conn->prepare("INSERT INTO students (name, roll_number, department, email, rfid) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $roll_number, $department, $email, $rfid);
        
        if ($stmt->execute()) {
            $success = "Student added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if (isset($_POST['bulk_upload']) && isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($file, "r")) !== FALSE) {
            $count = 0;
            fgetcsv($handle); // Skip header row
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $stmt = $conn->prepare("INSERT INTO students (name, roll_number, department, email, rfid) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $data[0], $data[1], $data[2], $data[3], $data[4]);
                if ($stmt->execute()) $count++;
            }
            fclose($handle);
            $success = "$count students uploaded successfully!";
        } else {
            $error = "Error reading CSV file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - I.R.I.S</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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
        
        .logo p { color: #666; font-size: 0.9rem; }
        
        .nav-menu { list-style: none; padding: 0 20px; }
        .nav-item { margin-bottom: 10px; }
        
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
        
        .nav-link span { transition: opacity 0.3s ease; }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded { margin-left: 60px; }
        
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
        
        .toggle-btn:hover { transform: scale(1.1); }
        
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
        
        .header-title p { color: #666; }
        
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
            text-decoration: none;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.4);
        }
        
        .card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
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
            padding: 15px 20px;
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
            padding: 15px 30px;
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .header { flex-direction: column; gap: 20px; text-align: center; }
            .form-grid { grid-template-columns: 1fr; }
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
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="add_student.php" class="nav-link active">
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
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2>Add New Student</h2>
                <p>Register a new student in the system</p>
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
        
        <div class="card">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                <button onclick="showSingleForm()" class="btn btn-primary" id="singleBtn">
                    <i class="fas fa-user-plus"></i> Single Add
                </button>
                <button onclick="showBulkForm()" class="btn" id="bulkBtn" style="background: #e2e8f0; color: #555;">
                    <i class="fas fa-upload"></i> Bulk Upload
                </button>
            </div>
            
            <form method="POST" id="singleForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="roll_number"><i class="fas fa-id-badge"></i> Roll Number</label>
                        <input type="text" id="roll_number" name="roll_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department"><i class="fas fa-building"></i> Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="CE">Computer Engineering</option>
                            <option value="IT">Information Technology</option>
                            <option value="ME">Mechanical Engineering</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rfid"><i class="fas fa-id-card"></i> RFID UID</label>
                        <input type="text" id="rfid" name="rfid" required>
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" name="single_add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Student
                    </button>
                </div>
            </form>
            
            <form method="POST" enctype="multipart/form-data" id="bulkForm" style="display: none;">
                <div class="form-group">
                    <label for="csv_file"><i class="fas fa-file-csv"></i> CSV File</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    <small style="color: #666; margin-top: 5px; display: block;">CSV format: name,roll_number,department,email,rfid</small>
                    <a href="#" onclick="downloadTemplate()" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
                
                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" name="bulk_upload" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Students
                    </button>
                </div>
            </form>
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
        
        function showSingleForm() {
            document.getElementById('singleForm').style.display = 'block';
            document.getElementById('bulkForm').style.display = 'none';
            document.getElementById('singleBtn').style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            document.getElementById('singleBtn').style.color = 'white';
            document.getElementById('bulkBtn').style.background = '#e2e8f0';
            document.getElementById('bulkBtn').style.color = '#555';
        }
        
        function showBulkForm() {
            document.getElementById('singleForm').style.display = 'none';
            document.getElementById('bulkForm').style.display = 'block';
            document.getElementById('bulkBtn').style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            document.getElementById('bulkBtn').style.color = 'white';
            document.getElementById('singleBtn').style.background = '#e2e8f0';
            document.getElementById('singleBtn').style.color = '#555';
        }
        
        function downloadTemplate() {
            const csv = 'name,roll_number,department,email,rfid\nJohn Doe,2021001,CE,john.doe@college.edu,ABC123DEF\nJane Smith,2021002,IT,jane.smith@college.edu,DEF456GHI';
            const blob = new Blob([csv], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'students_template.csv';
            link.click();
        }
    </script>
</body>
</html>