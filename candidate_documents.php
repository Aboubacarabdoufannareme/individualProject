<?php
// candidate_documents.php
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

    // Create uploads directory if not exists
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $result = upload_file($_FILES['document'], $upload_dir);

    if (isset($result['error'])) {
        $error_msg = $result['error'];
    } else {
        $file_path = $result['path'];
        $original_name = $_FILES['document']['name'];

        try {
            $stmt = $conn->prepare("INSERT INTO documents (candidate_id, type, file_path, original_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $type, $file_path, $original_name]);
            $success_msg = "Document uploaded successfully!";
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
        if (file_exists('uploads/' . $doc['file_path'])) {
            unlink('uploads/' . $doc['file_path']);
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

<div class="container mt-2 mb-2">
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
                        <label class="form-label">Select File (PDF, DOC, IMG)</label>
                        <input type="file" name="document" class="form-control" required style="padding: 0.5rem;">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
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
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <a href="uploads/<?php echo $doc['file_path']; ?>" target="_blank"
                                            class="btn btn-outline"
                                            style="font-size: 0.85rem; padding: 0.25rem 0.5rem;">View</a>
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

<?php require_once 'includes/footer.php'; ?>