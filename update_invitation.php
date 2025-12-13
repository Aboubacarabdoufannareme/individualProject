<?php
// update_invitation.php
require_once 'includes/header.php';
require_login();

if (get_role() !== 'candidate') {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invitation_id = (int) $_POST['invitation_id'];
    $status = $_POST['status']; // accepted or declined
    $candidate_id = $_SESSION['user_id'];

    if (!in_array($status, ['accepted', 'declined'])) {
        flash('error', "Invalid status.");
        redirect('candidate_dashboard.php');
    }

    try {
        // Verify invitation belongs to candidate
        $stmt = $conn->prepare("SELECT id FROM invitations WHERE id = ? AND candidate_id = ?");
        $stmt->execute([$invitation_id, $candidate_id]);

        if ($stmt->fetch()) {
            $stmt = $conn->prepare("UPDATE invitations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $invitation_id]);

            $msg = ($status === 'accepted') ? "Invitation accepted! The employer has been notified." : "Invitation declined.";
            flash('success', $msg);
        } else {
            flash('error', "Invitation not found.");
        }
    } catch (PDOException $e) {
        flash('error', "Error updating invitation.");
    }

    redirect('candidate_dashboard.php');
} else {
    redirect('candidate_dashboard.php');
}
?>