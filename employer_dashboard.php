<?php
// employer_dashboard.php
require_once 'includes/header.php';
require_login();

// Ensure user is an employer
if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$employer_id = $_SESSION['user_id'];

// Get Employer Info
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$employer_id]);
$user = $stmt->fetch();

// Get Stats
// 1. Active Jobs
$stmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'active'");
$stmt->execute([$employer_id]);
$active_jobs = $stmt->fetchColumn();

// 2. Total Applications received (across all jobs)
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.employer_id = ?
");
$stmt->execute([$employer_id]);
$total_applications = $stmt->fetchColumn();

// Get Recent Applications
$stmt = $conn->prepare("
    SELECT a.*, j.title as job_title, c.full_name as candidate_name, c.id as candidate_id 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN candidates c ON a.candidate_id = c.id 
    WHERE j.employer_id = ? 
    ORDER BY a.applied_at DESC 
    LIMIT 5
");
$stmt->execute([$employer_id]);
$recent_apps = $stmt->fetchAll();

// Get Recent Jobs
$stmt = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$employer_id]);
$recent_jobs = $stmt->fetchAll();
?>

<div class="container mt-2">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card">
                <div class="text-center mb-2">
                    <?php
                    $logo_url = (isset($user['logo']) && $user['logo']) ? 'uploads/logos/' . $user['logo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['company_name']) . "&background=0ea5e9&color=fff";
                    ?>
                    <img src="<?php echo $logo_url; ?>" alt="Company"
                        style="width: 80px; height: 80px; border-radius: var(--radius-lg); margin: 0 auto 1rem; object-fit: contain;">
                    <h4><?php echo sanitize($user['company_name']); ?></h4>
                </div>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="employer_dashboard.php"
                            style="color: var(--secondary); font-weight: 600;">Dashboard</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_profile.php"
                            style="color: var(--text-main);">Edit Profile</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_post_job.php"
                            style="color: var(--text-main);">Post a Job</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_applications.php"
                            style="color: var(--text-main);">Applications</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidates.php" style="color: var(--text-main);">Search
                            Candidates</a></li>
                    <li style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;"><a href="logout.php"
                            style="color: var(--danger);">Logout</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <h2 class="mb-2">Employer Dashboard</h2>

            <!-- Stats -->
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="card" style="border-left: 4px solid var(--secondary);">
                    <h5 style="color: var(--text-muted); font-size: 0.9rem;">Active Jobs</h5>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--primary); margin: 0;">
                        <?php echo $active_jobs; ?>
                    </p>
                </div>
                <div class="card" style="border-left: 4px solid var(--accent);">
                    <h5 style="color: var(--text-muted); font-size: 0.9rem;">Total Applications</h5>
                    <p style="font-size: 2rem; font-weight: 700; color: var(--primary); margin: 0;">
                        <?php echo $total_applications; ?>
                    </p>
                </div>
                <div class="card"
                    style="border-left: 4px solid var(--success); display: flex; align-items: center; justify-content: center;">
                    <a href="employer_post_job.php" class="btn btn-primary">Post New Job</a>
                </div>
            </div>

            <?php
            // Fetch Sent Invitations
            $stmt = $conn->prepare("
                SELECT i.*, j.title as job_title, c.full_name as candidate_name 
                FROM invitations i 
                JOIN jobs j ON i.job_id = j.id 
                JOIN candidates c ON i.candidate_id = c.id 
                WHERE i.employer_id = ? 
                ORDER BY i.created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$employer_id]);
            $invitations = $stmt->fetchAll();
            ?>

            <!-- Sent Invitations -->
            <?php if (count($invitations) > 0): ?>
                <div class="card mb-2">
                    <h3 class="mb-2">Sent Invitations</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                <th style="padding: 0.75rem;">Candidate</th>
                                <th style="padding: 0.75rem;">Job</th>
                                <th style="padding: 0.75rem;">Date</th>
                                <th style="padding: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invitations as $inv): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem; font-weight: 500;">
                                        <?php echo sanitize($inv['candidate_name']); ?>
                                    </td>
                                    <td style="padding: 0.75rem;"><?php echo sanitize($inv['job_title']); ?></td>
                                    <td style="padding: 0.75rem; color: var(--text-muted);">
                                        <?php echo date('M d', strtotime($inv['created_at'])); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <span style="
                                            padding: 0.25rem 0.75rem; 
                                            border-radius: 999px; 
                                            font-size: 0.85rem; 
                                            background: <?php echo $inv['status'] == 'accepted' ? '#dcfce7' : ($inv['status'] == 'declined' ? '#fee2e2' : '#e0f2fe'); ?>;
                                            color: <?php echo $inv['status'] == 'accepted' ? '#166534' : ($inv['status'] == 'declined' ? '#991b1b' : '#075985'); ?>;
                                        ">
                                            <?php echo ucfirst($inv['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Recent Applications -->
            <div class="card mb-2">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0;">Recent Applications</h3>
                    <a href="employer_applications.php" style="font-size: 0.9rem; color: var(--secondary);">View All</a>
                </div>

                <?php if (count($recent_apps) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                <th style="padding: 0.75rem;">Candidate</th>
                                <th style="padding: 0.75rem;">Job</th>
                                <th style="padding: 0.75rem;">Date</th>
                                <th style="padding: 0.75rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_apps as $app): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem; font-weight: 500;">
                                        <?php echo sanitize($app['candidate_name']); ?>
                                    </td>
                                    <td style="padding: 0.75rem;"><?php echo sanitize($app['job_title']); ?></td>
                                    <td style="padding: 0.75rem; color: var(--text-muted);">
                                        <?php echo date('M d', strtotime($app['applied_at'])); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <a href="employer_applications.php?id=<?php echo $app['id']; ?>" class="btn btn-outline"
                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No applications received yet.</p>
                <?php endif; ?>
            </div>

            <!-- Your Jobs -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0;">Your Recent Jobs</h3>
                </div>
                <?php if (count($recent_jobs) > 0): ?>
                    <?php foreach ($recent_jobs as $job): ?>
                        <div
                            style="padding: 1rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md); margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600;"><?php echo sanitize($job['title']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    <?php echo ucfirst($job['status']); ?> • Posted
                                    <?php echo date('M d', strtotime($job['created_at'])); ?>
                                </div>
                            </div>
                            <!-- In a real app we'd add edit/delete here -->
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted);">You haven't posted any jobs yet.</p>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>