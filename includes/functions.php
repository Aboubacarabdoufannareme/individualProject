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
/**
 * Upload a file - FIXED VERSION for shared hosting
 * returns path on success or false on failure
 */
/**
 * Upload a file - ULTIMATE FIX for strict shared hosting
 * Stores file in PHP temp directory (always writable)
 * Saves file content as BLOB in database
 */
function upload_file($file, $conn)  // Added $conn parameter
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

    // METHOD 1: Store file content directly in database (BLOB)
    $file_content = file_get_contents($filetmp);
    
    if ($file_content === false) {
        return ["error" => "Cannot read uploaded file."];
    }

    // Return success with file content
    return [
        "success" => true, 
        "path" => $new_name,
        "original_name" => $filename,
        "content" => $file_content,  // File content for database
        "size" => $file['size'],
        "type" => $file['type']
    ];
}

/**
 * Save file to database (new function)
 */
function save_file_to_db($conn, $user_id, $type, $file_data)
{
    try {
        $stmt = $conn->prepare("INSERT INTO documents (candidate_id, type, file_path, original_name, file_content, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id, 
            $type, 
            $file_data['path'], 
            $file_data['original_name'],
            $file_data['content'],
            $file_data['size'],
            $file_data['type']
        ]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Database save error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get file from database
 */
function get_file_from_db($conn, $file_id, $user_id = null)
{
    $sql = "SELECT * FROM documents WHERE id = ?";
    $params = [$file_id];
    
    if ($user_id) {
        $sql .= " AND candidate_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Delete file from database
 */
function delete_file_from_db($conn, $file_id, $user_id = null)
{
    $sql = "DELETE FROM documents WHERE id = ?";
    $params = [$file_id];
    
    if ($user_id) {
        $sql .= " AND candidate_id = ?";
        $params[] = $user_id;
    }
    
    $stmt = $conn->prepare($sql);
    return $stmt->execute($params);
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

/**
 * Get profile picture URL from database
 */

/**
 * Get profile picture URL from database
 */
function get_profile_picture_url($user_id, $conn) {
    try {
        // Try to get profile picture from documents table
        $stmt = $conn->prepare("SELECT file_content, mime_type FROM documents 
                                WHERE user_id = ? AND user_type = 'candidate' AND type = 'profile_pic' 
                                ORDER BY uploaded_at DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        if ($result && !empty($result['file_content'])) {
            // Return as data URL
            return 'data:' . $result['mime_type'] . ';base64,' . base64_encode($result['file_content']);
        }
    } catch (PDOException $e) {
        error_log("Profile picture fetch error: " . $e->getMessage());
    }
    
    // Default avatar if no custom picture found
    try {
        $stmt = $conn->prepare("SELECT full_name FROM candidates WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $name = urlencode($user['full_name'] ?? 'User');
        return "https://ui-avatars.com/api/?name=$name&background=0ea5e9&color=fff&size=128";
    } catch (Exception $e) {
        // Fallback to generic avatar
        return "https://ui-avatars.com/api/?name=User&background=0ea5e9&color=fff&size=128";
    }
}
?>
