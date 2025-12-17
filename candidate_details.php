<?php
// candidate_preview.php
require_once 'includes/header.php';
require_login();

// Ensure user is a candidate
if (get_role() !== 'candidate') {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];

// Fetch candidate data
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$user_id]);
$candidate = $stmt->fetch();

if (!$candidate) {
    redirect('candidate_dashboard.php');
}

// Fetch documents
$stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = ? AND user_type = 'candidate' AND type != 'profile_pic'");
$stmt->execute([$user_id]);
$documents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview My Profile - DigiCareer</title>
    <style>
        .preview-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .preview-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ff4757;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .profile-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-4">
        <div class="preview-header">
            <div style="position: relative;">
                <div class="preview-badge">PREVIEW MODE</div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="font-size: 2.5em;">👁️</div>
                    <div>
                        <h1 style="margin: 0 0 0.5rem 0; font-size: 1.8rem;">Employer's View of Your Profile</h1>
                        <p style="margin: 0; opacity: 0.9; font-size: 1rem;">
                            This is exactly how employers see your profile. Use this to check what information is visible and make improvements.
                        </p>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="candidate_profile.php" class="btn" style="padding: 10px 20px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3);">
                        ✏️ Edit Profile
                    </a>
                    <a href="candidate_dashboard.php" class="btn" style="padding: 10px 20px; background: white; color: #667eea; text-decoration: none; border-radius: 5px; font-weight: 500;">
                        ← Back to Dashboard
                    </a>
                    <button onclick="window.print()" class="btn" style="padding: 10px 20px; background: rgba(255,255,255,0.1); color: white; text-decoration: none; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3);">
                        🖨️ Print Preview
                    </button>
                </div>
            </div>
        </div>

        <!-- The profile display from candidate_details.php goes here -->
        <div class="card">
            <div class="profile-container">
                <!-- Left Column: Profile Picture & Basic Info -->
                <div class="text-center">
                    <?php 
                    $photo_url = get_profile_picture_url($user_id, $conn);
                    ?>
                    <img src="<?php echo $photo_url; ?>"
                        alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 4px solid #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($candidate['full_name']); ?></h2>
                    <p style="color: #007bff; font-weight: 600;">
                        <?php echo htmlspecialchars($candidate['title'] ?: 'Job Seeker'); ?>
                    </p>

                    <div style="margin-top: 1.5rem; text-align: left; padding: 0 15px;">
                        <!-- ... display basic info ... -->
                    </div>
                </div>

                <!-- Right Column: Detailed Info -->
                <div>
                    <!-- ... display bio, skills, documents ... -->
                </div>
            </div>
        </div>

        <!-- Preview Tips Section -->
        <div class="card mt-4" style="background: #f0f9ff; border: 1px solid #bae6fd;">
            <h3 style="color: #0369a1; border-bottom: 2px solid #0369a1; padding-bottom: 10px; margin-bottom: 1rem;">📝 Profile Review Tips</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div style="padding: 1rem; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 1.5em; margin-bottom: 10px;">📸</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #333;">Profile Picture</h4>
                    <p style="margin: 0; color: #666; font-size: 0.9em;">Use a professional, clear headshot. Smile naturally.</p>
                </div>
                <div style="padding: 1rem; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 1.5em; margin-bottom: 10px;">📝</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #333;">Professional Summary</h4>
                    <p style="margin: 0; color: #666; font-size: 0.9em;">Keep it concise (150-300 words) highlighting key achievements.</p>
                </div>
                <div style="padding: 1rem; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 1.5em; margin-bottom: 10px;">🔧</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #333;">Skills</h4>
                    <p style="margin: 0; color: #666; font-size: 0.9em;">List relevant skills. Use keywords from job descriptions.</p>
                </div>
                <div style="padding: 1rem; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 1.5em; margin-bottom: 10px;">📁</div>
                    <h4 style="margin: 0 0 0.5rem 0; color: #333;">Documents</h4>
                    <p style="margin: 0; color: #666; font-size: 0.9em;">Upload updated CV, diplomas, and certificates.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight incomplete sections
            const profileCheck = {
                'profile_picture': <?php echo $photo_url && !str_contains($photo_url, 'ui-avatars.com') ? 'true' : 'false'; ?>,
                'bio': <?php echo !empty($candidate['bio']) ? 'true' : 'false'; ?>,
                'skills': <?php echo !empty($candidate['skills']) ? 'true' : 'false'; ?>,
                'documents': <?php echo count($documents) > 0 ? 'true' : 'false'; ?>
            };

            // Show completion status
            let completed = 0;
            for (const key in profileCheck) {
                if (profileCheck[key]) completed++;
            }
            const completionRate = Math.round((completed / Object.keys(profileCheck).length) * 100);
            
            // Add completion badge
            const header = document.querySelector('.preview-header');
            const completionBadge = document.createElement('div');
            completionBadge.innerHTML = `
                <div style="margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <span>Profile Completeness:</span>
                        <span style="font-weight: bold;">${completionRate}%</span>
                    </div>
                    <div style="height: 6px; background: rgba(255,255,255,0.2); border-radius: 3px; overflow: hidden;">
                        <div style="width: ${completionRate}%; height: 100%; background: white; border-radius: 3px;"></div>
                    </div>
                </div>
            `;
            header.querySelector('.action-buttons').parentNode.appendChild(completionBadge);
        });
    </script>
</body>
</html>
