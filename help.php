<?php
// help.php
require_once 'includes/header.php';
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 3rem 0; color: white;">
    <div class="container text-center">
        <h1>Help Center</h1>
        <p>Find answers, guides, and support for using DigiCareer Niger</p>
    </div>
</div>

<div class="container mt-2 mb-2">
    <!-- Quick Help Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <a href="#for-candidates" style="text-decoration: none;">
            <div class="card" style="text-align: center; padding: 1.5rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">👤</div>
                <h3 style="margin: 0;">Job Seekers</h3>
                <p style="color: var(--text-muted); margin: 0.5rem 0 0 0;">Find jobs, apply, and manage applications</p>
            </div>
        </a>
        
        <a href="#for-employers" style="text-decoration: none;">
            <div class="card" style="text-align: center; padding: 1.5rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🏢</div>
                <h3 style="margin: 0;">Employers</h3>
                <p style="color: var(--text-muted); margin: 0.5rem 0 0 0;">Post jobs, find candidates, and manage hiring</p>
            </div>
        </a>
        
        <a href="#account" style="text-decoration: none;">
            <div class="card" style="text-align: center; padding: 1.5rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔐</div>
                <h3 style="margin: 0;">Account Help</h3>
                <p style="color: var(--text-muted); margin: 0.5rem 0 0 0;">Login, registration, and security</p>
            </div>
        </a>
    </div>

    <!-- For Candidates Section -->
    <div class="card mb-2" id="for-candidates">
        <h2 style="display: flex; align-items: center; gap: 0.5rem;">
            <span>👤</span> For Job Seekers
        </h2>
        
        <div class="faq-section" style="margin-top: 1.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--secondary);">Getting Started</h3>
            
            <div class="faq-item" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">How do I create a compelling profile?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    1. Complete all profile sections (personal info, education, experience)<br>
                    2. Upload your updated CV in PDF format<br>
                    3. Add relevant skills and certifications<br>
                    4. Write a professional bio summarizing your expertise<br>
                    5. Set your job preferences (location, salary, job type)
                </p>
            </div>
            
            <div class="faq-item" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">How do I apply for jobs effectively?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    • Search for jobs using filters (location, salary, job type)<br>
                    • Read the full job description before applying<br>
                    • Tailor your cover letter for each application<br>
                    • Ensure your documents are up-to-date<br>
                    • Apply within 48 hours of job posting for better visibility
                </p>
            </div>
            
            <div class="faq-item" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">How can I improve my chances of getting hired?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    • Keep your profile 100% complete<br>
                    • Upload professional documents (CV, diplomas, certificates)<br>
                    • Set job alerts for new postings matching your skills<br>
                    • Respond promptly to employer messages<br>
                    • Maintain an active profile by updating skills regularly
                </p>
            </div>
        </div>
        
        <div class="faq-section" style="margin-top: 2rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--secondary);">Application Process</h3>
            
            <div class="faq-item" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">What happens after I apply?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    <strong>1. Application Received:</strong> You'll see "Pending" status in your dashboard<br>
                    <strong>2. Under Review:</strong> Employer reviews your application (status changes to "Reviewed")<br>
                    <strong>3. Interview Invitation:</strong> If shortlisted, employer may contact you via email/phone<br>
                    <strong>4. Final Decision:</strong> Application status updates to "Accepted" or "Rejected"<br>
                    <strong>Note:</strong> Check your dashboard regularly for updates
                </p>
            </div>
        </div>
    </div>

    <!-- For Employers Section -->
    <div class="card mb-2" id="for-employers">
        <h2 style="display: flex; align-items: center; gap: 0.5rem;">
            <span>🏢</span> For Employers
        </h2>
        
        <div class="faq-section" style="margin-top: 1.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--secondary);">Posting Jobs</h3>
            
            <div class="faq-item" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">How do I create an effective job posting?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    • <strong>Clear Title:</strong> Use specific job titles (e.g., "Junior Web Developer" not just "Developer")<br>
                    • <strong>Detailed Description:</strong> Include responsibilities, requirements, and benefits<br>
                    • <strong>Accurate Information:</strong> Specify location, salary range, and job type<br>
                    • <strong>Company Profile:</strong> Complete your company profile with logo and description<br>
                    • <strong>Application Deadline:</strong> Set realistic deadlines for applications
                </p>
            </div>
            
            <div class="faq-item" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">How can I attract quality candidates?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    1. <strong>Competitive Offer:</strong> Include salary range and benefits<br>
                    2. <strong>Company Culture:</strong> Describe your work environment and values<br>
                    3. <strong>Growth Opportunities:</strong> Mention career development possibilities<br>
                    4. <strong>Clear Requirements:</strong> Specify must-have vs nice-to-have skills<br>
                    5. <strong>Prompt Responses:</strong> Review applications within 3-5 business days
                </p>
            </div>
        </div>
        
        <div class="faq-section" style="margin-top: 2rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--secondary);">Candidate Management</h3>
            
            <div class="faq-item" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">How do I manage applications effectively?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    <strong>Dashboard Tools:</strong><br>
                    • View all applications in one place<br>
                    • Filter candidates by status (Pending, Reviewed, Accepted, Rejected)<br>
                    • Download candidate documents (CVs, diplomas)<br>
                    • Contact candidates directly via email<br>
                    • Update application status to keep candidates informed<br>
                    <br>
                    <strong>Best Practice:</strong> Update application status within 48 hours of receiving applications
                </p>
            </div>
        </div>
    </div>

    <!-- Account & Technical Help -->
    <div class="card mb-2" id="account">
        <h2 style="display: flex; align-items: center; gap: 0.5rem;">
            <span>🔐</span> Account & Technical Support
        </h2>
        
        <div class="faq-section" style="margin-top: 1.5rem;">
            <div class="faq-item" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">How do I reset my password?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    1. Go to the Login page<br>
                    2. Click "Forgot Password"<br>
                    3. Enter your registered email address<br>
                    4. Check your email for password reset instructions<br>
                    5. Follow the link to create a new password<br>
                    <em>Note: The reset link expires after 24 hours for security</em>
                </p>
            </div>
            
            <div class="faq-item" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">What file formats are supported for uploads?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    <strong>Accepted Formats:</strong><br>
                    • CV/Resume: PDF, DOC, DOCX (Max: 5MB)<br>
                    • Diplomas/Certificates: PDF, JPG, PNG (Max: 5MB each)<br>
                    • Profile Picture: JPG, PNG (Recommended: 400x400px)<br>
                    • Company Logo: JPG, PNG (Recommended: 300x300px)<br>
                    <br>
                    <strong>Tip:</strong> Use PDF format for documents to preserve formatting
                </p>
            </div>
            
            <div class="faq-item" style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Why can't I upload my documents?</h4>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    Common issues and solutions:<br>
                    1. <strong>File too large:</strong> Compress files to under 5MB<br>
                    2. <strong>Wrong format:</strong> Convert to supported formats (PDF, DOC, JPG, PNG)<br>
                    3. <strong>Slow internet:</strong> Try during off-peak hours<br>
                    4. <strong>Browser issue:</strong> Clear cache or try different browser<br>
                    5. <strong>System maintenance:</strong> Check status notification on homepage<br>
                    <br>
                    If problems persist, contact our support team
                </p>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="card" style="background: #f8fafc; border-left: 4px solid var(--secondary);">
        <h2 style="margin-top: 0;">Still Need Help?</h2>
        <p style="margin-bottom: 1.5rem;">Our support team is here to assist you. Choose your preferred contact method:</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">📧</div>
                <h4 style="margin-bottom: 0.5rem;">Email Support</h4>
                <p style="color: var(--text-muted); margin: 0;">
                    <strong>General:</strong> support@digicareerniger.com<br>
                    <strong>Technical:</strong> tech@digicareerniger.com<br>
                    <strong>Response Time:</strong> 24-48 hours
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">📞</div>
                <h4 style="margin-bottom: 0.5rem;">Phone Support</h4>
                <p style="color: var(--text-muted); margin: 0;">
                    <strong>Hotline:</strong> +227 88903305<br>
                    <strong>Hours:</strong> Mon-Fri, 8AM-6PM<br>
                    <strong>Language:</strong> French, English, Hausa
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">📍</div>
                <h4 style="margin-bottom: 0.5rem;">Visit Us</h4>
                <p style="color: var(--text-muted); margin: 0;">
                    DigiCareer Niger Headquarters<br>
                    Niamey Business District<br>
                    By appointment only
                </p>
            </div>
        </div>
        
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
            <h4 style="margin-bottom: 0.5rem;">Before Contacting Support</h4>
            <p style="color: var(--text-muted); margin: 0; font-size: 0.9rem;">
                To help us serve you better, please have ready:<br>
                • Your account email address<br>
                • Screenshot of any error messages<br>
                • Description of the issue<br>
                • Browser and device information
            </p>
        </div>
    </div>
    
    <!-- Quick Tips Section -->
    <div style="margin-top: 2rem; padding: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: var(--radius-md);">
        <h3 style="margin-top: 0; display: flex; align-items: center; gap: 0.5rem;">
            <span>💡</span> Quick Tips
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div style="background: white; padding: 1rem; border-radius: var(--radius-md);">
                <strong>For Candidates:</strong> Set up job alerts to never miss opportunities matching your skills.
            </div>
            <div style="background: white; padding: 1rem; border-radius: var(--radius-md);">
                <strong>For Employers:</strong> Use the "Save Candidate" feature to build a talent pool for future openings.
            </div>
            <div style="background: white; padding: 1rem; border-radius: var(--radius-md);">
                <strong>Security:</strong> Never share your password. DigiCareer staff will never ask for it.
            </div>
            <div style="background: white; padding: 1rem; border-radius: var(--radius-md);">
                <strong>Updates:</strong> Follow us on social media for platform updates and job market insights.
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
