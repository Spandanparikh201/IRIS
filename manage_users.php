<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

include 'db_connect.php';
include 'rbac_helper.php';

// Handle user deletion via POST with CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    verify_csrf();
    $user_id = (int)$_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
    $stmt->bind_param("ii", $user_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully'); window.location.href='manage_users.php';</script>";
    }
}

// Get all users
$result = $conn->query("SELECT * FROM users ORDER BY name");

$activePage = 'manage_users';
$pageTitle = 'User Management';
$pageSubtitle = 'Manage system users and their roles';
$pageStyles = '.role-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
.role-admin { background: #fed7d7; color: #742a2a; }
.role-hod { background: #e9d8fd; color: #6b21a8; }
.role-teacher { background: #bee3f8; color: #2a4365; }
.role-staff { background: #c6f6d5; color: #22543d; }

.status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.status-first { background: #fef5e7; color: #d69e2e; }
table { table-layout: fixed; }
table th, table td { width: 16.66%; text-align: center; }
table th:first-child, table td:first-child { width: 8%; }
table th:last-child, table td:last-child { width: 18%; }';
include 'header.php';
?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 1.5rem;"><i class="fas fa-users"></i> All Users</h3>
                <a href="create_user.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create New User</a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Role</th>
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
                            <td>
                                <span class="status-badge status-<?= $user['first_login'] ? 'first' : 'active' ?>">
                                    <?= $user['first_login'] ? 'First Login' : 'Active' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        <?= csrf_token() ?>
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #666;">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php include 'footer.php'; ?>
