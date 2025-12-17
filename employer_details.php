<?php
// application_review.php
require_once 'includes/header.php';
require_login();

// Ensure user is an employer
if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$employer_id = $_SESSION['user_id'];
$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch application with security check
$stmt = $conn->prepare("
    SELECT a.*, j.title as job_title, j.id as job_id, j.employer_id,
           c.full_name, c.email, c.phone, c.title as candidate_title, c.bio, c.skills
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN candidates c ON a.candidate_id = c.id 
    WHERE a.id = ? AND j.employer_id = ?
");
$stmt->execute([$application_id, $employer_id]);
$application = $stmt->fetch();

if (!$application) {
    echo "<div class='container mt-4'>";
    echo "<div class='alert alert-danger'>Application not found or you don't have permission to view it.</div>";
    echo "<a href='employer_dashboard.php' class='btn btn-primary'>Back to Dashboard</a>";
    echo "</div>";
    require_once 'includes/footer.php';
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes'] ?? '');
    
    $update_stmt = $conn->prepare("UPDATE applications SET status = ?, notes = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $notes, $application_id]);
    
    $success_msg = "Application status updated to: " . ucfirst($new_status);
    
    // Refresh application data
    $stmt->execute([$application_id, $employer_id]);
    $application = $stmt->fetch();
}

// Fetch candidate documents
$stmt = $conn->prepare("
    SELECT * FROM documents 
    WHERE user_id = ? AND user_type = 'candidate' AND type IN ('cv', 'diploma', 'certificate')
    ORDER BY type, uploaded_at DESC
");
$stmt->execute([$application['candidate_id']]);
$documents = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Review Application</h1>
        <a href="employer_applications.php" class="btn btn-outline">← Back to Applications</a>
    </div>

    <?php if (isset($success_msg)): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>

    <div class="row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Main Content -->
        <div>
            <!-- Application Details -->
            <div class="card mb-4">
                <h3>Application Details</h3>
                <table class="table">
                    <tr>
                        <th width="150">Candidate:</th>
                        <td>
                            <strong><?php echo htmlspecialchars($application['full_name']); ?></strong>
                            <br>
                            <small><?php echo htmlspecialchars($application['candidate_title']); ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th>Job:</th>
                        <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                    </tr>
                    <tr>
                        <th>Applied:</th>
                        <td><?php echo date('F j, Y \a\t g:i A', strtotime($application['applied_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Current Status:</th>
                        <td>
                            <span class="badge" style="
                                background: <?php 
                                    if ($application['status'] == 'pending') echo '#e0f2fe';
                                    elseif ($application['status'] == 'reviewed') echo '#fef3c7';
                                    elseif ($application['status'] == 'accepted') echo '#dcfce7';
                                    else echo '#fee2e2';
                                ?>;
                                color: <?php
                                    if ($application['status'] == 'pending') echo '#075985';
                                    elseif ($application['status'] == 'reviewed') echo '#92400e';
                                    elseif ($application['status'] == 'accepted') echo '#166534';
                                    else echo '#991b1b';
                                ?>;
                                padding: 5px 12px;
                                border-radius: 20px;
                                font-weight: 500;
                            ">
                                <?php echo ucfirst($application['status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Cover Letter -->
            <?php if ($application['cover_letter']): ?>
            <div class="card mb-4">
                <h3>Cover Letter</h3>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Candidate Documents -->
            <?php if (count($documents) > 0): ?>
            <div class="card">
                <h3>Candidate Documents</h3>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($documents as $doc): ?>
                        <div style="
                            display: flex; 
                            justify-content: space-between; 
                            align-items: center; 
                            padding: 1rem; 
                            background: #f8fafc; 
                            border-radius: 8px; 
                            border: 1px solid #e2e8f0;
                        ">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="
                                    background: white; 
                                    padding: 0.75rem; 
                                    border-radius: 8px; 
                                    border: 1px solid #e2e8f0;
                                ">
                                    <?php 
                                    $icon = '📄';
                                    if ($doc['type'] == 'cv') $icon = '📝';
                                    elseif ($doc['type'] == 'diploma') $icon = '🎓';
                                    elseif ($doc['type'] == 'certificate') $icon = '🏆';
                                    echo $icon;
                                    ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($doc['original_name']); ?></div>
                                    <div style="font-size: 0.85rem; color: #666;">
                                        <?php echo strtoupper($doc['type']); ?> • 
                                        <?php echo round($doc['file_size'] / 1024, 1); ?> KB
                                    </div>
                                </div>
                            </div>
                            <a href="view_document.php?id=<?php echo $doc['id']; ?>" 
                               target="_blank" 
                               class="btn btn-primary">
                                View
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar - Actions -->
        <div>
            <!-- Update Status Form -->
            <div class="card mb-4">
                <h3>Update Status</h3>
                <form method="POST" action="application_review.php?id=<?php echo $application_id; ?>">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">New Status</label>
                        <select name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="pending" <?php echo $application['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="reviewed" <?php echo $application['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                            <option value="accepted" <?php echo $application['status'] == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                            <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Notes (Optional)</label>
                        <textarea name="notes" 
                                  style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; height: 100px;"
                                  placeholder="Add private notes about this application..."><?php echo htmlspecialchars($application['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">
                        Update Application Status
                    </button>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h3>Quick Actions</h3>
                <div style="display: grid; gap: 0.75rem;">
                    <a href="candidate_details.php?id=<?php echo $application['candidate_id']; ?>" 
                       class="btn btn-outline" 
                       style="text-align: left; padding: 12px; display: flex; align-items: center; gap: 10px;">
                        👤 View Candidate Profile
                    </a>
                    
                    <a href="job_details.php?id=<?php echo $application['job_id']; ?>" 
                       class="btn btn-outline" 
                       style="text-align: left; padding: 12px; display: flex; align-items: center; gap: 10px;">
                        📋 View Job Details
                    </a>
                    
                    <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>" 
                       class="btn btn-outline" 
                       style="text-align: left; padding: 12px; display: flex; align-items: center; gap: 10px;">
                        ✉️ Email Candidate
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
