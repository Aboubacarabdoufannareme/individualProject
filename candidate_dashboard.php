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

<div class="container mt-4">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card" style="position: sticky; top: 20px;">
                <div class="text-center mb-3">
                    <?php
                    $photo_url = get_profile_picture_url($user_id, $conn);
                    ?>
                    <img src="<?php echo $photo_url; ?>" alt="Profile"
                        style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4 style="margin: 1rem 0 0.5rem;"><?php echo sanitize($user['full_name']); ?></h4>
                    <p style="color: #666; font-size: 0.9em;">
                        <?php echo sanitize($user['title'] ?: 'Job Seeker'); ?>
                    </p>
                    <div style="font-size: 0.8em; color: #666; margin-top: 0.5rem;">
                        <span style="background: #e7f3ff; padding: 2px 8px; border-radius: 12px;">
                            Candidate
                        </span>
                    </div>
                </div>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_dashboard.php"
                           style="color: #007bff; font-weight: 600; text-decoration: none; display: block; padding: 10px; border-radius: 5px; background-color: #e7f3ff;"
                           onmouseover="this.style.backgroundColor='#d9ebff'" onmouseout="this.style.backgroundColor='#e7f3ff'">
                           📊 Dashboard
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_profile.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           👤 My Profile
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_documents.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📁 My Documents
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_cv_builder.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           ✏️ CV Builder
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="jobs.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           🔍 Find Jobs
                        </a>
                    </li>
                    <li style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                        <a href="logout.php"
                           style="color: #dc3545; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#ffe6e6'" onmouseout="this.style.backgroundColor='transparent'">
                           🚪 Logout
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h1 style="margin: 0; font-size: 1.8rem;">Dashboard Overview</h1>
                    <a href="jobs.php" class="btn btn-primary" style="text-decoration: none; padding: 8px 16px; background: #007bff; color: white; border-radius: 5px;">
                        🔍 Find Jobs
                    </a>
                </div>

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
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div class="card" style="border-left: 4px solid #007bff; padding: 1rem;">
                        <h5 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Applications</h5>
                        <p style="font-size: 2rem; font-weight: 700; color: #007bff; margin: 0;">
                            <?php echo count($applications); ?>
                        </p>
                        <a href="candidate_documents.php?filter=applications" style="font-size: 0.85em; color: #666; text-decoration: none;">
                            View all →
                        </a>
                    </div>
                    <div class="card" style="border-left: 4px solid #28a745; padding: 1rem;">
                        <h5 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Invitations</h5>
                        <p style="font-size: 2rem; font-weight: 700; color: #28a745; margin: 0;">
                            <?php echo count($invitations); ?>
                        </p>
                        <?php if (count($invitations) > 0): ?>
                        <a href="#invitations" style="font-size: 0.85em; color: #666; text-decoration: none;">
                            View invitations →
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card" style="border-left: 4px solid #fd7e14; padding: 1rem;">
                        <h5 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Documents</h5>
                        <p style="font-size: 2rem; font-weight: 700; color: #fd7e14; margin: 0;">
                            <?php echo $doc_count; ?>
                        </p>
                        <a href="candidate_documents.php" style="font-size: 0.85em; color: #666; text-decoration: none;">
                            Manage documents →
                        </a>
                    </div>
                    <div class="card" style="border-left: 4px solid #6f42c1; padding: 1rem;">
                        <h5 style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Profile</h5>
                        <p style="font-size: 2rem; font-weight: 700; color: #6f42c1; margin: 0;">
                            <?php echo (!empty($user['profile_picture']) && $user['visibility'] === 'visible') ? '👁️' : '👤'; ?>
                        </p>
                        <a href="candidate_profile.php" style="font-size: 0.85em; color: #666; text-decoration: none;">
                            Edit profile →
                        </a>
                    </div>
                </div>

                <!-- Invitations Section -->
                <?php if (count($invitations) > 0): ?>
                    <div class="card mb-4" id="invitations" style="border: 1px solid #bae6fd;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                            <h3 style="margin: 0;">🎯 Job Invitations</h3>
                            <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85em;">
                                New: <?php echo count($invitations); ?>
                            </span>
                        </div>
                        
                        <?php foreach ($invitations as $inv): ?>
                            <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;"
                                 onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                        <div style="background: #007bff; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                            <?php echo substr($inv['company_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <h4 style="margin: 0 0 5px 0; font-size: 1.1em;">Invited to: <?php echo sanitize($inv['job_title']); ?></h4>
                                            <p style="margin: 0; font-size: 0.9em; color: #666;">
                                                by <strong><?php echo sanitize($inv['company_name']); ?></strong>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($inv['message']): ?>
                                    <div style="background: #f8fafc; padding: 10px; border-radius: 5px; border-left: 3px solid #007bff; margin: 10px 0;">
                                        <p style="margin: 0; font-size: 0.9em; color: #555; font-style: italic;">
                                            "<?php echo sanitize($inv['message']); ?>"
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div style="font-size: 0.85em; color: #888; margin-top: 10px;">
                                        Sent: <?php echo date('F j, Y', strtotime($inv['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                                    <form action="update_invitation.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="invitation_id" value="<?php echo $inv['id']; ?>">
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-primary"
                                                style="background-color: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500;">
                                            ✅ Accept
                                        </button>
                                    </form>
                                    <form action="update_invitation.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="invitation_id" value="<?php echo $inv['id']; ?>">
                                        <input type="hidden" name="status" value="declined">
                                        <button type="submit" class="btn btn-outline"
                                                style="color: #dc3545; border: 1px solid #dc3545; padding: 8px 16px; border-radius: 4px; cursor: pointer; background: white;">
                                            ❌ Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Recent Applications -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                        <h3 style="margin: 0;">📋 Recent Applications</h3>
                        <?php if (count($applications) > 0): ?>
                        <a href="candidate_documents.php?filter=applications" style="font-size: 0.9em; color: #007bff; text-decoration: none;">
                            View all applications →
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($applications) > 0): ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                                <thead>
                                    <tr style="text-align: left; border-bottom: 2px solid #f1f5f9; background: #f8f9fa;">
                                        <th style="padding: 12px; font-weight: 600; color: #495057;">Job Title</th>
                                        <th style="padding: 12px; font-weight: 600; color: #495057;">Company</th>
                                        <th style="padding: 12px; font-weight: 600; color: #495057;">Date</th>
                                        <th style="padding: 12px; font-weight: 600; color: #495057;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;"
                                            onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                                            <td style="padding: 12px; font-weight: 500;">
                                                <div style="font-weight: 600;"><?php echo sanitize($app['job_title']); ?></div>
                                                <?php if ($app['cover_letter']): ?>
                                                <div style="font-size: 0.85em; color: #666; margin-top: 4px;">
                                                    With cover letter
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 12px;">
                                                <div style="font-weight: 500;"><?php echo sanitize($app['company_name']); ?></div>
                                            </td>
                                            <td style="padding: 12px; color: #666;">
                                                <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                            </td>
                                            <td style="padding: 12px;">
                                                <?php
                                                $status_colors = [
                                                    'pending' => ['bg' => '#e0f2fe', 'text' => '#075985', 'label' => '⏳ Pending'],
                                                    'reviewed' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => '👀 Reviewed'],
                                                    'accepted' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => '✅ Accepted'],
                                                    'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => '❌ Rejected']
                                                ];
                                                $status = $app['status'];
                                                $color = $status_colors[$status] ?? $status_colors['pending'];
                                                ?>
                                                <span style="
                                                    padding: 6px 12px; 
                                                    border-radius: 20px; 
                                                    font-size: 0.85em; 
                                                    font-weight: 500;
                                                    background: <?php echo $color['bg']; ?>;
                                                    color: <?php echo $color['text']; ?>;
                                                    display: inline-block;
                                                ">
                                                    <?php echo $color['label']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px;">
                            <div style="font-size: 3em; color: #dee2e6; margin-bottom: 10px;">📝</div>
                            <h4 style="margin-bottom: 10px; color: #6c757d;">No Applications Yet</h4>
                            <p style="color: #6c757d; margin-bottom: 20px;">Start applying to jobs to track your progress here.</p>
                            <a href="jobs.php" class="btn btn-primary" style="text-decoration: none; padding: 10px 20px; background: #007bff; color: white; border-radius: 5px;">
                                🔍 Browse Jobs
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="card" style="margin-top: 2rem; background: #f8fafc;">
                    <h3 style="margin-bottom: 1rem;">⚡ Quick Actions</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <a href="candidate_profile.php" style="text-decoration: none;">
                            <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;"
                                 onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                                <div style="font-size: 2em; margin-bottom: 10px;">👤</div>
                                <div style="font-weight: 600; color: #333;">Update Profile</div>
                                <div style="font-size: 0.85em; color: #666; margin-top: 5px;">Edit your information</div>
                            </div>
                        </a>
                        <a href="candidate_documents.php" style="text-decoration: none;">
                            <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;"
                                 onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                                <div style="font-size: 2em; margin-bottom: 10px;">📁</div>
                                <div style="font-weight: 600; color: #333;">Upload Documents</div>
                                <div style="font-size: 0.85em; color: #666; margin-top: 5px;">CV, certificates, etc.</div>
                            </div>
                        </a>
                        <a href="candidate_cv_builder.php" style="text-decoration: none;">
                            <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;"
                                 onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                                <div style="font-size: 2em; margin-bottom: 10px;">✏️</div>
                                <div style="font-weight: 600; color: #333;">Build CV</div>
                                <div style="font-size: 0.85em; color: #666; margin-top: 5px;">Create professional CV</div>
                            </div>
                        </a>
                        <a href="jobs.php" style="text-decoration: none;">
                            <div style="background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;"
                                 onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                                <div style="font-size: 2em; margin-bottom: 10px;">🔍</div>
                                <div style="font-weight: 600; color: #333;">Find Jobs</div>
                                <div style="font-size: 0.85em; color: #666; margin-top: 5px;">Browse opportunities</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
