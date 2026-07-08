<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "studentdb";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: text/plain');
echo "=== DATABASE SCHEMA: $database ===\n\n";

// Get all tables
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    echo "--- Table: $table ---\n\n";
    
    // Get table structure
    $structure = $conn->query("DESCRIBE $table");
    echo "Columns:\n";
    echo sprintf("%-20s %-15s %-8s %-8s %-10s %s\n", "Field", "Type", "Null", "Key", "Default", "Extra");
    echo str_repeat("-", 80) . "\n";
    
    while ($col = $structure->fetch_assoc()) {
        echo sprintf("%-20s %-15s %-8s %-8s %-10s %s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key'], 
            $col['Default'] ?? 'NULL', 
            $col['Extra']
        );
    }
    
    echo "\n";
    
    // Get table status
    $status = $conn->query("SHOW TABLE STATUS WHERE Name='$table'");
    if ($status->num_rows > 0) {
        $statusData = $status->fetch_assoc();
        echo "Table Info:\n";
        echo "  Engine: " . $statusData['Engine'] . "\n";
        echo "  Rows: " . $statusData['Rows'] . "\n";
        echo "  Avg Row Length: " . $statusData['Avg_row_length'] . "\n";
        echo "  Data Length: " . $statusData['Data_length'] . "\n";
        echo "  Max Data Length: " . $statusData['Max_data_length'] . "\n";
        echo "  Index Length: " . $statusData['Index_length'] . "\n";
        echo "  Create Time: " . $statusData['Create_time'] . "\n";
        echo "  Update Time: " . $statusData['Update_time'] . "\n";
        echo "\n";
    }
    
    // Get foreign keys
    $fks = $conn->query("
        SELECT 
            COLUMN_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$database' 
        AND TABLE_NAME = '$table'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($fks->num_rows > 0) {
        echo "Foreign Keys:\n";
        while ($fk = $fks->fetch_assoc()) {
            echo "  {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}({$fk['REFERENCED_COLUMN_NAME']})\n";
        }
        echo "\n";
    }
    
    echo str_repeat("=", 80) . "\n\n";
}

$conn->close();
echo "Schema retrieval complete.\n";
?>
