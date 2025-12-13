<?php
// logout.php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Clear Remember Me Cookie and Token
if (isset($_COOKIE['remember_me'])) {
    list($selector, $validator) = explode(':', $_COOKIE['remember_me']);

    // Delete from DB
    $stmt = $conn->prepare("DELETE FROM user_tokens WHERE selector = ?");
    $stmt->execute([$selector]);

    // Clear cookie
    setcookie('remember_me', '', time() - 3600, '/', '', false, true);
    unset($_COOKIE['remember_me']);
}

session_unset();
session_destroy();
session_start(); // Start new session to set flash message

flash('success', "You have been logged out successfully.");
redirect('login.php');
?>