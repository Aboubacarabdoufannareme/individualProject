<?php
// about.php
require_once 'includes/header.php';
?>

<div class="header-banner"
    style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 4rem 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">About DigiCareer Niger</h1>
        <p style="font-size: 1.2rem; max-width: 800px; margin: 0 auto;">
            Empowering Niger's workforce through digital innovation and connecting talent with opportunity
        </p>
    </div>
</div>

<div class="container mt-2 mb-2">
    <!-- Our Story -->
    <div class="card mb-2">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
            <div>
                <h2 style="margin-top: 0;">Our Story</h2>
                <p style="line-height: 1.7; color: var(--text-muted);">
                    Founded in 2024, DigiCareer Niger emerged from a simple observation: while Niger's youth are 
                    increasingly educated and tech-savvy, traditional job search methods remain fragmented and inefficient. 
                    Employers struggle to find qualified candidates, while job seekers miss opportunities due to lack 
                    of visibility.
                </p>
                <p style="line-height: 1.7; color: var(--text-muted);">
                    We set out to create Niger's first comprehensive digital employment platform—a solution that 
                    bridges the gap between ambitious professionals and forward-thinking companies. Today, we serve 
                    thousands of users across all eight regions of Niger, from Niamey to Agadez.
                </p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 5rem; color: var(--primary);">📈</div>
                <h3 style="color: var(--secondary);">3,000+ Jobs Posted</h3>
                <p style="color: var(--text-muted);">Connecting talent across Niger</p>
            </div>
        </div>
    </div>

    <!-- Our Mission & Vision -->
    <div class="card mb-2">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 2rem; border-radius: var(--radius-md);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎯</div>
                <h3 style="color: var(--secondary); margin-top: 0;">Our Mission</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    To democratize employment opportunities in Niger by providing a transparent, efficient, 
                    and accessible platform that empowers both job seekers and employers. We're committed to 
                    reducing unemployment and underemployment through technology-driven solutions.
                </p>
            </div>
            
            <div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 2rem; border-radius: var(--radius-md);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">👁️</div>
                <h3 style="color: var(--secondary); margin-top: 0;">Our Vision</h3>
                <p style="color: var(--text-muted); line-height: 1.6;">
                    To become Niger's leading employment ecosystem where every qualified individual finds 
                    meaningful work and every organization discovers the talent needed to grow. We envision 
                    a future where geography is no barrier to employment in Niger.
                </p>
            </div>
        </div>
    </div>

    <!-- Core Values -->
    <div class="card mb-2">
        <h2 style="text-align: center; margin-bottom: 2rem;">Our Core Values</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div style="text-align: center; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md); transition: all 0.3s;" 
                 onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">🤝</div>
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">Inclusivity</h3>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                    We serve all regions of Niger, from urban centers to rural communities, ensuring equal 
                    access to opportunities regardless of location or background.
                </p>
            </div>
            
            <div style="text-align: center; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md); transition: all 0.3s;"
                 onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">🎓</div>
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">Empowerment</h3>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                    We provide tools and resources to help job seekers build professional profiles and 
                    employers make informed hiring decisions.
                </p>
            </div>
            
            <div style="text-align: center; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md); transition: all 0.3s;"
                 onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">🔒</div>
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">Trust & Security</h3>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                    We prioritize data protection and privacy, implementing robust security measures to 
                    safeguard user information.
                </p>
            </div>
            
            <div style="text-align: center; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: var(--radius-md); transition: all 0.3s;"
                 onmouseover="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                <div style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);">💡</div>
                <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">Innovation</h3>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">
                    Continuously improving our platform with user feedback and technological advancements 
                    to better serve Niger's employment market.
                </p>
            </div>
        </div>
    </div>

    <!-- How We Help -->
    <div class="card mb-2">
        <h2 style="text-align: center; margin-bottom: 2rem;">How DigiCareer Helps Niger</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <h3 style="color: var(--secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span>👤</span> For Job Seekers
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <strong>Professional Profile Builder:</strong> Create comprehensive CVs with our guided templates
                    </li>
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <strong>Smart Job Matching:</strong> Get personalized job recommendations based on your skills
                    </li>
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <strong>Document Management:</strong> Securely store and share diplomas, certificates, and CVs
                    </li>
                    <li style="padding: 0.5rem 0;">
                        <strong>Career Resources:</strong> Access guides and tips for successful job hunting
                    </li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: var(--secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🏢</span> For Employers
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <strong>Targeted Recruitment:</strong> Reach qualified candidates across all regions of Niger
                    </li>
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <strong>Application Management:</strong> Streamline hiring with our organized dashboard
                    </li>
                    <li style="padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                        <strong>Candidate Verification:</strong> Access verified profiles and documents
                    </li>
                    <li style="padding: 0.5rem 0;">
                        <strong>Market Insights:</strong> Understand salary trends and talent availability
                    </li>
                </ul>
            </div>
        </div>
        
        <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--secondary);">
            <h4 style="margin-top: 0; color: var(--secondary);">Our Impact in Numbers</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; text-align: center;">
                <div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">2,500+</div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Registered Job Seekers</div>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">300+</div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Partner Companies</div>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">8</div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">Regions of Niger Served</div>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">85%</div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;">User Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Team & Commitment -->
    <div class="card">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
            <div>
                <h2 style="margin-top: 0;">Our Commitment to Niger</h2>
                <p style="line-height: 1.7; color: var(--text-muted);">
                    As a Nigerien company, we understand the unique challenges and opportunities of our local 
                    job market. Our platform is built with Niger's specific needs in mind—supporting multiple 
                    languages, understanding regional employment patterns, and respecting cultural contexts.
                </p>
                <p style="line-height: 1.7; color: var(--text-muted);">
                    We actively collaborate with educational institutions, government agencies, and 
                    non-profit organizations to enhance employment opportunities and support workforce 
                    development initiatives across Niger.
                </p>
            </div>
            <div style="background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%); padding: 2rem; border-radius: var(--radius-md); color: white; text-align: center;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🇳🇪</div>
                <h3 style="margin-top: 0; margin-bottom: 0.5rem;">Made for Niger</h3>
                <p style="margin: 0; opacity: 0.9;">
                    Local solutions for local challenges.<br>
                    Supporting Niger's economic growth through employment.
                </p>
            </div>
        </div>
        
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;">
        
        <div style="text-align: center;">
            <h3 style="margin-bottom: 1rem;">Join Our Mission</h3>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 1.5rem;">
                Whether you're looking for your next career opportunity or searching for the perfect candidate, 
                DigiCareer Niger is here to support your journey.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="register.php" class="btn btn-primary" style="padding: 0.75rem 2rem;">Start Your Journey</a>
                <a href="contact.php" class="btn btn-outline" style="padding: 0.75rem 2rem;">Contact Us</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
