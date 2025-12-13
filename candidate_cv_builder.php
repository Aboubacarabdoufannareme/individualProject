<?php
// candidate_cv_builder.php
require_once 'includes/header.php';
require_login();

// Ensure user is a candidate
if (get_role() !== 'candidate') {
    redirect('employer_dashboard.php');
}

$user_id = $_SESSION['user_id'];

// Default CV data structure
$cv_data = [
    'experience' => [
        ['title' => '', 'company' => '', 'years' => '', 'description' => '']
    ],
    'education' => [
        ['degree' => '', 'school' => '', 'year' => '']
    ],
    'skills' => ''
];

// Handle Form Submission (Save or Preview)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect data (In a real app, we'd save this to a 'cvs' table)
    // For this simple version, we'll just display the preview

    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $summary = sanitize($_POST['summary']);
    $skills = sanitize($_POST['skills']);

    // Process experience
    $experiences = [];
    if (isset($_POST['exp_title'])) {
        for ($i = 0; $i < count($_POST['exp_title']); $i++) {
            if (!empty($_POST['exp_title'][$i])) {
                $experiences[] = [
                    'title' => sanitize($_POST['exp_title'][$i]),
                    'company' => sanitize($_POST['exp_company'][$i]),
                    'years' => sanitize($_POST['exp_years'][$i]),
                    'description' => sanitize($_POST['exp_desc'][$i])
                ];
            }
        }
    }

    // Process education
    $educations = [];
    if (isset($_POST['edu_degree'])) {
        for ($i = 0; $i < count($_POST['edu_degree']); $i++) {
            if (!empty($_POST['edu_degree'][$i])) {
                $educations[] = [
                    'degree' => sanitize($_POST['edu_degree'][$i]),
                    'school' => sanitize($_POST['edu_school'][$i]),
                    'year' => sanitize($_POST['edu_year'][$i])
                ];
            }
        }
    }
} else {
    // Populate form with basic profile data
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $full_name = $user['full_name'];
    $email = $user['email'];
    $phone = $user['phone'];
    $summary = $user['bio'];
    $skills = $user['skills'];

    $experiences = $cv_data['experience'];
    $educations = $cv_data['education'];
}
?>

<div class="container mt-2 mb-2">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <!-- CV PREVIEW MODE -->
        <div class="card" id="printable-cv" style="max-width: 800px; margin: 0 auto; padding: 40px;">
            <div
                style="text-align: center; border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: 20px;">
                <h1 style="margin: 0; color: var(--primary);"><?php echo $full_name; ?></h1>
                <p style="margin: 5px 0 0; color: var(--text-muted);">
                    <?php echo $email; ?> | <?php echo $phone; ?>
                </p>
            </div>

            <?php if (!empty($summary)): ?>
                <div style="margin-bottom: 20px;">
                    <h3 style="color: var(--secondary); border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Professional
                        Summary</h3>
                    <p><?php echo nl2br($summary); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($skills)): ?>
                <div style="margin-bottom: 20px;">
                    <h3 style="color: var(--secondary); border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Skills</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach (explode(',', $skills) as $skill): ?>
                            <span
                                style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 0.9em;"><?php echo trim($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($experiences)): ?>
                <div style="margin-bottom: 20px;">
                    <h3 style="color: var(--secondary); border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Experience</h3>
                    <?php foreach ($experiences as $exp): ?>
                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between;">
                                <h4 style="margin: 0; font-size: 1.1em;"><?php echo $exp['title']; ?></h4>
                                <span style="font-weight: 600; color: var(--text-muted);"><?php echo $exp['years']; ?></span>
                            </div>
                            <div style="font-weight: 500; color: var(--text-main);"><?php echo $exp['company']; ?></div>
                            <p style="margin-top: 5px; font-size: 0.95em;"><?php echo nl2br($exp['description'] ?? ''); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($educations)): ?>
                <div style="margin-bottom: 20px;">
                    <h3 style="color: var(--secondary); border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Education</h3>
                    <?php foreach ($educations as $edu): ?>
                        <div style="margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <h4 style="margin: 0; font-size: 1.1em;"><?php echo $edu['degree']; ?></h4>
                                <span style="font-weight: 600; color: var(--text-muted);"><?php echo $edu['year']; ?></span>
                            </div>
                            <div style="color: var(--text-main);"><?php echo $edu['school']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-2">
            <button onclick="window.print()" class="btn btn-primary">Print PDF</button>
            <a href="candidate_cv_builder.php" class="btn btn-outline" style="margin-left: 10px;">Edit Again</a>
        </div>

    <?php else: ?>
        <!-- FORM MODE -->
        <h2 class="mb-2">CV Builder</h2>
        <div class="card">
            <form method="POST" action="candidate_cv_builder.php">
                <h3 class="mb-2">Personal Info</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo sanitize($full_name); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo sanitize($email); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo sanitize($phone); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Skills (comma separated)</label>
                        <input type="text" name="skills" class="form-control" value="<?php echo sanitize($skills); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Professional Summary</label>
                    <textarea name="summary" class="form-control" rows="3"><?php echo sanitize($summary); ?></textarea>
                </div>

                <h3 class="mb-2 mt-2">Experience</h3>
                <div id="experience-container">
                    <div class="form-group"
                        style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; margin-bottom: 1rem;">
                        <input type="text" name="exp_title[]" class="form-control mb-1" placeholder="Job Title"
                            style="margin-bottom: 0.5rem;">
                        <input type="text" name="exp_company[]" class="form-control mb-1" placeholder="Company Name"
                            style="margin-bottom: 0.5rem;">
                        <input type="text" name="exp_years[]" class="form-control mb-1" placeholder="Years (e.g. 2020-2022)"
                            style="margin-bottom: 0.5rem;">
                        <textarea name="exp_desc[]" class="form-control" rows="2" placeholder="Description"></textarea>
                    </div>
                </div>
                <!-- Simple JS to add more logic could be added here, keeping it static for V1 -->

                <h3 class="mb-2 mt-2">Education</h3>
                <div id="education-container">
                    <div class="form-group"
                        style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; margin-bottom: 1rem;">
                        <input type="text" name="edu_degree[]" class="form-control mb-1" placeholder="Degree / Diploma"
                            style="margin-bottom: 0.5rem;">
                        <input type="text" name="edu_school[]" class="form-control mb-1" placeholder="School / University"
                            style="margin-bottom: 0.5rem;">
                        <input type="text" name="edu_year[]" class="form-control" placeholder="Year Graduated">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-2">Generate CV</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Print Styles -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printable-cv,
        #printable-cv * {
            visibility: visible;
        }

        #printable-cv {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }

        .navbar,
        .footer,
        .btn {
            display: none !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>