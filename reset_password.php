<?php
session_start();
if (!isset($_SESSION['force_password_reset']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password && strlen($new_password) >= 6) {
        $stmt = $conn->prepare("UPDATE users SET password = ?, first_login = FALSE WHERE id = ?");
        $stmt->bind_param("si", $new_password, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            unset($_SESSION['force_password_reset']);
            echo "<script>alert('Password updated successfully!'); window.location.href='dashboard.php';</script>";
            exit();
        } else {
            $error = "Failed to update password";
        }
    } else {
        $error = "Passwords don't match or password too short (minimum 6 characters)";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - I.R.I.S</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-container { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 50px 40px; border-radius: 25px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); width: 100%; max-width: 400px; text-align: center; }
        .logo { font-size: 3rem; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 40px; font-size: 1.1rem; }
        .form-group { position: relative; margin-bottom: 25px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 15px 20px; border: 2px solid #e1e5e9; border-radius: 15px; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .reset-btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 15px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .reset-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(102,126,234,0.4); }
        .error { color: #e53e3e; margin-bottom: 20px; padding: 10px; background: #fed7d7; border-radius: 10px; }
        .warning { color: #d69e2e; margin-bottom: 20px; padding: 15px; background: #fef5e7; border-radius: 10px; border-left: 4px solid #d69e2e; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo">I.R.I.S</div>
        <p class="subtitle">First Time Login - Reset Password</p>
        
        <div class="warning">
            <strong>Security Notice:</strong> You must change your password before accessing the system.
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <button type="submit" class="reset-btn">Update Password</button>
        </form>
    </div>
</body>
</html>