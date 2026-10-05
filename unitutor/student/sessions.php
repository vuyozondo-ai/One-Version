<?php
/**
 * Student Sessions Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * View student's tutoring sessions
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$student_id = getCurrentUserId();
$status_filter = $_GET['status'] ?? '';

// Get sessions
$query = "
    SELECT s.*, b.booking_id, t.first_name as tutor_name, t.last_name as tutor_last_name,
    m.module_code, m.module_name
    FROM sessions s
    JOIN bookings b ON s.booking_id = b.booking_id
    JOIN tutors t ON b.tutor_id = t.tutor_id
    JOIN modules m ON b.module_id = m.module_id
    WHERE b.student_id = ?
";

$params = [$student_id];

if ($status_filter) {
    $query .= " AND s.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY s.session_date DESC, s.start_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sessions - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>My Sessions</h1>
                    <p>View your tutoring session history</p>
                </div>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <form method="GET" action="" style="display: flex; gap: 1rem; width: 100%;">
                        <div class="filter-group">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="scheduled" <?php echo $status_filter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <?php if ($status_filter): ?>
                            <a href="sessions.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Sessions Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>My Sessions (<?php echo count($sessions); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sessions)): ?>
                            <p>No sessions found.</p>
                            <a href="find_tutor.php" class="btn btn-primary">Find a Tutor</a>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Session ID</th>
                                            <th>Booking ID</th>
                                            <th>Tutor</th>
                                            <th>Module</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td>#<?php echo $session['session_id']; ?></td>
                                            <td>#<?php echo $session['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($session['tutor_name'] . ' ' . $session['tutor_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['module_code']); ?></td>
                                            <td><?php echo formatDate($session['session_date']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($session['start_time'])) . ' - ' . date('g:i A', strtotime($session['end_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($session['location'] ?? 'TBD'); ?></td>
                                            <td><span class="badge <?php echo getSessionStatusBadge($session['status']); ?>"><?php echo ucfirst($session['status']); ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
