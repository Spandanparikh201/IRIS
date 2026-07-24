<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user_role'] != 'admin') {
    header("Location: dashboard.php");
    exit(0);
}

include 'db_connect.php';
include 'rbac_helper.php';

function generatePassword($length = 12) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();
    $name = trim($_POST['name']);
    $dept = trim($_POST['dept']);
    $role = $_POST['role'];
    $rawPassword = generatePassword();
    $hashedPassword = password_hash($rawPassword, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, dept, role, password, first_login) VALUES (?, ?, ?, ?, TRUE)");
    $stmt->bind_param("ssss", $name, $dept, $role, $hashedPassword);
    
    if ($stmt->execute()) {
        $success = "User created successfully! Password: " . htmlspecialchars($rawPassword);
    } else {
        $error = "Error creating user.";
    }
}

// Get all departments for dropdown
$deptResult = $conn->query("SELECT dept_code, dept_name FROM departments WHERE status = 'active' ORDER BY dept_name");

$activePage = 'create_user';
$pageTitle = 'Create New User';
$pageSubtitle = 'Register a new system user';
include 'header.php';
?>
        
        <div class="card">
            <?php if (isset($success)): ?>
                <div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <?= csrf_token() ?>
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="dept"><i class="fas fa-building"></i> Department</label>
                    <select id="dept" name="dept" required>
                        <option value="">Select Department</option>
                        <?php while ($row = $deptResult->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['dept_code']) ?>"><?= htmlspecialchars($row['dept_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="role"><i class="fas fa-id-badge"></i> Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin (Full Access)</option>
                        <option value="hod">HOD (Department Head)</option>
                        <option value="teacher">Teacher</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                
                <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Create User</button>
            </form>
        </div>
    </div>
    
<?php include 'footer.php'; ?>
