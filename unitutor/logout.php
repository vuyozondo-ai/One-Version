<?php
/**
 * Logout Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Handles user logout and session destruction
 */
require_once 'includes/functions.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page with message
setFlashMessage('info', 'You have been logged out successfully.');
redirect('login.php');
?>
