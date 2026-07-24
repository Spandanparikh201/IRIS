<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}
include 'db_connect.php';
include 'rbac_helper.php';

// Handle deletion
if ($_POST && isset($_POST['delete_student'])) {
    verify_csrf();
    $id = (int)$_POST['student_id'];
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('Student deleted successfully!'); window.location.href='view_students.php';</script>";
    exit;
}

$search = trim($_GET['search'] ?? '');
$filterDept = getUserDept();

$activePage = 'view_students';
$pageTitle = 'View Students';
$pageSubtitle = 'Browse and manage student records';
$pageStyles = '.search-bar { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
.search-bar input { flex: 1; min-width: 200px; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; }
.search-bar input:focus { outline: none; border-color: #667eea; }';
include 'header.php';
?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> All Students</h3>
                <a href="add_student.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Student</a>
            </div>

            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search by name, roll number, or department..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <?php if ($search): ?>
                    <a href="view_students.php" class="btn" style="background:#e2e8f0;color:#555;"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Roll Number</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>RFID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($filterDept) {
                            if ($search) {
                                $like = "%$search%";
                                $stmt = $conn->prepare("SELECT * FROM students WHERE department = ? AND (name LIKE ? OR roll_number LIKE ? OR department LIKE ?) ORDER BY name");
                                $stmt->bind_param("ssss", $filterDept, $like, $like, $like);
                            } else {
                                $stmt = $conn->prepare("SELECT * FROM students WHERE department = ? ORDER BY name");
                                $stmt->bind_param("s", $filterDept);
                            }
                        } elseif ($search) {
                            $like = "%$search%";
                            $stmt = $conn->prepare("SELECT * FROM students WHERE name LIKE ? OR roll_number LIKE ? OR department LIKE ? ORDER BY name");
                            $stmt->bind_param("sss", $like, $like, $like);
                        } else {
                            $stmt = $conn->prepare("SELECT * FROM students ORDER BY name");
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0):
                            $i = 1;
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['roll_number']) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($row['department']) ?></span></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><code style="font-size:0.8rem;"><?= htmlspecialchars($row['rfid']) ?></code></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete <?= htmlspecialchars($row['name']) ?>?')">
                                    <?= csrf_token() ?>
                                    <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete_student" class="btn btn-danger" style="padding:8px 16px;"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" style="text-align:center;color:#999;padding:40px;">
                                <i class="fas fa-inbox" style="font-size:3rem;display:block;margin-bottom:15px;"></i>
                                <?= $search ? 'No students match your search.' : 'No students found.' ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php include 'footer.php'; ?>
