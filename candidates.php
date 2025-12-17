<?php
// candidates.php - IMPROVED CARD DESIGN
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

// Calculate found count
$found_count = count($candidates);
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
    <!-- Results Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #f1f5f9;">
        <h2 style="margin: 0; color: #333; display: flex; align-items: center; gap: 10px;">
            <span>Available Candidates</span>
            <?php if ($found_count > 0): ?>
            <span style="background: #e7f3ff; color: #007bff; padding: 6px 14px; border-radius: 20px; font-size: 0.9em; font-weight: 600;">
                <?php echo $found_count; ?> candidate<?php echo $found_count !== 1 ? 's' : ''; ?> found
            </span>
            <?php endif; ?>
        </h2>
        <?php if (is_logged_in() && get_role() === 'employer'): ?>
        <div style="font-size: 0.9em; color: #666; display: flex; align-items: center; gap: 8px;">
            <span style="width: 10px; height: 10px; background: #28a745; border-radius: 50%;"></span>
            Employer View
        </div>
        <?php endif; ?>
    </div>

    <?php if ($found_count > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.75rem;">
            <?php foreach ($candidates as $c): ?>
                <div class="card" 
                     style="border: 1px solid #e2e8f0; 
                            border-radius: 12px; 
                            overflow: hidden; 
                            transition: all 0.3s ease;
                            background: white;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.04);"
                     onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 10px 30px rgba(0,0,0,0.08)';"
                     onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.04)';">
                    
                    <!-- Profile Header with Gradient -->
                    <div style="
                        padding: 1.75rem 1.75rem 1.25rem;
                        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
                        border-bottom: 1px solid #f1f5f9;
                        position: relative;
                    ">
                        <!-- Profile Picture -->
                        <div style="display: flex; align-items: flex-start; gap: 1.25rem;">
                            <div style="position: relative;">
                                <?php
                                $photo_url = get_profile_picture_url($c['id'], $conn);
                                ?>
                                <img src="<?php echo $photo_url; ?>" 
                                     alt="Profile"
                                     style="width: 75px; 
                                            height: 75px; 
                                            border-radius: 50%; 
                                            object-fit: cover; 
                                            border: 4px solid white; 
                                            box-shadow: 0 3px 12px rgba(0,0,0,0.08);">
                                <!-- Online Status Indicator -->
                                <div style="
                                    position: absolute; 
                                    bottom: 5px; 
                                    right: 5px;
                                    width: 12px; 
                                    height: 12px; 
                                    background: #28a745; 
                                    border-radius: 50%; 
                                    border: 2px solid white;
                                "></div>
                            </div>
                            
                            <!-- Candidate Info -->
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="
                                    margin: 0 0 6px 0; 
                                    font-size: 1.25rem; 
                                    color: #1e293b;
                                    font-weight: 700;
                                    white-space: nowrap;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                ">
                                    <?php echo htmlspecialchars($c['full_name']); ?>
                                </h4>
                                
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                    <span style="
                                        color: #007bff; 
                                        font-weight: 600; 
                                        font-size: 0.95rem;
                                        background: #e7f3ff;
                                        padding: 4px 10px;
                                        border-radius: 6px;
                                        display: inline-block;
                                    ">
                                        <?php echo htmlspecialchars($c['title'] ?: 'Job Seeker'); ?>
                                    </span>
                                    
                                    <?php if (!empty($c['visibility']) && $c['visibility'] === 'visible'): ?>
                                    <span style="
                                        font-size: 0.7em; 
                                        background: #d4edda; 
                                        color: #155724; 
                                        padding: 3px 8px; 
                                        border-radius: 10px;
                                        font-weight: 500;
                                    ">
                                        Public
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Education Badge -->
                                <div style="
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 6px;
                                    background: #f8fafc;
                                    padding: 5px 12px;
                                    border-radius: 20px;
                                    border: 1px solid #e2e8f0;
                                ">
                                    <span style="color: #666; font-size: 0.85em;">
                                        🎓
                                    </span>
                                    <span style="font-size: 0.85em; color: #475569; font-weight: 500;">
                                        <?php echo $c['education_level'] ? htmlspecialchars($c['education_level']) : 'Education not specified'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skills Section -->
                    <div style="padding: 1.25rem 1.75rem;">
                        <div style="
                            display: flex; 
                            align-items: center; 
                            gap: 8px; 
                            margin-bottom: 12px;
                            color: #64748b;
                        ">
                            <span style="font-size: 1.1em;">🔧</span>
                            <h5 style="
                                margin: 0; 
                                font-size: 0.85rem; 
                                text-transform: uppercase; 
                                letter-spacing: 0.5px;
                                font-weight: 600;
                            ">
                                Key Skills
                            </h5>
                        </div>
                        
                        <?php if ($c['skills']): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <?php 
                                $skills = explode(',', $c['skills']);
                                foreach (array_slice($skills, 0, 5) as $skill): 
                                    $trimmed_skill = trim($skill);
                                    if (!empty($trimmed_skill)):
                                ?>
                                    <span style="
                                        background: #e7f3ff; 
                                        padding: 6px 12px; 
                                        border-radius: 20px; 
                                        font-size: 0.85rem; 
                                        color: #007bff; 
                                        border: 1px solid #bae6fd;
                                        font-weight: 500;
                                    ">
                                        <?php echo htmlspecialchars($trimmed_skill); ?>
                                    </span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                                <?php if (count($skills) > 5): ?>
                                    <span style="
                                        background: #f8fafc; 
                                        padding: 6px 12px; 
                                        border-radius: 20px; 
                                        font-size: 0.85rem; 
                                        color: #64748b;
                                        border: 1px solid #e2e8f0;
                                    ">
                                        +<?php echo count($skills) - 5; ?> more
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div style="
                                background: #f8fafc; 
                                padding: 1rem; 
                                border-radius: 8px; 
                                text-align: center; 
                                color: #94a3b8;
                                border: 1px dashed #e2e8f0;
                            ">
                                <span style="font-style: italic;">Skills not specified</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bio Preview -->
                    <?php if ($c['bio']): ?>
                    <div style="
                        padding: 0 1.75rem 1.25rem;
                        border-bottom: 1px solid #f1f5f9;
                    ">
                        <div style="
                            font-size: 0.9em; 
                            color: #64748b; 
                            line-height: 1.6; 
                            max-height: 72px; 
                            overflow: hidden;
                            position: relative;
                            padding: 0.75rem;
                            background: #f8fafc;
                            border-radius: 8px;
                            border-left: 3px solid #007bff;
                        ">
                            <?php 
                            $short_bio = strip_tags($c['bio']);
                            if (strlen($short_bio) > 140) {
                                $short_bio = substr($short_bio, 0, 140) . '...';
                            }
                            echo htmlspecialchars($short_bio);
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Button -->
                    <div style="padding: 1.25rem 1.75rem;">
                        <?php if (is_logged_in() && get_role() === 'employer'): ?>
                            <a href="candidate_details.php?id=<?php echo $c['id']; ?>" 
                               style="
                                    display: block; 
                                    text-align: center; 
                                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); 
                                    color: white; 
                                    padding: 12px; 
                                    border-radius: 8px; 
                                    text-decoration: none; 
                                    font-weight: 600;
                                    font-size: 0.95rem;
                                    transition: all 0.2s;
                                    box-shadow: 0 2px 6px rgba(0,123,255,0.2);
                               "
                               onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,123,255,0.3)';"
                               onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 6px rgba(0,123,255,0.2)';">
                                👁️ View Full Profile
                            </a>
                        <?php else: ?>
                            <div style="display: flex; gap: 10px;">
                                <a href="login.php?redirect=candidate_details.php?id=<?php echo $c['id']; ?>" 
                                   style="
                                        flex: 1; 
                                        text-align: center; 
                                        border: 2px solid #007bff; 
                                        color: #007bff; 
                                        padding: 10px; 
                                        border-radius: 8px; 
                                        text-decoration: none; 
                                        font-weight: 600;
                                        font-size: 0.9rem;
                                        transition: all 0.2s;
                                        background: white;
                                   "
                                   onmouseover="this.style.background='#e7f3ff';"
                                   onmouseout="this.style.background='white';">
                                    🔑 Login to View
                                </a>
                                <a href="register.php?type=employer" 
                                   style="
                                        flex: 1; 
                                        text-align: center; 
                                        border: 2px solid #28a745; 
                                        color: #28a745; 
                                        padding: 10px; 
                                        border-radius: 8px; 
                                        text-decoration: none; 
                                        font-weight: 600;
                                        font-size: 0.9rem;
                                        transition: all 0.2s;
                                        background: white;
                                   "
                                   onmouseover="this.style.background='#e7f7ec';"
                                   onmouseout="this.style.background='white';">
                                    📝 Register
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- No Results State -->
        <div class="card" style="
            text-align: center; 
            padding: 4rem 2rem; 
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
        ">
            <div style="
                font-size: 5em; 
                color: #cbd5e1; 
                margin-bottom: 1.5rem;
                line-height: 1;
            ">
                👥
            </div>
            <h3 style="
                color: #475569; 
                margin-bottom: 1rem;
                font-size: 1.5rem;
            ">
                No matching candidates found
            </h3>
            <p style="
                color: #94a3b8; 
                margin-bottom: 2rem; 
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            ">
                Try adjusting your search terms or filters to find the right talent for your needs.
            </p>
            <a href="candidates.php" 
               style="
                    display: inline-block;
                    text-decoration: none; 
                    padding: 12px 28px; 
                    background: #007bff; 
                    color: white; 
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 0.95rem;
                    transition: all 0.2s;
               "
               onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,123,255,0.3)';"
               onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                🔄 Clear Filters & Show All
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
