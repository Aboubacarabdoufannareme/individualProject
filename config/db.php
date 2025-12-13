<?php
// Config: Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'webtech_2025A_fannareme_abdou');
define('DB_USER', 'fannareme.abdou');
define('DB_PASS', 'fa889033');

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Enable exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log this to a file instead of showing it
    error_log("Database Error: " . $e->getMessage());
    die("Connection failed. Please check the database configuration.");
}
?>
