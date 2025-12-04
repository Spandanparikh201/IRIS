<?php
include 'db_connect.php';

if (isset($_POST['preview'])) {
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        fgetcsv($file); // Skip header

        echo "<h2>Preview Students</h2>";
        echo "<form method='POST' action='import_students.php'>";
        echo "<table border='1'><tr><th>Name</th><th>Roll No</th><th>Dept</th><th>Email</th><th>RFID</th></tr>";

        $rows = [];
        while (($data = fgetcsv($file)) !== FALSE) {
            echo "<tr>";
            for ($i = 0; $i < 5; $i++) {
                echo "<td>" . htmlspecialchars($data[$i]) . "</td>";
            }
            echo "</tr>";
            $rows[] = $data;
        }
        echo "</table>";

        fclose($file);

        $_SESSION['csv_preview'] = $rows;
        echo "<button type='submit' name='import'>✅ Confirm & Import</button>";
        echo "</form>";
    } else {
        echo "❌ File upload error.";
    }
}

if (isset($_POST['import']) && isset($_SESSION['csv_preview'])) {
    $rows = $_SESSION['csv_preview'];
    unset($_SESSION['csv_preview']);

    $stmt = $conn->prepare("INSERT INTO students (name, roll_number, department, email, rfid) VALUES (?, ?, ?, ?, ?)");
    foreach ($rows as $row) {
        $stmt->bind_param("sssss", $row[0], $row[1], $row[2], $row[3], $row[4]);
        $stmt->execute();
    }

    echo "✅ Students imported successfully.";
    echo "<br><a href='dashboard.php'>🔙 Back to Dashboard</a>";
}
?>
