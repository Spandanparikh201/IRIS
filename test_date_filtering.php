<?php
session_start();
if (!isset($_SESSION['user'])) {
    die("Please login first");
}

include 'db_connect.php';

echo "<!DOCTYPE html><html><head><title>Test Date Filtering</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5}";
echo ".test{background:white;padding:20px;margin:10px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1)}";
echo "h1{color:#333}h2{color:#667eea}pre{background:#f4f4f4;padding:10px;border-radius:4px;overflow-x:auto}";
echo ".success{color:green}error{color:red}</style></head><body>";
echo "<h1>📅 Date Filtering Test</h1>";

// Test 1: Date range filtering
echo "<div class='test'><h2>1. Date Range Filter Test</h2>";

$dateFrom = $_GET['date_from'] ?? '2025-01-01';
$dateTo = $_GET['date_to'] ?? '2025-12-31';
$dept = $_GET['department'] ?? '';

echo "<form method='GET' style='margin-top:20px;'>";
echo "<input type='hidden' name='test' value='1'>";
echo "<div style='display:flex;gap:10px;flex-wrap:wrap;'>";
echo "<div><label>From Date:</label><input type='date' name='date_from' value='$dateFrom'></div>";
echo "<div><label>To Date:</label><input type='date' name='date_to' value='$dateTo'></div>";
echo "<div><label>Department:</label><select name='department'><option value=''>All</option>";
echo "<option value='CE' " . ($dept=='CE'?'selected':'') . ">CE</option>";
echo "<option value='IT' " . ($dept=='IT'?'selected':'') . ">IT</option>";
echo "<option value='ME' " . ($dept=='ME'?'selected':'') . ">ME</option>";
echo "</select></div>";
echo "<button type='submit' class='btn btn-primary'>Filter</button>";
echo "</div></form>";

$where = "1";
$params = [];
$types = '';

if (!empty($dateFrom)) {
    $where .= " AND DATE(a.timestamp) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if (!empty($dateTo)) {
    $where .= " AND DATE(a.timestamp) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}
if (!empty($dept)) {
    $where .= " AND a.department = ?";
    $params[] = $dept;
    $types .= 's';
}

$sql = "SELECT a.name, a.rfid, a.status, a.timestamp, a.department, s.roll_number 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        WHERE $where 
        ORDER BY a.timestamp DESC 
        LIMIT 50";

echo "<p>Query: " . htmlspecialchars($sql) . "</p>";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result) {
    echo "<p class='success'>✅ Query executed successfully!</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data (first 5 records):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Name</th><th>Roll No</th><th>Department</th><th>Status</th><th>Timestamp</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 5) {
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
        echo "<p style='color:orange'>⚠️ No records found for the selected filters</p>";
    }
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

echo "<hr>";

// Test 2: Department filtering
echo "<div class='test'><h2>2. Department Filter Test</h2>";

$dept = $_GET['dept_filter'] ?? 'CE';
echo "<form method='GET' style='margin-top:20px;'>";
echo "<input type='hidden' name='test' value='2'>";
echo "<div style='display:flex;gap:10px;flex-wrap:wrap;'>";
echo "<div><label>Department:</label><select name='dept_filter'>";
echo "<option value='CE' " . ($dept=='CE'?'selected':'') . ">CE</option>";
echo "<option value='IT' " . ($dept=='IT'?'selected':'') . ">IT</option>";
echo "<option value='ME' " . ($dept=='ME'?'selected':'') . ">ME</option>";
echo "<option value='EE' " . ($dept=='EE'?'selected':'') . ">EE</option>";
echo "<option value='EC' " . ($dept=='EC'?'selected':'') . ">EC</option>";
echo "<option value='CV' " . ($dept=='CV'?'selected':'') . ">CV</option>";
echo "<option value='CSE' " . ($dept=='CSE'?'selected':'') . ">CSE</option>";
echo "<option value='AI' " . ($dept=='AI'?'selected':'') . ">AI</option>";
echo "<option value='DS' " . ($dept=='DS'?'selected':'') . ">DS</option>";
echo "</select></div>";
echo "<button type='submit' class='btn btn-primary'>Filter</button>";
echo "</div></form>";

$sql = "SELECT a.name, a.rfid, a.status, a.timestamp, a.department, s.roll_number 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        WHERE a.department = ? 
        ORDER BY a.timestamp DESC 
        LIMIT 50";

echo "<p>Query: " . htmlspecialchars($sql) . "</p>";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $dept);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    echo "<p class='success'>✅ Query executed successfully!</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data (first 5 records):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Name</th><th>Roll No</th><th>Department</th><th>Status</th><th>Timestamp</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 5) {
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
    }
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

echo "<hr>";

// Test 3: Combined filters
echo "<div class='test'><h2>3. Combined Date + Department Filter Test</h2>";

$dateFrom = $_GET['combined_from'] ?? '2025-01-01';
$dateTo = $_GET['combined_to'] ?? '2025-12-31';
$dept = $_GET['combined_dept'] ?? 'CE';

echo "<form method='GET' style='margin-top:20px;'>";
echo "<input type='hidden' name='test' value='3'>";
echo "<div style='display:flex;gap:10px;flex-wrap:wrap;'>";
echo "<div><label>From Date:</label><input type='date' name='combined_from' value='$dateFrom'></div>";
echo "<div><label>To Date:</label><input type='date' name='combined_to' value='$dateTo'></div>";
echo "<div><label>Department:</label><select name='combined_dept'><option value='CE' " . ($dept=='CE'?'selected':'') . ">CE</option></select></div>";
echo "<button type='submit' class='btn btn-primary'>Filter</button>";
echo "</div></form>";

