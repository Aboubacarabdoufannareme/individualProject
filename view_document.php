<?php
// view_document.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/header.php';
require_login();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view documents.");
}

$doc_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($doc_id <= 0) {
    die("Invalid document ID.");
}

// Fetch document
try {
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$doc_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if (!$doc) {
    die("Document not found.");
}

// Debug information (remove in production)
error_log("User ID: " . $_SESSION['user_id'] . ", Role: " . get_role() . ", Doc ID: " . $doc_id);

$user_id = $_SESSION['user_id'];
$role = get_role();
$access_granted = false;

// Determine candidate ID from document (handles both old and new structure)
$candidate_id = null;
if (isset($doc['candidate_id']) && !empty($doc['candidate_id'])) {
    // Old structure: candidate_id column exists
    $candidate_id = $doc['candidate_id'];
} elseif (isset($doc['user_id']) && isset($doc['user_type']) && $doc['user_type'] == 'candidate') {
    // New structure: user_id + user_type columns
    $candidate_id = $doc['user_id'];
}

error_log("Candidate ID from document: " . ($candidate_id ?? 'Not found'));

// Permission checking based on role
if ($role === 'candidate') {
    // Candidates can view their own documents
    if ($candidate_id && $candidate_id == $user_id) {
        $access_granted = true;
        error_log("Candidate access granted: owns the document");
    } else {
        // Check if this document belongs to the logged-in candidate
        // Try alternative methods to find connection
        $stmt = $conn->prepare("
            SELECT id FROM documents 
            WHERE id = ? 
            AND (
                (candidate_id = ?) OR 
                (user_id = ? AND user_type = 'candidate')
            )
            LIMIT 1
        ");
        $stmt->execute([$doc_id, $user_id, $user_id]);
        $access_granted = ($stmt->rowCount() > 0);
        error_log("Candidate alternative check: " . ($access_granted ? 'granted' : 'denied'));
    }
    
} elseif ($role === 'employer') {
    // Employers can view documents of candidates who applied to their jobs
    if ($candidate_id) {
        // Check if this candidate applied to any of employer's jobs
        $stmt = $conn->prepare("
            SELECT a.id 
            FROM applications a 
            INNER JOIN jobs j ON a.job_id = j.id 
            WHERE a.candidate_id = ? 
            AND j.employer_id = ?
            LIMIT 1
        ");
        $stmt->execute([$candidate_id, $user_id]);
        $access_granted = ($stmt->rowCount() > 0);
        error_log("Employer check: candidate $candidate_id applied to employer $user_id jobs: " . 
                 ($access_granted ? 'Yes' : 'No'));
    } else {
        error_log("Employer access denied: Could not determine candidate ID from document");
    }
    
    // Additional fallback: check if document is a company logo for this employer
    if (!$access_granted && isset($doc['user_type']) && $doc['user_type'] == 'employer') {
        if (isset($doc['user_id']) && $doc['user_id'] == $user_id) {
            $access_granted = true;
            error_log("Employer access granted: owns the company logo");
        }
    }
    
} else {
    die("Access denied. Invalid user role.");
}

if (!$access_granted) {
    // More informative error message
    $error_msg = "Access denied. You don't have permission to view this document.\n";
    $error_msg .= "Your role: $role, Your ID: $user_id\n";
    $error_msg .= "Document owner (candidate_id): " . ($candidate_id ?? 'Not found') . "\n";
    
    error_log($error_msg);
    
    // Show user-friendly message
    echo "<div style='padding: 20px; max-width: 600px; margin: 50px auto; text-align: center;'>";
    echo "<h2 style='color: #dc3545;'>Access Denied</h2>";
    echo "<p>You don't have permission to view this document.</p>";
    echo "<p><a href='javascript:history.back()' style='color: #007bff;'>Go Back</a></p>";
    echo "</div>";
    exit;
}

// Now we have permission, send the file
if (!empty($doc['file_content'])) {
    // Get file information
    $mime_type = $doc['mime_type'] ?? 'application/octet-stream';
    $file_name = $doc['original_name'] ?? ('document_' . $doc_id . '.pdf');
    $file_size = $doc['file_size'] ?? strlen($doc['file_content']);
    
    // Clean filename
    $file_name = preg_replace('/[^\w\.\-]/', '_', $file_name);
    
    // Set headers
    header('Content-Type: ' . $mime_type);
    
    // Decide whether to show inline or force download
    $disposition = 'attachment'; // Default to download
    
    // Show these file types inline in browser
    $inline_types = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'text/plain', 'text/html', 'text/css',
        'application/json'
    ];
    
    if (in_array($mime_type, $inline_types)) {
        $disposition = 'inline';
    }
    
    header('Content-Disposition: ' . $disposition . '; filename="' . $file_name . '"');
    header('Content-Length: ' . $file_size);
    header('Cache-Control: private, max-age=3600, must-revalidate');
    header('Pragma: public');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT'); // 1 hour cache
    
    // For PDFs, add additional headers
    if ($mime_type == 'application/pdf') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $file_name . '"');
    }
    
    // Output the file content
    echo $doc['file_content'];
    
} elseif (isset($doc['file_path']) && !empty($doc['file_path'])) {
    // Fallback to file system storage
    $possible_paths = [
        $doc['file_path'],
        'uploads/' . $doc['file_path'],
        'uploads/documents/' . $doc['file_path'],
        'uploads/logos/' . $doc['file_path'],
        '../uploads/' . $doc['file_path']
    ];
    
    $file_found = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path) && is_file($path)) {
            $mime_type = mime_content_type($path) ?: 'application/octet-stream';
            $file_size = filesize($path);
            $file_name = $doc['original_name'] ?? basename($path);
            
            // Clean filename
            $file_name = preg_replace('/[^\w\.\-]/', '_', $file_name);
            
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Content-Length: ' . $file_size);
            
            readfile($path);
            $file_found = true;
            break;
        }
    }
    
    if (!$file_found) {
        die("File not found on server. Please contact support.");
    }
    
} else {
    // No file content available
    echo "<div style='padding: 20px; max-width: 600px; margin: 50px auto; text-align: center;'>";
    echo "<h2 style='color: #dc3545;'>File Error</h2>";
    echo "<p>The document file is not available. It may have been deleted or not properly uploaded.</p>";
    echo "<p>File ID: $doc_id</p>";
    echo "<p><a href='javascript:history.back()' style='color: #007bff;'>Go Back</a></p>";
    echo "</div>";
}

exit;
?>
