<?php
/**
 * Sidebar Navigation
 * UniTutor - University Peer Tutoring Management System
 * 
 * Role-based sidebar navigation for dashboards
 * @param string $role - User role (admin, student, tutor)
 * @param string $active_page - Current active page
 */
require_once __DIR__ . '/functions.php';
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2><?php echo ucfirst(getCurrentUserRole()); ?> Portal</h2>
    </div>
    
    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
            <a href="admin/dashboard.php" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                <span>📊</span> Dashboard
            </a>
            <a href="admin/students.php" class="<?php echo $active_page === 'students' ? 'active' : ''; ?>">
                <span>👨‍🎓</span> Students
            </a>
            <a href="admin/tutors.php" class="<?php echo $active_page === 'tutors' ? 'active' : ''; ?>">
                <span>👨‍🏫</span> Tutors
            </a>
            <a href="admin/departments.php" class="<?php echo $active_page === 'departments' ? 'active' : ''; ?>">
                <span>🏢</span> Departments
            </a>
            <a href="admin/programs.php" class="<?php echo $active_page === 'programs' ? 'active' : ''; ?>">
                <span>📚</span> Programs
            </a>
            <a href="admin/modules.php" class="<?php echo $active_page === 'modules' ? 'active' : ''; ?>">
                <span>📖</span> Modules
            </a>
            <a href="admin/applications.php" class="<?php echo $active_page === 'applications' ? 'active' : ''; ?>">
                <span>📝</span> Applications
            </a>
            <a href="admin/bookings.php" class="<?php echo $active_page === 'bookings' ? 'active' : ''; ?>">
                <span>📅</span> Bookings
            </a>
            <a href="admin/sessions.php" class="<?php echo $active_page === 'sessions' ? 'active' : ''; ?>">
                <span>⏰</span> Sessions
            </a>
            <a href="admin/payments.php" class="<?php echo $active_page === 'payments' ? 'active' : ''; ?>">
                <span>💳</span> Payments
            </a>
            <a href="admin/feedback.php" class="<?php echo $active_page === 'feedback' ? 'active' : ''; ?>">
                <span>⭐</span> Feedback
            </a>
            <a href="admin/reports.php" class="<?php echo $active_page === 'reports' ? 'active' : ''; ?>">
                <span>📈</span> Reports
            </a>
            <a href="admin/database.php" class="<?php echo $active_page === 'database' ? 'active' : ''; ?>">
                <span>🗄️</span> Database
            </a>
            
        <?php elseif ($role === 'student'): ?>
            <a href="student/dashboard.php" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                <span>📊</span> Dashboard
            </a>
            <a href="student/find_tutor.php" class="<?php echo $active_page === 'find_tutor' ? 'active' : ''; ?>">
                <span>🔍</span> Find Tutor
            </a>
            <a href="student/bookings.php" class="<?php echo $active_page === 'bookings' ? 'active' : ''; ?>">
                <span>📅</span> My Bookings
            </a>
            <a href="student/sessions.php" class="<?php echo $active_page === 'sessions' ? 'active' : ''; ?>">
                <span>⏰</span> My Sessions
            </a>
            <a href="student/feedback.php" class="<?php echo $active_page === 'feedback' ? 'active' : ''; ?>">
                <span>⭐</span> My Feedback
            </a>
            <a href="student/profile.php" class="<?php echo $active_page === 'profile' ? 'active' : ''; ?>">
                <span>👤</span> Profile
            </a>
            
        <?php elseif ($role === 'tutor'): ?>
            <a href="tutor/dashboard.php" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                <span>📊</span> Dashboard
            </a>
            <a href="tutor/modules.php" class="<?php echo $active_page === 'modules' ? 'active' : ''; ?>">
                <span>📖</span> My Modules
            </a>
            <a href="tutor/slots.php" class="<?php echo $active_page === 'slots' ? 'active' : ''; ?>">
                <span>📅</span> Available Slots
            </a>
            <a href="tutor/bookings.php" class="<?php echo $active_page === 'bookings' ? 'active' : ''; ?>">
                <span>📋</span> Bookings
            </a>
            <a href="tutor/sessions.php" class="<?php echo $active_page === 'sessions' ? 'active' : ''; ?>">
                <span>⏰</span> Sessions
            </a>
            <a href="tutor/feedback.php" class="<?php echo $active_page === 'feedback' ? 'active' : ''; ?>">
                <span>⭐</span> Feedback
            </a>
            <a href="tutor/profile.php" class="<?php echo $active_page === 'profile' ? 'active' : ''; ?>">
                <span>👤</span> Profile
            </a>
        <?php endif; ?>
        
        <a href="logout.php" class="logout">
            <span>🚪</span> Logout
        </a>
    </nav>
</aside>
