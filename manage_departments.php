<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
include 'rbac_helper.php';

$conn->query("SET time_zone = '+05:30'");

// Handle form submissions
if ($_POST) {
    verify_csrf();
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

$activePage = 'manage_departments';
$pageTitle = 'Manage Departments';
$pageSubtitle = '';
$pageStyles = '.status-active { background: #c6f6d5; color: #22543d; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; }
.status-inactive { background: #fed7d7; color: #742a2a; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; }
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.modal-content { background: white; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); padding: 30px; border-radius: 20px; width: 600px; max-height: 90vh; overflow-y: auto; }';
include 'header.php';
?>
        
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 1.5rem;">📊 Department Overview</h3>
                <button onclick="showAddModal()" class="btn btn-primary"><i class="fas fa-plus"></i> Add Department</button>
            </div>
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
                        if ($result):
                        while ($row = $result->fetch_assoc()) {
                            $dc = htmlspecialchars($row['dept_code'], ENT_QUOTES);
                            $dn = htmlspecialchars($row['dept_name'], ENT_QUOTES);
                            $dh = htmlspecialchars($row['dept_head'] ?? '', ENT_QUOTES);
                            $ce = htmlspecialchars($row['contact_email'] ?? '', ENT_QUOTES);
                            $cp = htmlspecialchars($row['contact_phone'] ?? '', ENT_QUOTES);
                            $bl = htmlspecialchars($row['building'] ?? '', ENT_QUOTES);
                            $fl = htmlspecialchars($row['floor_number'] ?? '', ENT_QUOTES);
                            $st = htmlspecialchars($row['status'] ?? '', ENT_QUOTES);
                            $sc = htmlspecialchars($row['student_count'] ?? '0', ENT_QUOTES);
                            echo "<tr>
                                <td><strong>$dc</strong></td>
                                <td>$dn</td>
                                <td>$dh</td>
                                <td><span class='status-active'>$sc Students</span></td>
                                <td>$ce<br><small>$cp</small></td>
                                <td>$bl - Floor $fl</td>
                                <td><span class='status-$st'>" . ucfirst($st) . "</span></td>
                                <td>
                                    <button onclick='editDept(" . htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") . ")' class='btn btn-success' style='padding: 8px 12px; font-size: 0.9rem;'>
                                        <i class='fas fa-edit'></i> Edit
                                    </button>
                                </td>
                            </tr>";
                        }
                        else:
                        echo "<tr><td colspan='8' style='text-align:center;padding:30px;color:#999;'>No departments found.</td></tr>";
                        endif;
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
                <?= csrf_token() ?>
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
                <?= csrf_token() ?>
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
    
<?php $pageScripts = '
function showAddModal() { document.getElementById("addModal").style.display = "block"; }
function editDept(dept) {
    document.getElementById("edit_dept_id").value = dept.id;
    document.getElementById("edit_dept_code").value = dept.dept_code;
    document.getElementById("edit_dept_name").value = dept.dept_name;
    document.getElementById("edit_dept_head").value = dept.dept_head || "";
    document.getElementById("edit_contact_email").value = dept.contact_email || "";
    document.getElementById("edit_contact_phone").value = dept.contact_phone || "";
    document.getElementById("edit_building").value = dept.building || "";
    document.getElementById("edit_floor_number").value = dept.floor_number || "";
    document.getElementById("edit_status").value = dept.status;
    document.getElementById("editModal").style.display = "block";
}
function closeModal() {
    document.getElementById("addModal").style.display = "none";
    document.getElementById("editModal").style.display = "none";
}
window.onclick = function(event) {
    if (event.target == document.getElementById("addModal") || event.target == document.getElementById("editModal")) closeModal();
};
'; ?>
<?php include 'footer.php'; ?>


