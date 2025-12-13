<?php
// forgot_password.php
require_once 'includes/header.php';

// Ensure table exists (Self-healing for dev environment)
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        selector VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires BIGINT NOT NULL
    )");
} catch (PDOException $e) {
    // Silent fail or log
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role']);

    if (empty($email) || empty($role)) {
        $error = "Please enter both email and role.";
    } else {
        // Check if user exists
        $table = ($role === 'employer') ? 'employers' : 'candidates';
        $stmt = $conn->prepare("SELECT id, username FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate tokens
            $selector = bin2hex(random_bytes(8));
            $token = random_bytes(32);
            $hashedToken = password_hash($token, PASSWORD_DEFAULT); // We hash the token for storage
            $expires = time() + 1800; // 30 mins

            // Delete old resets for this email
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Insert new reset
            $stmt = $conn->prepare("INSERT INTO password_resets (email, selector, token, expires) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $selector, $hashedToken, $expires]);

            // Create Link (url is hex of token because we can't pass raw binary)
            $url = "reset_password.php?selector=" . $selector . "&validator=" . bin2hex($token) . "&role=" . $role;

            // In a real app, send this via email.
            // For localhost, flash it.
            $link = "<a href='$url'>Click here to reset your password</a>";
            $success = "For testing purposes (Emails don't work on localhost): <br> $link";
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>

<div class="container mt-2 mb-2" style="max-width: 500px;">
    <div class="card">
        <h2 class="text-center">Forgot Password</h2>
        <p class="text-center text-muted">Enter your email to reset your password.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label class="form-label">I am a...</label>
                <select name="role" class="form-control" required>
                    <option value="candidate">Candidate</option>
                    <option value="employer">Employer</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>

        <p class="text-center mt-1">
            <a href="login.php" style="color: var(--secondary);">Back to Login</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>