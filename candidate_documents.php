<?php
// candidate_documents.php - FIXED VERSION
require_once 'includes/header.php';
require_login();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    // FIRST: Create uploads directory if it doesn't exist (alternative location)
    $alt_upload_dir = dirname(__DIR__) . '/user_uploads/';
    if (!is_dir($alt_upload_dir)) {
        @mkdir($alt_upload_dir, 0755, true);
    }
    
    // Check multiple possible upload locations
    $upload_dirs = [
        dirname(__DIR__) . '/uploads/',
        dirname(__DIR__) . '/user_uploads/',
        '/home/fannareme.abdou/public_html/individualProject/user_uploads/',
        '/home/fannareme.abdou/my_uploads/'
    ];
    
    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (is_writable($dir)) {
            error_log("Found writable directory: $dir");
            break;
        }
    }

    $result = upload_file($_FILES['document'], 'my_uploads/');

    if (isset($result['error'])) {
        $error_msg = $result['error'];
    } else {
        $file_path = $result['path'];
        $original_name = $_FILES['document']['name'];

        try {
            $stmt = $conn->prepare("INSERT INTO documents (candidate_id, type, file_path, original_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $type, $file_path, $original_name]);
            $success_msg = "✅ Document uploaded successfully!";
            
            // Debug: Show where file was saved
            if (isset($result['full_path'])) {
                $success_msg .= " File saved to: " . $result['full_path'];
            }
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $doc_id = $_GET['delete'];
    // Verify ownership
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND candidate_id = ?");
    $stmt->execute([$doc_id, $user_id]);
    $doc = $stmt->fetch();

    if ($doc) {
        // Try multiple locations for the file
        $possible_paths = [
            dirname(__DIR__) . '/uploads/' . $doc['file_path'],
            dirname(__DIR__) . '/user_uploads/' . $doc['file_path'],
            '/home/fannareme.abdou/my_uploads/' . basename($doc['file_path']),
            '/home/fannareme.abdou/public_html/individualProject/' . $doc['file_path']
        ];
        
        $deleted = false;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                unlink($path);
                $deleted = true;
                break;
            }
        }
        
        $conn->prepare("DELETE FROM documents WHERE id = ?")->execute([$doc_id]);
        $success_msg = "Document deleted.";
    }
}

// Fetch Documents
$stmt = $conn->prepare("SELECT * FROM documents WHERE candidate_id = ? ORDER BY uploaded_at DESC");
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
        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container mt-2 mb-2">
    <div class="debug-info">
        <strong>Debug Info:</strong><br>
        PHP User: <?php echo exec('whoami'); ?><br>
        Upload Max Size: <?php echo ini_get('upload_max_filesize'); ?><br>
        Temp Dir: <?php echo ini_get('upload_tmp_dir'); ?>
    </div>
    
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card">
                <div class="text-center mb-2">
                    <?php
                    $photo_url = "https://ui-avatars.com/api/?name=" . urlencode($user['full_name']) . "&background=0ea5e9&color=fff";
                    if (isset($user['profile_picture']) && $user['profile_picture']) {
                        $photo_url = 'uploads/photos/' . $user['profile_picture'];
                    }
                    ?>
                    <img src="<?php echo $photo_url; ?>" alt="Profile"
                        style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem;">
                    <h4><?php echo sanitize($user['full_name']); ?></h4>
                    <p style="color: var(--text-muted);"><?php echo sanitize($user['title'] ?: 'Job Seeker'); ?></p>
                </div>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_dashboard.php"
                            style="color: var(--text-main);">Dashboard</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_profile.php"
                            style="color: var(--text-main);">My Profile</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_documents.php"
                            style="color: var(--secondary); font-weight: 600;">My Documents</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_cv_builder.php"
                            style="color: var(--text-main);">CV Builder</a></li>
                    <li style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;"><a href="logout.php"
                            style="color: var(--danger);">Logout</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="card mb-2">
                <h2 class="mb-2">Upload Document</h2>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-error"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="candidate_documents.php" enctype="multipart/form-data"
                    style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Document Type</label>
                        <select name="type" class="form-control">
                            <option value="cv">CV / Resume</option>
                            <option value="diploma">Diploma / Degree</option>
                            <option value="certificate">Certificate</option>
                            <option value="cover_letter">Cover Letter</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Select File (PDF, DOC, IMG, Max 5MB)</label>
                        <input type="file" name="document" class="form-control" required style="padding: 0.5rem;">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
                
                <div class="mt-2" style="font-size: 12px; color: #666;">
                    <strong>Note:</strong> If upload fails, the system will automatically try alternative locations.
                </div>
            </div>

            <div class="card">
                <h3>My Documents</h3>
                <?php if (count($documents) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem;">
                                        <div style="font-weight: 600;"><?php echo sanitize($doc['original_name']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">
                                            <?php echo str_replace('_', ' ', $doc['type']); ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #999;">
                                            Path: <?php echo $doc['file_path']; ?>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <?php
                                        // Try multiple locations to find the file
                                        $view_paths = [
                                            'uploads/' . $doc['file_path'],
                                            'user_uploads/' . basename($doc['file_path']),
                                            $doc['file_path']
                                        ];
                                        $found = false;
                                        foreach ($view_paths as $path) {
                                            if (file_exists(dirname(__DIR__) . '/' . $path)) {
                                                $found = true;
                                                ?>
                                                <a href="<?php echo $path; ?>" target="_blank"
                                                    class="btn btn-outline"
                                                    style="font-size: 0.85rem; padding: 0.25rem 0.5rem;">View</a>
                                                <?php
                                                break;
                                            }
                                        }
                                        if (!$found) {
                                            echo '<span style="color: #dc3545; font-size: 0.85rem;">File not found</span>';
                                        }
                                        ?>
                                        <a href="candidate_documents.php?delete=<?php echo $doc['id']; ?>"
                                            class="btn btn-outline"
                                            style="font-size: 0.85rem; padding: 0.25rem 0.5rem; border-color: var(--danger); color: var(--danger);"
                                            onclick="return confirm('Delete this file?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-muted); padding: 1rem;">No documents uploaded yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php 
// Remove error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
require_once 'includes/footer.php'; 
?>
