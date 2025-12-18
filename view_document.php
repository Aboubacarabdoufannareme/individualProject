<?php
// view_document.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any errors
ob_start();

require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    die("You must be logged in to view documents.");
}

$doc_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($doc_id <= 0) {
    die("Invalid document ID.");
}

// First, let's see what we're working with
error_log("=== DOCUMENT VIEW REQUEST ===");
error_log("Document ID: $doc_id");
error_log("User ID: " . $_SESSION['user_id']);
error_log("User Role: " . (get_role() ?? 'unknown'));

try {
    // Fetch document
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$doc_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doc) {
        die("Document not found in database.");
    }
    
    error_log("Document found: " . print_r(array_keys($doc), true));
    
    // Check file content
    if (empty($doc['file_content'])) {
        error_log("WARNING: file_content is empty for document $doc_id");
        // Try to get from file_path
        if (!empty($doc['file_path'])) {
            error_log("Trying file_path: " . $doc['file_path']);
        }
    } else {
        error_log("file_content size: " . strlen($doc['file_content']) . " bytes");
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// SIMPLIFIED PERMISSION CHECK FOR NOW
// In production, implement proper permissions
$allow_access = true; // Temporary for testing

if (!$allow_access) {
    header('Content-Type: text/html');
    echo "<h2>Access Denied</h2>";
    echo "<p>You don't have permission to view this document.</p>";
    exit;
}

// Clear any previous output
ob_end_clean();

// Determine file type and name
$mime_type = 'application/octet-stream'; // Default
$file_name = 'document_' . $doc_id;

if (!empty($doc['mime_type'])) {
    $mime_type = $doc['mime_type'];
} elseif (!empty($doc['original_name'])) {
    // Guess from file extension
    $ext = pathinfo($doc['original_name'], PATHINFO_EXTENSION);
    $mime_types = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'txt' => 'text/plain',
    ];
    if (isset($mime_types[strtolower($ext)])) {
        $mime_type = $mime_types[strtolower($ext)];
    }
}

if (!empty($doc['original_name'])) {
    $file_name = $doc['original_name'];
}

// Clean filename for safe download
$file_name = preg_replace('/[^\w\.\-]/', '_', $file_name);

// Set headers
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');

if (!empty($doc['file_size'])) {
    header('Content-Length: ' . $doc['file_size']);
}

// Check if we have BLOB content
if (!empty($doc['file_content'])) {
    // Check if content is base64 encoded
    if (base64_decode($doc['file_content'], true) !== false) {
        error_log("Content appears to be base64 encoded, decoding...");
        $decoded = base64_decode($doc['file_content']);
        if ($decoded !== false) {
            error_log("Base64 decode successful, outputting " . strlen($decoded) . " bytes");
            echo $decoded;
            exit;
        }
    }
    
    // Output raw content
    error_log("Outputting raw content (" . strlen($doc['file_content']) . " bytes)");
    echo $doc['file_content'];
    
} elseif (!empty($doc['file_path'])) {
    // Try to get from file system
    error_log("No BLOB content, trying file_path: " . $doc['file_path']);
    
    $possible_paths = [
        $doc['file_path'],
        __DIR__ . '/' . $doc['file_path'],
        __DIR__ . '/uploads/' . $doc['file_path'],
        __DIR__ . '/../uploads/' . $doc['file_path'],
        'uploads/' . $doc['file_path'],
        'uploads/documents/' . $doc['file_path']
    ];
    
    $found = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path) && is_file($path)) {
            error_log("Found file at: $path");
            $file_size = filesize($path);
            header('Content-Length: ' . $file_size);
            readfile($path);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        error_log("File not found in any location");
        die("File not found on server.");
    }
} else {
    error_log("No file content available");
    die("File content is not available.");
}

exit;
