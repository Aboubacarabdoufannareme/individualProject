<?php
// employer_profile.php
require_once 'includes/header.php';
require_login();

// Ensure user is an employer
if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Self-healing: Add logo column if not exists
    try {
        $conn->query("SELECT logo FROM employers LIMIT 1");
    } catch (PDOException $e) {
        $conn->exec("ALTER TABLE employers ADD COLUMN logo VARCHAR(255) DEFAULT NULL AFTER email");
    }

    $company_name = sanitize($_POST['company_name']);
    $website = sanitize($_POST['website']);
    $location = sanitize($_POST['location']);
    $industry = sanitize($_POST['industry']);
    $description = sanitize($_POST['description']);
    $phone = sanitize($_POST['phone']);

    // Handle File Upload
    // Fetch user first to get existing logo if needed
    $stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();

    $logo = $current_user['logo'] ?? null;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload = upload_file($_FILES['logo'], 'uploads/logos/');
        if (isset($upload['success'])) {
            $logo = $upload['path'];
        } else {
            $error_msg = $upload['error'];
        }
    }

    if (!$error_msg) {
        try {
            $stmt = $conn->prepare("
                UPDATE employers 
                SET company_name = ?, website = ?, location = ?, industry = ?, description = ?, phone = ?, logo = ? 
                WHERE id = ?
            ");
            $stmt->execute([$company_name, $website, $location, $industry, $description, $phone, $logo, $user_id]);
            $success_msg = "Company profile updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Fetch Current Data
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
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
                    $logo_url = (isset($user['logo']) && $user['logo']) ? 'uploads/logos/' . $user['logo'] : "https://ui-avatars.com/api/?name=" . urlencode($user['company_name']) . "&background=0ea5e9&color=fff";
                    ?>
                    <img src="<?php echo $logo_url; ?>" alt="Company"
                        style="width: 80px; height: 80px; border-radius: var(--radius-lg); margin: 0 auto 1rem; object-fit: contain; border: 1px solid #e2e8f0;">
                    <h4><?php echo sanitize($user['company_name']); ?></h4>
                </div>
                <!-- ... Sidebar Menu ... -->
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="employer_dashboard.php"
                            style="color: var(--text-main);">Dashboard</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_profile.php"
                            style="color: var(--secondary); font-weight: 600;">Edit Profile</a></li>
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
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="margin: 0;">Edit Company Profile</h2>
                    <a href="employer_details.php?id=<?php echo $user_id; ?>" target="_blank"
                        class="btn btn-outline">Preview Public Profile</a>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-error"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="employer_profile.php" enctype="multipart/form-data">
                    <div class="form-group"
                        style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <label class="form-label">Company Logo</label>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="<?php echo $logo_url; ?>"
                                style="width: 60px; height: 60px; border-radius: 8px; object-fit: contain; border: 1px solid #e2e8f0; background: white;">
                            <input type="file" name="logo" class="form-control" accept="image/*" style="flex: 1;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control"
                            value="<?php echo sanitize($user['company_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control"
                            value="<?php echo sanitize($user['website'] ?? ''); ?>" placeholder="https://example.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control"
                            value="<?php echo sanitize($user['location'] ?? ''); ?>" placeholder="e.g. Niamey, Niger">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Industry</label>
                        <input type="text" name="industry" class="form-control"
                            value="<?php echo sanitize($user['industry'] ?? ''); ?>"
                            placeholder="e.g. Technology, Education">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                            value="<?php echo sanitize($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">About Company</label>
                        <textarea name="description" class="form-control" rows="5"
                            placeholder="Describe your company..."><?php echo sanitize($user['description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="employer_dashboard.php" class="btn btn-outline" style="margin-left: 1rem;">Cancel</a>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>