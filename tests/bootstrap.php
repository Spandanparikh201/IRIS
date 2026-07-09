<?php
// Load Composer's PSR-4 autoloader (includes IRIS\ namespace)
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Override DB_NAME for test environment — tests use studentdb_test,
// while legacy code (via db_connect.php) continues to connect to studentdb
$_ENV['DB_NAME'] = 'studentdb_test';
putenv('DB_NAME=studentdb_test');

// Establish test database connection (available to tests via $GLOBALS)
$testConn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if ($testConn->connect_error) {
    echo "Warning: Test database connection failed: " . $testConn->connect_error . "\n";
    echo "Tests requiring a database will be skipped or fail.\n";
    echo "Ensure MySQL is running and studentdb_test has been created.\n";
    $GLOBALS['test_conn'] = null;
} else {
    $GLOBALS['test_conn'] = $testConn;
}
