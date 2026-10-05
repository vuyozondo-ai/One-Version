<?php
/**
 * Tutor Performance Report
 * UniTutor - University Peer Tutoring Management System
 * 
 * Report 2: Tutor Performance
 * Shows tutor sessions, completed sessions, average rating, and students helped
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

// Get tutor performance data
$tutor_performance = $pdo->query("
    SELECT t.tutor_id, t.tutor_number, t.first_name, t.last_name, t.email, t.status,
    (SELECT COUNT(*) FROM sessions sess JOIN bookings b ON sess.booking_id = b.booking_id WHERE b.tutor_id = t.tutor_id) as total_sessions,
    (SELECT COUNT(*) FROM sessions sess JOIN bookings b ON sess.booking_id = b.booking_id WHERE b.tutor_id = t.tutor_id AND sess.status = 'completed') as completed_sessions,
    (SELECT AVG(rating) FROM feedback WHERE tutor_id = t.tutor_id) as avg_rating,
    (SELECT COUNT(DISTINCT student_id) FROM bookings WHERE tutor_id = t.tutor_id) as students_helped
    FROM tutors t
    ORDER BY avg_rating DESC, completed_sessions DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Performance Report - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <a href="../admin/reports.php" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Reports</a>
                    <h1>Tutor Performance Report</h1>
                    <p>Tutor performance metrics and ratings</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Report Results (<?php echo count($tutor_performance); ?> tutors)</h2>
                        <button onclick="window.print()" class="btn btn-secondary" style="float: right;">Print Report</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tutor Number</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Total Sessions</th>
                                        <th>Completed Sessions</th>
                                        <th>Average Rating</th>
                                        <th>Students Helped</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tutor_performance as $tutor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tutor['tutor_number']); ?></td>
                                        <td><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($tutor['email']); ?></td>
                                        <td><span class="badge <?php echo $tutor['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo ucfirst($tutor['status']); ?></span></td>
                                        <td><strong><?php echo $tutor['total_sessions']; ?></strong></td>
                                        <td><span class="badge badge-success"><?php echo $tutor['completed_sessions']; ?></span></td>
                                        <td>
                                            <span class="stars"><?php echo generateStarRating(round($tutor['avg_rating'])); ?></span>
                                            <?php echo number_format($tutor['avg_rating'], 1); ?>/5
                                        </td>
                                        <td><strong><?php echo $tutor['students_helped']; ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
