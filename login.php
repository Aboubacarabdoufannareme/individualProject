<?php
// login.php
require_once 'includes/header.php';

if (is_logged_in()) {
    if (get_role() === 'employer') {
        redirect('employer_dashboard.php');
    } else {
        redirect('candidate_dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Check Candidates table
        $stmt = $conn->prepare("SELECT * FROM candidates WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        $role = 'candidate';

        if (!$user) {
            // Check Employers table
            $stmt = $conn->prepare("SELECT * FROM employers WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            $role = 'employer';
        }

        if ($user && password_verify($password, $user['password'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $user['email'];

            if ($role === 'candidate') {
                $_SESSION['candidate_username'] = $user['username']; // Legacy compatibility
            } else {
                $_SESSION['company_name'] = $user['company_name'];
            }

            if (isset($_POST['remember'])) {
                $selector = bin2hex(random_bytes(12));
                $validator = bin2hex(random_bytes(32));
                $hashed_validator = hash('sha256', $validator);
                $expires = date('Y-m-d H:i:s', time() + 86400 * 30); // 30 days

                $stmt = $conn->prepare("INSERT INTO user_tokens (selector, hashed_validator, user_id, user_type, expires) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$selector, $hashed_validator, $user['id'], $role, $expires]);

                setcookie('remember_me', "$selector:$validator", time() + 86400 * 30, '/', '', false, true);
            }

            flash('success', "Welcome back, " . $user['username'] . "!");

            if ($role === 'employer') {
                redirect('employer_dashboard.php');
            } else {
                redirect('candidate_dashboard.php');
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<div class="container mt-2 mb-2" style="max-width: 450px;">
    <div class="card">
        <h2 class="text-center">Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label">Username or Email</label>
                <input type="text" name="username" class="form-control" required
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required style="margin-bottom: 0.5rem;">
                <div style="text-align: right; font-size: 0.9rem;">
                    <a href="forgot_password.php" style="color: var(--secondary);">Forgot Password?</a>
                </div>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="font-size: 0.95rem; user-select: none;">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>

            <p class="text-center mt-1">
                Don't have an account? <a href="register.php" style="color: var(--secondary);">Register here</a>
            </p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>