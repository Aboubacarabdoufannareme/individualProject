<?php
// employer_dashboard.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/header.php';
require_once 'includes/functions.php';
require_login();

// Ensure user is an employer
if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$employer_id = $_SESSION['user_id'];

// Get Employer Info
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$employer_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('login.php');
}

// Get Stats
// 1. Active Jobs
$stmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'active'");
$stmt->execute([$employer_id]);
$active_jobs = $stmt->fetchColumn();

// 2. Total Applications received (across all jobs)
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.employer_id = ?
");
$stmt->execute([$employer_id]);
$total_applications = $stmt->fetchColumn();

// Get Recent Applications
$stmt = $conn->prepare("
    SELECT a.*, j.title as job_title, c.full_name as candidate_name, c.id as candidate_id 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN candidates c ON a.candidate_id = c.id 
    WHERE j.employer_id = ? 
    ORDER BY a.applied_at DESC 
    LIMIT 5
");
$stmt->execute([$employer_id]);
$recent_apps = $stmt->fetchAll();

// Get Recent Jobs
$stmt = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$employer_id]);
$recent_jobs = $stmt->fetchAll();

// Fetch Sent Invitations
$stmt = $conn->prepare("
    SELECT i.*, j.title as job_title, c.full_name as candidate_name 
    FROM invitations i 
    JOIN jobs j ON i.job_id = j.id 
    JOIN candidates c ON i.candidate_id = c.id 
    WHERE i.employer_id = ? 
    ORDER BY i.created_at DESC 
    LIMIT 5
");
$stmt->execute([$employer_id]);
$invitations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard - DigiCareer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .mt-2 {
            margin-top: 2rem;
        }
        
        .mb-2 {
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .row {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #007bff;
            color: #007bff;
        }
        
        .btn-outline:hover {
            background-color: #007bff;
            color: white;
        }
        
        aside {
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .review-btn {
            display: inline-block;
            padding: 6px 12px;
            background: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .review-btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: white;
            text-decoration: none;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background: #e0f2fe;
            color: #075985;
        }
        
        .status-reviewed {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-accepted {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        @media (max-width: 768px) {
            .row {
                grid-template-columns: 1fr;
            }
            
            aside {
                position: static;
            }
            
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
<div class="container mt-2">
    <div class="row">
        <!-- Sidebar -->
        <aside>
            <div class="card">
                <div class="text-center mb-2">
                    <?php
                    // Get company logo
                    function get_company_logo_url($user_id, $conn) {
                        try {
                            $stmt = $conn->prepare("SELECT logo FROM employers WHERE id = ?");
                            $stmt->execute([$user_id]);
                            $employer = $stmt->fetch();
                            
                            if (!empty($employer['logo'])) {
                                // Check documents table first
                                try {
                                    $stmt = $conn->prepare("SELECT file_content, mime_type FROM documents 
                                                            WHERE user_id = ? AND user_type = 'employer' AND file_path = ? AND type = 'company_logo' 
                                                            ORDER BY uploaded_at DESC LIMIT 1");
                                    $stmt->execute([$user_id, $employer['logo']]);
                                    $result = $stmt->fetch();
                                    
                                    if ($result && !empty($result['file_content'])) {
                                        return 'data:' . $result['mime_type'] . ';base64,' . base64_encode($result['file_content']);
                                    }
                                } catch (Exception $e) {
                                    // Continue to filesystem check
                                }
                                
                                // Try filesystem
                                $possible_paths = [
                                    'uploads/logos/' . $employer['logo'],
                                    'uploads/' . $employer['logo'],
                                    $employer['logo']
                                ];
                                
                                foreach ($possible_paths as $path) {
                                    if (file_exists($path)) {
                                        return $path;
                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            // Fall through
                        }
                        
                        $stmt = $conn->prepare("SELECT company_name FROM employers WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $employer = $stmt->fetch();
                        $name = urlencode($employer['company_name'] ?? 'Company');
                        return "https://ui-avatars.com/api/?name=$name&background=0ea5e9&color=fff&size=128";
                    }
                    
                    $logo_url = get_company_logo_url($employer_id, $conn);
                    ?>
                    <img src="<?php echo $logo_url; ?>" alt="Company Logo" 
                         style="width: 80px; height: 80px; border-radius: 12px; margin: 0 auto 1rem; object-fit: contain; border: 2px solid #e2e8f0;">
                    <h4><?php echo htmlspecialchars($user['company_name']); ?></h4>
                    <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                        <?php echo htmlspecialchars($user['industry'] ?? 'No industry specified'); ?>
                    </p>
                </div>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_dashboard.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #007bff; font-weight: 600; text-decoration: none; background-color: #e7f3ff;">
                           📊 Dashboard
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_profile.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #333; text-decoration: none;">
                           🏢 Edit Profile
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_post_job.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #333; text-decoration: none;">
                           📝 Post a Job
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_applications.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #333; text-decoration: none;">
                           📄 Applications
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidates.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #333; text-decoration: none;">
                           🔍 Search Candidates
                        </a>
                    </li>
                    <li style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                        <a href="logout.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #dc3545; text-decoration: none;">
                           🚪 Logout
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <h2 style="margin-bottom: 1.5rem; font-size: 1.8rem; color: #333;">Employer Dashboard</h2>

            <!-- Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="card" style="border-left: 4px solid #6c757d;">
                    <h5 style="color: #666; font-size: 0.9rem; margin: 0;">Active Jobs</h5>
                    <p style="font-size: 2rem; font-weight: 700; color: #007bff; margin: 0;">
                        <?php echo $active_jobs; ?>
                    </p>
                </div>
                <div class="card" style="border-left: 4px solid #fd7e14;">
                    <h5 style="color: #666; font-size: 0.9rem; margin: 0;">Total Applications</h5>
                    <p style="font-size: 2rem; font-weight: 700; color: #007bff; margin: 0;">
                        <?php echo $total_applications; ?>
                    </p>
                </div>
                <div class="card" style="border-left: 4px solid #28a745; display: flex; align-items: center; justify-content: center;">
                    <a href="employer_post_job.php" class="btn btn-primary">Post New Job</a>
                </div>
            </div>

            <!-- Sent Invitations -->
            <?php if (count($invitations) > 0): ?>
                <div class="card mb-2">
                    <h3 style="margin: 0 0 1rem 0; font-size: 1.3rem; color: #333;">Sent Invitations</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                <th style="padding: 0.75rem;">Candidate</th>
                                <th style="padding: 0.75rem;">Job</th>
                                <th style="padding: 0.75rem;">Date</th>
                                <th style="padding: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invitations as $inv): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem; font-weight: 500;">
                                        <?php echo htmlspecialchars($inv['candidate_name']); ?>
                                    </td>
                                    <td style="padding: 0.75rem;"><?php echo htmlspecialchars($inv['job_title']); ?></td>
                                    <td style="padding: 0.75rem; color: #666;">
                                        <?php echo date('M d', strtotime($inv['created_at'])); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php
                                        $status_class = 'status-' . $inv['status'];
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($inv['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Recent Applications -->
            <div class="card mb-2">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0; font-size: 1.3rem; color: #333;">Recent Applications</h3>
                    <?php if (count($recent_apps) > 0): ?>
                    <a href="employer_applications.php" style="font-size: 0.9rem; color: #6c757d; text-decoration: none;">View All</a>
                    <?php endif; ?>
                </div>

                <?php if (count($recent_apps) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                <th style="padding: 0.75rem;">Candidate</th>
                                <th style="padding: 0.75rem;">Job</th>
                                <th style="padding: 0.75rem;">Date</th>
                                <th style="padding: 0.75rem;">Status</th>
                                <th style="padding: 0.75rem;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_apps as $app): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem; font-weight: 500;">
                                        <?php echo htmlspecialchars($app['candidate_name']); ?>
                                    </td>
                                    <td style="padding: 0.75rem;"><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td style="padding: 0.75rem; color: #666;">
                                        <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php
                                        $status = $app['status'];
                                        $status_class = 'status-' . $status;
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <a href="employer_applications.php?id=<?php echo $app['id']; ?>" 
                                           class="review-btn">
                                           Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #666;">
                        <div style="font-size: 3em; color: #dee2e6; margin-bottom: 1rem;">📝</div>
                        <h4 style="margin-bottom: 0.5rem; color: #6c757d;">No Applications Yet</h4>
                        <p style="color: #6c757d;">Applications will appear here when candidates apply to your jobs.</p>
                        <a href="employer_post_job.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">
                            Post Your First Job
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Your Jobs -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0; font-size: 1.3rem; color: #333;">Your Recent Jobs</h3>
                    <a href="employer_jobs.php" style="font-size: 0.9rem; color: #6c757d; text-decoration: none;">View All</a>
                </div>
                <?php if (count($recent_jobs) > 0): ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($recent_jobs as $job): ?>
                            <div
                                style="padding: 1.25rem; 
                                       border: 1px solid #e2e8f0; 
                                       border-radius: 8px; 
                                       background: #f8fafc;
                                       display: flex; 
                                       justify-content: space-between; 
                                       align-items: center;
                                       transition: all 0.2s;"
                                onmouseover="this.style.borderColor='#007bff'; this.style.boxShadow='0 2px 8px rgba(0,123,255,0.1)';"
                                onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                <div>
                                    <div style="font-weight: 600; color: #333; font-size: 1.1rem;"><?php echo htmlspecialchars($job['title']); ?></div>
                                    <div style="font-size: 0.85rem; color: #666; margin-top: 5px;">
                                        <span style="background: <?php echo $job['status'] == 'active' ? '#d4edda' : '#f8d7da'; ?>; 
                                              padding: 3px 10px; 
                                              border-radius: 12px; 
                                              font-size: 0.8em;
                                              color: <?php echo $job['status'] == 'active' ? '#155724' : '#721c24'; ?>;">
                                            <?php echo ucfirst($job['status']); ?>
                                        </span>
                                        • 
                                        <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="job_details.php?id=<?php echo $job['id']; ?>" 
                                       style="padding: 6px 12px; 
                                              background: #007bff; 
                                              color: white; 
                                              border-radius: 4px; 
                                              text-decoration: none; 
                                              font-size: 0.85rem;
                                              font-weight: 500;">
                                       View
                                    </a>
                                    <a href="employer_edit_job.php?id=<?php echo $job['id']; ?>" 
                                       style="padding: 6px 12px; 
                                              background: white; 
                                              color: #6c757d; 
                                              border: 1px solid #dee2e6; 
                                              border-radius: 4px; 
                                              text-decoration: none; 
                                              font-size: 0.85rem;">
                                       Edit
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 1.5rem; color: #666;">
                        <div style="font-size: 2em; color: #dee2e6; margin-bottom: 0.5rem;">📋</div>
                        <p style="margin: 0;">You haven't posted any jobs yet.</p>
                        <a href="employer_post_job.php" style="display: inline-block; margin-top: 1rem; color: #007bff; font-weight: 500;">
                            Post your first job →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
