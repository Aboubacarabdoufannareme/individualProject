<?php
// employer_applications.php
// REMOVE session_start() since it's already in header.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/header.php';
require_once 'includes/functions.php';
require_login();

if (get_role() !== 'employer') {
    redirect('candidate_dashboard.php');
}

$employer_id = $_SESSION['user_id'];
$application_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $app_id = (int) $_POST['app_id'];

    try {
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $app_id]);
        flash('success', "Application status updated.");
        redirect("employer_applications.php?id=$app_id");
    } catch (PDOException $e) {
        flash('error', "Error updating status: " . $e->getMessage());
    }
}

if ($application_id) {
    // === VIEW SINGLE APPLICATION ===
    $stmt = $conn->prepare("
        SELECT a.*, 
               j.title as job_title, 
               c.full_name, c.email, c.phone, c.id as candidate_id, c.title as candidate_title
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        JOIN candidates c ON a.candidate_id = c.id 
        WHERE a.id = ? AND j.employer_id = ?
    ");
    $stmt->execute([$application_id, $employer_id]);
    $application = $stmt->fetch();

    if (!$application) {
        flash('error', "Application not found or you don't have permission to view it.");
        redirect('employer_applications.php');
    }

    // Get Candidate Documents - FIXED QUERY
    // Check which column exists in your documents table
    try {
        // Try with candidate_id first (old structure)
        $stmt = $conn->prepare("SELECT * FROM documents WHERE candidate_id = ?");
        $stmt->execute([$application['candidate_id']]);
        $documents = $stmt->fetchAll();
        
        // If no results, try with user_id (new structure)
        if (empty($documents)) {
            $stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = ? AND user_type = 'candidate'");
            $stmt->execute([$application['candidate_id']]);
            $documents = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        // If both queries fail, documents will be empty
        $documents = [];
        error_log("Error fetching documents: " . $e->getMessage());
    }

} else {
    // === VIEW LIST ===
    $stmt = $conn->prepare("
        SELECT a.*, j.title as job_title, c.full_name 
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        JOIN candidates c ON a.candidate_id = c.id 
        WHERE j.employer_id = ? 
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$employer_id]);
    $applications = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - DigiCareer</title>
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
            margin-bottom: 1.5rem;
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
        
        .form-control {
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
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
        
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #6c757d;
            text-decoration: none;
        }
        
        .back-link:hover {
            color: #007bff;
            text-decoration: underline;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-muted {
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .row {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
<div class="container mt-2 mb-2">
    <div class="row">
        <aside>
            <div class="card">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_dashboard.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #333; text-decoration: none;">
                           📊 Dashboard
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
                           style="display: block; padding: 10px; border-radius: 5px; color: #007bff; font-weight: 600; text-decoration: none; background-color: #e7f3ff;">
                           📄 Applications
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="candidates.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #333; text-decoration: none;">
                           🔍 Search Candidates
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="employer_profile.php" 
                           style="display: block; padding: 10px; border-radius: 5px; color: #333; text-decoration: none;">
                           🏢 Edit Profile
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <main>
            <?php
            // Display flash messages
            if ($success = flash('success')): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error = flash('error')): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($application_id): ?>
                <!-- DETAIL VIEW -->
                <a href="employer_applications.php" class="back-link">&larr; Back to Applications</a>

                <div class="card mb-2">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h2 style="margin: 0;"><?php echo htmlspecialchars($application['full_name']); ?></h2>
                            <p style="color: #6c757d; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($application['candidate_title']); ?>
                            </p>
                            <p style="margin: 0;">
                                <strong>Applied for:</strong> <?php echo htmlspecialchars($application['job_title']); ?>
                            </p>
                            <p style="margin: 0.5rem 0 0 0;">
                                <strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?> | 
                                <strong>Phone:</strong> <?php echo htmlspecialchars($application['phone']); ?>
                            </p>
                        </div>
                        <div>
                            <?php
                            $status = $application['status'];
                            $status_class = 'status-' . $status;
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card mb-2">
                    <h3 style="margin-top: 0;">Cover Letter</h3>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; white-space: pre-wrap;">
                        <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                    </div>
                </div>

                <?php if (count($documents) > 0): ?>
                    <div class="card mb-2">
                        <h3 style="margin-top: 0;">Candidate Documents</h3>
                        <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                            <?php foreach ($documents as $doc): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($doc['original_name']); ?></strong>
                                        <div style="font-size: 0.85rem; color: #6c757d; margin-top: 0.25rem;">
                                            <?php echo strtoupper($doc['type']); ?> • 
                                            <?php echo round($doc['file_size'] / 1024, 1); ?> KB
                                        </div>
                                    </div>
                                    <?php if (!empty($doc['file_content'])): ?>
                                        <a href="download.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.75rem;">
                                            Download
                                        </a>
                                    <?php elseif (file_exists('uploads/' . $doc['file_path'])): ?>
                                        <a href="uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-outline" style="padding: 0.25rem 0.75rem;">
                                            Download
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #6c757d; font-size: 0.85rem;">File not found</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card mb-2">
                        <h3 style="margin-top: 0;">Candidate Documents</h3>
                        <p style="color: #6c757d; text-align: center; padding: 2rem;">
                            No documents uploaded by this candidate.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="card mb-2">
                    <div style="text-align: center;">
                        <a href="candidate_details.php?id=<?php echo $application['candidate_id']; ?>" 
                           class="btn btn-outline" style="margin-right: 1rem;">
                            View Full Profile
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>" 
                           class="btn btn-primary">
                            Contact Candidate
                        </a>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-top: 0;">Update Application Status</h3>
                    <form method="POST" style="display: flex; gap: 1rem; align-items: center;">
                        <input type="hidden" name="app_id" value="<?php echo $application['id']; ?>">
                        <input type="hidden" name="update_status" value="1">
                        <select name="status" class="form-control" style="width: 200px;">
                            <option value="pending" <?php echo $application['status'] == 'pending' ? 'selected' : ''; ?>>
                                Pending
                            </option>
                            <option value="reviewed" <?php echo $application['status'] == 'reviewed' ? 'selected' : ''; ?>>
                                Reviewed
                            </option>
                            <option value="accepted" <?php echo $application['status'] == 'accepted' ? 'selected' : ''; ?>>
                                Accepted
                            </option>
                            <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>
                                Rejected
                            </option>
                        </select>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>

            <?php else: ?>
                <!-- LIST VIEW -->
                <div class="card">
                    <h2 style="margin-top: 0;">Received Applications</h2>
                    
                    <?php if (count($applications) > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                    <th style="padding: 1rem;">Candidate</th>
                                    <th style="padding: 1rem;">Job</th>
                                    <th style="padding: 1rem;">Date</th>
                                    <th style="padding: 1rem;">Status</th>
                                    <th style="padding: 1rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 1rem; font-weight: 500;">
                                            <?php echo htmlspecialchars($app['full_name']); ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <?php echo htmlspecialchars($app['job_title']); ?>
                                        </td>
                                        <td style="padding: 1rem; color: #6c757d;">
                                            <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <?php
                                            $status = $app['status'];
                                            $status_class = 'status-' . $status;
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <a href="employer_applications.php?id=<?php echo $app['id']; ?>" 
                                               class="btn btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                               View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 3rem 1rem; color: #6c757d;">
                            <div style="font-size: 3em; color: #dee2e6; margin-bottom: 1rem;">📄</div>
                            <h3 style="margin-bottom: 0.5rem; color: #6c757d;">No Applications Yet</h3>
                            <p style="margin-bottom: 1.5rem;">Applications will appear here when candidates apply to your jobs.</p>
                            <a href="employer_post_job.php" class="btn btn-primary">
                                Post a Job to Get Applications
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
