<?php
session_start();
if (!isset($_SESSION['user'])) {
    die("Please login first");
}

include 'db_connect.php';

// Test department report
echo "<h2>Testing Department Report Query</h2>";

$dept = 'CE';
$sql = "SELECT a.name, s.roll_number, a.department, d.dept_name, a.status, a.timestamp 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        LEFT JOIN departments d ON a.department = d.dept_code 
        WHERE a.department = ? 
        ORDER BY a.timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $dept);
$stmt->execute();
$result = $stmt->get_result();

echo "<p>Query: " . htmlspecialchars($sql) . "</p>";
echo "<p>Department: $dept</p>";

if ($result) {
    echo "<p style='color:green'>✅ Query executed successfully!</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data (first 3 records):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Name</th><th>Roll No</th><th>Department</th><th>Status</th><th>Timestamp</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 3) {
            echo "<tr>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['roll_number']}</td>";
            echo "<td>{$row['department']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['timestamp']}</td>";
            echo "</tr>";
            $count++;
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>⚠️ No records found for department $dept</p>";
        echo "<p>This might be because:</p>";
        echo "<ul>";
        echo "<li>No attendance records exist for students in department $dept</li>";
        echo "<li>Students in department $dept don't have matching RFID in attendance table</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color:red'>❌ Query failed: " . $conn->error . "</p>";
}

echo "<hr>";

// Test student report
echo "<h2>Testing Student Report Query</h2>";

$student = '2021001';
$sql = "SELECT a.name, s.roll_number, a.department, a.status, a.timestamp 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        WHERE s.roll_number = ? 
        ORDER BY a.timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $student);
$stmt->execute();
$result = $stmt->get_result();

echo "<p>Query: " . htmlspecialchars($sql) . "</p>";
echo "<p>Student Roll Number: $student</p>";

if ($result) {
    echo "<p style='color:green'>✅ Query executed successfully!</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data (first 3 records):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Name</th><th>Roll No</th><th>Department</th><th>Status</th><th>Timestamp</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 3) {
            echo "<tr>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['roll_number']}</td>";
            echo "<td>{$row['department']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['timestamp']}</td>";
            echo "</tr>";
            $count++;
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>⚠️ No records found for student $student</p>";
    }
} else {
    echo "<p style='color:red'>❌ Query failed: " . $conn->error . "</p>";
}

echo "<hr>";

// Check if students exist in the database
echo "<h2>Checking Students in Database</h2>";
$sql = "SELECT roll_number, name, department, rfid FROM students";
$result = $conn->query($sql);

echo "<p>Total students: " . $result->num_rows . "</p>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Roll No</th><th>Name</th><th>Department</th><th>RFID</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['roll_number']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['department']}</td>";
        echo "<td>{$row['rfid']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

// Check attendance records
echo "<h2>Checking Attendance Records</h2>";
$sql = "SELECT COUNT(*) as total FROM attendance";
$result = $conn->query($sql);
$total = $result->fetch_assoc()['total'];

echo "<p>Total attendance records: $total</p>";

if ($total > 0) {
    echo "<h3>Sample Attendance Records (first 5):</h3>";
    $sql = "SELECT name, rfid, status, timestamp, department FROM attendance LIMIT 5";
    $result = $conn->query($sql);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Name</th><th>RFID</th><th>Status</th><th>Timestamp</th><th>Department</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['rfid']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['timestamp']}</td>";
        echo "<td>{$row['department']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

// Test direct URL generation
echo "<h2>Test Report Generation URLs</h2>";
echo "<p>Click these links to test report generation:</p>";
echo "<ul>";
echo "<li><a href='reports.php?report=department&format=excel&dept=CE' target='_blank'>Department CE Report (Excel)</a></li>";
echo "<li><a href='reports.php?report=department&format=excel' target='_blank'>All Departments Report (Excel)</a></li>";
echo "<li><a href='reports.php?report=student&format=excel&student=2021001' target='_blank'>Student 2021001 Report (Excel)</a></li>";
echo "<li><a href='reports.php?report=student&format=excel' target='_blank'>Student Summary Report (Excel)</a></li>";
echo "</ul>";

echo "<hr>";

// Check for errors
echo "<h2>Error Log Check</h2>";
if (file_exists('debug_log.txt')) {
    $log = file_get_contents('debug_log.txt');
    echo "<pre>" . htmlspecialchars(substr($log, -1000)) . "</pre>";
} else {
    echo "<p>No debug log file found</p>";
}

echo "<hr>";

// Test with all departments
echo "<h2>Testing All Departments</h2>";
$sql = "SELECT dept_code, dept_name FROM departments WHERE status = 'active'";
$result = $conn->query($sql);

echo "<p>Total departments: " . $result->num_rows . "</p>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Code</th><th>Name</th><th>Test Link</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['dept_code']}</td>";
        echo "<td>{$row['dept_name']}</td>";
        echo "<td><a href='reports.php?report=department&format=excel&dept={$row['dept_code']}' target='_blank'>Test</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

echo "<h2>✅ All Tests Completed!</h2>";
echo "<p>If you see data in the tables above, the queries are working correctly.</p>";
echo "<p>If you see 'No records found', it means there's no matching data in the database.</p>";
?>
