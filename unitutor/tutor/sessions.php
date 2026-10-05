<?php
/**
 * Tutor Sessions Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * View tutor's sessions
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('tutor');

$tutor_id = getCurrentUserId();
$status_filter = $_GET['status'] ?? '';

// Handle session completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete') {
    $session_id = intval($_POST['session_id'] ?? 0);
    if ($session_id !== 0) {
        try {
            $stmt = $pdo->prepare("
                UPDATE sessions s
                JOIN bookings b ON s.booking_id = b.booking_id
                SET s.status = 'completed', b.status = 'completed'
                WHERE s.session_id = ? AND b.tutor_id = ?
            ");
            $stmt->execute([$session_id, $tutor_id]);
            setFlashMessage('success', 'Session marked as completed!');
            redirect('sessions.php');
        } catch (PDOException $e) {
            $error = 'Error completing session.';
        }
    }
}

// Get sessions
$query = "
    SELECT s.*, b.booking_id, st.first_name as student_name, st.last_name as student_last_name,
    m.module_code, m.module_name
    FROM sessions s
    JOIN bookings b ON s.booking_id = b.booking_id
    JOIN students st ON b.student_id = st.student_id
    JOIN modules m ON b.module_id = m.module_id
    WHERE b.tutor_id = ?
";

$params = [$tutor_id];

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
                
                <?php if (isset($error)): ?>
                    <?php showError($error); ?>
                <?php endif; ?>
                
                <?php displayFlashMessage(); ?>
                
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
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Session ID</th>
                                            <th>Booking ID</th>
                                            <th>Student</th>
                                            <th>Module</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td>#<?php echo $session['session_id']; ?></td>
                                            <td>#<?php echo $session['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($session['student_name'] . ' ' . $session['student_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($session['module_code']); ?></td>
                                            <td><?php echo formatDate($session['session_date']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($session['start_time'])) . ' - ' . date('g:i A', strtotime($session['end_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($session['location'] ?? 'TBD'); ?></td>
                                            <td><span class="badge <?php echo getSessionStatusBadge($session['status']); ?>"><?php echo ucfirst($session['status']); ?></span></td>
                                            <td>
                                                <?php if ($session['status'] === 'scheduled'): ?>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="complete">
                                                        <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">Complete</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
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
