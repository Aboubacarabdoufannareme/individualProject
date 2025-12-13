<?php
// employer_post_job.php
require_once 'includes/header.php';
require_login();

if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$employer_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']); // Note: Sanitize strips HTML, for rich text use a library or careful filtering
    $location = sanitize($_POST['location']);
    $type = sanitize($_POST['type']);
    $salary_range = sanitize($_POST['salary_range']);

    if (empty($title) || empty($description)) {
        $error_msg = "Title and Description are required.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, description, location, type, salary_range) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employer_id, $title, $description, $location, $type, $salary_range]);
            $success_msg = "Job posted successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error posting job: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-2 mb-2">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card">
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="employer_dashboard.php"
                            style="color: var(--text-main);">Dashboard</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_post_job.php"
                            style="color: var(--secondary); font-weight: 600;">Post a Job</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="employer_applications.php"
                            style="color: var(--text-main);">Applications</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidates.php" style="color: var(--text-main);">Search
                            Candidates</a></li>
                </ul>
            </div>
        </aside>

        <main>
            <div class="card">
                <h2 class="mb-2">Post a New Job</h2>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-error"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="employer_post_job.php">
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="title" class="form-control" required
                            placeholder="e.g. Senior PHP Developer">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control"
                            placeholder="e.g. Niamey, Niger (or Remote)">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Job Type</label>
                            <select name="type" class="form-control">
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Internship">Internship</option>
                                <option value="Freelance">Freelance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Salary Range (Optional)</label>
                            <input type="text" name="salary_range" class="form-control"
                                placeholder="e.g. 200,000 - 300,000 FCFA">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Job Description</label>
                        <textarea name="description" class="form-control" rows="10" required
                            placeholder="Describe the role, responsibilities, and requirements..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Publish Job</button>
                    <a href="employer_dashboard.php" class="btn btn-outline" style="margin-left: 1rem;">Cancel</a>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>