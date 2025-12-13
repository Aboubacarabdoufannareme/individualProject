<?php
// candidate_details.php
require_once 'includes/header.php';
require_login();

// Check if user is employer (or the candidate themselves, though they have dashboard)
// Allowing candidates to view other candidates? Maybe limit to employers.
// Check if user is employer or the candidate themselves
$can_view = false;
$request_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (get_role() === 'employer') {
    $can_view = true;
} elseif (get_role() === 'candidate' && $request_id == $_SESSION['user_id']) {
    $can_view = true;
}

if (!$can_view) {
    redirect('index.php');
}

$candidate_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch();

if (!$candidate) {
    redirect('candidates.php');
}

// Fetch Documents
$stmt = $conn->prepare("SELECT * FROM documents WHERE candidate_id = ?");
$stmt->execute([$candidate_id]);
$documents = $stmt->fetchAll();
?>

<div class="container mt-2 mb-2">
    <a href="candidates.php" style="color: var(--text-muted); margin-bottom: 1rem; display: inline-block;">&larr; Back
        to Candidates</a>

    <div class="card">
        <div class="row" style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem;">
            <div class="text-center">
                <?php 
                $photo_url = "https://ui-avatars.com/api/?name=" . urlencode($candidate['full_name']) . "&background=0ea5e9&color=fff&size=200";
                if (isset($candidate['profile_picture']) && $candidate['profile_picture']) {
                    $photo_url = 'uploads/photos/' . $candidate['profile_picture'];
                }
                ?>
                <img src="<?php echo $photo_url; ?>"
                    alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 4px solid #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo sanitize($candidate['full_name']); ?>
                </h2>
                <p style="color: var(--secondary); font-weight: 600;">
                    <?php echo sanitize($candidate['title'] ?: 'Job Seeker'); ?></p>

                <div style="margin-top: 1.5rem; text-align: left;">
                    <p style="margin-bottom: 0.5rem;"><strong>Email:</strong><br>
                        <?php echo sanitize($candidate['email']); ?></p>
                    <p style="margin-bottom: 0.5rem;"><strong>Phone:</strong><br>
                        <?php echo sanitize($candidate['phone']); ?></p>
                    <p><strong>Education:</strong><br> <?php echo sanitize($candidate['education_level']); ?></p>
                </div>
            </div>

            <div>
                <h3>About</h3>
                <p style="color: var(--text-main); line-height: 1.7; white-space: pre-line;">
                    <?php echo sanitize($candidate['bio'] ?: 'No bio provided.'); ?>
                </p>

                <h3 class="mt-2">Skills</h3>
                <?php if ($candidate['skills']): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                        <?php foreach (explode(',', $candidate['skills']) as $skill): ?>
                            <span
                                style="background: #f0f9ff; padding: 0.5rem 1rem; border-radius: 99px; color: var(--secondary); font-weight: 500; border: 1px solid #bae6fd;">
                                <?php echo trim($skill); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No skills listed.</p>
                <?php endif; ?>

                <h3 class="mt-2">Documents</h3>
                <?php if (count($documents) > 0): ?>
                    <div style="display: grid; gap: 1rem; margin-top: 0.5rem;">
                        <?php foreach ($documents as $doc): ?>
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid #e2e8f0;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div
                                        style="background: white; padding: 0.5rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        📄</div>
                                    <div>
                                        <div style="font-weight: 600;"><?php echo sanitize($doc['original_name']); ?></div>
                                        <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">
                                            <?php echo str_replace('_', ' ', $doc['type']); ?>
                                        </div>
                                    </div>
                                </div>
                                <a href="uploads/<?php echo $doc['file_path']; ?>" target="_blank" class="btn btn-primary"
                                    style="font-size: 0.9rem; padding: 0.5rem 1rem;">Download</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" style="background: #fffbeb; color: #92400e; margin-top: 0.5rem;">
                        No public documents (CVs, Diplomas) available.
                    </div>
                <?php endif; ?>

                <?php
                // Employer Invitation Section
                if (get_role() === 'employer') {
                    $employer_id = $_SESSION['user_id'];
                    // Fetch active jobs
                    $stmt = $conn->prepare("SELECT id, title FROM jobs WHERE employer_id = ? AND status = 'active'");
                    $stmt->execute([$employer_id]);
                    $my_jobs = $stmt->fetchAll();
                ?>
                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #e2e8f0;">

                    <h3>Invite to Apply</h3>
                    <?php if (count($my_jobs) > 0): ?>
                        <div class="card" style="background: #f0f9ff; border: 1px solid #bae6fd; box-shadow: none;">
                            <form action="send_invitation.php" method="POST">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                
                                <div class="form-group">
                                    <label class="form-label">Select Job Opportunity</label>
                                    <select name="job_id" class="form-control" required>
                                        <option value="">-- Choose a Job --</option>
                                        <?php foreach ($my_jobs as $job): ?>
                                            <option value="<?php echo $job['id']; ?>"><?php echo sanitize($job['title']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Message (Optional)</label>
                                    <textarea name="message" class="form-control" rows="3" placeholder="Hi, we think your profile is a great match..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">Send Invitation</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            You need to post an active job before you can invite candidates. 
                            <a href="employer_post_job.php">Post a Job</a>
                        </div>
                    <?php endif; ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>