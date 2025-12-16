<?php
// candidate_dashboard.php
require_once 'includes/header.php';
require_login();

// Ensure user is a candidate
if (get_role() !== 'candidate') {
    redirect('employer_dashboard.php');
}

$user_id = $_SESSION['user_id'];

// Fetch Candidate Info
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch Recent Applications
$stmt = $conn->prepare("
    SELECT a.*, j.title as job_title, e.company_name 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN employers e ON j.employer_id = e.id 
    WHERE a.candidate_id = ? 
    ORDER BY a.applied_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

// Fetch Documents stats
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM documents WHERE candidate_id = ?");
$stmt->execute([$user_id]);
$doc_count = $stmt->fetch()['count'];
?>

<div class="container mt-2">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        
<aside>
    <div class="card">
        <div class="text-center mb-2">
            <?php
            $photo_url = get_profile_picture_url($user_id, $conn);
            ?>
            <img src="<?php echo $photo_url; ?>" alt="Profile"
                style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem;">
            <h4><?php echo sanitize($user['full_name']); ?></h4>
            <p style="color: var(--text-muted);"><?php echo sanitize($user['title'] ?: 'Job Seeker'); ?></p>
        </div>
        <!-- ... rest of sidebar ... -->
    </div>
</aside>

        <!-- Main Content -->
        <main>
            <h2 class="mb-2">Dashboard Overview</h2>

            <?php
            // Fetch Pending Invitations
            $stmt = $conn->prepare("
                SELECT i.*, j.title as job_title, e.company_name 
                FROM invitations i 
                JOIN jobs j ON i.job_id = j.id 
                JOIN employers e ON i.employer_id = e.id 
                WHERE i.candidate_id = ? AND i.status = 'pending'
                ORDER BY i.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $invitations = $stmt->fetchAll();
            ?>

            <!-- Stats -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="card" style="border-left: 4px solid var(--secondary);">
                    <h5 style="color: var(--text-muted); font-size: 0.9rem;">Applications</h5>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--primary); margin: 0;">
                        <?php echo count($applications); ?>
                    </p>
                </div>
                <div class="card" style="border-left: 4px solid var(--accent);">
                    <h5 style="color: var(--text-muted); font-size: 0.9rem;">Invitations</h5>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--primary); margin: 0;">
                        <?php echo count($invitations); ?>
                    </p>
                </div>
                <div class="card" style="border-left: 4px solid var(--success);">
                    <h5 style="color: var(--text-muted); font-size: 0.9rem;">Documents</h5>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--primary); margin: 0;">
                        <?php echo $doc_count; ?>
                    </p>
                </div>
            </div>

            <!-- Invitations Section -->
            <?php if (count($invitations) > 0): ?>
                <h3 class="mb-2">Job Invitations</h3>
                <div class="card mb-2" style="background: #f0f9ff; border: 1px solid #bae6fd;">
                    <?php foreach ($invitations as $inv): ?>
                        <div
                            style="padding: 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <h4 style="margin-bottom: 0.25rem;">Invited to apply for:
                                    <?php echo sanitize($inv['job_title']); ?>
                                </h4>
                                <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">by
                                    <strong><?php echo sanitize($inv['company_name']); ?></strong>
                                </p>
                                <?php if ($inv['message']): ?>
                                    <p style="font-style: italic; color: var(--text-muted); font-size: 0.9rem;">
                                        "<?php echo sanitize($inv['message']); ?>"</p>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <form action="update_invitation.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="invitation_id" value="<?php echo $inv['id']; ?>">
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="btn btn-primary"
                                        style="background-color: var(--success);">Accept</button>
                                </form>
                                <form action="update_invitation.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="invitation_id" value="<?php echo $inv['id']; ?>">
                                    <input type="hidden" name="status" value="declined">
                                    <button type="submit" class="btn btn-outline"
                                        style="color: var(--danger); border-color: var(--danger);">Decline</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Recent Applications -->
            <h3 class="mb-2">Recent Applications</h3>
            <div class="card">
                <?php if (count($applications) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                <th style="padding: 1rem;">Job Title</th>
                                <th style="padding: 1rem;">Company</th>
                                <th style="padding: 1rem;">Date</th>
                                <th style="padding: 1rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem; font-weight: 500;"><?php echo sanitize($app['job_title']); ?></td>
                                    <td style="padding: 1rem;"><?php echo sanitize($app['company_name']); ?></td>
                                    <td style="padding: 1rem; color: var(--text-muted);">
                                        <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <span style="
                                            padding: 0.25rem 0.75rem; 
                                            border-radius: 999px; 
                                            font-size: 0.85rem; 
                                            background: <?php echo $app['status'] == 'accepted' ? '#dcfce7' : ($app['status'] == 'rejected' ? '#fee2e2' : '#e0f2fe'); ?>;
                                            color: <?php echo $app['status'] == 'accepted' ? '#166534' : ($app['status'] == 'rejected' ? '#991b1b' : '#075985'); ?>;
                                        ">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem;">You haven't applied to any jobs
                        yet. <a href="jobs.php" style="color: var(--secondary);">Find a job</a></p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
