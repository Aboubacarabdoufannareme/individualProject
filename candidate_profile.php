<?php
// candidate_profile.php
require_once 'includes/header.php';
require_login();

// Ensure user is a candidate
if (get_role() !== 'candidate') {
    redirect('employer_dashboard.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Form Submission
// Self-healing: Add visibility column if not exists
try {
    $conn->query("SELECT visibility FROM candidates LIMIT 1");
} catch (PDOException $e) {
    $conn->exec("ALTER TABLE candidates ADD COLUMN visibility ENUM('visible', 'hidden') DEFAULT 'visible' AFTER skills");
}
// Self-healing: Add profile_picture column if not exists
try {
    $conn->query("SELECT profile_picture FROM candidates LIMIT 1");
} catch (PDOException $e) {
    $conn->exec("ALTER TABLE candidates ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER email");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $title = sanitize($_POST['title']);
    $phone = sanitize($_POST['phone']);
    $bio = sanitize($_POST['bio']);
    $education_level = sanitize($_POST['education_level']);
    $skills = sanitize($_POST['skills']); // Storing as comma separated string for simplicity
    $visibility = sanitize($_POST['visibility'] ?? 'visible');
    
    // Handle File Upload
    $profile_picture = $user['profile_picture'] ?? null; // Keep existing by default
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload = upload_file($_FILES['profile_picture'], 'uploads/photos/');
        if (isset($upload['success'])) {
            $profile_picture = $upload['path'];
        } else {
            $error_msg = $upload['error'];
        }
    }

    if (!$error_msg) {
        try {
            $stmt = $conn->prepare("
                UPDATE candidates 
                SET full_name = ?, title = ?, phone = ?, bio = ?, education_level = ?, skills = ?, visibility = ?, profile_picture = ? 
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $title, $phone, $bio, $education_level, $skills, $visibility, $profile_picture, $user_id]);
            $success_msg = "Profile updated successfully!";
            
            // Refresh user data immediately
            $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error_msg = "Error updating profile: " . $e->getMessage();
        }
    }
}


// Fetch Current Data (if not already fetched in POST)
if (!isset($user)) {
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
?>

<div class="container mt-2 mb-2">
    <div class="row" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">
        <!-- Sidebar -->
        <aside>
            <div class="card">
                <div class="text-center mb-2">
                    <?php 
                    $photo = $user['profile_picture'] ? 'uploads/photos/' . $user['profile_picture'] : "https://ui-avatars.com/api/?name=" . urlencode($user['full_name']) . "&background=0ea5e9&color=fff";
                    ?>
                    <img src="<?php echo $photo; ?>"
                        alt="Profile" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem;">
                    <h4><?php echo sanitize($user['full_name']); ?></h4>
                    <p style="color: var(--text-muted);"><?php echo sanitize($user['title'] ?: 'Job Seeker'); ?></p>
                </div>
                <!-- ... existing sidebar menu ... -->
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_dashboard.php"
                            style="color: var(--text-main);">Dashboard</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_profile.php"
                            style="color: var(--secondary); font-weight: 600;">My Profile</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_documents.php"
                            style="color: var(--text-main);">My Documents</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="candidate_cv_builder.php"
                            style="color: var(--text-main);">CV Builder</a></li>
                    <li style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;"><a href="logout.php"
                            style="color: var(--danger);">Logout</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="margin: 0;">Edit Profile</h2>
                    <a href="candidate_details.php?id=<?php echo $user_id; ?>" target="_blank" class="btn btn-outline">Preview Public Profile</a>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-error"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="candidate_profile.php" enctype="multipart/form-data">
                    <div class="form-group" style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                         <!-- Visibility Toggle -->
                        <label class="form-label">Profile Visibility</label>
                        <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="visibility" value="visible" 
                                    <?php echo (!isset($user['visibility']) || $user['visibility'] === 'visible') ? 'checked' : ''; ?>>
                                <span>Visible</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="radio" name="visibility" value="hidden" 
                                   <?php echo (isset($user['visibility']) && $user['visibility'] === 'hidden') ? 'checked' : ''; ?>>
                                <span>Hidden</span>
                            </label>
                        </div>

                        <!-- Profile Picture Upload -->
                         <label class="form-label">Profile Picture</label>
                         <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="<?php echo $photo; ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0;">
                            <input type="file" name="profile_picture" class="form-control" accept="image/*" style="flex: 1;">
                         </div>
                    </div>

                    <!-- ... Rest of the form ... -->
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control"
                            value="<?php echo sanitize($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Professional Title</label>
                        <input type="text" name="title" class="form-control"
                            value="<?php echo sanitize($user['title'] ?? ''); ?>" placeholder="e.g. Software Engineer">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                            value="<?php echo sanitize($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Education Level</label>
                        <select name="education_level" class="form-control">
                            <option value="">Select Level</option>
                            <option value="High School" <?php echo ($user['education_level'] == 'High School') ? 'selected' : ''; ?>>High School</option>
                            <option value="Bachelor" <?php echo ($user['education_level'] == 'Bachelor') ? 'selected' : ''; ?>>Bachelor's Degree</option>
                            <option value="Master" <?php echo ($user['education_level'] == 'Master') ? 'selected' : ''; ?>>Master's Degree</option>
                            <option value="PhD" <?php echo ($user['education_level'] == 'PhD') ? 'selected' : ''; ?>>PhD
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Skills (comma separated)</label>
                        <input type="text" name="skills" class="form-control"
                            value="<?php echo sanitize($user['skills'] ?? ''); ?>"
                            placeholder="e.g. PHP, MySQL, JavaScript">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="5"
                            placeholder="Tell us about yourself..."><?php echo sanitize($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="candidate_dashboard.php" class="btn btn-outline" style="margin-left: 1rem;">Cancel</a>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>