<?php
// candidate_profile.php - FIXED VERSION
require_once 'includes/header.php';
require_login();

// Enable error reporting temporarily
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure user is a candidate
if (get_role() !== 'candidate') {
    redirect('employer_dashboard.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Self-healing: Add missing columns if needed
$self_healing_queries = [
    'visibility' => "ALTER TABLE candidates ADD COLUMN visibility ENUM('visible', 'hidden') DEFAULT 'visible' AFTER skills",
    'profile_picture' => "ALTER TABLE candidates ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER email",
    'title' => "ALTER TABLE candidates ADD COLUMN title VARCHAR(100) DEFAULT 'Job Seeker' AFTER full_name",
    'phone' => "ALTER TABLE candidates ADD COLUMN phone VARCHAR(20) AFTER email",
    'bio' => "ALTER TABLE candidates ADD COLUMN bio TEXT AFTER phone",
    'skills' => "ALTER TABLE candidates ADD COLUMN skills TEXT AFTER bio",
    'education_level' => "ALTER TABLE candidates ADD COLUMN education_level VARCHAR(50) AFTER skills"
];

foreach ($self_healing_queries as $column => $sql) {
    try {
        $conn->query("SELECT $column FROM candidates LIMIT 1");
    } catch (PDOException $e) {
        $conn->exec($sql);
        error_log("Added missing column: $column");
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $error_msg = "User not found!";
    // Create minimal user record if it doesn't exist
    try {
        $stmt = $conn->prepare("INSERT INTO candidates (id, username, full_name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $_SESSION['username'], $_SESSION['username'], $_SESSION['email']]);
        $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error_msg = "Database error: " . $e->getMessage();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? $user['full_name']);
    $title = sanitize($_POST['title'] ?? ($user['title'] ?? 'Job Seeker'));
    $phone = sanitize($_POST['phone'] ?? ($user['phone'] ?? ''));
    $bio = sanitize($_POST['bio'] ?? ($user['bio'] ?? ''));
    $education_level = sanitize($_POST['education_level'] ?? ($user['education_level'] ?? ''));
    $skills = sanitize($_POST['skills'] ?? ($user['skills'] ?? ''));
    $visibility = sanitize($_POST['visibility'] ?? ($user['visibility'] ?? 'visible'));
    
    // Handle Profile Picture Upload
    $profile_picture = $user['profile_picture'] ?? null;
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        // IMPORTANT: Use database storage method for profile pictures too
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($_FILES['profile_picture']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                // Read file content
                $file_content = file_get_contents($_FILES['profile_picture']['tmp_name']);
                if ($file_content !== false) {
                    // Generate unique filename
                    $new_name = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                    
                    // Store in database (create a separate table or use existing documents table)
                    try {
                        // Store in documents table with type 'profile_pic'
                        $stmt = $conn->prepare("INSERT INTO documents (candidate_id, type, file_path, original_name, file_content, file_size, mime_type) 
                                                VALUES (?, 'profile_pic', ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $user_id,
                            $new_name,
                            $filename,
                            $file_content,
                            $_FILES['profile_picture']['size'],
                            $_FILES['profile_picture']['type']
                        ]);
                        
                        // Update profile picture reference
                        $profile_picture = $new_name;
                    } catch (PDOException $e) {
                        // If documents table doesn't exist or fails, use base64 encoding in candidate table
                        $profile_picture = 'data:' . $_FILES['profile_picture']['type'] . ';base64,' . base64_encode($file_content);
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
    }

    if (empty($error_msg)) {
        try {
            // Update all profile fields including profile_picture
            $stmt = $conn->prepare("
                UPDATE candidates 
                SET full_name = ?, 
                    title = ?, 
                    phone = ?, 
                    bio = ?, 
                    education_level = ?, 
                    skills = ?, 
                    visibility = ?, 
                    profile_picture = ?,
                    updated_at = NOW()
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
                $profile_picture,
                $user_id
            ]);
            
            $success_msg = "✅ Profile updated successfully!";
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
            error_log("Profile update error: " . $e->getMessage());
        }
    }
}

// Get profile picture URL
function get_profile_picture_url($user, $conn = null) {
    if (!empty($user['profile_picture'])) {
        // Check if it's a base64 encoded image
        if (strpos($user['profile_picture'], 'data:image') === 0) {
            return $user['profile_picture'];
        }
        
        // Check if it's stored in documents table
        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT file_content, mime_type FROM documents 
                                        WHERE candidate_id = ? AND file_path = ? AND type = 'profile_pic' 
                                        ORDER BY uploaded_at DESC LIMIT 1");
                $stmt->execute([$user['id'], $user['profile_picture']]);
                $result = $stmt->fetch();
                
                if ($result && !empty($result['file_content'])) {
                    return 'data:' . $result['mime_type'] . ';base64,' . base64_encode($result['file_content']);
                }
            } catch (PDOException $e) {
                // Fall through to default
            }
        }
        
        // Try to find the file
        $possible_paths = [
            'uploads/photos/' . $user['profile_picture'],
            'uploads/' . $user['profile_picture'],
            $user['profile_picture']
        ];
        
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
    }
    
    // Default avatar
    return "https://ui-avatars.com/api/?name=" . urlencode($user['full_name'] ?? 'User') . "&background=0ea5e9&color=fff&size=128";
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
        }
        .upload-hint {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        .form-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .required::after {
            content: " *";
            color: #dc3545;
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
                    <h4 style="margin: 1rem 0 0.5rem;"><?php echo sanitize($user['full_name'] ?? 'User'); ?></h4>
                    <p style="color: var(--text-muted); font-size: 0.9em;">
                        <?php echo sanitize($user['title'] ?? 'Job Seeker'); ?>
                    </p>
                    <div style="font-size: 0.8em; color: #666; margin-top: 0.5rem;">
                        <span style="background: <?php echo ($user['visibility'] ?? 'visible') === 'visible' ? '#d4edda' : '#f8d7da'; ?>; 
                              padding: 2px 8px; border-radius: 12px;">
                            <?php echo ($user['visibility'] ?? 'visible') === 'visible' ? '👁️ Visible' : '👻 Hidden'; ?>
                        </span>
                    </div>
                </div>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_dashboard.php"
                           style="color: var(--text-main); text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📊 Dashboard
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_profile.php"
                           style="color: var(--primary); font-weight: 600; text-decoration: none; display: block; padding: 10px; border-radius: 5px; background-color: #e7f3ff;"
                           onmouseover="this.style.backgroundColor='#d9ebff'" onmouseout="this.style.backgroundColor='#e7f3ff'">
                           👤 My Profile
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_documents.php"
                           style="color: var(--text-main); text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📁 My Documents
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidate_cv_builder.php"
                           style="color: var(--text-main); text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           ✏️ CV Builder
                        </a>
                    </li>
                    <li style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                        <a href="logout.php"
                           style="color: var(--danger); text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
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
                    <a href="candidate_dashboard.php" class="btn btn-outline" style="text-decoration: none;">
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
                    
                    <!-- Profile Picture & Visibility Section -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333;">Profile Identity</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                            <!-- Profile Picture -->
                            <div>
                                <label class="form-label required">Profile Picture</label>
                                <div style="margin-bottom: 1rem;">
                                    <img src="<?php echo get_profile_picture_url($user, $conn); ?>" 
                                         alt="Current" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
                                </div>
                                <input type="file" name="profile_picture" id="profilePictureInput" 
                                       accept="image/*" class="form-control"
                                       onchange="previewImage(this)">
                                <div class="upload-hint">Max 2MB. JPG, PNG, or GIF recommended.</div>
                            </div>
                            
                            <!-- Visibility -->
                            <div>
                                <label class="form-label required">Profile Visibility</label>
                                <div style="background: white; padding: 1rem; border-radius: 8px; border: 1px solid #ddd;">
                                    <div style="margin-bottom: 1rem;">
                                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 8px; border-radius: 6px;"
                                               onmouseover="this.style.backgroundColor='#f0f9ff'" onmouseout="this.style.backgroundColor='transparent'">
                                            <input type="radio" name="visibility" value="visible" 
                                                   <?php echo (!isset($user['visibility']) || $user['visibility'] === 'visible') ? 'checked' : ''; ?>>
                                            <div>
                                                <div style="font-weight: 500;">👁️ Visible to Employers</div>
                                                <div style="font-size: 0.85em; color: #666;">Your profile can be found in search results</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 8px; border-radius: 6px;"
                                               onmouseover="this.style.backgroundColor='#f0f9ff'" onmouseout="this.style.backgroundColor='transparent'">
                                            <input type="radio" name="visibility" value="hidden" 
                                                   <?php echo (isset($user['visibility']) && $user['visibility'] === 'hidden') ? 'checked' : ''; ?>>
                                            <div>
                                                <div style="font-weight: 500;">👻 Hidden</div>
                                                <div style="font-size: 0.85em; color: #666;">Only visible to you</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333;">Personal Information</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label required">Full Name</label>
                                <input type="text" name="full_name" class="form-control"
                                    value="<?php echo sanitize($user['full_name'] ?? ''); ?>" required
                                    placeholder="Your full name">
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Professional Title</label>
                                <input type="text" name="title" class="form-control"
                                    value="<?php echo sanitize($user['title'] ?? 'Job Seeker'); ?>" required
                                    placeholder="e.g. Software Engineer, Marketing Specialist">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control"
                                    value="<?php echo sanitize($user['phone'] ?? ''); ?>"
                                    placeholder="+227 XX XX XX XX">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Education Level</label>
                                <select name="education_level" class="form-control">
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

                    <!-- Skills Section -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333;">Skills & Expertise</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Skills (comma separated)</label>
                            <input type="text" name="skills" class="form-control"
                                value="<?php echo sanitize($user['skills'] ?? ''); ?>"
                                placeholder="e.g. PHP, MySQL, JavaScript, React, Project Management">
                            <div class="upload-hint">Separate each skill with a comma</div>
                        </div>
                    </div>

                    <!-- Bio Section -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333;">Professional Summary</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Bio / Professional Summary</label>
                            <textarea name="bio" class="form-control" rows="6"
                                placeholder="Tell employers about your experience, achievements, and career goals..."><?php echo sanitize($user['bio'] ?? ''); ?></textarea>
                            <div class="upload-hint">Recommended: 150-300 words highlighting your key achievements</div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-weight: 500;">
                            💾 Save Profile Changes
                        </button>
                        <button type="reset" class="btn btn-outline" style="padding: 12px 24px;">
                            ↩️ Reset Changes
                        </button>
                        <a href="candidate_dashboard.php" class="btn btn-outline" style="padding: 12px 24px; text-decoration: none;">
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
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
            // Also update the preview in the form
            const formPreview = input.previousElementSibling.querySelector('img');
            if (formPreview) {
                formPreview.src = e.target.result;
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const fullName = this.querySelector('input[name="full_name"]').value.trim();
    const title = this.querySelector('input[name="title"]').value.trim();
    
    if (!fullName || !title) {
        e.preventDefault();
        alert('Please fill in all required fields (marked with *)');
        return false;
    }
    
    // Check file size if image is selected
    const fileInput = this.querySelector('input[name="profile_picture"]');
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
        if (fileSize > 2) {
            e.preventDefault();
            alert('Profile picture must be less than 2MB');
            return false;
        }
    }
});
</script>

<?php 
// Remove error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
require_once 'includes/footer.php'; 
?>
