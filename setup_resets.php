<?php
require_once 'config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        selector VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires BIGINT NOT NULL
    )";

    $conn->exec($sql);
    echo "Table password_resets created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>