<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}

include 'db_connect.php';
include 'rbac_helper.php';
checkPermission('bulk_upload_students');

if ($_POST) {
    verify_csrf();

    if (isset($_POST['preview']) && isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $filename = $_FILES['csv_file']['name'];
        $allowed_extensions = ['csv'];
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $error = "Invalid file type. Only CSV files are allowed.";
        } else {
            if (($handle = fopen($file, "r")) !== FALSE) {
                fgetcsv($handle);
                $rows = [];
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 6) {
                        $rows[] = $data;
                    }
                }
                fclose($handle);
                if (!empty($rows)) {
                    $_SESSION['csv_preview'] = $rows;
                } else {
                    $error = "No valid data found in CSV.";
                }
            } else {
                $error = "Error reading CSV file.";
            }
        }
    }

    if (isset($_POST['confirm_import']) && isset($_SESSION['csv_preview'])) {
        $rows = $_SESSION['csv_preview'];
        unset($_SESSION['csv_preview']);

        $count = 0;
        $stmt = $conn->prepare("INSERT INTO students (name, roll_number, department, email, rfid) VALUES (?, ?, ?, ?, ?)");
        foreach ($rows as $data) {
            $name = $data[0];
            $year = $data[1];
            $department = $data[2];
            $roll_no = $data[3];
            $email = $data[4];
            $rfid = $data[5];
            $roll_number = $year . $department . str_pad($roll_no, 3, '0', STR_PAD_LEFT);
            $stmt->bind_param("sssss", $name, $roll_number, $department, $email, $rfid);
            if ($stmt->execute()) $count++;
        }
        $success = "$count students imported successfully!";
    }

    if (isset($_POST['cancel_import'])) {
        unset($_SESSION['csv_preview']);
    }
}

$activePage = 'add_student';
$pageTitle = 'Bulk Upload Students';
$pageSubtitle = 'Import multiple students via CSV file';
$pageStyles = '.btn { padding: 15px 30px; }';
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
                <a href="add_student.php" class="btn" id="singleBtn" style="background: #e2e8f0; color: #555; text-decoration: none;">
                    <i class="fas fa-user-plus"></i> Single Add
                </a>
                <button class="btn btn-primary" id="bulkBtn" style="cursor: default;">
                    <i class="fas fa-upload"></i> Bulk Upload
                </button>
            </div>
            
            <?php if (!isset($_SESSION['csv_preview'])): ?>
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_token() ?>
                <div class="form-group">
                    <label for="csv_file"><i class="fas fa-file-csv"></i> Select CSV File</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    <small style="color: #666; margin-top: 5px; display: block;">CSV format: name, year, department, roll_no, email, rfid</small>
                </div>
                
                <div style="margin-top: 20px;">
                    <a href="students_template.csv" download class="btn" style="background: #e2e8f0; color: #555; text-decoration: none;">
                        <i class="fas fa-download"></i> Download CSV Template
                    </a>
                </div>
                
                <div style="margin-top: 25px; text-align: center;">
                    <button type="submit" name="preview" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Preview Students
                    </button>
                </div>
            </form>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['csv_preview'])): ?>
            <h3 style="margin-bottom: 15px;"><i class="fas fa-eye"></i> Preview (<?= count($_SESSION['csv_preview']) ?> students)</h3>
            <div style="overflow-x: auto; margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">Name</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">Year</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">Dept</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">Roll No</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">Email</th>
                            <th style="border: 1px solid #ddd; padding: 8px; background: #f2f2f2;">RFID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['csv_preview'] as $row): ?>
                        <tr>
                            <?php for ($i = 0; $i < 6; $i++): ?>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?= htmlspecialchars($row[$i] ?? '') ?></td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form method="POST" style="display: flex; gap: 10px; justify-content: center;">
                <?= csrf_token() ?>
                <button type="submit" name="confirm_import" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirm & Import
                </button>
                <button type="submit" name="cancel_import" class="btn" style="background: #e2e8f0; color: #555;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php include 'footer.php'; ?>
