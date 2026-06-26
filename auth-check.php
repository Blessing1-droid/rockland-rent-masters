<?php
/**
 * auth-check.php - Authentication Check for Protected Pages
 * Include this file at the top of any page that requires login
 */

// Include database and auth functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Store the requested URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    header('Location: ' . BASE_URL . '/pages/auth/login.php');
    exit;
}

// Optional: Check if user has specific role
if (isset($required_role)) {
    if ($_SESSION['user_role'] !== $required_role) {
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}

// Refresh user data in session if needed (optional)
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_data_fetched'])) {
    $user = getCurrentUser();
    if ($user) {
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_phone'] = $user['phone'] ?? '';
        $_SESSION['user_data_fetched'] = true;
    }
}
?>