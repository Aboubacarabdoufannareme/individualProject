<?php
// index.php
require_once 'includes/header.php';
?>

<div class="hero-section text-center"
    style="padding: 6rem 1rem; background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); color: white;">
    <h1 style="color: white; margin-bottom: 1.5rem;">Launch Your Digital Career in Niger</h1>
    <p style="font-size: 1.25rem; max-width: 700px; margin: 0 auto 2.5rem; color: #cbd5e1;">
        DigiCareer connects talented graduates with top employers. Build your professional profile, showcase your
        achievements, and find your dream job today.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="register.php?role=candidate" class="btn btn-primary"
            style="font-size: 1.1rem; padding: 1rem 2rem;">Create Candidate Profile</a>
        <a href="register.php?role=employer" class="btn btn-outline"
            style="border-color: white; color: white; font-size: 1.1rem; padding: 1rem 2rem;">Post a Job</a>
    </div>
</div>

<div class="container mt-2">
    <div class="features-grid"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: -3rem; position: relative; z-index: 10;">
        <div class="card">
            <h3>For Candidates</h3>
            <p>Create a stunning digital CV, upload your diplomas securely, and stand out to recruiters across the
                country.</p>
            <ul style="margin-left: 1.5rem; list-style-type: disc; color: var(--text-muted);">
                <li>CV Builder</li>
                <li>Secure Document Storage</li>
                <li>Apply with One Click</li>
            </ul>
        </div>
        <div class="card">
            <h3>For Employers</h3>
            <p>Access a database of verified talents. Filter by skills, education, and experience to find the perfect
                match.</p>
            <ul style="margin-left: 1.5rem; list-style-type: disc; color: var(--text-muted);">
                <li>Post Unlimited Jobs</li>
                <li>Advanced Candidate Search</li>
                <li>Direct Document Access</li>
            </ul>
        </div>
        <div class="card">
            <h3>Verified & Secure</h3>
            <p>We ensure that all uploaded documents are secure and accessible only to authorized recruiters.</p>
            <ul style="margin-left: 1.5rem; list-style-type: disc; color: var(--text-muted);">
                <li>Encrypted Storage</li>
                <li>Verified Profiles</li>
                <li>Data Privacy First</li>
            </ul>
        </div>
    </div>
</div>

<div class="section container mt-2 mb-2 text-center">
    <h2>Latest Opportunities</h2>
    <p class="mb-2">Explore the newest openings from top companies in Niger.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php
        // Fetch 3 latest active jobs
        $stmt = $conn->query("
            SELECT j.*, e.company_name 
            FROM jobs j 
            JOIN employers e ON j.employer_id = e.id 
            WHERE j.status = 'active' 
            ORDER BY j.created_at DESC 
            LIMIT 3
        ");
        $jobs = $stmt->fetchAll();

        if (count($jobs) > 0):
            foreach ($jobs as $job):
                ?>
                <div class="card" style="text-align: left;">
                    <h4 style="margin-bottom: 0.5rem;"><?php echo sanitize($job['title']); ?></h4>
                    <p style="color: var(--secondary); font-weight: 600; margin-bottom: 0.5rem;">
                        <?php echo sanitize($job['company_name']); ?></p>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
                        <?php echo sanitize($job['location']); ?> • <?php echo sanitize($job['type']); ?>
                    </p>
                    <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-outline"
                        style="width: 100%; text-align: center;">View Details</a>
                </div>
            <?php
            endforeach;
        else:
            ?>
            <div class="card" style="grid-column: 1 / -1; text-align: center;">
                <p>No job postings available at the moment. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-2">
        <a href="jobs.php" class="btn btn-primary">Browse All Jobs</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>