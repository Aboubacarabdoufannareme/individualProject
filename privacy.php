<?php
// privacy.php
require_once 'includes/header.php';
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 4rem 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Privacy Policy</h1>
        <p style="font-size: 1.2rem; max-width: 800px; margin: 0 auto;">
            Protecting your personal data is our priority. Learn how we collect, use, and safeguard your information.
        </p>
    </div>
</div>

<div class="container mt-2 mb-2">
    <!-- Quick Navigation -->
    <div class="card" style="margin-bottom: 1.5rem; background: #f8fafc;">
        <h3 style="margin-top: 0; color: var(--secondary);">Quick Navigation</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
            <a href="#introduction" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">Introduction</a>
            <a href="#data-collection" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">Data We Collect</a>
            <a href="#data-use" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">How We Use Data</a>
            <a href="#data-sharing" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">Data Sharing</a>
            <a href="#data-security" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">Data Security</a>
            <a href="#your-rights" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">Your Rights</a>
            <a href="#cookies" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">Cookies</a>
            <a href="#contact" style="padding: 0.5rem 1rem; background: white; border-radius: 20px; text-decoration: none; color: var(--text-main); border: 1px solid #e2e8f0; font-size: 0.9rem;">Contact Us</a>
        </div>
    </div>

    <div class="card">
        <!-- Policy Header -->
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem; border-left: 4px solid var(--secondary);">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <p style="margin: 0 0 0.5rem 0; color: var(--text-muted);">
                        <strong>Last Updated:</strong> December 09, 2025<br>
                        <strong>Effective Date:</strong> December 02, 2025<br>
                        <strong>Version:</strong> 2.0
                    </p>
                </div>
                <div>
                    <p style="margin: 0; color: var(--text-muted);">
                        <strong>Applicable Laws:</strong><br>
                        • Niger Data Protection Regulations<br>
                        • General Data Protection Principles
                    </p>
                </div>
            </div>
        </div>

        <!-- Introduction -->
        <div id="introduction" style="margin-bottom: 2.5rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">1</span>
                Introduction
            </h2>
            <p style="color: var(--text-muted); line-height: 1.7;">
                DigiCareer Niger ("we," "us," "our") is committed to protecting your privacy and personal data. 
                This Privacy Policy explains how we collect, use, disclose, and safeguard your information when 
                you use our platform, services, and applications.
            </p>
            <div style="background: #f0f9ff; padding: 1.25rem; border-radius: var(--radius-md); margin-top: 1rem;">
                <p style="margin: 0; color: var(--text-muted); font-size: 0.95rem;">
                    <strong>Scope:</strong> This policy applies to all users of DigiCareer Niger, including 
                    candidates, employers, and visitors to our website. By using our services, you consent to 
                    the data practices described in this policy.
                </p>
            </div>
        </div>

        <!-- Data We Collect -->
        <div id="data-collection" style="margin-bottom: 2.5rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">2</span>
                Information We Collect
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0;">
                    <h3 style="margin-top: 0; color: var(--secondary); margin-bottom: 1rem;">Information You Provide</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <h4 style="font-size: 1rem; color: var(--text-main); margin-bottom: 0.5rem;">Candidates</h4>
                            <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.9rem;">
                                <li>Full name & contact details</li>
                                <li>Education history</li>
                                <li>Work experience</li>
                                <li>Skills & certifications</li>
                                <li>CVs & documents</li>
                                <li>Profile photo</li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; color: var(--text-main); margin-bottom: 0.5rem;">Employers</h4>
                            <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.9rem;">
                                <li>Company information</li>
                                <li>Contact person details</li>
                                <li>Company logo</li>
                                <li>Job postings</li>
                                <li>Application data</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0;">
                    <h3 style="margin-top: 0; color: var(--secondary); margin-bottom: 1rem;">Automatically Collected</h3>
                    <ul style="color: var(--text-muted); padding-left: 1.5rem; margin: 0; line-height: 1.7;">
                        <li><strong>Usage Data:</strong> Pages visited, time spent, features used</li>
                        <li><strong>Device Information:</strong> IP address, browser type, operating system</li>
                        <li><strong>Location Data:</strong> General location (country, region)</li>
                        <li><strong>Cookies & Similar Technologies:</strong> Session data, preferences</li>
                        <li><strong>Log Data:</strong> Server logs, error reports</li>
                    </ul>
                </div>
            </div>
            
            <div style="background: #fef3c7; padding: 1.25rem; border-radius: var(--radius-md); margin-top: 1.5rem;">
                <h4 style="margin-top: 0; color: #92400e; margin-bottom: 0.5rem;">Sensitive Personal Data</h4>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                    We may process sensitive personal data (diplomas, certificates) only when necessary for 
                    employment purposes and with your explicit consent. We do not collect or process data 
                    revealing racial/ethnic origin, political opinions, religious beliefs, or health data.
                </p>
            </div>
        </div>

        <!-- How We Use Your Information -->
        <div id="data-use" style="margin-bottom: 2.5rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">3</span>
                How We Use Your Information
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="background: #f0f9ff; padding: 1.5rem; border-radius: var(--radius-md);">
                    <div style="font-size: 2rem; margin-bottom: 1rem; color: var(--primary);">👤</div>
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Candidate Data Usage</h4>
                    <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.95rem;">
                        <li>Create and display your profile</li>
                        <li>Match you with relevant job opportunities</li>
                        <li>Process job applications</li>
                        <li>Send job alerts and notifications</li>
                        <li>Verify credentials when requested</li>
                    </ul>
                </div>
                
                <div style="background: #f0f9ff; padding: 1.5rem; border-radius: var(--radius-md);">
                    <div style="font-size: 2rem; margin-bottom: 1rem; color: var(--primary);">🏢</div>
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Employer Data Usage</h4>
                    <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.95rem;">
                        <li>Display company profiles and job postings</li>
                        <li>Process job applications</li>
                        <li>Provide candidate matching services</li>
                        <li>Send application notifications</li>
                        <li>Generate recruitment analytics</li>
                    </ul>
                </div>
                
                <div style="background: #f0f9ff; padding: 1.5rem; border-radius: var(--radius-md);">
                    <div style="font-size: 2rem; margin-bottom: 1rem; color: var(--primary);">🔧</div>
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Platform Operations</h4>
                    <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.95rem;">
                        <li>Maintain and improve our services</li>
                        <li>Ensure platform security</li>
                        <li>Comply with legal obligations</li>
                        <li>Prevent fraud and misuse</li>
                        <li>Communicate important updates</li>
                    </ul>
                </div>
            </div>
            
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); margin-top: 1.5rem;">
                <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Legal Basis for Processing</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem;">✅</div>
                        <div style="font-weight: 600; color: var(--text-main);">Consent</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">When you explicitly agree</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem;">📋</div>
                        <div style="font-weight: 600; color: var(--text-main);">Contract</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">To fulfill our services</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem;">⚖️</div>
                        <div style="font-weight: 600; color: var(--text-main);">Legal Obligation</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">To comply with laws</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem;">🎯</div>
                        <div style="font-weight: 600; color: var(--text-main);">Legitimate Interest</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">To improve our services</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Sharing -->
        <div id="data-sharing" style="margin-bottom: 2.5rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">4</span>
                How We Share Your Information
            </h2>
            
            <div style="background: #fef3c7; padding: 1.5rem; border-radius: var(--radius-md); margin-top: 1rem;">
                <h4 style="margin-top: 0; color: #92400e; margin-bottom: 0.5rem;">Core Platform Sharing</h4>
                <p style="color: var(--text-muted); margin: 0;">
                    The primary purpose of DigiCareer Niger is to connect candidates with employers. Therefore:
                </p>
                <ul style="color: var(--text-muted); padding-left: 1.5rem; margin: 0.5rem 0 0 0;">
                    <li><strong>Candidates:</strong> Your profile and application materials are shared with employers you apply to</li>
                    <li><strong>Employers:</strong> Your job postings and company profile are visible to candidates</li>
                    <li><strong>Mutual Sharing:</strong> Only necessary information is shared for the recruitment process</li>
                </ul>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                <div>
                    <h3 style="color: var(--text-main); margin-top: 0; margin-bottom: 1rem;">Third-Party Service Providers</h3>
                    <p style="color: var(--text-muted); line-height: 1.7;">
                        We may share data with trusted partners who assist in operating our platform:
                    </p>
                    <ul style="color: var(--text-muted); padding-left: 1.5rem; margin-top: 0.5rem; line-height: 1.7;">
                        <li>Cloud hosting providers (data storage)</li>
                        <li>Email service providers (communications)</li>
                        <li>Analytics services (platform improvement)</li>
                        <li>Payment processors (future premium features)</li>
                        <li>Technical support providers</li>
                    </ul>
                </div>
                
                <div>
                    <h3 style="color: var(--text-main); margin-top: 0; margin-bottom: 1rem;">Legal Requirements</h3>
                    <p style="color: var(--text-muted); line-height: 1.7;">
                        We may disclose your information if required by law:
                    </p>
                    <ul style="color: var(--text-muted); padding-left: 1.5rem; margin-top: 0.5rem; line-height: 1.7;">
                        <li>To comply with legal obligations</li>
                        <li>To protect our rights and property</li>
                        <li>To prevent or investigate wrongdoing</li>
                        <li>To ensure safety of our users</li>
                        <li>In connection with legal proceedings</li>
                    </ul>
                </div>
            </div>
            
            <div style="background: #f8fafc; padding: 1.25rem; border-radius: var(--radius-md); margin-top: 1.5rem;">
                <p style="margin: 0; color: var(--text-muted); font-size: 0.95rem;">
                    <strong>No Sale of Personal Data:</strong> We do not sell, trade, or rent your personal 
                    identification information to third parties for marketing purposes.
                </p>
            </div>
        </div>

        <!-- Data Security -->
        <div id="data-security" style="margin-bottom: 2.5rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">5</span>
                Data Security and Protection
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">🔒</div>
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Technical Measures</h4>
                    <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.95rem; text-align: left;">
                        <li>SSL/TLS encryption for data transmission</li>
                        <li>Secure server infrastructure</li>
                        <li>Regular security audits</li>
                        <li>Firewall protection</li>
                    </ul>
                </div>
                
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">👥</div>
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Organizational Measures</h4>
                    <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.95rem; text-align: left;">
                        <li>Limited employee access to data</li>
                        <li>Data protection training</li>
                        <li>Confidentiality agreements</li>
                        <li>Access control policies</li>
                    </ul>
                </div>
                
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">📁</div>
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Data Management</h4>
                    <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.95rem; text-align: left;">
                        <li>Data minimization principles</li>
                        <li>Regular data backups</li>
                        <li>Secure data disposal</li>
                        <li>Incident response plan</li>
                    </ul>
                </div>
            </div>
            
            <div style="background: #fee2e2; padding: 1.5rem; border-radius: var(--radius-md); margin-top: 1.5rem;">
                <h4 style="margin-top: 0; color: #991b1b; margin-bottom: 0.5rem;">Your Role in Security</h4>
                <p style="color: var(--text-muted); margin: 0;">
                    While we implement robust security measures, you also play a crucial role:
                </p>
                <ul style="color: var(--text-muted); padding-left: 1.5rem; margin: 0.5rem 0 0 0;">
                    <li>Keep your login credentials confidential</li>
                    <li>Use strong, unique passwords</li>
                    <li>Log out after using shared devices</li>
                    <li>Report suspicious activity immediately</li>
                    <li>Be cautious about information you share</li>
                </ul>
            </div>
        </div>

        <!-- Your Rights -->
        <div id="your-rights" style="margin-bottom: 2.5rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">6</span>
                Your Data Protection Rights
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="background: #f0f9ff; padding: 1.5rem; border-radius: var(--radius-md);">
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Access & Portability</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                        You have the right to access your personal data and receive it in a structured, 
                        commonly used format.
                    </p>
                </div>
                
                <div style="background: #f0f9ff; padding: 1.5rem; border-radius: var(--radius-md);">
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Correction & Deletion</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                        You can request correction of inaccurate data or deletion of your data under 
                        certain conditions.
                    </p>
                </div>
                
                <div style="background: #f0f9ff; padding: 1.5rem; border-radius: var(--radius-md);">
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Restriction & Objection</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                        You may request restriction of processing or object to certain types of 
                        data processing.
                    </p>
                </div>
                
                <div style="background: #f0f9ff; padding: 1.5rem; border-radius: var(--radius-md);">
                    <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Withdraw Consent</h4>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                        Where processing is based on consent, you have the right to withdraw consent 
                        at any time.
                    </p>
                </div>
            </div>
            
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); margin-top: 1.5rem;">
                <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Exercising Your Rights</h4>
                <p style="color: var(--text-muted); margin: 0;">
                    To exercise any of these rights, please contact us at <strong>privacy@digicareerniger.com</strong>. 
                    We will respond to your request within 30 days. Note that some rights may be limited 
                    by legal requirements or legitimate business needs.
                </p>
            </div>
        </div>

        <!-- Cookies -->
        <div id="cookies" style="margin-bottom: 2.5rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">7</span>
                Cookies and Tracking Technologies
            </h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
                <div>
                    <h3 style="color: var(--text-main); margin-top: 0; margin-bottom: 1rem;">Types of Cookies We Use</h3>
                    <div style="background: white; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0;">
                        <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Essential Cookies</h4>
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                            Required for basic platform functionality. Cannot be disabled.
                        </p>
                    </div>
                    <div style="background: white; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; margin-top: 1rem;">
                        <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Functional Cookies</h4>
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                            Remember preferences and settings to enhance user experience.
                        </p>
                    </div>
                    <div style="background: white; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; margin-top: 1rem;">
                        <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">Analytics Cookies</h4>
                        <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                            Help us understand how users interact with our platform.
                        </p>
                    </div>
                </div>
                
                <div>
                    <h3 style="color: var(--text-main); margin-top: 0; margin-bottom: 1rem;">Cookie Management</h3>
                    <p style="color: var(--text-muted); line-height: 1.7;">
                        You can control cookies through your browser settings. However, disabling essential 
                        cookies may affect platform functionality.
                    </p>
                    <div style="background: #f0f9ff; padding: 1.25rem; border-radius: var(--radius-md); margin-top: 1rem;">
                        <h4 style="margin-top: 0; color: var(--secondary); margin-bottom: 0.5rem;">How to Manage Cookies</h4>
                        <ul style="color: var(--text-muted); padding-left: 1rem; margin: 0; font-size: 0.95rem;">
                            <li>Browser settings (Chrome, Firefox, Safari, etc.)</li>
                            <li>Private/incognito browsing modes</li>
                            <li>Browser extensions for cookie management</li>
                            <li>Clear cookies regularly if desired</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact & Changes -->
        <div id="contact" style="margin-bottom: 2rem;">
            <h2 style="display: flex; align-items: center; gap: 0.5rem; color: var(--secondary);">
                <span style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">8</span>
                Contact Us & Policy Changes
            </h2>
            
            <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); margin-top: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h3 style="color: var(--text-main); margin-top: 0; margin-bottom: 1rem;">Contact Information</h3>
                        <div style="color: var(--text-muted);">
                            <p style="margin: 0.5rem 0;">
                                <strong>Data Protection Officer:</strong><br>
                                privacy@digicareerniger.com
                            </p>
                            <p style="margin: 0.5rem 0;">
                                <strong>Physical Address:</strong><br>
                                DigiCareer Niger<br>
                                Niamey Business District<br>
                                Niamey, Niger
                            </p>
                            <p style="margin: 0.5rem 0;">
                                <strong>Response Time:</strong> 30 days for data rights requests
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 style="color: var(--text-main); margin-top: 0; margin-bottom: 1rem;">Policy Updates</h3>
                        <p style="color: var(--text-muted); line-height: 1.7;">
                            We may update this Privacy Policy periodically. We will notify you of significant 
                            changes by posting the new policy on our platform and updating the "Last Updated" date.
                        </p>
                        <div style="background: white; padding: 1rem; border-radius: var(--radius-md); margin-top: 1rem;">
                            <p style="margin: 0; color: var(--text-muted); font-size: 0.95rem;">
                                <strong>Notification Methods:</strong><br>
                                • Platform notification<br>
                                • Email to registered users<br>
                                • Updated policy on website
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Note -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 2rem; border-radius: var(--radius-md); color: white; margin-top: 2rem; text-align: center;">
            <h3 style="margin-top: 0; color: white; margin-bottom: 1rem;">Thank You for Trusting DigiCareer Niger</h3>
            <p style="margin: 0; opacity: 0.9;">
                We are committed to protecting your privacy and ensuring a secure experience on our platform.
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
