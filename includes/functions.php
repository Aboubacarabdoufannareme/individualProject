<?php
// includes/functions.php

session_start();

/**
 * Redirect to a specific URL
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Sanitize input data
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * If not, check cookies for Remember Me
 */
function is_logged_in()
{
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    // Check cookie
    $token = $_COOKIE['remember_me'] ?? '';
    if ($token && check_remember_me($token)) {
        return true;
    }
    return false;
}

/**
 * Validate Remember Me Token
 */
function check_remember_me($token)
{
    global $conn;

    list($selector, $validator) = explode(':', $token);

    // Find selector in DB
    $stmt = $conn->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires > NOW() LIMIT 1");
    $stmt->execute([$selector]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        return false;
    }

    // Verify validator
    if (hash_equals($result['hashed_validator'], hash('sha256', $validator))) {
        // Token Valid! Log the user in.

        // Fetch user details
        $table = ($result['user_type'] === 'employer') ? 'employers' : 'candidates';
        $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$result['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $result['user_type'];
            $_SESSION['email'] = $user['email'];

            if ($result['user_type'] === 'candidate') {
                $_SESSION['candidate_username'] = $user['username'];
            } else {
                $_SESSION['company_name'] = $user['company_name'];
            }
            return true;
        }
    }
    return false;
}

/**
 * Ensure user is logged in, else redirect to login
 */
function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/**
 * Upload a file
 * returns path on success or false on failure
 */
function upload_file($file, $destination_folder = '../uploads/')
{
    // Allowed extensions
    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $filename = $file['name'];
    $filetmp = $file['tmp_name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return ["error" => "Invalid file type. Allowed: " . implode(", ", $allowed)];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        return ["error" => "File size too large (Max 5MB)."];
    }

    // Generate unique name
    $new_name = uniqid('doc_', true) . '.' . $ext;

    // Determine Absolute Path
    // __DIR__ is .../includes
    // dirname(__DIR__) is .../project_root
    $project_root = dirname(__DIR__);

    // Ensure destination folder is clean (no leading/trailing slashes for consistency)
    $clean_folder = trim($destination_folder, '/');

    // Full absolute path to upload directory
    $upload_dir = $project_root . '/' . $clean_folder . '/';

    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 777, true)) {
            return ["error" => "Failed to create upload directory: $clean_folder. Check permissions."];
        }
    }

    $destination = $upload_dir . $new_name;

    if (move_uploaded_file($filetmp, $destination)) {
        return ["success" => true, "path" => $new_name];
    } else {
        return ["error" => "Failed to move uploaded file. Check folder permissions."];
    }
}

/**
 * Flash message helper (Set or Get)
 */
function flash($key, $message = null)
{
    if ($message) {
        $_SESSION['flash'][$key] = $message;
    } else {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
}

/**
 * Get current user role
 */
function get_role()
{
    return $_SESSION['role'] ?? null;
}
?>
