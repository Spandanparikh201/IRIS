<?php
session_start();
if (!isset($_SESSION['user'])) {
    die("Please login first");
}

include 'db_connect.php';

echo "<!DOCTYPE html><html><head><title>Test Reports</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5}";
echo ".test{background:white;padding:20px;margin:10px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1)}";
echo "h1{color:#333}h2{color:#667eea}pre{background:#f4f4f4;padding:10px;border-radius:4px;overflow-x:auto}";
echo ".success{color:green}error{color:red}</style></head><body>";
echo "<h1>📊 IRIS Report Testing</h1>";

// Test 1: Department Report
echo "<div class='test'><h2>1. Department Report Test</h2>";
$dept = 'CE';
$sql = "SELECT a.name, s.roll_number, s.department, d.dept_name, a.status, a.timestamp 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        LEFT JOIN departments d ON s.department = d.dept_code 
        WHERE s.department = ? 
        ORDER BY a.timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $dept);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    echo "<p class='success'>✅ Query executed successfully</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data:</h3>";
        echo "<pre>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 3) {
            print_r($row);
            $count++;
        }
        echo "</pre>";
    } else {
        echo "<p>No records found for department $dept</p>";
    }
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

// Test 2: Individual Student Report
echo "<div class='test'><h2>2. Individual Student Report Test</h2>";
$student = '2021001';
$sql = "SELECT a.name, s.roll_number, s.department, a.status, a.timestamp 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        WHERE s.roll_number = ? 
        ORDER BY a.timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $student);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    echo "<p class='success'>✅ Query executed successfully</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data:</h3>";
        echo "<pre>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 3) {
            print_r($row);
            $count++;
        }
        echo "</pre>";
    } else {
        echo "<p>No records found for student $student</p>";
    }
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

// Test 3: Student Summary Report
echo "<div class='test'><h2>3. Student Summary Report Test</h2>";
$sql = "SELECT s.name, s.roll_number, s.department, COUNT(*) as total_attendance 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        GROUP BY s.roll_number, s.name, s.department 
        ORDER BY s.name";
$result = $conn->query($sql);

if ($result) {
    echo "<p class='success'>✅ Query executed successfully</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data:</h3>";
        echo "<pre>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 3) {
            print_r($row);
            $count++;
        }
        echo "</pre>";
    } else {
        echo "<p>No records found</p>";
    }
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

// Test 4: All Departments Available
echo "<div class='test'><h2>4. All Departments Available</h2>";
$sql = "SELECT dept_code, dept_name FROM departments WHERE status = 'active' ORDER BY dept_name";
$result = $conn->query($sql);

if ($result) {
    echo "<p class='success'>✅ Query executed successfully</p>";
    echo "<p>Total departments: " . $result->num_rows . "</p>";
    echo "<h3>Departments:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['dept_code']}: {$row['dept_name']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

echo "<div class='test'><h2>5. Database Statistics</h2>";
$stats = [
    'Total Students' => $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0],
    'Total Attendance Records' => $conn->query("SELECT COUNT(*) FROM attendance")->fetch_row()[0],
    'Today\'s Records' => $conn->query("SELECT COUNT(*) FROM attendance WHERE DATE(timestamp) = CURDATE()")->fetch_row()[0],
    'Total Departments' => $conn->query("SELECT COUNT(*) FROM departments WHERE status = 'active'")->fetch_row()[0],
];

echo "<pre>";
foreach ($stats as $label => $value) {
    echo "$label: $value\n";
}
echo "</pre>";
echo "</div>";

echo "<div class='test'><h2>6. Test Report Generation Links</h2>";
echo "<p>Click the links below to test report generation:</p>";
echo "<ul>";
echo "<li><a href='reports.php?report=daily&format=excel' target='_blank'>Daily Report (Excel)</a></li>";
echo "<li><a href='reports.php?report=weekly&format=pdf' target='_blank'>Weekly Report (PDF)</a></li>";
echo "<li><a href='reports.php?report=monthly&format=excel' target='_blank'>Monthly Report (Excel)</a></li>";
echo "<li><a href='reports.php?report=department&format=excel' target='_blank'>All Departments Report (Excel)</a></li>";
echo "<li><a href='reports.php?report=student&format=excel' target='_blank'>Student Summary (Excel)</a></li>";
echo "</ul>";
echo "</div>";

echo "<div class='test'><h2>7. Test Individual Student Report</h2>";
echo "<p>Enter a student roll number to test:</p>";
echo "<form method='GET' action='reports.php'>";
echo "<input type='hidden' name='report' value='student'>";
echo "<input type='hidden' name='format' value='excel'>";
echo "<input type='text' name='student' placeholder='Enter roll number' required>";
echo "<button type='submit'>Generate Report</button>";
echo "</form>";
echo "</div>";

echo "<div class='test'><h2>8. Test Department Report</h2>";
echo "<p>Select a department to test:</p>";
echo "<form method='GET' action='reports.php'>";
echo "<input type='hidden' name='report' value='department'>";
echo "<input type='hidden' name='format' value='excel'>";
echo "<select name='dept' required>";
echo "<option value=''>All Departments</option>";
echo "<option value='CE'>Computer Engineering (CE)</option>";
echo "<option value='IT'>Information Technology (IT)</option>";
echo "<option value='ME'>Mechanical Engineering (ME)</option>";
echo "<option value='EE'>Electrical Engineering (EE)</option>";
echo "<option value='EC'>Electronics & Communication (EC)</option>";
echo "<option value='CV'>Civil Engineering (CV)</option>";
echo "<option value='CSE'>Computer Science & Engineering (CSE)</option>";
echo "<option value='AI'>Artificial Intelligence (AI)</option>";
echo "<option value='DS'>Data Science (DS)</option>";
echo "</select>";
echo "<button type='submit'>Generate Report</button>";
echo "</form>";
echo "</div>";

echo "<div class='test'><h2>✅ All Tests Completed!</h2>";
echo "<p>If all tests passed, your reports are ready for submission.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Test with actual data</li>";
echo "<li>Verify all departments are working</li>";
echo "<li>Test CSV and PDF export</li>";
echo "<li>Test on different browsers</li>";
echo "<li>Test on mobile devices</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
