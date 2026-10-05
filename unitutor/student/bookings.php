<?php
/**
 * Student Bookings Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * View and manage student's bookings
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$student_id = getCurrentUserId();
$status_filter = $_GET['status'] ?? '';

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    if ($booking_id !== 0) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ? AND student_id = ?");
            $stmt->execute([$booking_id, $student_id]);
            setFlashMessage('success', 'Booking cancelled successfully.');
            redirect('bookings.php');
        } catch (PDOException $e) {
            $error = 'Error cancelling booking.';
        }
    }
}

// Get bookings
$query = "
    SELECT b.*, t.first_name as tutor_name, t.last_name as tutor_last_name,
    m.module_code, m.module_name
    FROM bookings b
    JOIN tutors t ON b.tutor_id = t.tutor_id
    JOIN modules m ON b.module_id = m.module_id
    WHERE b.student_id = ?
";

$params = [$student_id];

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
    <title>My Bookings - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>My Bookings</h1>
                    <p>View and manage your tutoring bookings</p>
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
                        <h2>My Bookings (<?php echo count($bookings); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <p>No bookings found.</p>
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
                                            <th>Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['tutor_name'] . ' ' . $booking['tutor_last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['module_code'] . ' - ' . $booking['module_name']); ?></td>
                                            <td><?php echo formatDateTime($booking['booking_date']); ?></td>
                                            <td><span class="badge <?php echo getBookingStatusBadge($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                            <td><?php echo truncateText(htmlspecialchars($booking['reason'] ?? 'N/A'), 30); ?></td>
                                            <td>
                                                <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                        <input type="hidden" name="action" value="cancel">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
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
