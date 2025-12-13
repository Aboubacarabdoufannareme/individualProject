<?php
// job_details.php
require_once 'includes/header.php';

$job_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    // Check if column exists
    $conn->query("SELECT logo FROM employers LIMIT 1");
    $stmt = $conn->prepare("
        SELECT j.*, e.company_name, e.description as company_bio, e.website, e.logo 
        FROM jobs j 
        JOIN employers e ON j.employer_id = e.id 
        WHERE j.id = ?
    ");
} catch (PDOException $e) {
    $stmt = $conn->prepare("
        SELECT j.*, e.company_name, e.description as company_bio, e.website 
        FROM jobs j 
        JOIN employers e ON j.employer_id = e.id 
        WHERE j.id = ?
    ");
}
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    redirect('jobs.php');
}

// Handle Application
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    if (!is_logged_in()) {
        redirect('login.php');
    }

    if (get_role() !== 'candidate') {
        $error_msg = "Only candidates can apply to jobs.";
    } else {
        $candidate_id = $_SESSION['user_id'];
        $cover_letter = sanitize($_POST['cover_letter']);

        // Check if already applied
        $stmt = $conn->prepare("SELECT * FROM applications WHERE job_id = ? AND candidate_id = ?");
        $stmt->execute([$job_id, $candidate_id]);

        if ($stmt->rowCount() > 0) {
            $error_msg = "You have already applied for this position.";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO applications (job_id, candidate_id, cover_letter) VALUES (?, ?, ?)");
                $stmt->execute([$job_id, $candidate_id, $cover_letter]);
                $success_msg = "Application submitted successfully! Good luck.";
            } catch (PDOException $e) {
                $error_msg = "Error submitting application.";
            }
        }
    }
}
?>

<div class="container mt-2 mb-2">
    <a href="jobs.php" style="color: var(--text-muted); margin-bottom: 1rem; display: inline-block;">&larr; Back to
        Jobs</a>

    <div class="row" style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem;">
        <main>
            <div class="card mb-2">
                <h1 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo sanitize($job['title']); ?></h1>
                <p style="font-size: 1.1rem; color: var(--secondary); font-weight: 600; margin-bottom: 1rem;">
                    <?php echo sanitize($job['company_name']); ?>
                </p>
                <div
                    style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1.5rem;">
                    <span>📍 <?php echo sanitize($job['location']); ?></span>
                    <span>💼 <?php echo sanitize($job['type']); ?></span>
                    <?php if ($job['salary_range']): ?>
                        <span>💰 <?php echo sanitize($job['salary_range']); ?></span>
                    <?php endif; ?>
                    <span>📅 Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                </div>

                <h3>Job Description</h3>
                <div style="line-height: 1.7; white-space: pre-wrap; margin-bottom: 2rem;">
                    <?php echo sanitize($job['description']); ?>
                </div>
            </div>

            <!-- Application Form -->
            <?php if (is_logged_in() && get_role() === 'candidate'): ?>
                <div class="card" id="apply">
                    <h3>Apply for this position</h3>
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php elseif ($error_msg): ?>
                        <div class="alert alert-error"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <?php if (!$success_msg): ?>
                        <form method="POST" action="job_details.php?id=<?php echo $job_id; ?>#apply">
                            <div class="form-group">
                                <label class="form-label">Cover Letter</label>
                                <textarea name="cover_letter" class="form-control" rows="5"
                                    placeholder="Explain why you are the best fit for this role..."></textarea>
                            </div>
                            <button type="submit" name="apply" class="btn btn-primary btn-block">Submit Application</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php elseif (!is_logged_in()): ?>
                <div class="card bg-light text-center">
                    <p>Please <a href="login.php" style="color: var(--secondary); font-weight: bold;">Login</a> or <a
                            href="register.php" style="color: var(--secondary); font-weight: bold;">Register</a> as a
                        candidate to apply.</p>
                </div>
            <?php endif; ?>
        </main>

        <aside>
            <div class="card">
                <div class="text-center mb-1">
                    <?php 
                    $logo_url = "https://ui-avatars.com/api/?name=" . urlencode($job['company_name']) . "&background=0f172a&color=fff";
                    if (isset($job['logo']) && $job['logo']) {
                        $logo_url = 'uploads/logos/' . $job['logo'];
                    }
                    ?>
                    <img src="<?php echo $logo_url; ?>" alt="Company Logo" style="width: 80px; height: 80px; object-fit: contain; border-radius: var(--radius-md); border: 1px solid #e2e8f0; margin-bottom: 0.5rem;">
                </div>
                <h3>About the Company</h3>
                <p><?php echo sanitize($job['company_bio']); ?></p>
                <?php if ($job['website']): ?>
                    <a href="<?php echo sanitize($job['website']); ?>" target="_blank"
                        class="btn btn-outline btn-block mt-1">Visit Website</a>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>