<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
include 'rbac_helper.php';

if ($_POST) {
    verify_csrf();
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        $stmt = $conn->prepare("SELECT password FROM users WHERE name = ?");
        $stmt->bind_param("s", $_SESSION['user']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result && password_verify($current, $result['password']) && $new === $confirm && strlen($new) >= 8) {
            $hashed = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, first_login = FALSE WHERE name = ?");
            $stmt->bind_param("ss", $hashed, $_SESSION['user']);
            if ($stmt->execute()) $success = "Password updated successfully!";
        } else {
            $error = "Invalid current password or passwords don't match or too short!";
        }
    }
    
    if (isset($_POST['add_user'])) {
        $name = trim($_POST['name']);
        $dept = trim($_POST['dept']);
        $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("INSERT INTO users (name, dept, password, role, first_login) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->bind_param("ssss", $name, $dept, $hashed, $role);
        if ($stmt->execute()) $success = "User added successfully!";
        else $error = "Error adding user!";
    }
}

// Get all departments for dropdown
$deptResult = $conn->query("SELECT dept_code, dept_name FROM departments WHERE status = 'active' ORDER BY dept_name");

$activePage = 'settings';
$pageTitle = 'System Settings';
$pageSubtitle = 'Manage system configuration and user accounts';
$pageStyles = '.settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; }
.form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
input, select { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; }
input:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
@media (max-width: 1024px) { .settings-grid { grid-template-columns: 1fr; } }';
include 'header.php';
?>
        
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
                    <?= csrf_token() ?>
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
                    <?= csrf_token() ?>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="dept" required>
                            <option value="">Select Department</option>
                            <?php while ($row = $deptResult->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['dept_code']) ?>"><?= htmlspecialchars($row['dept_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
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
                            <option value="hod">HOD (Head of Department)</option>
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
<?php include 'footer.php'; ?>
