<?php
/**
 * Navigation Bar
 * UniTutor - University Peer Tutoring Management System
 * 
 * Dynamic navigation based on user role
 */
require_once __DIR__ . '/functions.php';
?>

<nav class="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="<?php echo isLoggedIn() ? getDashboardUrl() : 'index.php'; ?>">
                <h1>UniTutor</h1>
            </a>
        </div>
        
        <ul class="nav-menu">
            <?php if (!isLoggedIn()): ?>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="index.php#how-it-works">How It Works</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn btn-primary">Register</a></li>
            <?php else: ?>
                <?php if (hasRole('admin')): ?>
                    <li><a href="admin/dashboard.php">Dashboard</a></li>
                    <li><a href="admin/reports.php">Reports</a></li>
                    <li><a href="admin/database.php">Database</a></li>
                <?php elseif (hasRole('student')): ?>
                    <li><a href="student/dashboard.php">Dashboard</a></li>
                    <li><a href="student/find_tutor.php">Find Tutor</a></li>
                    <li><a href="student/bookings.php">My Bookings</a></li>
                <?php elseif (hasRole('tutor')): ?>
                    <li><a href="tutor/dashboard.php">Dashboard</a></li>
                    <li><a href="tutor/bookings.php">Bookings</a></li>
                    <li><a href="tutor/slots.php">Available Slots</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
        
        <div class="mobile-menu-toggle">
            <button class="btn btn-icon" onclick="toggleMobileMenu()">☰</button>
        </div>
    </div>
</nav>

<?php
/**
 * Get dashboard URL based on user role
 * @return string - Dashboard URL
 */
function getDashboardUrl() {
    $role = getCurrentUserRole();
    switch ($role) {
        case 'admin':
            return 'admin/dashboard.php';
        case 'student':
            return 'student/dashboard.php';
        case 'tutor':
            return 'tutor/dashboard.php';
        default:
            return 'index.php';
    }
}
?>
