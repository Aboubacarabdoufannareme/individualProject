<?php
// candidate_profile.php - FIXED VERSION
require_once 'includes/header.php';
require_login();

// Ensure user is a candidate
if (get_role() !== 'candidate') {
    redirect('employer_dashboard.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? $user['full_name']);
    $title = sanitize($_POST['title'] ?? ($user['title'] ?? 'Job Seeker'));
    $phone = sanitize($_POST['phone'] ?? ($user['phone'] ?? ''));
    $bio = sanitize($_POST['bio'] ?? ($user['bio'] ?? ''));
    $education_level = sanitize($_POST['education_level'] ?? ($user['education_level'] ?? ''));
    $skills = sanitize($_POST['skills'] ?? ($user['skills'] ?? ''));
    $visibility = sanitize($_POST['visibility'] ?? ($user['visibility'] ?? 'visible'));
    
    // Handle Profile Picture
    $profile_picture_filename = $user['profile_picture'] ?? null;
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($_FILES['profile_picture']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                
                // Read file content
                $file_content = file_get_contents($_FILES['profile_picture']['tmp_name']);
                if ($file_content !== false) {
                    
                    // Generate unique filename
                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                    
                    try {
                        // 1. Delete any existing profile picture
                        $conn->prepare("DELETE FROM documents WHERE candidate_id = ? AND type = 'profile_pic'")
                             ->execute([$user_id]);
                        
                        // 2. Store new profile picture in documents table
                        $stmt = $conn->prepare("INSERT INTO documents (candidate_id, type, file_path, original_name, file_content, file_size, mime_type) 
                                                VALUES (?, 'profile_pic', ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $user_id,
                            $new_filename,
                            $filename,
                            $file_content,
                            $_FILES['profile_picture']['size'],
                            $_FILES['profile_picture']['type']
                        ]);
                        
                        // 3. Store filename reference in candidates table
                        $profile_picture_filename = $new_filename;
                        
                    } catch (PDOException $e) {
                        $error_msg = "Failed to save profile picture to database.";
                    }
                } else {
                    $error_msg = "Could not read uploaded image.";
                }
            } else {
                $error_msg = "Image too large (max 2MB).";
            }
        } else {
            $error_msg = "Invalid image format. Use JPG, PNG, or GIF.";
        }
    } elseif (isset($_POST['remove_profile_picture'])) {
        // Remove profile picture
        try {
            $conn->prepare("DELETE FROM documents WHERE candidate_id = ? AND type = 'profile_pic'")
                 ->execute([$user_id]);
            $profile_picture_filename = null;
        } catch (Exception $e) {
            // Silently continue
            $profile_picture_filename = null;
        }
    }

    if (empty($error_msg)) {
        try {
            // Update candidates table
            $stmt = $conn->prepare("
                UPDATE candidates 
                SET full_name = ?, 
                    title = ?, 
                    phone = ?, 
                    bio = ?, 
                    education_level = ?, 
                    skills = ?, 
                    visibility = ?, 
                    profile_picture = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $full_name, 
                $title, 
                $phone, 
                $bio, 
                $education_level, 
                $skills, 
                $visibility, 
                $profile_picture_filename,
                $user_id
            ]);
            
            $success_msg = "✅ Profile updated successfully!";
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error_msg = "Error saving profile: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - DigiCareer</title>
    <style>
        .profile-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: #f8f9fa;
        }
        .form-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .photo-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .visibility-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
            margin-top: 5px;
        }
        .visible-badge {
            background: #d4edda;
            color: #155724;
        }
        .hidden-badge {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card" style="position: sticky; top: 20px;">
                <div class="text-center mb-3">
                    <?php
                    // FIXED: Use the correct function signature
                    $photo_url = get_profile_picture_url($user_id, $conn);
                    ?>
                    <img src="<?php echo $photo_url; ?>" 
                         alt="Profile" class="profile-preview" id="profilePreview">
                    <h4 style="margin: 1rem 0 0.5rem;"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p style="color: #666; font-size: 0.9em;">
                        <?php echo htmlspecialchars($user['title'] ?? 'Job Seeker'); ?>
                    </p>
                    <div class="visibility-badge <?php echo ($user['visibility'] ?? 'visible') === 'visible' ? 'visible-badge' : 'hidden-badge'; ?>">
                        <?php echo ($user['visibility'] ?? 'visible') === 'visible' ? '👁️ Visible' : '👻 Hidden'; ?>
                    </div>
                </div>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_dashboard.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📊 Dashboard
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_profile.php"
                           style="color: #007bff; font-weight: 600; text-decoration: none; display: block; padding: 10px; border-radius: 5px; background-color: #e7f3ff;"
                           onmouseover="this.style.backgroundColor='#d9ebff'" onmouseout="this.style.backgroundColor='#e7f3ff'">
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Edit Profile</h1>
                    <a href="candidate_dashboard.php" class="btn btn-outline" style="text-decoration: none; padding: 8px 16px; border: 1px solid #ddd; border-radius: 5px;">
                        ← Back to Dashboard
                    </a>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success" style="padding: 12px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                        ✅ <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_msg): ?>
                    <div class="alert alert-error" style="padding: 12px; background: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 20px;">
                        ❌ <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="candidate_profile.php" enctype="multipart/form-data" id="profileForm">
                    
                    <!-- Profile Picture -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Profile Picture</h3>
                        <div class="photo-controls">
                            <?php
                            // FIXED: Use the correct function signature here too
                            $current_photo_url = get_profile_picture_url($user_id, $conn);
                            ?>
                            <img src="<?php echo $current_photo_url; ?>" 
                                 alt="Current" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 3px solid #ddd;"
                                 id="currentPhoto">
                            
                            <input type="file" name="profile_picture" id="profilePictureInput" 
                                   accept="image/*" style="margin-bottom: 10px; padding: 8px;">
                            
                            <?php if (!empty($user['profile_picture'])): ?>
                            <label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; cursor: pointer;">
                                <input type="checkbox" name="remove_profile_picture" value="1" id="removePhoto">
                                <span style="font-size: 0.9em; color: #721c24;">🗑️ Remove current photo</span>
                            </label>
                            <?php endif; ?>
                            
                            <div style="font-size: 0.85em; color: #666; margin-top: 5px;">
                                Max 2MB. JPG, PNG, or GIF recommended.
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px;">Personal Information</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1rem;">
                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Full Name *</label>
                                <input type="text" name="full_name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>

                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Professional Title *</label>
                                <input type="text" name="title" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['title'] ?? 'Job Seeker'); ?>" required
                                    placeholder="e.g. Software Engineer">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Phone Number</label>
                                <input type="tel" name="phone" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    placeholder="+227 XX XX XX XX">
                            </div>

                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Education Level</label>
                                <select name="education_level" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; height: auto;">
                                    <option value="">Select Level</option>
                                    <option value="High School" <?php echo ($user['education_level'] ?? '') == 'High School' ? 'selected' : ''; ?>>High School</option>
                                    <option value="Bachelor" <?php echo ($user['education_level'] ?? '') == 'Bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                    <option value="Master" <?php echo ($user['education_level'] ?? '') == 'Master' ? 'selected' : ''; ?>>Master's Degree</option>
                                    <option value="PhD" <?php echo ($user['education_level'] ?? '') == 'PhD' ? 'selected' : ''; ?>>PhD</option>
                                    <option value="Other" <?php echo ($user['education_level'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #fd7e14; padding-bottom: 10px;">Skills & Expertise</h3>
                        <div style="margin-top: 1rem;">
                            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Skills (comma separated)</label>
                            <input type="text" name="skills" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>"
                                placeholder="PHP, MySQL, JavaScript, React, Project Management">
                            <div style="font-size: 0.85em; color: #666; margin-top: 5px;">
                                Separate each skill with a comma.
                            </div>
                        </div>
                    </div>

                    <!-- Bio -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #6f42c1; padding-bottom: 10px;">Professional Summary</h3>
                        <div style="margin-top: 1rem;">
                            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Bio / Professional Summary</label>
                            <textarea name="bio" style="width: 100%; padding: 10px; height: 150px; border: 1px solid #ddd; border-radius: 4px;"
                                placeholder="Tell employers about your experience, achievements, and career goals..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <div style="font-size: 0.85em; color: #666; margin-top: 5px;">
                                Recommended: 150-300 words highlighting your key achievements.
                            </div>
                        </div>
                    </div>

                    <!-- Visibility -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">Profile Visibility</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                            <div style="background: white; padding: 1rem; border-radius: 8px; border: 2px solid #e2e8f0;"
                                 onmouseover="this.style.borderColor='#28a745'" onmouseout="this.style.borderColor='#e2e8f0'">
                                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; width: 100%;">
                                    <input type="radio" name="visibility" value="visible" 
                                           <?php echo (!isset($user['visibility']) || $user['visibility'] === 'visible') ? 'checked' : ''; ?>
                                           style="margin: 0;">
                                    <div>
                                        <div style="font-weight: 600; color: #155724;">👁️ Visible to Employers</div>
                                        <div style="font-size: 0.9em; color: #666; margin-top: 5px;">Your profile can be found in search results</div>
                                    </div>
                                </label>
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: 8px; border: 2px solid #e2e8f0;"
                                 onmouseover="this.style.borderColor='#dc3545'" onmouseout="this.style.borderColor='#e2e8f0'">
                                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; width: 100%;">
                                    <input type="radio" name="visibility" value="hidden" 
                                           <?php echo (isset($user['visibility']) && $user['visibility'] === 'hidden') ? 'checked' : ''; ?>
                                           style="margin: 0;">
                                    <div>
                                        <div style="font-weight: 600; color: #721c24;">👻 Hidden (Private)</div>
                                        <div style="font-size: 0.9em; color: #666; margin-top: 5px;">Only visible to you</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
                        <button type="submit" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 500;">
                            💾 Save Profile Changes
                        </button>
                        <button type="reset" style="padding: 12px 24px; background: white; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;">
                            ↩️ Reset Changes
                        </button>
                        <a href="candidate_dashboard.php" style="padding: 12px 24px; text-decoration: none; background: white; border: 1px solid #ddd; border-radius: 5px;">
                            ❌ Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
// Preview image before upload
document.getElementById('profilePictureInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
            document.getElementById('currentPhoto').src = e.target.result;
            // Uncheck remove photo if selecting new one
            if (document.getElementById('removePhoto')) {
                document.getElementById('removePhoto').checked = false;
            }
        }
        reader.readAsDataURL(this.files[0]);
    }
});

// When remove photo is checked
document.getElementById('removePhoto')?.addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('profilePictureInput').value = '';
        // Show default avatar
        const defaultAvatar = '<?php echo get_profile_picture_url($user_id, $conn); ?>';
        document.getElementById('profilePreview').src = defaultAvatar;
        document.getElementById('currentPhoto').src = defaultAvatar;
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const fullName = this.querySelector('input[name="full_name"]').value.trim();
    const title = this.querySelector('input[name="title"]').value.trim();
    
    if (!fullName || !title) {
        e.preventDefault();
        alert('Please fill in Full Name and Professional Title');
        return false;
    }
    
    const fileInput = this.querySelector('input[name="profile_picture"]');
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size / 1024 / 1024;
        if (fileSize > 2) {
            e.preventDefault();
            alert('Profile picture must be less than 2MB');
            return false;
        }
    }
    
    return true;
});
</script>

<?php 
// Remove error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
require_once 'includes/footer.php'; 
?>
