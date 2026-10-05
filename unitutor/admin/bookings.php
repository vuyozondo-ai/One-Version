<?php
/**
 * Bookings Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * Manage all bookings
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        
        if ($booking_id === 0 || empty($status)) {
            $message = 'Invalid booking ID or status.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
                $stmt->execute([$status, $booking_id]);
                
                // If confirmed, create session
                if ($status === 'confirmed') {
                    $booking = $pdo->prepare("SELECT b.*, t.tutor_id, m.module_id FROM bookings b JOIN tutors t ON b.tutor_id = t.tutor_id JOIN modules m ON b.module_id = m.module_id WHERE b.booking_id = ?");
                    $booking->execute([$booking_id]);
                    $booking_data = $booking->fetch();
                    
                    if ($booking_data) {
                        $stmt = $pdo->prepare("INSERT INTO sessions (booking_id, session_date, start_time, end_time, location, status) VALUES (?, CURDATE(), '09:00:00', '10:00:00', 'Library Room', 'scheduled')");
                        $stmt->execute([$booking_id]);
                    }
                }
                
                $message = 'Booking status updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating booking status.';
            }
        }
    } elseif ($action === 'delete') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        if ($booking_id === 0) {
            $message = 'Invalid booking ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ?");
                $stmt->execute([$booking_id]);
                $message = 'Booking deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting booking.';
            }
        }
    }
}

// Build query
$query = "
    SELECT b.*, s.first_name as student_name, s.last_name as student_last_name,
    t.first_name as tutor_name, t.last_name as tutor_last_name,
    m.module_code, m.module_name
    FROM bookings b
    JOIN students s ON b.student_id = s.student_id
    JOIN tutors t ON b.tutor_id = t.tutor_id
    JOIN modules m ON b.module_id = m.module_id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR t.first_name LIKE ? OR t.last_name LIKE ? OR m.module_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_fill(0, 5, $searchParam);
}

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
    <title>Manage Bookings - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Bookings</h1>
                    <p>View and manage all tutoring bookings</p>
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
                            <input type="text" name="search" class="form-control" placeholder="Search by name or module..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
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
                        <?php if ($search || $status_filter): ?>
                            <a href="bookings.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Bookings List (<?php echo count($bookings); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Student</th>
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
                                        <td><?php echo htmlspecialchars($booking['student_name'] . ' ' . $booking['student_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['tutor_name'] . ' ' . $booking['tutor_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['module_code'] . ' - ' . $booking['module_name']); ?></td>
                                        <td><?php echo formatDateTime($booking['booking_date']); ?></td>
                                        <td><span class="badge <?php echo getBookingStatusBadge($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                        <td><?php echo truncateText(htmlspecialchars($booking['reason'] ?? 'N/A'), 30); ?></td>
                                        <td>
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                                                </form>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
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
