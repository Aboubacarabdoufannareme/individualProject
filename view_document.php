<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// view_document.php
require_once 'includes/header.php';
require_login();

$doc_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch document
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->execute([$doc_id]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Document not found.");
}

// Check permissions
if (get_role() === 'candidate') {
    // Candidate can only view their own documents
    if ($doc['candidate_id'] != $_SESSION['user_id']) {
        die("Access denied.");
    }
} elseif (get_role() === 'employer') {
    // Employer can view documents of candidates they're viewing
    // You might want to add additional checks here
    $stmt = $conn->prepare("SELECT id FROM candidates WHERE id = ? AND visibility = 'visible'");
    $stmt->execute([$doc['candidate_id']]);
    $candidate = $stmt->fetch();
    
    if (!$candidate) {
        die("Access denied.");
    }
} else {
    die("Access denied.");
}

// Send file to browser
header('Content-Type: ' . $doc['mime_type']);
header('Content-Disposition: attachment; filename="' . $doc['original_name'] . '"');
header('Content-Length: ' . $doc['file_size']);
echo $doc['file_content'];
exit;
?>
