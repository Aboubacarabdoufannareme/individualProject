<?php
// Turn on full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// candidate_profile.php - SIMPLIFIED WORKING VERSION
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
    
    // Handle Profile Picture - SIMPLE DATABASE APPROACH
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

/* // Get profile picture URL
function get_profile_picture_url($user, $conn = null) {
    if (!empty($user['profile_picture']) && $conn) {
        try {
            // Get from documents table
            $stmt = $conn->prepare("SELECT file_content, mime_type FROM documents 
                                    WHERE candidate_id = ? AND file_path = ? AND type = 'profile_pic' 
                                    LIMIT 1");
            $stmt->execute([$user['id'], $user['profile_picture']]);
            $result = $stmt->fetch();
            
            if ($result && !empty($result['file_content'])) {
                return 'data:' . $result['mime_type'] . ';base64,' . base64_encode($result['file_content']);
            }
        } catch (PDOException $e) {
            // Continue to default
        }
    }
    
    // Default avatar
    $name = urlencode($user['full_name'] ?? 'User');
    return "https://ui-avatars.com/api/?name=$name&background=0ea5e9&color=fff&size=128";
}
**/
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
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card" style="position: sticky; top: 20px;">
                <div class="text-center mb-3">
                    <img src="<?php echo get_profile_picture_url($user, $conn); ?>" 
                         alt="Profile" class="profile-preview" id="profilePreview">
                    <h4 style="margin: 1rem 0 0.5rem;"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p style="color: #666; font-size: 0.9em;">
                        <?php echo htmlspecialchars($user['title'] ?? 'Job Seeker'); ?>
                    </p>
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
                <h1 style="margin-bottom: 1.5rem;">Edit Profile</h1>

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

                <form method="POST" action="candidate_profile.php" enctype="multipart/form-data">
                    
                    <!-- Profile Picture -->
                    <div class="form-section">
                        <h3>Profile Picture</h3>
                        <div class="photo-controls">
                            <img src="<?php echo get_profile_picture_url($user, $conn); ?>" 
                                 alt="Current" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;"
                                 id="currentPhoto">
                            
                            <input type="file" name="profile_picture" id="profilePictureInput" 
                                   accept="image/*" style="margin-bottom: 10px;">
                            
                            <?php if (!empty($user['profile_picture'])): ?>
                            <label style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                                <input type="checkbox" name="remove_profile_picture" value="1">
                                <span style="font-size: 0.9em;">Remove current photo</span>
                            </label>
                            <?php endif; ?>
                            
                            <div style="font-size: 0.85em; color: #666; margin-top: 5px;">
                                Max 2MB. JPG, PNG, or GIF.
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1rem;">
                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Full Name *</label>
                                <input type="text" name="full_name" style="width: 100%; padding: 10px;"
                                    value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>

                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Professional Title *</label>
                                <input type="text" name="title" style="width: 100%; padding: 10px;"
                                    value="<?php echo htmlspecialchars($user['title'] ?? 'Job Seeker'); ?>" required>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Phone Number</label>
                                <input type="tel" name="phone" style="width: 100%; padding: 10px;"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <div>
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Education Level</label>
                                <select name="education_level" style="width: 100%; padding: 10px;">
                                    <option value="">Select Level</option>
                                    <option value="High School" <?php echo ($user['education_level'] ?? '') == 'High School' ? 'selected' : ''; ?>>High School</option>
                                    <option value="Bachelor" <?php echo ($user['education_level'] ?? '') == 'Bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                    <option value="Master" <?php echo ($user['education_level'] ?? '') == 'Master' ? 'selected' : ''; ?>>Master's Degree</option>
                                    <option value="PhD" <?php echo ($user['education_level'] ?? '') == 'PhD' ? 'selected' : ''; ?>>PhD</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="form-section">
                        <h3>Skills & Expertise</h3>
                        <div style="margin-top: 1rem;">
                            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Skills (comma separated)</label>
                            <input type="text" name="skills" style="width: 100%; padding: 10px;"
                                value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>"
                                placeholder="PHP, MySQL, JavaScript">
                        </div>
                    </div>

                    <!-- Bio -->
                    <div class="form-section">
                        <h3>Professional Summary</h3>
                        <div style="margin-top: 1rem;">
                            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Bio / Professional Summary</label>
                            <textarea name="bio" style="width: 100%; padding: 10px; height: 150px;"
                                placeholder="Tell employers about your experience..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Visibility -->
                    <div class="form-section">
                        <h3>Profile Visibility</h3>
                        <div style="display: flex; gap: 2rem; margin-top: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="radio" name="visibility" value="visible" 
                                       <?php echo (!isset($user['visibility']) || $user['visibility'] === 'visible') ? 'checked' : ''; ?>>
                                <span>Visible to Employers</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="radio" name="visibility" value="hidden" 
                                       <?php echo (isset($user['visibility']) && $user['visibility'] === 'hidden') ? 'checked' : ''; ?>>
                                <span>Hidden (Private)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div style="margin-top: 2rem;">
                        <button type="submit" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            Save Profile Changes
                        </button>
                        <a href="candidate_dashboard.php" style="padding: 12px 24px; margin-left: 1rem; text-decoration: none;">
                            Cancel
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
        }
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
