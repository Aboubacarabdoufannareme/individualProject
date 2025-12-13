<?php
// jobs.php
require_once 'includes/header.php';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$location = isset($_GET['location']) ? sanitize($_GET['location']) : '';

// Build Query
// Self-healing: Check if logo column exists to avoid query error if not yet added
try {
    $conn->query("SELECT logo FROM employers LIMIT 1");
    // If successful, select logo
    $sql = "SELECT j.*, e.company_name, e.logo FROM jobs j JOIN employers e ON j.employer_id = e.id WHERE j.status = 'active'";
} catch (PDOException $e) {
    // Column doesn't exist yet, fallback
    $sql = "SELECT j.*, e.company_name FROM jobs j JOIN employers e ON j.employer_id = e.id WHERE j.status = 'active'";
}
$params = [];

if ($search) {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR e.company_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($type) {
    $sql .= " AND j.type = ?";
    $params[] = $type;
}

if ($location) {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}

$sql .= " ORDER BY j.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, #0ea5e9 0%, #1e293b 100%); padding: 3rem 0; color: white;">
    <div class="container text-center">
        <h1>Find Your Dream Job</h1>
        <p>Explore hundreds of opportunities from top companies</p>

        <form action="jobs.php" method="GET"
            style="max-width: 800px; margin: 2rem auto 0; display: flex; gap: 0.5rem; background: white; padding: 0.5rem; border-radius: var(--radius-md); flex-wrap: wrap;">
            <input type="text" name="q" placeholder="Job title, keywords..." value="<?php echo $search; ?>"
                style="flex: 1; border: none; padding: 0.75rem; outline: none; font-size: 1rem; min-width: 200px;">
            <input type="text" name="location" placeholder="City or Region" value="<?php echo $location; ?>"
                style="flex: 1; border: none; padding: 0.75rem; outline: none; font-size: 1rem; border-left: 1px solid #e2e8f0; min-width: 150px;">
            <select name="type"
                style="border: none; padding: 0 1rem; border-left: 1px solid #e2e8f0; outline: none; color: var(--text-muted); min-width: 150px;">
                <option value="">Any Type</option>
                <option value="Full-time" <?php echo $type == 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                <option value="Part-time" <?php echo $type == 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                <option value="Internship" <?php echo $type == 'Internship' ? 'selected' : ''; ?>>Internship</option>
                <option value="Freelance" <?php echo $type == 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
            </select>
            <button type="submit" class="btn btn-primary" style="flex-shrink: 0;">Search Jobs</button>
        </form>
    </div>
</div>

<div class="container mt-2 mb-2">
    <?php if (count($jobs) > 0): ?>
        <div style="display: grid; gap: 1rem;">
            <?php foreach ($jobs as $job): ?>
                <div class="card" style="display: flex; gap: 1.5rem; align-items: center; transition: transform 0.2s;">
                    <div style="flex-shrink: 0; display: none; @media(min-width: 600px){display: block;}">
                        <?php
                        $logo_url = "https://ui-avatars.com/api/?name=" . urlencode($job['company_name']) . "&background=0f172a&color=fff";
                        if (isset($job['logo']) && $job['logo']) {
                            $logo_url = 'uploads/logos/' . $job['logo'];
                        }
                        ?>
                        <img src="<?php echo $logo_url; ?>" alt="Company"
                            style="width: 60px; height: 60px; border-radius: var(--radius-md); object-fit: contain; border: 1px solid #e2e8f0;">
                    </div>
                    <div style="flex-grow: 1;">
                        <h3 style="margin: 0; font-size: 1.25rem;">
                            <a href="job_details.php?id=<?php echo $job['id']; ?>"
                                style="color: var(--primary); text-decoration: none;"><?php echo sanitize($job['title']); ?></a>
                        </h3>
                        <p style="margin: 0.25rem 0; font-weight: 500; color: var(--secondary);">
                            <?php echo sanitize($job['company_name']); ?>
                        </p>
                        <div style="display: flex; gap: 1rem; color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">
                            <span>📍 <?php echo sanitize($job['location']); ?></span>
                            <span>💼 <?php echo sanitize($job['type']); ?></span>
                            <?php if ($job['salary_range']): ?>
                                <span>💰 <?php echo sanitize($job['salary_range']); ?></span>
                            <?php endif; ?>
                            <span>📅 <?php echo date('M d', strtotime($job['created_at'])); ?></span>
                        </div>
                    </div>
                    <div style="flex-shrink: 0;">
                        <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-outline">Apply Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center" style="padding: 4rem 0;">
            <h3 style="color: var(--text-muted);">No jobs found matching your criteria.</h3>
            <p><a href="jobs.php" style="color: var(--secondary);">View all jobs</a></p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>