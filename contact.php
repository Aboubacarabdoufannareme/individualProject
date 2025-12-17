<?php
// contact.php
require_once 'includes/header.php';

// Simple form handling logic
$messageSent = false;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message_type = sanitize($_POST['message_type'] ?? 'general');
    $message = sanitize($_POST['message'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields marked with *.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real app, you would send email here or save to DB
        // Simulate processing delay
        sleep(1);
        $messageSent = true;
        
        // Log contact attempt (simulated)
        error_log("Contact form submitted: $name <$email>, Type: $message_type, Subject: $subject");
    }
}
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 4rem 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Contact DigiCareer Niger</h1>
        <p style="font-size: 1.2rem; max-width: 800px; margin: 0 auto;">
            Have questions? We're here to help! Reach out to our dedicated support team
        </p>
    </div>
</div>

<div class="container mt-2 mb-2">
    <!-- Contact Information Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="card" style="text-align: center; padding: 2rem; transition: transform 0.3s;" 
             onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">📧</div>
            <h3 style="margin-bottom: 0.5rem; color: var(--secondary);">Email Support</h3>
            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">
                <strong>General Inquiries:</strong><br>
                info@digicareerniger.com
            </p>
            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">
                <strong>Technical Support:</strong><br>
                support@digicareerniger.com
            </p>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                <em>Response time: 24-48 hours</em>
            </p>
        </div>
        
        <div class="card" style="text-align: center; padding: 2rem; transition: transform 0.3s;" 
             onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">📞</div>
            <h3 style="margin-bottom: 0.5rem; color: var(--secondary);">Phone Support</h3>
            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">
                <strong>Hotline:</strong><br>
                +227 8890 3305
            </p>
            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">
                <strong>WhatsApp Business:</strong><br>
                +227 8890 3305
            </p>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                <em>Mon-Fri: 8:00 AM - 6:00 PM</em>
            </p>
        </div>
        
        <div class="card" style="text-align: center; padding: 2rem; transition: transform 0.3s;" 
             onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">📍</div>
            <h3 style="margin-bottom: 0.5rem; color: var(--secondary);">Office Location</h3>
            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">
                DigiCareer Niger Headquarters<br>
                Niamey Business District<br>
                Niamey, Niger
            </p>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                <em>By appointment only</em>
            </p>
        </div>
    </div>

    <?php if ($messageSent): ?>
        <!-- Success Message -->
        <div class="card" style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div style="font-size: 4rem; color: var(--success); margin-bottom: 1rem;">✓</div>
            <h2 style="color: var(--secondary); margin-bottom: 1rem;">Message Sent Successfully!</h2>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.6;">
                Thank you for contacting DigiCareer Niger. We have received your message and our team will 
                respond to you within <strong>24-48 hours</strong> during business days.
            </p>
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; text-align: left;">
                <h4 style="margin-top: 0; color: var(--secondary);">What happens next?</h4>
                <ul style="color: var(--text-muted); padding-left: 1.5rem; margin: 0;">
                    <li>You'll receive an automated confirmation email shortly</li>
                    <li>Our support team will review your inquiry</li>
                    <li>We'll contact you via email or phone with a response</li>
                    <li>If urgent, call our hotline for immediate assistance</li>
                </ul>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="contact.php" class="btn btn-primary">Send Another Message</a>
                <a href="index.php" class="btn btn-outline">Return to Homepage</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Contact Form -->
        <div class="card" style="max-width: 1000px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                <div>
                    <h2 style="margin-top: 0; color: var(--secondary);">Get in Touch</h2>
                    <p style="color: var(--text-muted); line-height: 1.7; margin-bottom: 2rem;">
                        Whether you have questions about our platform, need technical support, or want to 
                        explore partnership opportunities, our team is ready to assist you. Fill out the 
                        form and we'll get back to you promptly.
                    </p>
                    
                    <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                        <h4 style="color: var(--secondary); margin-top: 0; margin-bottom: 0.5rem;">📋 Before You Contact</h4>
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem; line-height: 1.6;">
                            • Check our <a href="help.php" style="color: var(--primary); font-weight: 500;">Help Center</a> for quick answers<br>
                            • Have your account email ready for faster support<br>
                            • For urgent matters, use our phone support<br>
                            • Include relevant details in your message
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: var(--secondary); margin-bottom: 1rem;">🌍 Regional Offices</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div style="background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0;">
                                <strong style="color: var(--secondary);">Zinder Office</strong>
                                <p style="color: var(--text-muted); margin: 0.25rem 0 0 0; font-size: 0.9rem;">
                                    +227 XX XXX XXX<br>
                                    Available Wed-Fri
                                </p>
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0;">
                                <strong style="color: var(--secondary);">Maradi Office</strong>
                                <p style="color: var(--text-muted); margin: 0.25rem 0 0 0; font-size: 0.9rem;">
                                    +227 XX XXX XXX<br>
                                    Available Tue-Thu
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <form method="POST" action="contact.php">
                        <h3 style="color: var(--secondary); margin-top: 0; margin-bottom: 1.5rem;">Contact Form</h3>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                                <strong>⚠️ Error:</strong> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name" class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   placeholder="Enter your full name" required
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address <span style="color: #ef4444;">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       placeholder="you@example.com" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       placeholder="+227 XX XXX XXX"
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="message_type" class="form-label">Inquiry Type</label>
                            <select id="message_type" name="message_type" class="form-control">
                                <option value="general" <?php echo (isset($_POST['message_type']) && $_POST['message_type'] == 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="technical" <?php echo (isset($_POST['message_type']) && $_POST['message_type'] == 'technical') ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="billing" <?php echo (isset($_POST['message_type']) && $_POST['message_type'] == 'billing') ? 'selected' : ''; ?>>Billing Question</option>
                                <option value="partnership" <?php echo (isset($_POST['message_type']) && $_POST['message_type'] == 'partnership') ? 'selected' : ''; ?>>Partnership Inquiry</option>
                                <option value="feedback" <?php echo (isset($_POST['message_type']) && $_POST['message_type'] == 'feedback') ? 'selected' : ''; ?>>Feedback & Suggestions</option>
                                <option value="other" <?php echo (isset($_POST['message_type']) && $_POST['message_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" 
                                   placeholder="Brief description of your inquiry"
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="message" class="form-label">Message <span style="color: #ef4444;">*</span></label>
                            <textarea id="message" name="message" class="form-control" rows="6" 
                                      placeholder="Please provide detailed information about your inquiry..."
                                      required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                                Minimum 20 characters
                            </div>
                        </div>

                        <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                                <input type="checkbox" id="privacy" name="privacy" required style="margin-top: 0.25rem;">
                                <label for="privacy" style="color: var(--text-muted); font-size: 0.9rem;">
                                    I agree to the <a href="privacy.php" style="color: var(--primary);">Privacy Policy</a> and 
                                    understand that DigiCareer Niger will process my personal data in accordance with applicable laws.
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
                            <span style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                📨 Send Message
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Additional Information -->
        <div class="card" style="margin-top: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div>
                    <h4 style="color: var(--secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        ⏱️ Response Times
                    </h4>
                    <ul style="color: var(--text-muted); padding-left: 1.5rem; margin: 0;">
                        <li><strong>Technical Support:</strong> 24-48 hours</li>
                        <li><strong>General Inquiries:</strong> 1-2 business days</li>
                        <li><strong>Partnership Requests:</strong> 3-5 business days</li>
                        <li><strong>Urgent Matters:</strong> Call our hotline</li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="color: var(--secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        📞 Emergency Contact
                    </h4>
                    <p style="color: var(--text-muted); margin: 0;">
                        For urgent platform issues affecting multiple users:<br>
                        <strong>Emergency Line:</strong> +227 8890 3305 (Ext. 2)<br>
                        <strong>Available:</strong> 24/7 for critical issues only
                    </p>
                </div>
                
                <div>
                    <h4 style="color: var(--secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        💼 Business Hours
                    </h4>
                    <ul style="color: var(--text-muted); padding-left: 1.5rem; margin: 0;">
                        <li><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM</li>
                        <li><strong>Saturday:</strong> 9:00 AM - 1:00 PM</li>
                        <li><strong>Sunday:</strong> Closed</li>
                        <li><strong>Public Holidays:</strong> Limited support</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
