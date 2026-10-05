<?php
/**
 * Student Dashboard
 * UniTutor - University Peer Tutoring Management System
 * 
 * Main dashboard for students
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$student_id = getCurrentUserId();

// Get student statistics
try {
    $stats = [
        'bookings' => getStudentBookingCount($pdo, $student_id),
        'completed_sessions' => getStudentCompletedSessionCount($pdo, $student_id),
        'pending_bookings' => $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE student_id = ? AND status = 'pending'"),
        'avg_rating' => $pdo->prepare("SELECT AVG(rating) as avg_rating FROM feedback WHERE student_id = ?")
    ];
    
    $stats['pending_bookings']->execute([$student_id]);
    $stats['pending_bookings'] = $stats['pending_bookings']->fetch()['count'];
    
    $stats['avg_rating']->execute([$student_id]);
    $stats['avg_rating'] = $stats['avg_rating']->fetch()['avg_rating'] ?: 0;
    
    // Get upcoming sessions
    $upcoming_sessions = $pdo->prepare("
        SELECT s.*, b.booking_id, t.first_name as tutor_name, t.last_name as tutor_last_name,
        m.module_code, m.module_name
        FROM sessions s
        JOIN bookings b ON s.booking_id = b.booking_id
        JOIN tutors t ON b.tutor_id = t.tutor_id
        JOIN modules m ON b.module_id = m.module_id
        WHERE b.student_id = ? AND s.status = 'scheduled' AND s.session_date >= CURDATE()
        ORDER BY s.session_date ASC, s.start_time ASC
        LIMIT 5
    ");
    $upcoming_sessions->execute([$student_id]);
    $upcoming_sessions = $upcoming_sessions->fetchAll();
    
    // Get recent bookings
    $recent_bookings = $pdo->prepare("
        SELECT b.*, t.first_name as tutor_name, t.last_name as tutor_last_name,
        m.module_code, m.module_name
        FROM bookings b
        JOIN tutors t ON b.tutor_id = t.tutor_id
        JOIN modules m ON b.module_id = m.module_id
        WHERE b.student_id = ?
        ORDER BY b.booking_date DESC
        LIMIT 5
    ");
    $recent_bookings->execute([$student_id]);
    $recent_bookings = $recent_bookings->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading dashboard data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php 
        $role = 'student';
        $active_page = 'dashboard';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Student Dashboard</h1>
                    <p>Welcome back, <?php echo getCurrentUserName(); ?>!</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <?php showError($error); ?>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $stats['bookings']; ?></h3>
                        <p>My Bookings</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['pending_bookings']; ?></h3>
                        <p>Pending Bookings</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['completed_sessions']; ?></h3>
                        <p>Completed Sessions</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo number_format($stats['avg_rating'], 1); ?></h3>
                        <p>Average Rating Given</p>
                    </div>
                </div>
                
                <!-- Upcoming Sessions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Upcoming Sessions</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_sessions)): ?>
                            <p>No upcoming sessions scheduled.</p>
                            <a href="find_tutor.php" class="btn btn-primary">Find a Tutor</a>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Tutor</th>
                                            <th>Module</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcoming_sessions as $session): ?>
                                        <tr>
                                            <td><?php echo formatDate($session['session_date']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($session['start_time'])) . ' - ' . date('g:i A', strtotime($session['end_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($session['tutor_name'] . ' ' . $session['tutor_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['module_code']); ?></td>
                                            <td><?php echo htmlspecialchars($session['location'] ?? 'TBD'); ?></td>
                                            <td><span class="badge <?php echo getSessionStatusBadge($session['status']); ?>"><?php echo ucfirst($session['status']); ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="sessions.php" class="btn btn-primary">View All Sessions</a>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Bookings</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_bookings)): ?>
                            <p>No bookings yet.</p>
                            <a href="find_tutor.php" class="btn btn-primary">Find a Tutor</a>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Tutor</th>
                                            <th>Module</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['tutor_name'] . ' ' . $booking['tutor_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['module_code']); ?></td>
                                            <td><?php echo formatDateTime($booking['booking_date']); ?></td>
                                            <td><span class="badge <?php echo getBookingStatusBadge($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="bookings.php" class="btn btn-primary">View All Bookings</a>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <a href="find_tutor.php" class="btn btn-primary">Find a Tutor</a>
                            <a href="bookings.php" class="btn btn-secondary">My Bookings</a>
                            <a href="sessions.php" class="btn btn-secondary">My Sessions</a>
                            <a href="feedback.php" class="btn btn-secondary">My Feedback</a>
                            <a href="profile.php" class="btn btn-secondary">My Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
