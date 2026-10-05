<?php
/**
 * Student Activity Report
 * UniTutor - University Peer Tutoring Management System
 * 
 * Report 1: Student Tutoring Activity
 * Shows student bookings, completed sessions, and cancelled sessions
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

// Get student activity data
$student_activity = $pdo->query("
    SELECT s.student_id, s.student_number, s.first_name, s.last_name, s.email, p.program_name,
    (SELECT COUNT(*) FROM bookings WHERE student_id = s.student_id) as total_bookings,
    (SELECT COUNT(*) FROM sessions sess JOIN bookings b ON sess.booking_id = b.booking_id WHERE b.student_id = s.student_id AND sess.status = 'completed') as completed_sessions,
    (SELECT COUNT(*) FROM bookings WHERE student_id = s.student_id AND status = 'cancelled') as cancelled_sessions
    FROM students s
    LEFT JOIN programs p ON s.program_id = p.program_id
    ORDER BY total_bookings DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Activity Report - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <a href="../admin/reports.php" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Reports</a>
                    <h1>Student Activity Report</h1>
                    <p>Student tutoring activity and statistics</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Report Results (<?php echo count($student_activity); ?> students)</h2>
                        <button onclick="window.print()" class="btn btn-secondary" style="float: right;">Print Report</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Number</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Program</th>
                                        <th>Total Bookings</th>
                                        <th>Completed Sessions</th>
                                        <th>Cancelled Sessions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($student_activity as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['program_name'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo $student['total_bookings']; ?></strong></td>
                                        <td><span class="badge badge-success"><?php echo $student['completed_sessions']; ?></span></td>
                                        <td><span class="badge badge-danger"><?php echo $student['cancelled_sessions']; ?></span></td>
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
