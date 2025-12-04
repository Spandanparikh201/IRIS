<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user_role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

include 'db_connect.php';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
    $stmt->bind_param("ii", $user_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully'); window.location.href='manage_users.php';</script>";
    }
}

// Get all users
$result = $conn->query("SELECT * FROM users ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - I.R.I.S</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 30px; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .logo { font-size: 2rem; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .btn { padding: 10px 20px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-danger { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; }
        .btn:hover { transform: translateY(-2px); }
        .table-container { overflow-x: auto; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: white; }
        th { background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%); color: white; padding: 20px 16px; text-align: left; font-weight: 600; }
        td { padding: 16px; border-bottom: 1px solid #e2e8f0; }
        tr:hover td { background-color: #f7fafc; }
        .role-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .role-admin { background: #fed7d7; color: #742a2a; }
        .role-teacher { background: #bee3f8; color: #2a4365; }
        .role-staff { background: #c6f6d5; color: #22543d; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-first { background: #fef5e7; color: #d69e2e; }
        .status-active { background: #c6f6d5; color: #22543d; }
        .back-link { color: #667eea; text-decoration: none; font-weight: 500; margin-top: 20px; display: inline-block; }
        .back-link:hover { color: #764ba2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="logo">I.R.I.S</div>
                <h2>User Management</h2>
            </div>
            <a href="create_user.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create New User
            </a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Password</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['dept']) ?></td>
                        <td><span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                        <td><code><?= $user['password'] ?></code></td>
                        <td>
                            <span class="status-badge status-<?= $user['first_login'] ? 'first' : 'active' ?>">
                                <?= $user['first_login'] ? 'First Login' : 'Active' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=<?= $user['id'] ?>" class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            <?php else: ?>
                                <span style="color: #666;">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>