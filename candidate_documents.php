<?php
// candidate_documents.php - DATABASE STORAGE VERSION
require_once 'includes/header.php';
require_login();

// Ensure user is a candidate
if (get_role() !== 'candidate') {
    redirect('employer_dashboard.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $type = $_POST['type'];
    
    // Use the upload function
    $result = upload_file($_FILES['document'], $conn);
    
    if (isset($result['error'])) {
        $error_msg = $result['error'];
    } else {
        // Save to database - FIXED: Use correct column name 'user_id' and add user_type
        try {
            $stmt = $conn->prepare("INSERT INTO documents (user_id, user_type, type, file_path, original_name, file_content, file_size, mime_type) 
                                    VALUES (?, 'candidate', ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id, 
                $type, 
                $result['path'], 
                $result['original_name'],
                $result['content'],
                $result['size'],
                $result['type']
            ]);
            $success_msg = "✅ Document uploaded successfully! (Stored in database)";
        } catch (PDOException $e) {
            $error_msg = "Failed to save document to database: " . $e->getMessage();
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $doc_id = $_GET['delete'];
    
    // Verify ownership and delete - FIXED: Use user_id and user_type
    $stmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND user_id = ? AND user_type = 'candidate'");
    if ($stmt->execute([$doc_id, $user_id])) {
        $success_msg = "Document deleted successfully.";
        // Redirect to remove delete parameter from URL
        redirect('candidate_documents.php');
    } else {
        $error_msg = "Failed to delete document.";
    }
}

// Handle File Download/View
if (isset($_GET['view'])) {
    $doc_id = $_GET['view'];
    
    // FIXED: Use user_id and user_type
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ? AND user_type = 'candidate'");
    $stmt->execute([$doc_id, $user_id]);
    $doc = $stmt->fetch();
    
    if ($doc && !empty($doc['file_content'])) {
        // Send file to browser
        header('Content-Type: ' . $doc['mime_type']);
        header('Content-Disposition: inline; filename="' . $doc['original_name'] . '"');
        header('Content-Length: ' . $doc['file_size']);
        echo $doc['file_content'];
        exit;
    } else {
        $error_msg = "File not found or empty.";
    }
}

// Fetch Documents - FIXED: Use user_id and user_type
$stmt = $conn->prepare("SELECT id, type, original_name, file_size, uploaded_at FROM documents 
                        WHERE user_id = ? AND user_type = 'candidate' 
                        ORDER BY uploaded_at DESC");
$stmt->execute([$user_id]);
$documents = $stmt->fetchAll();

// Fetch Profile for sidebar
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Documents - DigiCareer</title>
    <style>
        .document-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }
        .document-item:hover {
            background-color: #f8f9fa;
        }
        .document-info {
            flex-grow: 1;
        }
        .document-actions {
            display: flex;
            gap: 10px;
        }
        .file-size {
            color: #666;
            font-size: 0.9em;
        }
        .file-type {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .alert-success {
            padding: 12px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            padding: 12px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card" style="position: sticky; top: 20px;">
                <div class="text-center mb-3">
                    <?php
                    $photo_url = get_profile_picture_url($user_id, $conn);
                    ?>
                    <img src="<?php echo $photo_url; ?>" alt="Profile"
                        style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4 style="margin: 1rem 0 0.5rem;"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></h4>
                    <p style="color: #666; font-size: 0.9em;">
                        <?php echo htmlspecialchars($user['title'] ?? 'Job Seeker'); ?>
                    </p>
                </div>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_dashboard.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📊 Dashboard
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_profile.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           👤 My Profile
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_documents.php"
                           style="color: #007bff; font-weight: 600; text-decoration: none; display: block; padding: 10px; border-radius: 5px; background-color: #e7f3ff;"
                           onmouseover="this.style.backgroundColor='#d9ebff'" onmouseout="this.style.backgroundColor='#e7f3ff'">
                           📁 My Documents
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_cv_builder.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           ✏️ CV Builder
                        </a>
                    </li>
                    <li style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                        <a href="logout.php"
                           style="color: #dc3545; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#ffe6e6'" onmouseout="this.style.backgroundColor='transparent'">
                           🚪 Logout
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="card mb-4">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="margin: 0; font-size: 1.5rem;">Upload Document</h2>
                    <a href="candidate_dashboard.php" class="btn btn-outline" style="text-decoration: none; padding: 8px 16px; border: 1px solid #ddd; border-radius: 5px;">
                        ← Back to Dashboard
                    </a>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert-success">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert-error">
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="candidate_documents.php" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label class="form-label" style="display: block; margin-bottom: 5px; font-weight: 500;">Document Type</label>
                            <select name="type" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
                                <option value="">Select type...</option>
                                <option value="cv">📄 CV / Resume</option>
                                <option value="diploma">🎓 Diploma / Degree</option>
                                <option value="certificate">🏆 Certificate</option>
                                <option value="cover_letter">✉️ Cover Letter</option>
                                <option value="profile_pic">👤 Profile Picture</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display: block; margin-bottom: 5px; font-weight: 500;">Select File (PDF, DOC, JPG, PNG - Max 5MB)</label>
                            <input type="file" name="document" class="form-control" required 
                                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: white;"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" 
                            style="background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-weight: 500;">
                        📤 Upload Document
                    </button>
                </form>
                
                <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 0.9em;">
                    <strong>ℹ️ Note:</strong> Files are stored securely in the database. Max file size: 5MB.
                </div>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0;">📁 My Documents</h3>
                    <?php if (count($documents) > 0): ?>
                    <span style="background: #007bff; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.9em;">
                        Total: <?php echo count($documents); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <?php if (count($documents) > 0): ?>
                    <div style="border: 1px solid #e9ecef; border-radius: 5px; overflow: hidden;">
                        <?php foreach ($documents as $doc): ?>
                            <div class="document-item">
                                <div class="document-info">
                                    <div style="font-weight: 600; font-size: 1.1em; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($doc['original_name']); ?>
                                        <span class="file-type">
                                            <?php 
                                            $type_labels = [
                                                'cv' => 'CV',
                                                'diploma' => 'Diploma',
                                                'certificate' => 'Certificate',
                                                'cover_letter' => 'Cover Letter',
                                                'profile_pic' => 'Profile Picture'
                                            ];
                                            echo $type_labels[$doc['type']] ?? ucfirst($doc['type']);
                                            ?>
                                        </span>
                                    </div>
                                    <div class="file-size">
                                        Uploaded: <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?> • 
                                        Size: <?php echo format_file_size($doc['file_size']); ?>
                                    </div>
                                </div>
                                <div class="document-actions">
                                    <a href="candidate_documents.php?view=<?php echo $doc['id']; ?>" target="_blank"
                                       class="btn btn-outline" 
                                       style="text-decoration: none; padding: 6px 12px; border: 1px solid #007bff; color: #007bff; border-radius: 4px; font-size: 0.9em;">
                                        👁️ View
                                    </a>
                                    <a href="candidate_documents.php?delete=<?php echo $doc['id']; ?>"
                                       class="btn btn-outline"
                                       style="text-decoration: none; padding: 6px 12px; border: 1px solid #dc3545; color: #dc3545; border-radius: 4px; font-size: 0.9em;"
                                       onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars(addslashes($doc['original_name'])); ?>?');">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #6c757d;">
                        <div style="font-size: 3em; margin-bottom: 10px;">📂</div>
                        <h4 style="margin-bottom: 10px;">No Documents Yet</h4>
                        <p>Upload your first document using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php 
// Helper function to format file size
function format_file_size($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

require_once 'includes/footer.php'; 
?>
