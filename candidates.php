<?php
// candidates.php
require_once 'includes/header.php';
// No login required to browse? Or maybe required? Let's keep it open or at least open for listing, details might need login.
// For now, let's allow public browsing but viewing details requires login.

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$education = isset($_GET['education']) ? sanitize($_GET['education']) : '';

// Build Query
// Self-healing: Ensure visibility column exists to prevent crash on read if not migrated
try {
    $conn->query("SELECT visibility FROM candidates LIMIT 1");
    // If successful, filter
    $sql = "SELECT * FROM candidates WHERE (visibility = 'visible' OR visibility IS NULL)";
} catch (PDOException $e) {
    // Column doesn't exist yet, show all
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
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 3rem 0; color: white;">
    <div class="container text-center">
        <h1>Find Top Talent</h1>
        <p>Browse through our database of qualified professionals in Niger</p>

        <form action="candidates.php" method="GET"
            style="max-width: 600px; margin: 2rem auto 0; display: flex; gap: 0.5rem; background: white; padding: 0.5rem; border-radius: var(--radius-md);">
            <input type="text" name="q" placeholder="Job title, skills, or name..." value="<?php echo $search; ?>"
                style="flex: 1; border: none; padding: 0.75rem; outline: none; font-size: 1rem;">
            <select name="education"
                style="border: none; padding: 0 1rem; border-left: 1px solid #e2e8f0; outline: none; color: var(--text-muted);">
                <option value="">Any Education</option>
                <option value="High School" <?php echo $education == 'High School' ? 'selected' : ''; ?>>High School
                </option>
                <option value="Bachelor" <?php echo $education == 'Bachelor' ? 'selected' : ''; ?>>Bachelor's</option>
                <option value="Master" <?php echo $education == 'Master' ? 'selected' : ''; ?>>Master's</option>
                <option value="PhD" <?php echo $education == 'PhD' ? 'selected' : ''; ?>>PhD</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<div class="container mt-2 mb-2">
    <?php if (count($candidates) > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <?php foreach ($candidates as $c): ?>
                <div class="card" style="display: flex; flex-direction: column; height: 100%;">
                    <div style="display: flex; gap: 1rem; align-items: start; margin-bottom: 1rem;">
                        <?php
                        $photo_url = "https://ui-avatars.com/api/?name=" . urlencode($c['full_name']) . "&background=0ea5e9&color=fff";
                        if (isset($c['profile_picture']) && $c['profile_picture']) {
                            $photo_url = 'uploads/photos/' . $c['profile_picture'];
                        }
                        ?>
                        <img src="<?php echo $photo_url; ?>" alt="Profile"
                            style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <h4 style="margin: 0; font-size: 1.1rem;"><?php echo sanitize($c['full_name']); ?></h4>
                            <p style="color: var(--secondary); font-weight: 500; margin: 0;">
                                <?php echo sanitize($c['title'] ?: 'Job Seeker'); ?>
                            </p>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.25rem;">
                                <?php echo $c['education_level'] ? sanitize($c['education_level']) : 'Education not specified'; ?>
                            </p>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem; flex-grow: 1;">
                        <?php if ($c['skills']): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <?php foreach (array_slice(explode(',', $c['skills']), 0, 4) as $skill): ?>
                                    <span
                                        style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; color: var(--text-muted);"><?php echo trim($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="font-style: italic; color: #cbd5e1;">No skills listed</p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: auto;">
                        <?php if (is_logged_in() && get_role() === 'employer'): ?>
                            <a href="candidate_details.php?id=<?php echo $c['id']; ?>" class="btn btn-outline btn-block"
                                style="text-align: center;">View Profile</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline btn-block" style="text-align: center;">Login to View</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center" style="padding: 4rem 0;">
            <h3 style="color: var(--text-muted);">No candidates found matching your criteria.</h3>
            <p><a href="candidates.php" style="color: var(--secondary);">Clear filters</a></p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>