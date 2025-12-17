<?php
// candidate_details.php - UPDATED VERSION with Preview Mode
require_once 'includes/header.php';
require_login();

$candidate_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Check if user is employer OR the candidate themselves
$can_view = false;
$is_preview_mode = false;

if (get_role() === 'employer') {
    $can_view = true;
} elseif (get_role() === 'candidate' && $candidate_id == $_SESSION['user_id']) {
    $can_view = true;
    $is_preview_mode = true;
}

if (!$can_view) {
    redirect('index.php');
}

$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch();

if (!$candidate) {
    if ($is_preview_mode) {
        redirect('candidate_dashboard.php');
    } else {
        redirect('candidates.php');
    }
}

// Fetch Documents (exclude profile pictures)
$stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = ? AND user_type = 'candidate' AND type != 'profile_pic'");
$stmt->execute([$candidate_id]);
$documents = $stmt->fetchAll();
?>

<div class="container mt-4 mb-4">
    <!-- PREVIEW MODE HEADER -->
    <?php if ($is_preview_mode): ?>
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 2em;">👁️</div>
            <div>
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.5rem;">Profile Preview Mode</h3>
                <p style="margin: 0; opacity: 0.9;">This is how employers see your profile. Any changes you make will be reflected here.</p>
            </div>
        </div>
        <div style="margin-top: 1rem; display: flex; gap: 1rem;">
            <a href="candidate_profile.php" style="padding: 8px 16px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3);">
                ✏️ Edit Profile
            </a>
            <a href="candidate_dashboard.php" style="padding: 8px 16px; background: white; color: #667eea; text-decoration: none; border-radius: 5px; font-weight: 500;">
                ← Back to Dashboard
            </a>
        </div>
    </div>
    <?php else: ?>
        <a href="candidates.php" style="color: #666; margin-bottom: 1rem; display: inline-block; text-decoration: none;">
            &larr; Back to Candidates
        </a>
    <?php endif; ?>

    <div class="card">
        <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem; padding: 2rem;">
            <!-- Left Column: Profile Picture & Basic Info -->
            <div style="text-align: center;">
                <?php 
                $photo_url = get_profile_picture_url($candidate['id'], $conn);
                ?>
                <img src="<?php echo $photo_url; ?>"
                    alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 4px solid #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($candidate['full_name']); ?></h2>
                <p style="color: #007bff; font-weight: 600; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($candidate['title'] ?: 'Job Seeker'); ?>
                </p>

                <!-- Contact Information -->
                <div style="text-align: left; padding: 1rem; background: #f8fafc; border-radius: 8px; margin-top: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 0.25rem;">Email</div>
                        <a href="mailto:<?php echo htmlspecialchars($candidate['email']); ?>" 
                           style="color: #007bff; text-decoration: none; word-break: break-all;">
                            <?php echo htmlspecialchars($candidate['email']); ?>
                        </a>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 0.25rem;">Phone</div>
                        <div><?php echo htmlspecialchars($candidate['phone'] ?: 'Not provided'); ?></div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 0.25rem;">Education Level</div>
                        <div><?php echo htmlspecialchars($candidate['education_level'] ?: 'Not specified'); ?></div>
                    </div>
                    
                    <div>
                        <div style="font-weight: 600; color: #495057; margin-bottom: 0.25rem;">Profile Status</div>
                        <span style="background: <?php echo ($candidate['visibility'] ?? 'visible') === 'visible' ? '#d4edda' : '#f8d7da'; ?>; 
                              padding: 4px 12px; border-radius: 20px; font-size: 0.85em; display: inline-block; margin-top: 5px; color: <?php echo ($candidate['visibility'] ?? 'visible') === 'visible' ? '#155724' : '#721c24'; ?>;">
                            <?php echo ($candidate['visibility'] ?? 'visible') === 'visible' ? '👁️ Public' : '👻 Private'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Detailed Information -->
            <div>
                <!-- Professional Summary -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 1rem;">Professional Summary</h3>
                    <div style="color: #555; line-height: 1.7; white-space: pre-line; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; min-height: 120px;">
                        <?php 
                        if ($candidate['bio']) {
                            echo nl2br(htmlspecialchars($candidate['bio']));
                        } else {
                            echo '<div style="text-align: center; color: #6c757d; padding: 2rem;">';
                            echo 'No professional summary provided.';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Skills & Expertise -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 1rem;">Skills & Expertise</h3>
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
                        <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px; color: #666; text-align: center; border: 1px solid #e2e8f0;">
                            <div style="font-size: 2em; margin-bottom: 10px;">🔧</div>
                            <p style="margin: 0;">No skills listed yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Documents Section -->
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #333; border-bottom: 2px solid #fd7e14; padding-bottom: 10px; margin-bottom: 1rem;">Documents</h3>
                    <?php if (count($documents) > 0): ?>
                        <div style="display: grid; gap: 1rem; margin-top: 0.5rem;">
                            <?php foreach ($documents as $doc): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;"
                                     onmouseover="this.style.backgroundColor='#f0f9ff'" onmouseout="this.style.backgroundColor='#f8fafc'">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="background: white; padding: 0.5rem; border-radius: 4px; border: 1px solid #e2e8f0; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
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
                                            <div style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($doc['original_name']); ?></div>
                                            <div style="font-size: 0.85rem; color: #666; text-transform: capitalize; margin-top: 0.25rem;">
                                                <?php echo str_replace('_', ' ', $doc['type']); ?>
                                                • <?php echo format_file_size($doc['file_size']); ?>
                                                • Uploaded: <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="view_document.php?id=<?php echo $doc['id']; ?>" target="_blank" 
                                        style="font-size: 0.9rem; padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; white-space: nowrap;"
                                        onmouseover="this.style.backgroundColor='#0056b3'" onmouseout="this.style.backgroundColor='#007bff'">
                                        Download
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="padding: 2rem; background: #f8f9fa; border-radius: 8px; color: #666; text-align: center; border: 1px solid #e2e8f0;">
                            <div style="font-size: 3em; margin-bottom: 10px;">📁</div>
                            <h4 style="margin-bottom: 10px; color: #6c757d;">No Documents Available</h4>
                            <p style="margin: 0;">No public documents (CVs, Diplomas, Certificates) have been uploaded.</p>
                            <?php if ($is_preview_mode): ?>
                            <a href="candidate_documents.php" style="display: inline-block; margin-top: 1rem; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                                📤 Upload Documents
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Employer Invitation Section (Only for employers) -->
                <?php
                if (get_role() === 'employer' && !$is_preview_mode) {
                    $employer_id = $_SESSION['user_id'];
                    // Fetch active jobs
                    $stmt = $conn->prepare("SELECT id, title FROM jobs WHERE employer_id = ? AND status = 'active'");
                    $stmt->execute([$employer_id]);
                    $my_jobs = $stmt->fetchAll();
                ?>
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
                        <h3 style="color: #333; border-bottom: 2px solid #6f42c1; padding-bottom: 10px; margin-bottom: 1rem;">📩 Invite to Apply</h3>
                        <?php if (count($my_jobs) > 0): ?>
                            <div style="background: #f0f9ff; padding: 1.5rem; border-radius: 8px; border: 1px solid #bae6fd;">
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
                                                  placeholder="Hi <?php echo htmlspecialchars(explode(' ', $candidate['full_name'])[0]); ?>, we think your profile is a great match for our company..."></textarea>
                                    </div>

                                    <button type="submit" 
                                            style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;"
                                            onmouseover="this.style.backgroundColor='#0056b3'" onmouseout="this.style.backgroundColor='#007bff'">
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
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Profile Completeness Indicator (Only in preview mode) -->
    <?php if ($is_preview_mode): ?>
    <div class="card mt-4" style="background: #f0f9ff; border: 1px solid #bae6fd;">
        <h3 style="color: #0369a1; border-bottom: 2px solid #0369a1; padding-bottom: 10px; margin-bottom: 1rem;">📊 Profile Completeness</h3>
        
        <?php
        // Calculate profile completeness
        $completion_items = [];
        
        // Check profile picture
        $has_profile_pic = !str_contains(get_profile_picture_url($candidate['id'], $conn), 'ui-avatars.com');
        $completion_items[] = [
            'name' => 'Profile Picture',
            'complete' => $has_profile_pic,
            'icon' => '📸',
            'tip' => 'Add a professional profile picture'
        ];
        
        // Check professional title
        $has_title = !empty($candidate['title']) && $candidate['title'] !== 'Job Seeker';
        $completion_items[] = [
            'name' => 'Professional Title',
            'complete' => $has_title,
            'icon' => '💼',
            'tip' => 'Set a clear professional title'
        ];
        
        // Check bio
        $has_bio = !empty($candidate['bio']);
        $completion_items[] = [
            'name' => 'Professional Summary',
            'complete' => $has_bio,
            'icon' => '📝',
            'tip' => 'Write a compelling professional summary'
        ];
        
        // Check skills
        $has_skills = !empty($candidate['skills']);
        $completion_items[] = [
            'name' => 'Skills',
            'complete' => $has_skills,
            'icon' => '🔧',
            'tip' => 'List your key skills'
        ];
        
        // Check documents
        $has_documents = count($documents) > 0;
        $completion_items[] = [
            'name' => 'Documents',
            'complete' => $has_documents,
            'icon' => '📁',
            'tip' => 'Upload your CV and certificates'
        ];
        
        // Calculate completion percentage
        $completed_count = 0;
        foreach ($completion_items as $item) {
            if ($item['complete']) $completed_count++;
        }
        $completion_percentage = round(($completed_count / count($completion_items)) * 100);
        ?>
        
        <!-- Progress Bar -->
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <span style="font-weight: 500;">Overall Completion:</span>
                <span style="font-weight: bold; color: #0369a1;"><?php echo $completion_percentage; ?>%</span>
            </div>
            <div style="height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden;">
                <div style="width: <?php echo $completion_percentage; ?>%; height: 100%; background: linear-gradient(90deg, #28a745, #20c997); border-radius: 5px;"></div>
            </div>
        </div>
        
        <!-- Completion Items -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <?php foreach ($completion_items as $item): ?>
            <div style="padding: 1rem; background: white; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 1.5em;"><?php echo $item['icon']; ?></div>
                <div style="flex: 1;">
                    <div style="font-weight: 500; color: #333;"><?php echo $item['name']; ?></div>
                    <div style="font-size: 0.85em; color: #666;"><?php echo $item['tip']; ?></div>
                </div>
                <div style="width: 24px; height: 24px; border-radius: 50%; background: <?php echo $item['complete'] ? '#d4edda' : '#f8d7da'; ?>; display: flex; align-items: center; justify-content: center;">
                    <?php echo $item['complete'] ? '✓' : '!'; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Tips -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #bae6fd;">
            <h4 style="color: #0369a1; margin-bottom: 0.75rem;">💡 Tips for a Strong Profile:</h4>
            <ul style="color: #555; margin: 0; padding-left: 1.5rem;">
                <li style="margin-bottom: 0.5rem;">Use a professional, recent photo where your face is clearly visible</li>
                <li style="margin-bottom: 0.5rem;">Write a concise summary highlighting your achievements and career goals</li>
                <li style="margin-bottom: 0.5rem;">List skills relevant to the jobs you're applying for</li>
                <li style="margin-bottom: 0.5rem;">Upload your latest CV and any relevant certificates</li>
                <li style="margin-bottom: 0.5rem;">Keep your contact information up-to-date</li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
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
