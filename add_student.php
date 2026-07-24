<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}

include 'db_connect.php';
include 'rbac_helper.php';

if ($_POST) {
    verify_csrf();
    if (isset($_POST['single_add'])) {
        $name = $_POST['name'];
        $year = $_POST['year'];
        $department = $_POST['department'];
        $roll_no = $_POST['roll_no'];
        $email = $_POST['email'];
        $rfid = $_POST['rfid'];
        
        // Auto-generate roll number: 23CE198
        $roll_number = $year . $department . str_pad($roll_no, 3, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO students (name, roll_number, department, email, rfid) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $roll_number, $department, $email, $rfid);
        
        if ($stmt->execute()) {
            $success = "Student added successfully! Roll Number: " . $roll_number;
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
}
$curYear = date('y');
$curMonth = date('n');
$acadYear = $curMonth >= 6 ? $curYear : $curYear - 1;
$activePage = 'add_student';
$pageTitle = 'Add New Student';
$pageSubtitle = 'Register a new student in the system';
$pageStyles = '.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
input, select { padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; }
input:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
.btn { padding: 15px 30px; }
@media (max-width: 1024px) { .form-grid { grid-template-columns: 1fr; } }';
include 'header.php';
?>
        
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
                <a href="bulk_upload.php" class="btn" id="bulkBtn" style="background: #e2e8f0; color: #555;">
                    <i class="fas fa-upload"></i> Bulk Upload
                </a>
            </div>
            
            <form method="POST" id="singleForm">
                <?= csrf_token() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="year"><i class="fas fa-calendar"></i> Year of Admission</label>
                        <select id="year" name="year" required>
                            <option value="">Select Year</option>
                            <option value="26" <?= $acadYear == 26 ? 'selected' : '' ?>>2026-27 (26)</option>
                            <option value="25" <?= $acadYear == 25 ? 'selected' : '' ?>>2025-26 (25)</option>
                            <option value="24" <?= $acadYear == 24 ? 'selected' : '' ?>>2024-25 (24)</option>
                            <option value="23" <?= $acadYear == 23 ? 'selected' : '' ?>>2023-24 (23)</option>
                            <option value="22" <?= $acadYear == 22 ? 'selected' : '' ?>>2022-23 (22)</option>
                            <option value="21" <?= $acadYear == 21 ? 'selected' : '' ?>>2021-22 (21)</option>
                            <option value="20" <?= $acadYear == 20 ? 'selected' : '' ?>>2020-21 (20)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="department"><i class="fas fa-building"></i> Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="CE">Computer Engineering (CE)</option>
                            <option value="IT">Information Technology (IT)</option>
                            <option value="ME">Mechanical Engineering (ME)</option>
                            <option value="EE">Electrical Engineering (EE)</option>
                            <option value="EC">Electronics & Communication (EC)</option>
                            <option value="CV">Civil Engineering (CV)</option>
                            <option value="AI">Artificial Intelligence (AI)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="roll_no"><i class="fas fa-id-badge"></i> Roll No. (1-999)</label>
                        <input type="number" id="roll_no" name="roll_no" min="1" max="999" required placeholder="e.g., 198">
                        <small style="color: #666; margin-top: 5px;">Auto-generated: <strong id="generatedRoll" style="color: #667eea; font-weight: bold;"></strong></small>
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
            

        </div>
    </div>
<?php $pageScripts = '
function showSingleForm() {
    document.getElementById("singleForm").style.display = "block";
    document.getElementById("singleBtn").style.background = "linear-gradient(135deg, #667eea 0%, #764ba2 100%)";
    document.getElementById("singleBtn").style.color = "white";
    document.getElementById("bulkBtn").style.background = "#e2e8f0";
    document.getElementById("bulkBtn").style.color = "#555";
}
function downloadTemplate() {
    const csv = "name,year,department,roll_no,email,rfid\nJohn Doe,24,CE,198,john.doe@college.edu,ABC123DEF\nJane Smith,23,IT,184,jane.smith@college.edu,DEF456GHI";
    const blob = new Blob([csv], { type: "text/csv" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "students_template.csv";
    link.click();
}
function generateRollNumber() {
    const year = document.getElementById("year").value;
    const department = document.getElementById("department").value;
    const roll_no = document.getElementById("roll_no").value;
    if (year && department && roll_no) {
        document.getElementById("generatedRoll").textContent = year + department + roll_no.padStart(3, "0");
    } else {
        document.getElementById("generatedRoll").textContent = "";
    }
}
document.getElementById("year").addEventListener("change", generateRollNumber);
document.getElementById("department").addEventListener("change", generateRollNumber);
document.getElementById("roll_no").addEventListener("input", generateRollNumber);
'; ?>
<?php include 'footer.php'; ?>