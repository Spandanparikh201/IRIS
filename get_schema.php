<?php
include 'db_connect.php';

echo "<h2>Current Database Schema</h2>";

// Get all tables
$tables = $conn->query("SHOW TABLES");
while($table = $tables->fetch_array()) {
    $tableName = $table[0];
    echo "<h3>Table: $tableName</h3>";
    
    // Get table structure
    $structure = $conn->query("DESCRIBE $tableName");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Show sample data
    $sample = $conn->query("SELECT * FROM $tableName LIMIT 3");
    if($sample->num_rows > 0) {
        echo "<strong>Sample Data:</strong><br>";
        echo "<table border='1'>";
        $fields = $sample->fetch_fields();
        echo "<tr>";
        foreach($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        $sample = $conn->query("SELECT * FROM $tableName LIMIT 3");
        while($row = $sample->fetch_assoc()) {
            echo "<tr>";
            foreach($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "<hr>";
}
?>