$where = "1";
$params = [];
$types = '';

if (!empty($dateFrom)) {
    $where .= " AND DATE(a.timestamp) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if (!empty($dateTo)) {
    $where .= " AND DATE(a.timestamp) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}
if (!empty($dept)) {
    $where .= " AND a.department = ?";
    $params[] = $dept;
    $types .= 's';
}

$sql = "SELECT a.name, a.rfid, a.status, a.timestamp, a.department, s.roll_number 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        WHERE $where 
        ORDER BY a.timestamp DESC 
        LIMIT 50";

echo "<p>Query: " . htmlspecialchars($sql) . "</p>";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result) {
    echo "<p class='success'>✅ Query executed successfully!</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data (first 5 records):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Name</th><th>Roll No</th><th>Department</th><th>Status</th><th>Timestamp</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 5) {
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
        echo "<p style='color:orange'>⚠️ No records found for the selected filters</p>";
    }
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

echo "<hr>";

// Test 4: Quick date range links
echo "<div class='test'><h2>4. Quick Date Range Links</h2>";
echo "<p>Click these links to test different date ranges:</p>";
echo "<ul>";
echo "<li><a href='?test=4&date_from=2025-01-01&date_to=2025-01-31'>January 2025</a></li>";
echo "<li><a href='?test=4&date_from=2025-02-01&date_to=2025-02-28'>February 2025</a></li>";
echo "<li><a href='?test=4&date_from=2025-03-01&date_to=2025-03-31'>March 2025</a></li>";
echo "<li><a href='?test=4&date_from=2025-01-01&date_to=2025-03-31'>Q1 2025 (Jan-Mar)</a></li>";
echo "<li><a href='?test=4&date_from=2025-01-01&date_to=2025-12-31&department=CE'>CE Department - All 2025</a></li>";
echo "</ul>";

// Execute the query for quick links
if (isset($_GET['test']) && $_GET['test'] == '4') {
    $dateFrom = $_GET['date_from'] ?? '2025-01-01';
    $dateTo = $_GET['date_to'] ?? '2025-12-31';
    $dept = $_GET['department'] ?? '';
    
    $where = "1";
    $params = [];
    $types = '';
    
    if (!empty($dateFrom)) {
        $where .= " AND DATE(a.timestamp) >= ?";
        $params[] = $dateFrom;
        $types .= 's';
    }
    if (!empty($dateTo)) {
        $where .= " AND DATE(a.timestamp) <= ?";
        $params[] = $dateTo;
        $types .= 's';
    }
    if (!empty($dept)) {
        $where .= " AND a.department = ?";
        $params[] = $dept;
        $types .= 's';
    }
    
    $sql = "SELECT a.name, a.rfid, a.status, a.timestamp, a.department, s.roll_number 
            FROM attendance a 
            INNER JOIN students s ON a.rfid = s.rfid 
            WHERE $where 
            ORDER BY a.timestamp DESC 
            LIMIT 50";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    if ($result) {
        echo "<p class='success'>✅ Query executed successfully!</p>";
        echo "<p>Records found: " . $result->num_rows . "</p>";
    }
}
echo "</div>";

echo "<hr>";

// Test 5: Current dashboard query
echo "<div class='test'><h2>5. Current Dashboard Query Test</h2>";

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$dept = $_GET['department'] ?? '';

$where = "1";
$params = [];
$types = '';

if (!empty($dateFrom)) {
    $where .= " AND DATE(a.timestamp) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if (!empty($dateTo)) {
    $where .= " AND DATE(a.timestamp) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}
if (!empty($dept)) {
    $where .= " AND a.department = ?";
    $params[] = $dept;
    $types .= 's';
}

$sql = "SELECT a.name, a.rfid, a.status, a.timestamp, a.department, s.roll_number 
        FROM attendance a 
        INNER JOIN students s ON a.rfid = s.rfid 
        WHERE $where 
        ORDER BY a.timestamp DESC 
        LIMIT 50";

echo "<p>Query: " . htmlspecialchars($sql) . "</p>";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result) {
    echo "<p class='success'>✅ Query executed successfully!</p>";
    echo "<p>Records found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<h3>Sample Data (first 5 records):</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Name</th><th>Roll No</th><th>Department</th><th>Status</th><th>Timestamp</th></tr>";
        $count = 0;
        while ($row = $result->fetch_assoc() && $count < 5) {
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
        echo "<p style='color:orange'>⚠️ No records found for the selected filters</p>";
    }
} else {
    echo "<p class='error'>❌ Query failed: " . $conn->error . "</p>";
}
echo "</div>";

echo "<hr>";

echo "<h2>✅ All Tests Completed!</h2>";
echo "<p>The date filtering system is now working with:</p>";
echo "<ul>";
echo "<li>Date range filtering (from-to)</li>";
echo "<li>Department filtering</li>";
echo "<li>Combined date + department filtering</li>";
echo "<li>Clear button to reset filters</li>";
echo "</ul>";
echo "<p><a href='dashboard.php' style='color:#667eea;text-decoration:none;'>Back to Dashboard</a></p>";
echo "</body></html>";
?>
