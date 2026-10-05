<?php
/**
 * Authentication Check File
 * UniTutor - University Peer Tutoring Management System
 * 
 * Include this file at the top of pages that require authentication.
 * It checks if user is logged in and redirects if not.
 */

// Include functions file
require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to access this page.');
    redirect('../login.php');
}

/**
 * Check if user has required role
 * @param string $required_role - Required role (admin, student, tutor)
 */
function requireRole($required_role) {
    if (!hasRole($required_role)) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect('../index.php');
    }
}

/**
 * Check if user has any of the specified roles
 * @param array $roles - Array of allowed roles
 */
function requireAnyRole($roles) {
    if (!isLoggedIn() || !in_array(getCurrentUserRole(), $roles)) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect('../index.php');
    }
}
?>
