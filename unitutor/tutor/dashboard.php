<?php
/**
 * Tutor Dashboard
 * UniTutor - University Peer Tutoring Management System
 * 
 * Main dashboard for tutors
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('tutor');

$tutor_id = getCurrentUserId();

// Get tutor statistics
try {
    $stats = [
        'total_students' => $pdo->prepare("SELECT COUNT(DISTINCT student_id) as count FROM bookings WHERE tutor_id = ?"),
        'upcoming_sessions' => $pdo->prepare("SELECT COUNT(*) as count FROM sessions s JOIN bookings b ON s.booking_id = b.booking_id WHERE b.tutor_id = ? AND s.status = 'scheduled' AND s.session_date >= CURDATE()"),
        'pending_bookings' => $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE tutor_id = ? AND status = 'pending'"),
        'completed_sessions' => $pdo->prepare("SELECT COUNT(*) as count FROM sessions s JOIN bookings b ON s.booking_id = b.booking_id WHERE b.tutor_id = ? AND s.status = 'completed'"),
        'avg_rating' => $pdo->prepare("SELECT AVG(rating) as avg_rating FROM feedback WHERE tutor_id = ?")
    ];
    
    $stats['total_students']->execute([$tutor_id]);
    $stats['total_students'] = $stats['total_students']->fetch()['count'];
    
    $stats['upcoming_sessions']->execute([$tutor_id]);
    $stats['upcoming_sessions'] = $stats['upcoming_sessions']->fetch()['count'];
    
    $stats['pending_bookings']->execute([$tutor_id]);
    $stats['pending_bookings'] = $stats['pending_bookings']->fetch()['count'];
    
    $stats['completed_sessions']->execute([$tutor_id]);
    $stats['completed_sessions'] = $stats['completed_sessions']->fetch()['count'];
    
    $stats['avg_rating']->execute([$tutor_id]);
    $stats['avg_rating'] = $stats['avg_rating']->fetch()['avg_rating'] ?: 0;
    
    // Get upcoming sessions
    $upcoming_sessions = $pdo->prepare("
        SELECT s.*, b.booking_id, st.first_name as student_name, st.last_name as student_last_name,
        m.module_code, m.module_name
        FROM sessions s
        JOIN bookings b ON s.booking_id = b.booking_id
        JOIN students st ON b.student_id = st.student_id
        JOIN modules m ON b.module_id = m.module_id
        WHERE b.tutor_id = ? AND s.status = 'scheduled' AND s.session_date >= CURDATE()
        ORDER BY s.session_date ASC, s.start_time ASC
        LIMIT 5
    ");
    $upcoming_sessions->execute([$tutor_id]);
    $upcoming_sessions = $upcoming_sessions->fetchAll();
    
    // Get pending bookings
    $pending_bookings = $pdo->prepare("
        SELECT b.*, s.first_name as student_name, s.last_name as student_last_name,
        m.module_code, m.module_name
        FROM bookings b
        JOIN students s ON b.student_id = s.student_id
        JOIN modules m ON b.module_id = m.module_id
        WHERE b.tutor_id = ? AND b.status = 'pending'
        ORDER BY b.booking_date ASC
        LIMIT 5
    ");
    $pending_bookings->execute([$tutor_id]);
    $pending_bookings = $pending_bookings->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error loading dashboard data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Dashboard - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php 
        $role = 'tutor';
        $active_page = 'dashboard';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Tutor Dashboard</h1>
                    <p>Welcome back, <?php echo getCurrentUserName(); ?>!</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <?php showError($error); ?>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_students']; ?></h3>
                        <p>Total Students</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['upcoming_sessions']; ?></h3>
                        <p>Upcoming Sessions</p>
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
                        <p>Average Rating</p>
                    </div>
                </div>
                
                <!-- Pending Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h2>Pending Bookings (<?php echo count($pending_bookings); ?>)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_bookings)): ?>
                            <p>No pending bookings.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Student</th>
                                            <th>Module</th>
                                            <th>Date</th>
                                            <th>Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['student_name'] . ' ' . $booking['student_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['module_code']); ?></td>
                                            <td><?php echo formatDateTime($booking['booking_date']); ?></td>
                                            <td><?php echo truncateText(htmlspecialchars($booking['reason'] ?? 'N/A'), 30); ?></td>
                                            <td>
                                                <a href="bookings.php" class="btn btn-sm btn-primary">Review</a>
                                            </td>
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
                
                <!-- Upcoming Sessions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Upcoming Sessions</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_sessions)): ?>
                            <p>No upcoming sessions scheduled.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Student</th>
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
                                            <td><?php echo htmlspecialchars($session['student_name'] . ' ' . $session['student_last_name']); ?></td>
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
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <a href="slots.php" class="btn btn-primary">Manage Available Slots</a>
                            <a href="bookings.php" class="btn btn-secondary">Bookings</a>
                            <a href="sessions.php" class="btn btn-secondary">Sessions</a>
                            <a href="modules.php" class="btn btn-secondary">My Modules</a>
                            <a href="feedback.php" class="btn btn-secondary">Feedback</a>
                            <a href="profile.php" class="btn btn-secondary">Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
