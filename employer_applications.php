<?php
// employer_applications.php
require_once 'includes/header.php';
require_login();

if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$employer_id = $_SESSION['user_id'];
$application_id = isset($_GET['id']) ? (int) $_GET['id'] : 0; // If viewing a specific one

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $app_id = $_POST['app_id'];

    try {
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $app_id]);
        flash('success', "Application status updated.");
        redirect("employer_applications.php?id=$app_id");
    } catch (PDOException $e) {
        flash('error', "Error updating status.");
    }
}

if ($application_id) {
    // === VIEW SINGLE APPLICATION ===
    $stmt = $conn->prepare("
        SELECT a.*, 
               j.title as job_title, 
               c.full_name, c.email, c.phone, c.id as candidate_id, c.title as candidate_title
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        JOIN candidates c ON a.candidate_id = c.id 
        WHERE a.id = ? AND j.employer_id = ?
    ");
    $stmt->execute([$application_id, $employer_id]);
    $application = $stmt->fetch();

    if (!$application) {
        redirect('employer_applications.php');
    }

    // Get Candidate Documents
    $stmt = $conn->prepare("SELECT * FROM documents WHERE candidate_id = ?");
    $stmt->execute([$application['candidate_id']]);
    $documents = $stmt->fetchAll();

} else {
    // === VIEW LIST ===
    $stmt = $conn->prepare("
        SELECT a.*, j.title as job_title, c.full_name 
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        JOIN candidates c ON a.candidate_id = c.id 
        WHERE j.employer_id = ? 
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$employer_id]);
    $applications = $stmt->fetchAll();
}
?>

<div class="container mt-2 mb-2">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <aside>
            <div class="card">
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="employer_dashboard.php"
                            style="color: var(--text-main);">Dashboard</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_post_job.php"
                            style="color: var(--text-main);">Post a Job</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_applications.php"
                            style="color: var(--secondary); font-weight: 600;">Applications</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidates.php" style="color: var(--text-main);">Search
                            Candidates</a></li>
                </ul>
            </div>
        </aside>

        <main>
            <?php if ($application_id): ?>
                <!-- DETAIL VIEW -->
                <a href="employer_applications.php"
                    style="color: var(--text-muted); display: inline-block; margin-bottom: 1rem;">&larr; Back to List</a>

                <div class="card mb-2">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h2 style="margin: 0;"><?php echo sanitize($application['full_name']); ?></h2>
                            <p style="color: var(--secondary); margin-bottom: 0.5rem;">
                                <?php echo sanitize($application['candidate_title']); ?></p>
                            <p style="margin: 0;">Applied for:
                                <strong><?php echo sanitize($application['job_title']); ?></strong></p>
                        </div>
                        <div>
                            <span style="
                                padding: 0.5rem 1rem; 
                                border-radius: 999px; 
                                font-weight: 600;
                                background: <?php echo $application['status'] == 'accepted' ? '#dcfce7' : ($application['status'] == 'rejected' ? '#fee2e2' : '#e0f2fe'); ?>;
                                color: <?php echo $application['status'] == 'accepted' ? '#166534' : ($application['status'] == 'rejected' ? '#991b1b' : '#075985'); ?>;
                            ">
                                <?php echo ucfirst($application['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card mb-2">
                    <h3>Cover Letter</h3>
                    <p style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md);">
                        <?php echo nl2br(sanitize($application['cover_letter'])); ?>
                    </p>
                </div>

                <div class="card mb-2">
                    <h3>Candidate Documents</h3>
                    <?php if (count($documents) > 0): ?>
                        <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                            <?php foreach ($documents as $doc): ?>
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md);">
                                    <span><?php echo sanitize($doc['original_name']); ?>
                                        (<?php echo strtoupper($doc['type']); ?>)</span>
                                    <a href="uploads/<?php echo $doc['file_path']; ?>" target="_blank" class="btn btn-outline"
                                        style="padding: 0.25rem 0.5rem;">Download</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No documents.</p>
                    <?php endif; ?>
                    <div class="mt-2 text-center">
                        <a href="candidate_details.php?id=<?php echo $application['candidate_id']; ?>"
                            class="btn btn-outline">View Full Profile</a>
                    </div>
                </div>

                <div class="card">
                    <h3>Update Status</h3>
                    <form method="POST" style="display: flex; gap: 1rem; align-items: center;">
                        <input type="hidden" name="app_id" value="<?php echo $application['id']; ?>">
                        <input type="hidden" name="update_status" value="1">
                        <select name="status" class="form-control" style="width: auto;">
                            <option value="pending" <?php echo $application['status'] == 'pending' ? 'selected' : ''; ?>>
                                Pending</option>
                            <option value="reviewed" <?php echo $application['status'] == 'reviewed' ? 'selected' : ''; ?>>
                                Reviewed</option>
                            <option value="accepted" <?php echo $application['status'] == 'accepted' ? 'selected' : ''; ?>>
                                Accepted</option>
                            <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>
                                Rejected</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>

            <?php else: ?>
                <!-- LIST VIEW -->
                <div class="card">
                    <h2>Received Applications</h2>
                    <?php if (count($applications) > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                    <th style="padding: 1rem;">Candidate</th>
                                    <th style="padding: 1rem;">Job</th>
                                    <th style="padding: 1rem;">Date</th>
                                    <th style="padding: 1rem;">Status</th>
                                    <th style="padding: 1rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 1rem; font-weight: 500;"><?php echo sanitize($app['full_name']); ?></td>
                                        <td style="padding: 1rem;"><?php echo sanitize($app['job_title']); ?></td>
                                        <td style="padding: 1rem; color: var(--text-muted);">
                                            <?php echo date('M d', strtotime($app['applied_at'])); ?></td>
                                        <td style="padding: 1rem;">
                                            <span
                                                style="font-size: 0.9rem; color: <?php echo $app['status'] == 'accepted' ? 'var(--success)' : 'inherit'; ?>">
                                                <?php echo ucfirst($app['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <a href="employer_applications.php?id=<?php echo $app['id']; ?>" class="btn btn-outline"
                                                style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="mt-2 text-center text-muted">No applications received yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>