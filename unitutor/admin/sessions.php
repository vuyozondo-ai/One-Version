<?php
/**
 * Sessions Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * Manage all tutoring sessions
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';
$status_filter = $_GET['status'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $session_id = intval($_POST['session_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        
        if ($session_id === 0 || empty($status)) {
            $message = 'Invalid session ID or status.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE sessions SET status = ? WHERE session_id = ?");
                $stmt->execute([$status, $session_id]);
                $message = 'Session status updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating session status.';
            }
        }
    } elseif ($action === 'delete') {
        $session_id = intval($_POST['session_id'] ?? 0);
        if ($session_id === 0) {
            $message = 'Invalid session ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM sessions WHERE session_id = ?");
                $stmt->execute([$session_id]);
                $message = 'Session deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting session.';
            }
        }
    }
}

// Build query
$query = "
    SELECT s.*, b.booking_id, st.first_name as student_name, st.last_name as student_last_name,
    t.first_name as tutor_name, t.last_name as tutor_last_name,
    m.module_code, m.module_name
    FROM sessions s
    JOIN bookings b ON s.booking_id = b.booking_id
    JOIN students st ON b.student_id = st.student_id
    JOIN tutors t ON b.tutor_id = t.tutor_id
    JOIN modules m ON b.module_id = m.module_id
    WHERE 1=1
";

$params = [];

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
    <title>Manage Sessions - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Sessions</h1>
                    <p>View and manage all tutoring sessions</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
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
                        <h2>Sessions List (<?php echo count($sessions); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Session ID</th>
                                        <th>Booking ID</th>
                                        <th>Student</th>
                                        <th>Tutor</th>
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
                                        <td><?php echo htmlspecialchars($session['tutor_name'] . ' ' . $session['tutor_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($session['module_code']); ?></td>
                                        <td><?php echo formatDate($session['session_date']); ?></td>
                                        <td><?php echo date('g:i A', strtotime($session['start_time'])) . ' - ' . date('g:i A', strtotime($session['end_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($session['location'] ?? 'N/A'); ?></td>
                                        <td><span class="badge <?php echo getSessionStatusBadge($session['status']); ?>"><?php echo ucfirst($session['status']); ?></span></td>
                                        <td>
                                            <?php if ($session['status'] === 'scheduled'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-sm btn-success">Complete</button>
                                                </form>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this session?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
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
