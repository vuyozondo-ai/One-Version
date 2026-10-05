<?php
/**
 * Tutor Bookings Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Manage tutor's bookings
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('tutor');

$tutor_id = getCurrentUserId();
$message = '';
$status_filter = $_GET['status'] ?? '';

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'confirm') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        if ($booking_id !== 0) {
            try {
                // Update booking status
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ? AND tutor_id = ?");
                $stmt->execute([$booking_id, $tutor_id]);
                
                // Create session
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
                $stmt->execute([$booking_id]);
                $booking = $stmt->fetch();
                
                if ($booking) {
                    $stmt = $pdo->prepare("INSERT INTO sessions (booking_id, session_date, start_time, end_time, location, status) VALUES (?, CURDATE(), '09:00:00', '10:00:00', 'Library Room', 'scheduled')");
                    $stmt->execute([$booking_id]);
                }
                
                $message = 'Booking confirmed successfully!';
            } catch (PDOException $e) {
                $message = 'Error confirming booking.';
            }
        }
    } elseif ($action === 'reject') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        if ($booking_id !== 0) {
            try {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ? AND tutor_id = ?");
                $stmt->execute([$booking_id, $tutor_id]);
                $message = 'Booking rejected successfully.';
            } catch (PDOException $e) {
                $message = 'Error rejecting booking.';
            }
        }
    }
}

// Get bookings
$query = "
    SELECT b.*, s.first_name as student_name, s.last_name as student_last_name,
    m.module_code, m.module_name
    FROM bookings b
    JOIN students s ON b.student_id = s.student_id
    JOIN modules m ON b.module_id = m.module_id
    WHERE b.tutor_id = ?
";

$params = [$tutor_id];

if ($status_filter) {
    $query .= " AND b.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY b.booking_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>My Bookings</h1>
                    <p>View and manage tutoring booking requests</p>
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
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <?php if ($status_filter): ?>
                            <a href="bookings.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Bookings (<?php echo count($bookings); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <p>No bookings found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Student</th>
                                            <th>Module</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['student_name'] . ' ' . $booking['student_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['module_code'] . ' - ' . $booking['module_name']); ?></td>
                                            <td><?php echo formatDateTime($booking['booking_date']); ?></td>
                                            <td><span class="badge <?php echo getBookingStatusBadge($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                            <td><?php echo truncateText(htmlspecialchars($booking['reason'] ?? 'N/A'), 30); ?></td>
                                            <td>
                                                <?php if ($booking['status'] === 'pending'): ?>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="confirm">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                                                    </form>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
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
