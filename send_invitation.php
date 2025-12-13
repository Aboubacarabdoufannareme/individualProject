<?php
// send_invitation.php
require_once 'includes/header.php';
require_login();

// Ensure only employers can access
if (get_role() !== 'employer') {
    redirect('index.php');
}

// Self-healing: Create table if not exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS invitations (
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
    )");
} catch (PDOException $e) {
    // Ignore if already exists
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employer_id = $_SESSION['user_id'];
    $candidate_id = (int) $_POST['candidate_id'];
    $job_id = (int) $_POST['job_id'];
    $message = sanitize($_POST['message']);

    if (empty($job_id) || empty($candidate_id)) {
        flash('error', "Invalid request.");
        redirect('candidates.php');
    }

    try {
        // Check if already invited
        $stmt = $conn->prepare("SELECT id FROM invitations WHERE employer_id = ? AND candidate_id = ? AND job_id = ?");
        $stmt->execute([$employer_id, $candidate_id, $job_id]);

        if ($stmt->fetch()) {
            flash('error', "You have already invited this candidate to this job.");
        } else {
            $stmt = $conn->prepare("INSERT INTO invitations (employer_id, candidate_id, job_id, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$employer_id, $candidate_id, $job_id, $message]);
            flash('success', "Invitation sent successfully!");
        }
    } catch (PDOException $e) {
        flash('error', "Error sending invitation: " . $e->getMessage());
    }

    redirect("candidate_details.php?id=" . $candidate_id);
} else {
    redirect('candidates.php');
}
?>