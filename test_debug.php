<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting generic debug...\n";

// test db connection
require_once 'config/db.php';
echo "DB Connected.\n";

// check tables
try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(", ", $tables) . "\n";

    if (!in_array('user_tokens', $tables)) {
        echo "WARNING: user_tokens table missing!\n";
    }
} catch (Exception $e) {
    echo "Error listing tables: " . $e->getMessage() . "\n";
}

// test include header (mocking $_SERVER for CLI)
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/login.php';
// Note: header.php uses session_start likely via functions.php, might output headers, which is fine in CLI usually but warnings maybe.
// It also uses $_SESSION.

echo "Attempting to include header...\n";
try {
    require_once 'includes/header.php';
    echo "Header included successfully.\n";
} catch (Throwable $e) {
    echo "Error including header: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "Done.\n";
?>