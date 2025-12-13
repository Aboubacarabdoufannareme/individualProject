<?php
// contact.php
require_once 'includes/header.php';

// Simple form handling logic
$messageSent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if ($name && $email && $message) {
        // In a real app, you would send email here or save to DB.
        // For now, we simulate success.
        $messageSent = true;
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 3rem 0; color: white;">
    <div class="container text-center">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you. Get in touch with our team.</p>
    </div>
</div>

<div class="container mt-2 mb-2">
    <div class="card" style="max-width: 800px; margin: 0 auto;">

        <?php if ($messageSent): ?>
            <div class="alert alert-success" style="text-align: center;">
                <h3 style="margin-bottom: 0.5rem;">Message Sent!</h3>
                <p>Thank you for contacting us. We will get back to you shortly.</p>
                <a href="contact.php" class="btn btn-primary" style="margin-top: 1rem;">Send Another Message</a>
            </div>
        <?php else: ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h3>Get in Touch</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                        Have a question about our services? Need support?
                        Fill out the contact form or reach us via email or phone.
                    </p>

                    <div style="margin-bottom: 1rem;">
                        <strong>📍 Address</strong>
                        <p style="color: var(--text-muted);">Niamey, Niger</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <strong>📧 Email</strong>
                        <p style="color: var(--text-muted);">support@digicareerniger.com</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <strong>📞 Phone</strong>
                        <p style="color: var(--text-muted);">+227 88903305</p>
                    </div>
                </div>

                <form method="POST" action="contact.php">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Message <span style="color: red;">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </form>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>