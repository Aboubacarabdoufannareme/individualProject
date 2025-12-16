<?php
// candidates.php - FIXED VERSION
require_once 'includes/header.php';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$education = isset($_GET['education']) ? sanitize($_GET['education']) : '';

// Build Query
try {
    $conn->query("SELECT visibility FROM candidates LIMIT 1");
    $sql = "SELECT * FROM candidates WHERE (visibility = 'visible' OR visibility IS NULL)";
} catch (PDOException $e) {
    $sql = "SELECT * FROM candidates WHERE 1=1";
}
$params = [];

if ($search) {
    $sql .= " AND (full_name LIKE ? OR skills LIKE ? OR title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($education) {
    $sql .= " AND education_level = ?";
    $params[] = $education;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$candidates = $stmt->fetchAll();

// Helper function to get profile picture for candidate listing
function get_candidate_photo_url($candidate, $conn) {
    if (!empty($candidate['profile_picture'])) {
        try {
            // Check if it's stored in documents table
            $stmt = $conn->prepare("SELECT file_content, mime_type FROM documents 
                                    WHERE candidate_id = ? AND file_path = ? AND type = 'profile_pic' 
                                    ORDER BY uploaded_at DESC LIMIT 1");
            $stmt->execute([$candidate['id'], $candidate['profile_picture']]);
            $result = $stmt->fetch();
            
            if ($result && !empty($result['file_content'])) {
                return 'data:' . $result['mime_type'] . ';base64,' . base64_encode($result['file_content']);
            }
        } catch (PDOException $e) {
            // Fall through to default
        }
    }
    
    // Default avatar
    $name = urlencode($candidate['full_name'] ?? 'Candidate');
    return "https://ui-avatars.com/api/?name=$name&background=0ea5e9&color=fff&size=128";
}
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, #007bff 0%, #1e293b 100%); padding: 3rem 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Find Top Talent</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Browse through our database of qualified professionals in Niger</p>

        <form action="candidates.php" method="GET"
            style="max-width: 600px; margin: 2rem auto 0; display: flex; gap: 0.5rem; background: white; padding: 0.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <input type="text" name="q" placeholder="Job title, skills, or name..." value="<?php echo htmlspecialchars($search); ?>"
                style="flex: 1; border: none; padding: 0.75rem; outline: none; font-size: 1rem; border-radius: 4px;">
            <select name="education"
                style="border: none; padding: 0 1rem; border-left: 1px solid #e2e8f0; outline: none; color: #666; background: white; min-width: 150px;">
                <option value="">Any Education</option>
                <option value="High School" <?php echo $education == 'High School' ? 'selected' : ''; ?>>High School</option>
                <option value="Bachelor" <?php echo $education == 'Bachelor' ? 'selected' : ''; ?>>Bachelor's</option>
                <option value="Master" <?php echo $education == 'Master' ? 'selected' : ''; ?>>Master's</option>
                <option value="PhD" <?php echo $education == 'PhD' ? 'selected' : ''; ?>>PhD</option>
                <option value="Other" <?php echo $education == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
            <button type="submit" class="btn btn-primary" style="background: #007bff; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; color: white; cursor: pointer; font-weight: 500;">
                Search
            </button>
        </form>
    </div>
</div>

<div class="container mt-4 mb-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0; color: #333;">
            Candidates 
            <?php if (count($candidates) > 0): ?>
            <span style="background: #e7f3ff; color: #007bff; padding: 4px 12px; border-radius: 20px; font-size: 0.9em; margin-left: 10px;">
                <?php echo count($candidates); ?> found
            </span>
            <?php endif; ?>
        </h2>
        <?php if (is_logged_in() && get_role() === 'employer'): ?>
        <div style="font-size: 0.9em; color: #666;">
            👁️ Viewing as Employer
        </div>
        <?php endif; ?>
    </div>

    <?php if (count($candidates) > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            <?php foreach ($candidates as $c): ?>
                <div class="card" style="display: flex; flex-direction: column; height: 100%; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;"
                     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,0.1)';"
                     onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                    
                    <!-- Candidate Header -->
                    <div style="padding: 1.5rem; display: flex; gap: 1rem; align-items: flex-start; border-bottom: 1px solid #f1f5f9; background: linear-gradient(to right, #f8fafc, #ffffff);">
                        <?php
                        // FIXED: Use the helper function to get profile picture
                        $photo_url = get_candidate_photo_url($c, $conn);
                        ?>
                        <img src="<?php echo $photo_url; ?>" alt="Profile"
                            style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 5px 0; font-size: 1.2rem; color: #333;"><?php echo htmlspecialchars($c['full_name']); ?></h4>
                            <p style="color: #007bff; font-weight: 500; margin: 0 0 8px 0;">
                                <?php echo htmlspecialchars($c['title'] ?: 'Job Seeker'); ?>
                            </p>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.85em; color: #666; background: #f1f5f9; padding: 3px 8px; border-radius: 12px;">
                                    <?php echo $c['education_level'] ? htmlspecialchars($c['education_level']) : 'Education not specified'; ?>
                                </span>
                                <?php if (!empty($c['visibility']) && $c['visibility'] === 'visible'): ?>
                                <span style="font-size: 0.75em; background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 12px;">
                                    👁️ Public
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Skills Section -->
                    <div style="padding: 1.5rem; flex-grow: 1;">
                        <h5 style="margin: 0 0 10px 0; color: #555; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Skills</h5>
                        <?php if ($c['skills']): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <?php 
                                $skills = explode(',', $c['skills']);
                                foreach (array_slice($skills, 0, 6) as $skill): 
                                    $trimmed_skill = trim($skill);
                                    if (!empty($trimmed_skill)):
                                ?>
                                    <span style="background: #e7f3ff; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; color: #007bff; border: 1px solid #bae6fd;">
                                        <?php echo htmlspecialchars($trimmed_skill); ?>
                                    </span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                                <?php if (count($skills) > 6): ?>
                                    <span style="background: #f8f9fa; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; color: #666;">
                                        +<?php echo count($skills) - 6; ?> more
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center; color: #999;">
                                <span style="font-style: italic;">No skills listed yet</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Bio Preview -->
                    <?php if ($c['bio']): ?>
                    <div style="padding: 0 1.5rem; margin-bottom: 1rem;">
                        <div style="font-size: 0.9em; color: #666; line-height: 1.5; max-height: 60px; overflow: hidden; position: relative;">
                            <?php 
                            $short_bio = strip_tags($c['bio']);
                            if (strlen($short_bio) > 120) {
                                $short_bio = substr($short_bio, 0, 120) . '...';
                            }
                            echo htmlspecialchars($short_bio);
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Button -->
                    <div style="padding: 1.5rem; padding-top: 0; margin-top: auto;">
                        <?php if (is_logged_in() && get_role() === 'employer'): ?>
                            <a href="candidate_details.php?id=<?php echo $c['id']; ?>" 
                               class="btn btn-primary btn-block"
                               style="display: block; text-align: center; background: #007bff; color: white; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: background 0.2s;"
                               onmouseover="this.style.backgroundColor='#0056b3'"
                               onmouseout="this.style.backgroundColor='#007bff'">
                                👤 View Full Profile
                            </a>
                        <?php else: ?>
                            <div style="display: flex; gap: 10px;">
                                <a href="login.php?redirect=candidate_details.php?id=<?php echo $c['id']; ?>" 
                                   class="btn btn-outline"
                                   style="flex: 1; text-align: center; border: 1px solid #007bff; color: #007bff; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                                    🔑 Login to View
                                </a>
                                <a href="register.php?type=employer" 
                                   class="btn btn-outline"
                                   style="flex: 1; text-align: center; border: 1px solid #28a745; color: #28a745; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                                    📝 Register as Employer
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center" style="padding: 4rem 0;">
            <div style="font-size: 4em; color: #e9ecef; margin-bottom: 1rem;">🔍</div>
            <h3 style="color: #666; margin-bottom: 1rem;">No candidates found matching your criteria.</h3>
            <p style="color: #999; margin-bottom: 2rem;">Try adjusting your search terms or filters.</p>
            <a href="candidates.php" class="btn btn-primary" style="text-decoration: none; padding: 12px 24px; background: #007bff; color: white; border-radius: 8px;">
                Clear Filters & Show All
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
