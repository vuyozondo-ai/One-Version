<?php
/**
 * Admin Dashboard
 * UniTutor - University Peer Tutoring Management System
 * 
 * Main dashboard for administrators with system statistics
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

// Get dashboard statistics
try {
    $stats = [
        'students' => $pdo->query("SELECT COUNT(*) as count FROM students")->fetch()['count'],
        'tutors' => $pdo->query("SELECT COUNT(*) as count FROM tutors")->fetch()['count'],
        'departments' => $pdo->query("SELECT COUNT(*) as count FROM departments")->fetch()['count'],
        'programs' => $pdo->query("SELECT COUNT(*) as count FROM programs")->fetch()['count'],
        'modules' => $pdo->query("SELECT COUNT(*) as count FROM modules")->fetch()['count'],
        'bookings' => $pdo->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'],
        'sessions' => $pdo->query("SELECT COUNT(*) as count FROM sessions")->fetch()['count'],
        'payments' => $pdo->query("SELECT COUNT(*) as count FROM payments")->fetch()['count'],
        'avg_rating' => $pdo->query("SELECT AVG(rating) as avg_rating FROM feedback")->fetch()['avg_rating'] ?? 0
    ];
    
    // Get recent bookings
    $recent_bookings = $pdo->query("
        SELECT b.*, s.first_name as student_name, t.first_name as tutor_name, m.module_name
        FROM bookings b
        JOIN students s ON b.student_id = s.student_id
        JOIN tutors t ON b.tutor_id = t.tutor_id
        JOIN modules m ON b.module_id = m.module_id
        ORDER BY b.booking_date DESC
        LIMIT 5
    ")->fetchAll();
    
    // Get pending applications
    $pending_applications = $pdo->query("
        SELECT a.*, s.first_name, s.last_name
        FROM applications a
        LEFT JOIN students s ON a.student_id = s.student_id
        WHERE a.status = 'pending'
        ORDER BY a.application_date DESC
        LIMIT 5
    ")->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading dashboard data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php 
        $role = 'admin';
        $active_page = 'dashboard';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back, <?php echo getCurrentUserName(); ?>!</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <?php showError($error); ?>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $stats['students']; ?></h3>
                        <p>Total Students</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['tutors']; ?></h3>
                        <p>Total Tutors</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['departments']; ?></h3>
                        <p>Departments</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['programs']; ?></h3>
                        <p>Programs</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['modules']; ?></h3>
                        <p>Modules</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['bookings']; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['sessions']; ?></h3>
                        <p>Total Sessions</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['payments']; ?></h3>
                        <p>Total Payments</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo number_format($stats['avg_rating'], 1); ?></h3>
                        <p>Average Rating</p>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Bookings</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Tutor</th>
                                        <th>Module</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['tutor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['module_name']); ?></td>
                                        <td><?php echo formatDateTime($booking['booking_date']); ?></td>
                                        <td><span class="badge <?php echo getBookingStatusBadge($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="bookings.php" class="btn btn-primary">View All Bookings</a>
                    </div>
                </div>
                
                <!-- Pending Applications -->
                <div class="card">
                    <div class="card-header">
                        <h2>Pending Applications</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_applications)): ?>
                            <p>No pending applications.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Applicant</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_applications as $app): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $app['application_type'])); ?></td>
                                            <td><?php echo formatDate($app['application_date']); ?></td>
                                            <td><span class="badge <?php echo getApplicationStatusBadge($app['status']); ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                            <td>
                                                <a href="applications.php" class="btn btn-sm btn-primary">Review</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="applications.php" class="btn btn-primary">View All Applications</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
