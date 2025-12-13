<?php
// register.php
require_once 'includes/header.php';

$role = $_GET['role'] ?? 'candidate'; // default logic
if (!in_array($role, ['candidate', 'employer'])) {
    $role = 'candidate';
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'candidate';
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!isset($_POST['agree'])) {
        $error = "You must agree to the Terms & Conditions.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            if ($role === 'employer') {
                $company_name = sanitize($_POST['company_name']);
                if (empty($company_name)) {
                    throw new Exception("Company name is required.");
                }

                $stmt = $conn->prepare("INSERT INTO employers (username, email, password, company_name) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $company_name]);
            } else {
                $full_name = sanitize($_POST['full_name']);
                if (empty($full_name)) {
                    throw new Exception("Full name is required.");
                }

                $stmt = $conn->prepare("INSERT INTO candidates (username, email, password, full_name) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $full_name]);
            }

            flash('success', "Registration successful! You can now login.");
            redirect('login.php');

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $error = "Username or Email already exists.";
            } else {
                $error = "Registration failed: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<div class="container mt-2 mb-2" style="max-width: 500px;">
    <div class="card">
        <h2 class="text-center">Create Account</h2>
        <div class="text-center mb-2">
            <div
                style="display: inline-flex; border: 1px solid #cbd5e1; border-radius: var(--radius-md); overflow: hidden;">
                <a href="?role=candidate"
                    class="btn <?php echo $role === 'candidate' ? 'btn-primary' : 'btn-outline'; ?>"
                    style="border: none; border-radius: 0;">Candidate</a>
                <a href="?role=employer" class="btn <?php echo $role === 'employer' ? 'btn-primary' : 'btn-outline'; ?>"
                    style="border: none; border-radius: 0;">Employer</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="hidden" name="role" value="<?php echo $role; ?>">

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <?php if ($role === 'candidate'): ?>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required
                        value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" required
                        value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <div class="form-group" style="display: flex; align-items: start; gap: 0.5rem;">
                <input type="checkbox" name="agree" id="agree" required style="margin-top: 0.3rem;">
                <label for="agree" style="font-size: 0.9rem; user-select: none;">
                    I agree to the <a href="terms.php" target="_blank" style="color: var(--secondary);">Terms &
                        Conditions</a> and <a href="privacy.php" target="_blank"
                        style="color: var(--secondary);">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register as <?php echo ucfirst($role); ?></button>

            <p class="text-center mt-1">
                Already have an account? <a href="login.php" style="color: var(--secondary);">Login here</a>
            </p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>