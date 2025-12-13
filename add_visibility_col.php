<?php
require_once 'config/db.php';

try {
    // Check if column exists
    $stmt = $conn->query("SHOW COLUMNS FROM candidates LIKE 'visibility'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $conn->exec("ALTER TABLE candidates ADD COLUMN visibility ENUM('visible', 'hidden') DEFAULT 'visible' AFTER skills");
        echo "Column 'visibility' added successfully.";
    } else {
        echo "Column 'visibility' already exists.";
    }

} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>