<?php
/**
 * Reusable Functions File
 * UniTutor - University Peer Tutoring Management System
 * 
 * Contains common functions used throughout the application
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize input data to prevent XSS attacks
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 * @param string $email - Email to validate
 * @return bool - True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Display success message
 * @param string $message - Message to display
 */
function showSuccess($message) {
    echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display error message
 * @param string $message - Message to display
 */
function showError($message) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display warning message
 * @param string $message - Message to display
 */
function showWarning($message) {
    echo '<div class="alert alert-warning">' . htmlspecialchars($message) . '</div>';
}

/**
 * Display info message
 * @param string $message - Message to display
 */
function showInfo($message) {
    echo '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>';
}

/**
 * Redirect to a specific page
 * @param string $url - URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 * @return bool - True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user has specific role
 * @param string $role - Role to check (admin, student, tutor)
 * @return bool - True if user has role, false otherwise
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Get current user ID
 * @return int|null - User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null - User role or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user name
 * @return string|null - User name or null if not logged in
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? null;
}

/**
 * Format date for display
 * @param string $date - Date to format
 * @param string $format - Date format (default: Y-m-d)
 * @return string - Formatted date
 */
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime - Datetime to format
 * @return string - Formatted datetime
 */
function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

/**
 * Generate star rating HTML
 * @param int $rating - Rating (1-5)
 * @return string - HTML for star rating
 */
function generateStarRating($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '★';
        } else {
            $stars .= '☆';
        }
    }
    return $stars;
}

/**
 * Get booking status badge color
 * @param string $status - Booking status
 * @return string - CSS class for badge
 */
function getBookingStatusBadge($status) {
    $badges = [
        'pending' => 'badge-warning',
        'confirmed' => 'badge-success',
        'completed' => 'badge-info',
        'cancelled' => 'badge-danger'
    ];
    return $badges[$status] ?? 'badge-secondary';
}

/**
 * Get session status badge color
 * @param string $status - Session status
 * @return string - CSS class for badge
 */
function getSessionStatusBadge($status) {
    $badges = [
        'scheduled' => 'badge-primary',
        'completed' => 'badge-success',
        'cancelled' => 'badge-danger'
    ];
    return $badges[$status] ?? 'badge-secondary';
}

/**
 * Get payment status badge color
 * @param string $status - Payment status
 * @return string - CSS class for badge
 */
function getPaymentStatusBadge($status) {
    $badges = [
        'pending' => 'badge-warning',
        'completed' => 'badge-success',
        'refunded' => 'badge-danger'
    ];
    return $badges[$status] ?? 'badge-secondary';
}

/**
 * Get application status badge color
 * @param string $status - Application status
 * @return string - CSS class for badge
 */
function getApplicationStatusBadge($status) {
    $badges = [
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger'
    ];
    return $badges[$status] ?? 'badge-secondary';
}

/**
 * Truncate text to specified length
 * @param string $text - Text to truncate
 * @param int $length - Maximum length
 * @return string - Truncated text
 */
function truncateText($text, $length = 50) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Check if slot is available
 * @param string $status - Slot status
 * @return bool - True if available, false otherwise
 */
function isSlotAvailable($status) {
    return $status === 'available';
}

/**
 * Calculate average rating for a tutor
 * @param PDO $pdo - Database connection
 * @param int $tutor_id - Tutor ID
 * @return float - Average rating
 */
function getTutorAverageRating($pdo, $tutor_id) {
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM feedback WHERE tutor_id = ?");
    $stmt->execute([$tutor_id]);
    $result = $stmt->fetch();
    return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
}

/**
 * Count sessions for a tutor
 * @param PDO $pdo - Database connection
 * @param int $tutor_id - Tutor ID
 * @return int - Number of sessions
 */
function getTutorSessionCount($pdo, $tutor_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sessions s 
                          JOIN bookings b ON s.booking_id = b.booking_id 
                          WHERE b.tutor_id = ? AND s.status = 'completed'");
    $stmt->execute([$tutor_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Count bookings for a student
 * @param PDO $pdo - Database connection
 * @param int $student_id - Student ID
 * @return int - Number of bookings
 */
function getStudentBookingCount($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Count completed sessions for a student
 * @param PDO $pdo - Database connection
 * @param int $student_id - Student ID
 * @return int - Number of completed sessions
 */
function getStudentCompletedSessionCount($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sessions s 
                          JOIN bookings b ON s.booking_id = b.booking_id 
                          WHERE b.student_id = ? AND s.status = 'completed'");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Get department name by ID
 * @param PDO $pdo - Database connection
 * @param int $department_id - Department ID
 * @return string - Department name
 */
function getDepartmentName($pdo, $department_id) {
    $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE department_id = ?");
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    return $result['department_name'] ?? 'Unknown';
}

/**
 * Get program name by ID
 * @param PDO $pdo - Database connection
 * @param int $program_id - Program ID
 * @return string - Program name
 */
function getProgramName($pdo, $program_id) {
    $stmt = $pdo->prepare("SELECT program_name FROM programs WHERE program_id = ?");
    $stmt->execute([$program_id]);
    $result = $stmt->fetch();
    return $result['program_name'] ?? 'Unknown';
}

/**
 * Get module name by ID
 * @param PDO $pdo - Database connection
 * @param int $module_id - Module ID
 * @return string - Module name
 */
function getModuleName($pdo, $module_id) {
    $stmt = $pdo->prepare("SELECT module_name FROM modules WHERE module_id = ?");
    $stmt->execute([$module_id]);
    $result = $stmt->fetch();
    return $result['module_name'] ?? 'Unknown';
}

/**
 * Get student name by ID
 * @param PDO $pdo - Database connection
 * @param int $student_id - Student ID
 * @return string - Student full name
 */
function getStudentName($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch();
    return $result['name'] ?? 'Unknown';
}

/**
 * Get tutor name by ID
 * @param PDO $pdo - Database connection
 * @param int $tutor_id - Tutor ID
 * @return string - Tutor full name
 */
function getTutorName($pdo, $tutor_id) {
    $stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM tutors WHERE tutor_id = ?");
    $stmt->execute([$tutor_id]);
    $result = $stmt->fetch();
    return $result['name'] ?? 'Unknown';
}

/**
 * Get admin name by ID
 * @param PDO $pdo - Database connection
 * @param int $admin_id - Admin ID
 * @return string - Admin full name
 */
function getAdminName($pdo, $admin_id) {
    $stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM admins WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $result = $stmt->fetch();
    return $result['name'] ?? 'Unknown';
}

/**
 * Set flash message for display after redirect
 * @param string $type - Message type (success, error, warning, info)
 * @param string $message - Message content
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display and clear flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        switch ($flash['type']) {
            case 'success':
                showSuccess($flash['message']);
                break;
            case 'error':
                showError($flash['message']);
                break;
            case 'warning':
                showWarning($flash['message']);
                break;
            case 'info':
                showInfo($flash['message']);
                break;
        }
    }
}
?>
