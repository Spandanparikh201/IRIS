<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}
include 'db_connect.php';
include 'rbac_helper.php';
$conn->query("SET time_zone = '+05:30'");

$filterDept = getUserDept();

if ($_POST && isset($_POST['mark_attendance'])) {
    verify_csrf();
    $rfid = trim($_POST['rfid']);
    $status = $_POST['status'] === 'IN' ? 'IN' : 'OUT';

    $stmt = $conn->prepare("SELECT name, department FROM students WHERE rfid = ?");
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if ($student) {
        $stmt = $conn->prepare("INSERT INTO attendance (rfid, name, department, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $rfid, $student['name'], $student['department'], $status);
        if ($stmt->execute()) {
            echo "<script>alert('Attendance marked: " . htmlspecialchars($student['name']) . " — " . $status . "'); window.location.href='mark_attendance.php';</script>";
            exit;
        }
    } else {
        $error = "No student found with that RFID.";
    }
}

$activePage = 'mark_attendance';
$pageTitle = 'Mark Attendance';
$pageSubtitle = 'Manually record student attendance';
$pageStyles = '
.attendance-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
@media (max-width: 768px) { .attendance-grid { grid-template-columns: 1fr; } }
.status-toggle { display: flex; gap: 15px; margin: 20px 0; }
.status-option { flex: 1; padding: 20px; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.3s ease; font-weight: 600; font-size: 1.1rem; }
.status-option:hover { border-color: #667eea; }
.status-option.selected-in { border-color: #48bb78; background: #c6f6d5; color: #22543d; }
.status-option.selected-out { border-color: #f56565; background: #fed7d7; color: #742a2a; }
.status-option i { display: block; font-size: 2rem; margin-bottom: 8px; }
.recent-list { max-height: 450px; overflow-y: auto; }
.recent-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
.recent-item:last-child { border-bottom: none; }';
include 'header.php';
?>

        <div class="attendance-grid">
            <div class="card">
                <h3 class="card-title" style="margin-bottom:20px;"><i class="fas fa-pen"></i> Record Attendance</h3>

                <?php if (isset($error)): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="attendanceForm">
                    <?= csrf_token() ?>

                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Student RFID</label>
                        <input type="text" name="rfid" class="form-control" placeholder="Scan or enter RFID UID" required autofocus>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-exchange-alt"></i> Status</label>
                        <div class="status-toggle">
                            <label class="status-option" id="optIn" onclick="selectStatus('IN')">
                                <i class="fas fa-sign-in-alt"></i>
                                <input type="radio" name="status" value="IN" hidden checked> Check In
                            </label>
                            <label class="status-option" id="optOut" onclick="selectStatus('OUT')">
                                <i class="fas fa-sign-out-alt"></i>
                                <input type="radio" name="status" value="OUT" hidden> Check Out
                            </label>
                        </div>
                    </div>

                    <button type="submit" name="mark_attendance" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;">
                        <i class="fas fa-check-circle"></i> Mark Attendance
                    </button>
                </form>
            </div>

            <div class="card">
                <h3 class="card-title" style="margin-bottom:20px;"><i class="fas fa-clock"></i> Recently Marked</h3>
                <div class="recent-list">
                    <?php
                    $deptWhere = $filterDept ? " WHERE a.department = ?" : "";
                    $stmt = $conn->prepare("SELECT a.name, a.department, a.status, a.timestamp FROM attendance a$deptWhere ORDER BY a.timestamp DESC LIMIT 20");
                    if ($filterDept) $stmt->bind_param("s", $filterDept);
                    $stmt->execute();
                    $recent = $stmt->get_result();
                    if ($recent && $recent->num_rows > 0):
                        while ($r = $recent->fetch_assoc()):
                            $isIn = $r['status'] === 'IN';
                    ?>
                    <div class="recent-item">
                        <div style="width:36px;height:36px;border-radius:50%;background:<?= $isIn ? '#c6f6d5' : '#fed7d7' ?>;display:flex;align-items:center;justify-content:center;color:<?= $isIn ? '#22543d' : '#742a2a' ?>;">
                            <i class="fas fa-<?= $isIn ? 'sign-in-alt' : 'sign-out-alt' ?>"></i>
                        </div>
                        <div style="flex:1;">
                            <strong><?= htmlspecialchars($r['name']) ?></strong>
                            <small style="color:#999;display:block;"><?= htmlspecialchars($r['department']) ?> &middot; <?= date('h:i A', strtotime($r['timestamp'])) ?></small>
                        </div>
                        <span class="badge <?= $isIn ? 'badge-success' : 'badge-danger' ?>"><?= $r['status'] ?></span>
                    </div>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <p style="text-align:center;color:#999;padding:20px;">No records yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php $pageScripts = '
function selectStatus(val) {
    document.querySelectorAll(".status-option").forEach(el => el.className = "status-option");
    document.querySelector("#opt" + (val === "IN" ? "In" : "Out")).className = "status-option selected-" + val.toLowerCase();
    document.querySelector("input[name=status][value=" + val + "]").checked = true;
}
'; ?>
<?php include 'footer.php'; ?>
