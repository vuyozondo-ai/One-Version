<?php
/**
 * Module Demand Report
 * UniTutor - University Peer Tutoring Management System
 * 
 * Report 3: Module Demand
 * Shows module bookings, completed sessions, and students requesting tutoring
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

// Get module demand data
$module_demand = $pdo->query("
    SELECT m.module_id, m.module_code, m.module_name, p.program_name, d.department_name,
    (SELECT COUNT(*) FROM bookings WHERE module_id = m.module_id) as total_bookings,
    (SELECT COUNT(*) FROM sessions sess JOIN bookings b ON sess.booking_id = b.booking_id WHERE b.module_id = m.module_id AND sess.status = 'completed') as completed_sessions,
    (SELECT COUNT(DISTINCT student_id) FROM bookings WHERE module_id = m.module_id) as students_requesting
    FROM modules m
    JOIN programs p ON m.program_id = p.program_id
    JOIN departments d ON p.department_id = d.department_id
    ORDER BY total_bookings DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Demand Report - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <a href="../admin/reports.php" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Reports</a>
                    <h1>Module Demand Report</h1>
                    <p>Module popularity and tutoring demand</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Report Results (<?php echo count($module_demand); ?> modules)</h2>
                        <button onclick="window.print()" class="btn btn-secondary" style="float: right;">Print Report</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Module Code</th>
                                        <th>Module Name</th>
                                        <th>Department</th>
                                        <th>Program</th>
                                        <th>Total Bookings</th>
                                        <th>Completed Sessions</th>
                                        <th>Students Requesting</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($module_demand as $module): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($module['module_code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($module['module_name']); ?></td>
                                        <td><?php echo htmlspecialchars($module['department_name']); ?></td>
                                        <td><?php echo htmlspecialchars($module['program_name']); ?></td>
                                        <td><strong><?php echo $module['total_bookings']; ?></strong></td>
                                        <td><span class="badge badge-success"><?php echo $module['completed_sessions']; ?></span></td>
                                        <td><strong><?php echo $module['students_requesting']; ?></strong></td>
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
