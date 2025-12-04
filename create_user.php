<?php
include 'db_connect.php';

function generatePassword($length = 8) {
    return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $dept = $_POST['dept'];
    $role = $_POST['role'];
    $password = generatePassword();
    
    $stmt = $conn->prepare("INSERT INTO users (name, dept, role, password, first_login) VALUES (?, ?, ?, ?, TRUE)");
    $stmt->bind_param("ssss", $name, $dept, $role, $password);
    
    if ($stmt->execute()) {
        $success = "User created successfully! Password: " . $password;
    } else {
        $error = "Error creating user: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - I.R.I.S</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 40px; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 2.5rem; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 12px 16px; border: 2px solid #e1e5e9; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.3); }
        .success { color: #22543d; background: #c6f6d5; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .error { color: #742a2a; background: #fed7d7; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .back-link { display: inline-block; margin-top: 20px; color: #667eea; text-decoration: none; font-weight: 500; }
        .back-link:hover { color: #764ba2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">I.R.I.S</div>
            <h2>Create New User</h2>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="dept">Department</label>
                <input type="text" id="dept" name="dept" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Create User</button>
        </form>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>