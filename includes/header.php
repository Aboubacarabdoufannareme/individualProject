<?php
// includes/header.php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiCareer Niger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="nav-logo">Digi<span class="nav-brand-highlight">Career</span></a>

            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="jobs.php" class="nav-link">Find Jobs</a></li>
                <li><a href="candidates.php" class="nav-link">Find Candidates</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="help.php" class="nav-link">Help</a></li>

                <?php if (is_logged_in()): ?>
                    <li>
                        <?php if (get_role() == 'employer'): ?>
                            <a href="employer_dashboard.php" class="btn btn-primary">Dashboard</a>
                        <?php else: ?>
                            <a href="candidate_dashboard.php" class="btn btn-primary">Dashboard</a>
                        <?php endif; ?>
                    </li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="btn btn-outline">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="container mt-2 mb-2">
        <!-- Flash Messages -->
        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-error"><?php echo $msg; ?></div>
        <?php endif; ?>