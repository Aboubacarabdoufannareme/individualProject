<?php
// employer_profile.php - UPDATED VERSION (Option 1 - Modified documents table)
require_once 'includes/header.php';
require_login();

// Ensure user is an employer
if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Self-healing: Add missing columns
$self_healing_queries = [
    'logo' => "ALTER TABLE employers ADD COLUMN logo VARCHAR(255) DEFAULT NULL AFTER email",
    'website' => "ALTER TABLE employers ADD COLUMN website VARCHAR(100) AFTER email",
    'location' => "ALTER TABLE employers ADD COLUMN location VARCHAR(100) AFTER website",
    'industry' => "ALTER TABLE employers ADD COLUMN industry VARCHAR(50) AFTER location",
    'description' => "ALTER TABLE employers ADD COLUMN description TEXT AFTER industry",
    'phone' => "ALTER TABLE employers ADD COLUMN phone VARCHAR(20) AFTER email"
];

foreach ($self_healing_queries as $column => $sql) {
    try {
        $conn->query("SELECT $column FROM employers LIMIT 1");
    } catch (PDOException $e) {
        $conn->exec($sql);
    }
}

// Fetch Current Data
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = sanitize($_POST['company_name'] ?? $user['company_name']);
    $website = sanitize($_POST['website'] ?? ($user['website'] ?? ''));
    $location = sanitize($_POST['location'] ?? ($user['location'] ?? ''));
    $industry = sanitize($_POST['industry'] ?? ($user['industry'] ?? ''));
    $description = sanitize($_POST['description'] ?? ($user['description'] ?? ''));
    $phone = sanitize($_POST['phone'] ?? ($user['phone'] ?? ''));
    
    // Handle Logo Upload
    $logo_filename = $user['logo'] ?? null;
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $filename = $_FILES['logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($_FILES['logo']['size'] <= 2 * 1024 * 1024) { // 2MB limit
                
                // Generate unique filename
                $new_filename = 'logo_' . $user_id . '_' . time() . '.' . $ext;
                
                try {
                    // 1. Delete any existing logo from documents table
                    $conn->prepare("DELETE FROM documents WHERE user_id = ? AND user_type = 'employer' AND type = 'company_logo'")
                         ->execute([$user_id]);
                    
                    // 2. Read and store logo in documents table (NEW STRUCTURE)
                    $file_content = file_get_contents($_FILES['logo']['tmp_name']);
                    if ($file_content !== false) {
                        $stmt = $conn->prepare("INSERT INTO documents (user_id, user_type, type, file_path, original_name, file_content, file_size, mime_type) 
                                                VALUES (?, 'employer', 'company_logo', ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $user_id,
                            $new_filename,
                            $filename,
                            $file_content,
                            $_FILES['logo']['size'],
                            $_FILES['logo']['type']
                        ]);
                        
                        // 3. Store ONLY filename in employers table
                        $logo_filename = $new_filename;
                    }
                    
                } catch (PDOException $e) {
                    // Log the error for debugging
                    error_log("Database error saving logo: " . $e->getMessage());
                    
                    // If database storage fails, try filesystem as fallback
                    $upload_dir = dirname(__DIR__) . '/uploads/logos/';
                    if (!is_dir($upload_dir)) {
                        @mkdir($upload_dir, 0755, true);
                    }
                    
                    $destination = $upload_dir . $new_filename;
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $destination)) {
                        $logo_filename = $new_filename;
                    } else {
                        $error_msg = "Failed to save logo. Please try again.";
                    }
                }
            } else {
                $error_msg = "Logo too large (max 2MB).";
            }
        } else {
            $error_msg = "Invalid image format. Use JPG, PNG, GIF, or SVG.";
        }
    } elseif (isset($_POST['remove_logo'])) {
        // Remove logo
        try {
            $conn->prepare("DELETE FROM documents WHERE user_id = ? AND user_type = 'employer' AND type = 'company_logo'")
                 ->execute([$user_id]);
            $logo_filename = null;
        } catch (Exception $e) {
            // Silently continue
            $logo_filename = null;
        }
    }

    if (empty($error_msg)) {
        try {
            // Update employers table - store ONLY filename (not base64)
            $stmt = $conn->prepare("
                UPDATE employers 
                SET company_name = ?, 
                    website = ?, 
                    location = ?, 
                    industry = ?, 
                    description = ?, 
                    phone = ?, 
                    logo = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $company_name, 
                $website, 
                $location, 
                $industry, 
                $description, 
                $phone, 
                $logo_filename,  // This is just a filename like "logo_123_123456.jpg"
                $user_id
            ]);
            
            $success_msg = "✅ Company profile updated successfully!";
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error_msg = "Error saving profile: " . $e->getMessage();
            
            // If it's a logo column issue, try without logo
            if (strpos($e->getMessage(), 'logo') !== false) {
                try {
                    $stmt = $conn->prepare("
                        UPDATE employers 
                        SET company_name = ?, 
                            website = ?, 
                            location = ?, 
                            industry = ?, 
                            description = ?, 
                            phone = ?
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([
                        $company_name, 
                        $website, 
                        $location, 
                        $industry, 
                        $description, 
                        $phone,
                        $user_id
                    ]);
                    
                    $success_msg = "✅ Profile updated (logo not saved).";
                    $stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                    
                } catch (PDOException $e2) {
                    $error_msg = "Critical error: " . $e2->getMessage();
                }
            }
        }
    }
}

