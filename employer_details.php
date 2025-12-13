<?php
// employer_details.php
require_once 'includes/header.php';

$employer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$employer_id) {
    redirect('index.php');
}

// Fetch Employer Info
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$employer_id]);
$employer = $stmt->fetch();

if (!$employer) {
    echo "<div class='container mt-2'><p>Employer not found.</p></div>";
    require_once 'includes/footer.php';
    exit;
}

// Fetch Active Jobs
$stmt = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? AND status = 'active' ORDER BY created_at DESC");
$stmt->execute([$employer_id]);
$jobs = $stmt->fetchAll();
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 3rem 0; border-bottom: 1px solid #cbd5e1;">
    <div class="container text-center">
        <?php
        $logo_url = $employer['logo'] ? 'uploads/logos/' . $employer['logo'] : "https://ui-avatars.com/api/?name=" . urlencode($employer['company_name']) . "&background=0ea5e9&color=fff";
        ?>
        <img src="<?php echo $logo_url; ?>" alt="Logo"
            style="width: 120px; height: 120px; border-radius: 12px; object-fit: contain; background: white; padding: 10px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h1 style="margin-top: 1rem; color: var(--text-main);"><?php echo sanitize($employer['company_name']); ?></h1>

        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 0.5rem; color: var(--text-muted);">
            <?php if ($employer['industry']): ?>
                <span><i class="opacity-50">📂</i> <?php echo sanitize($employer['industry']); ?></span>
            <?php endif; ?>
            <?php if ($employer['location']): ?>
                <span><i class="opacity-50">📍</i> <?php echo sanitize($employer['location']); ?></span>
            <?php endif; ?>
        </div>

        <?php if ($employer['website']): ?>
            <div style="margin-top: 1rem;">
                <a href="<?php echo sanitize($employer['website']); ?>" target="_blank" class="btn btn-outline">Visit
                    Website</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container mt-2 mb-2">
    <div class="row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Main Content -->
        <main>
            <!-- About -->
            <div class="card mb-2">
                <h3>About Company</h3>
                <?php if ($employer['description']): ?>
                    <div style="white-space: pre-line; line-height: 1.6;">
                        <?php echo sanitize($employer['description']); ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No description added.</p>
                <?php endif; ?>
            </div>

            <!-- Jobs -->
            <div class="card">
                <h3>Open Positions</h3>
                <?php if (count($jobs) > 0): ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($jobs as $job): ?>
                            <div
                                style="border: 1px solid #e2e8f0; padding: 1rem; border-radius: var(--radius-md); transition: transform 0.2s;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <h4 style="margin: 0; margin-bottom: 0.25rem;">
                                            <a href="job_details.php?id=<?php echo $job['id']; ?>"
                                                style="color: var(--text-main); text-decoration: none;">
                                                <?php echo sanitize($job['title']); ?>
                                            </a>
                                        </h4>
                                        <div style="font-size: 0.9rem; color: var(--text-muted);">
                                            <?php echo sanitize($job['type']); ?> •
                                            <?php echo sanitize($job['location'] ?: 'Remote'); ?>
                                        </div>
                                    </div>
                                    <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-sm"
                                        style="background: var(--light); color: var(--primary);">View</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No active job openings at the moment.</p>
                <?php endif; ?>
            </div>
        </main>

        <!-- Sidebar -->
        <aside>
            <div class="card">
                <h3>Contact Info</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php if ($employer['email']): ?>
                        <li style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                            <strong>📧</strong> <span><?php echo sanitize($employer['email']); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($employer['phone']): ?>
                        <li style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                            <strong>📞</strong> <span><?php echo sanitize($employer['phone']); ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($employer['location']): ?>
                        <li style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                            <strong>🏢</strong> <span><?php echo sanitize($employer['location']); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>