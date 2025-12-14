<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Only admin can manage departments
if ($_SESSION['user_role'] !== 'admin') {
    echo "<script>alert('Access denied. Only administrators can manage departments.'); window.location.href='dashboard.php';</script>";
    exit();
}

include 'db_connect.php';
$conn->query("SET time_zone = '+05:30'");

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_dept'])) {
        $stmt = $conn->prepare("INSERT INTO departments (dept_code, dept_name, dept_head, contact_email, contact_phone, building, floor_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $_POST['dept_code'], $_POST['dept_name'], $_POST['dept_head'], $_POST['contact_email'], $_POST['contact_phone'], $_POST['building'], $_POST['floor_number']);
        if ($stmt->execute()) {
            echo "<script>alert('Department added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
    
    if (isset($_POST['update_dept'])) {
        $stmt = $conn->prepare("UPDATE departments SET dept_name=?, dept_head=?, contact_email=?, contact_phone=?, building=?, floor_number=?, status=? WHERE id=?");
        $stmt->bind_param("sssssssi", $_POST['dept_name'], $_POST['dept_head'], $_POST['contact_email'], $_POST['contact_phone'], $_POST['building'], $_POST['floor_number'], $_POST['status'], $_POST['dept_id']);
        if ($stmt->execute()) {
            echo "<script>alert('Department updated successfully!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - I.R.I.S</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100vh; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 30px 0; box-shadow: 5px 0 20px rgba(0,0,0,0.1); z-index: 1000; }
        .logo { text-align: center; padding: 0 30px 30px; border-bottom: 1px solid rgba(0,0,0,0.1); margin-bottom: 30px; }
        .logo h1 { font-size: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 5px; }
        .nav-menu { list-style: none; padding: 0 20px; }
        .nav-item { margin-bottom: 10px; }
        .nav-link { display: flex; align-items: center; padding: 15px 20px; color: #555; text-decoration: none; border-radius: 15px; transition: all 0.3s ease; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateX(5px); }
        .nav-link i { margin-right: 12px; width: 20px; }
        .main-content { margin-left: 280px; padding: 30px; }
        .header { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 25px 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn { padding: 12px 24px; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .form-control { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #667eea; }
        .table-responsive { overflow-x: auto; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: white; }
        th { background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%); color: white; padding: 15px 12px; text-align: left; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        tr:hover td { background-color: #f7fafc; }
        .status-active { background: #c6f6d5; color: #22543d; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; }
        .status-inactive { background: #fed7d7; color: #742a2a; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; }
        .row { display: flex; gap: 20px; }
        .col-md-6 { flex: 1; }
        .col-md-4 { flex: 1; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 30px; border-radius: 20px; width: 600px; max-height: 90vh; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h1>I.R.I.S</h1><p>Dashboard</p></div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="add_student.php" class="nav-link"><i class="fas fa-users"></i><span>Students</span></a></li>
            <li class="nav-item"><a href="attendance.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Reports</span></a></li>
            <li class="nav-item"><a href="library.php" class="nav-link"><i class="fas fa-book"></i><span>Library</span></a></li>
            <li class="nav-item"><a href="manage_departments.php" class="nav-link active"><i class="fas fa-building"></i><span>Departments</span></a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div>
                <h2>🏢 Department Management</h2>
                <p>Manage departments and their information</p>
            </div>
            <button onclick="showAddModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Department
            </button>
        </div>
        
        <div class="card">
            <h3>📊 Department Overview</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Department Name</th>
                            <th>Head</th>
                            <th>Students</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT d.*, 
                                               (SELECT COUNT(*) FROM students s WHERE s.department = d.dept_code) as student_count 
                                               FROM departments d ORDER BY d.dept_name");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td><strong>{$row['dept_code']}</strong></td>
                                <td>{$row['dept_name']}</td>
                                <td>{$row['dept_head']}</td>
                                <td><span class='status-active'>{$row['student_count']} Students</span></td>
                                <td>{$row['contact_email']}<br><small>{$row['contact_phone']}</small></td>
                                <td>{$row['building']} - Floor {$row['floor_number']}</td>
                                <td><span class='status-{$row['status']}'>" . ucfirst($row['status']) . "</span></td>
                                <td>
                                    <button onclick='editDept(" . json_encode($row) . ")' class='btn btn-success' style='padding: 8px 12px; font-size: 0.9rem;'>
                                        <i class='fas fa-edit'></i> Edit
                                    </button>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Department Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-plus"></i> Add New Department</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department Code</label>
                            <input type="text" name="dept_code" class="form-control" required maxlength="10" placeholder="e.g., CSE">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" name="dept_name" class="form-control" required placeholder="e.g., Computer Science Engineering">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Department Head</label>
                    <input type="text" name="dept_head" class="form-control" placeholder="Dr. John Doe">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" placeholder="dept@college.edu">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" placeholder="+91 9876543210">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Building</label>
                            <input type="text" name="building" class="form-control" placeholder="Main Building">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Floor Number</label>
                            <input type="number" name="floor_number" class="form-control" min="0" max="20">
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                    <button type="submit" name="add_dept" class="btn btn-primary">Add Department</button>
                    <button type="button" onclick="closeModal()" class="btn" style="background: #e2e8f0; color: #555;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Department Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-edit"></i> Edit Department</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="dept_id" id="edit_dept_id">
                <div class="form-group">
                    <label>Department Code</label>
                    <input type="text" id="edit_dept_code" class="form-control" readonly style="background: #f7fafc;">
                </div>
                <div class="form-group">
                    <label>Department Name</label>
                    <input type="text" name="dept_name" id="edit_dept_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Department Head</label>
                    <input type="text" name="dept_head" id="edit_dept_head" class="form-control">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_email" id="edit_contact_email" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="contact_phone" id="edit_contact_phone" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Building</label>
                            <input type="text" name="building" id="edit_building" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Floor Number</label>
                            <input type="number" name="floor_number" id="edit_floor_number" class="form-control" min="0" max="20">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                    <button type="submit" name="update_dept" class="btn btn-success">Update Department</button>
                    <button type="button" onclick="closeModal()" class="btn" style="background: #e2e8f0; color: #555;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function editDept(dept) {
            document.getElementById('edit_dept_id').value = dept.id;
            document.getElementById('edit_dept_code').value = dept.dept_code;
            document.getElementById('edit_dept_name').value = dept.dept_name;
            document.getElementById('edit_dept_head').value = dept.dept_head || '';
            document.getElementById('edit_contact_email').value = dept.contact_email || '';
            document.getElementById('edit_contact_phone').value = dept.contact_phone || '';
            document.getElementById('edit_building').value = dept.building || '';
            document.getElementById('edit_floor_number').value = dept.floor_number || '';
            document.getElementById('edit_status').value = dept.status;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target == addModal) closeModal();
            if (event.target == editModal) closeModal();
        }
    </script>
</body>
</html>