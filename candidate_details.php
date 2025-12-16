<?php
// candidate_details.php - FIXED VERSION
require_once 'includes/header.php';
require_login();

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

// Fetch Documents (exclude profile pictures)
$stmt = $conn->prepare("SELECT * FROM documents WHERE candidate_id = ? AND type != 'profile_pic'");
$stmt->execute([$candidate_id]);
$documents = $stmt->fetchAll();
?>

<div class="container mt-4 mb-4">
    <a href="candidates.php" style="color: #666; margin-bottom: 1rem; display: inline-block; text-decoration: none;">
        &larr; Back to Candidates
    </a>

    <div class="card">
        <div class="row" style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem;">
            <div class="text-center">
                <?php 
                $photo_url = get_profile_picture_url($candidate['id'], $conn);
                ?>
                <img src="<?php echo $photo_url; ?>"
                    alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 4px solid #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($candidate['full_name']); ?></h2>
                <p style="color: #007bff; font-weight: 600;">
                    <?php echo htmlspecialchars($candidate['title'] ?: 'Job Seeker'); ?>
                </p>

                <div style="margin-top: 1.5rem; text-align: left; padding: 0 15px;">
                    <p style="margin-bottom: 0.5rem;">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($candidate['email']); ?>" style="color: #007bff; text-decoration: none;">
                            <?php echo htmlspecialchars($candidate['email']); ?>
                        </a>
                    </p>
                    <p style="margin-bottom: 0.5rem;">
                        <strong>Phone:</strong><br>
                        <?php echo htmlspecialchars($candidate['phone'] ?: 'Not provided'); ?>
                    </p>
                    <p style="margin-bottom: 0.5rem;">
                        <strong>Education:</strong><br> 
                        <?php echo htmlspecialchars($candidate['education_level'] ?: 'Not specified'); ?>
                    </p>
                    <p style="margin-bottom: 0.5rem;">
                        <strong>Profile:</strong><br>
                        <span style="background: <?php echo ($candidate['visibility'] ?? 'visible') === 'visible' ? '#d4edda' : '#f8d7da'; ?>; 
                              padding: 4px 12px; border-radius: 20px; font-size: 0.85em; display: inline-block; margin-top: 5px;">
                            <?php echo ($candidate['visibility'] ?? 'visible') === 'visible' ? '👁️ Public' : '👻 Private'; ?>
                        </span>
                    </p>
                </div>
            </div>

            <div>
                <h3 style="color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 1rem;">About</h3>
                <div style="color: #555; line-height: 1.7; white-space: pre-line; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <?php echo htmlspecialchars($candidate['bio'] ?: 'No bio provided.'); ?>
                </div>

                <h3 style="color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin: 2rem 0 1rem 0;">Skills & Expertise</h3>
                <?php if ($candidate['skills']): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                        <?php 
                        $skills = explode(',', $candidate['skills']);
                        foreach ($skills as $skill): 
                            $trimmed_skill = trim($skill);
                            if (!empty($trimmed_skill)):
                        ?>
                            <span style="background: #f0f9ff; padding: 0.5rem 1rem; border-radius: 99px; color: #007bff; font-weight: 500; border: 1px solid #bae6fd;">
                                <?php echo htmlspecialchars($trimmed_skill); ?>
                            </span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                <?php else: ?>
                    <div style="padding: 1rem; background: #f8f9fa; border-radius: 8px; color: #666; text-align: center;">
                        No skills listed.
                    </div>
                <?php endif; ?>

                <h3 style="color: #333; border-bottom: 2px solid #fd7e14; padding-bottom: 10px; margin: 2rem 0 1rem 0;">Documents</h3>
                <?php if (count($documents) > 0): ?>
                    <div style="display: grid; gap: 1rem; margin-top: 0.5rem;">
                        <?php foreach ($documents as $doc): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;"
                                 onmouseover="this.style.backgroundColor='#f0f9ff'" onmouseout="this.style.backgroundColor='#f8fafc'">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="background: white; padding: 0.5rem; border-radius: 4px; border: 1px solid #e2e8f0; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <?php 
                                        $icon = '📄';
                                        if ($doc['type'] == 'cv') $icon = '📝';
                                        elseif ($doc['type'] == 'diploma') $icon = '🎓';
                                        elseif ($doc['type'] == 'certificate') $icon = '🏆';
                                        elseif ($doc['type'] == 'cover_letter') $icon = '✉️';
                                        echo $icon;
                                        ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($doc['original_name']); ?></div>
                                        <div style="font-size: 0.85rem; color: #666; text-transform: uppercase;">
                                            <?php echo str_replace('_', ' ', $doc['type']); ?>
                                            • <?php echo format_file_size($doc['file_size']); ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- FIXED: Use view.php instead of direct file path -->
                                <a href="view_document.php?id=<?php echo $doc['id']; ?>" target="_blank" class="btn btn-primary"
                                    style="font-size: 0.9rem; padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 4px; text-decoration: none; cursor: pointer;">
                                    Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" style="background: #fffbeb; color: #92400e; padding: 1rem; border-radius: 8px; border: 1px solid #fde68a; margin-top: 0.5rem;">
                        No public documents (CVs, Diplomas, Certificates) available.
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

                    <h3 style="color: #333; border-bottom: 2px solid #6f42c1; padding-bottom: 10px; margin-bottom: 1rem;">Invite to Apply</h3>
                    <?php if (count($my_jobs) > 0): ?>
                        <div class="card" style="background: #f0f9ff; padding: 1.5rem; border-radius: 8px; border: 1px solid #bae6fd;">
                            <form action="send_invitation.php" method="POST">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                
                                <div style="margin-bottom: 1rem;">
                                    <label style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Select Job Opportunity</label>
                                    <select name="job_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" required>
                                        <option value="">-- Choose a Job --</option>
                                        <?php foreach ($my_jobs as $job): ?>
                                            <option value="<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div style="margin-bottom: 1.5rem;">
                                    <label style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Message (Optional)</label>
                                    <textarea name="message" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-height: 100px;" 
                                              placeholder="Hi, we think your profile is a great match for our company..."></textarea>
                                </div>

                                <button type="submit" style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                                    📩 Send Invitation
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div style="background: #fff3cd; color: #856404; padding: 1rem; border-radius: 8px; border: 1px solid #ffeaa7; margin-top: 1rem;">
                            You need to post an active job before you can invite candidates. 
                            <a href="employer_post_job.php" style="color: #007bff; font-weight: 500;">Post a Job</a>
                        </div>
                    <?php endif; ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php 
// Helper function to format file size
function format_file_size($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

require_once 'includes/footer.php'; 
?>
