<?php
require_once 'config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS invitations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employer_id INT NOT NULL,
        candidate_id INT NOT NULL,
        job_id INT NOT NULL,
        status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
        FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
        FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
    )";

    $conn->exec($sql);
    echo "Table invitations created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>