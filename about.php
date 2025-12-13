<?php
// about.php
require_once 'includes/header.php';
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 3rem 0; color: white;">
    <div class="container text-center">
        <h1>About DigiCareer Niger</h1>
        <p>Connecting job seekers and employers across Niger through digital innovation</p>
    </div>
</div>

<div class="container mt-2 mb-2">
    <div class="card">
        <h2>Who We Are</h2>
        <p>
            DigiCareer Niger is a digital recruitment platform designed to make job hunting easier,
            faster, and more accessible for candidates and employers across Niger.
            Whether you’re a young graduate looking for your first opportunity or a company searching
            for qualified talent, DigiCareer is here to simplify the process.
        </p>

        <h2>Our Mission</h2>
        <p>
            Our mission is to bridge the gap between talent and opportunity by providing a modern,
            user-friendly system where:
        </p>
        <ul style="list-style: disc; margin-left: 1.5rem; margin-bottom: 2rem;">
            <li>Candidates can create profiles, build CVs, and apply for jobs easily.</li>
            <li>Employers can post job opportunities and review qualified candidates.</li>
            <li>Both parties benefit from a transparent and efficient recruitment process.</li>
        </ul>

        <h2>Why DigiCareer?</h2>
        <p>
            We focus on innovation, simplicity, and accessibility. Our platform helps eliminate
            geographical barriers and brings professional opportunities directly to your fingertips.
        </p>

        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <div
                style="background: var(--background); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">🌍 Accessible</h3>
                <p style="margin: 0;">Available to all candidates and employers across Niger.</p>
            </div>
            <div
                style="background: var(--background); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">⚡ Fast</h3>
                <p style="margin: 0;">Quick profile creation, application process, and job posting.</p>
            </div>
            <div
                style="background: var(--background); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">🔒 Secure</h3>
                <p style="margin: 0;">Your data is protected with modern security practices.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>