// Get company logo URL
function get_company_logo_url($user_id, $conn) {
    try {
        // First get the filename from employers table
        $stmt = $conn->prepare("SELECT logo FROM employers WHERE id = ?");
        $stmt->execute([$user_id]);
        $employer = $stmt->fetch();
        
        if (!empty($employer['logo'])) {
            // Check if it's stored in documents table (NEW QUERY)
            $stmt = $conn->prepare("SELECT file_content, mime_type FROM documents 
                                    WHERE user_id = ? AND user_type = 'employer' AND file_path = ? AND type = 'company_logo' 
                                    ORDER BY uploaded_at DESC LIMIT 1");
            $stmt->execute([$user_id, $employer['logo']]);
            $result = $stmt->fetch();
            
            if ($result && !empty($result['file_content'])) {
                return 'data:' . $result['mime_type'] . ';base64,' . base64_encode($result['file_content']);
            }
            
            // Try filesystem as fallback
            $possible_paths = [
                'uploads/logos/' . $employer['logo'],
                'uploads/' . $employer['logo'],
                $employer['logo']
            ];
            
            foreach ($possible_paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
    } catch (PDOException $e) {
        // Fall through to default
        error_log("Error getting logo URL: " . $e->getMessage());
    }
    
    // Default logo using UI Avatars
    $stmt = $conn->prepare("SELECT company_name FROM employers WHERE id = ?");
    $stmt->execute([$user_id]);
    $employer = $stmt->fetch();
    $name = urlencode($employer['company_name'] ?? 'Company');
    return "https://ui-avatars.com/api/?name=$name&background=0ea5e9&color=fff&size=128";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company Profile - DigiCareer</title>
    <style>
        .logo-preview {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            object-fit: contain;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: white;
            padding: 10px;
        }
        .form-section {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .logo-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .remove-logo-btn {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85em;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
            text-decoration: none;
        }
        .remove-logo-btn:hover {
            background: #f1b0b7;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
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
                    $logo_url = get_company_logo_url($user_id, $conn);
                    ?>
                    <img src="<?php echo $logo_url; ?>" alt="Company Logo" class="logo-preview" id="logoPreview">
                    <h4 style="margin: 1rem 0 0.5rem;"><?php echo htmlspecialchars($user['company_name']); ?></h4>
                    <p style="color: #666; font-size: 0.9em;">
                        <?php echo htmlspecialchars($user['industry'] ?? 'No industry specified'); ?>
                    </p>
                </div>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_dashboard.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📊 Dashboard
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_profile.php"
                           style="color: #007bff; font-weight: 600; text-decoration: none; display: block; padding: 10px; border-radius: 5px; background-color: #e7f3ff;"
                           onmouseover="this.style.backgroundColor='#d9ebff'" onmouseout="this.style.backgroundColor='#e7f3ff'">
                           🏢 Company Profile
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_post_job.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📝 Post a Job
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_applications.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           📄 Applications
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidates.php"
                           style="color: #333; text-decoration: none; display: block; padding: 10px; border-radius: 5px;"
                           onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                           🔍 Search Candidates
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Edit Company Profile</h1>
                    <a href="employer_dashboard.php" class="btn btn-outline" style="text-decoration: none; padding: 8px 16px; border: 1px solid #ddd; border-radius: 5px;">
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

                <form method="POST" action="employer_profile.php" enctype="multipart/form-data" id="profileForm">
                    
                    <!-- Company Logo Section -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Company Logo</h3>
                        <div class="logo-controls">
                            <div style="margin-bottom: 1rem; text-align: center;">
                                <img src="<?php echo get_company_logo_url($user_id, $conn); ?>" 
                                     alt="Current Logo" style="width: 150px; height: 150px; border-radius: 12px; object-fit: contain; margin-bottom: 10px; border: 3px solid #ddd; padding: 10px; background: white;"
                                     id="currentLogo">
                            </div>
                            
                            <input type="file" name="logo" id="logoInput" 
                                   accept="image/*" style="margin-bottom: 10px; padding: 8px; width: 100%; max-width: 300px;">
                            
                            <?php if (!empty($user['logo'])): ?>
                            <label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; cursor: pointer;">
                                <input type="checkbox" name="remove_logo" value="1" id="removeLogo">
                                <span style="font-size: 0.9em; color: #721c24;">🗑️ Remove current logo</span>
                            </label>
                            <?php endif; ?>
                            
                            <div style="font-size: 0.85em; color: #666; margin-top: 5px; text-align: center;">
                                <strong>Upload Tips:</strong><br>
                                • Max 2MB<br>
                                • Use JPG, PNG, GIF, or SVG<br>
                                • Logo stored securely in database
                            </div>
                        </div>
                    </div>

                    <!-- Company Information -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px;">Company Information</h3>
                        
                        <div class="form-grid" style="margin-top: 1rem;">
                            <div class="form-group">
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Company Name *</label>
                                <input type="text" name="company_name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['company_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Website</label>
                                <input type="url" name="website" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>"
                                    placeholder="https://example.com">
                            </div>
                        </div>
                        
                        <div class="form-grid" style="margin-top: 1.5rem;">
                            <div class="form-group">
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Location</label>
                                <input type="text" name="location" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>"
                                    placeholder="e.g. Niamey, Niger">
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Industry</label>
                                <input type="text" name="industry" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['industry'] ?? ''); ?>"
                                    placeholder="e.g. Technology, Education">
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem;">
                            <div class="form-group">
                                <label style="font-weight: 500; display: block; margin-bottom: 5px;">Phone Number</label>
                                <input type="tel" name="phone" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    placeholder="+227 XX XX XX XX">
                            </div>
                        </div>
                    </div>

                    <!-- Company Description -->
                    <div class="form-section">
                        <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #fd7e14; padding-bottom: 10px;">About Company</h3>
                        
                        <div style="margin-top: 1rem;">
                            <label style="font-weight: 500; display: block; margin-bottom: 5px;">Company Description</label>
                            <textarea name="description" rows="6" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                placeholder="Describe your company..."><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
                        <button type="submit" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            Save Changes
                        </button>
                        <a href="employer_dashboard.php" style="padding: 12px 24px; margin-left: 1rem; text-decoration: none;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
// Preview logo before upload
document.getElementById('logoInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('currentLogo').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
