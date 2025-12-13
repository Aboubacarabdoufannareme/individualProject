<?php
// reset_password.php
require_once 'includes/header.php';

$error = '';
$success = '';
$selector = $_GET['selector'] ?? '';
$validator = $_GET['validator'] ?? '';
$role = $_GET['role'] ?? '';

if (empty($selector) || empty($validator) || empty($role)) {
    $error = "Invalid request. Please use the link from your email.";
} else {
    // Check if token matches
    $current_time = time();
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE selector = ? AND expires >= ?");
    $stmt->execute([$selector, $current_time]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $error = "Invalid or expired reset link.";
    } else {
        // Verify token
        $tokenBin = hex2bin($validator);
        if (password_verify($tokenBin, $reset['token'])) {
            // Token valid. Show reset form.

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                if ($password !== $confirm_password) {
                    $error = "Passwords do not match.";
                } elseif (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters.";
                } else {
                    // Update Password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $table = ($role === 'employer') ? 'employers' : 'candidates';

                    // Update user
                    $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
                    if ($stmt->execute([$hashed_password, $reset['email']])) {
                        // Delete reset token
                        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                        $stmt->execute([$reset['email']]);

                        flash('success', "Password reset successfully! You can now login.");
                        redirect('login.php');
                    } else {
                        $error = "Failed to update password. Please try again.";
                    }
                }
            }

        } else {
            $error = "Invalid token signature.";
        }
    }
}
?>

<div class="container mt-2 mb-2" style="max-width: 450px;">
    <div class="card">
        <h2 class="text-center">Reset Password</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($error) || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>