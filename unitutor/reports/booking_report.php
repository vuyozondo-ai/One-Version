<?php
/**
 * Booking Report
 * UniTutor - University Peer Tutoring Management System
 * 
 * Report 4: Booking and Session Report
 * Shows detailed booking information with filtering options
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$date_filter = $_GET['date'] ?? '';
$module_filter = $_GET['module'] ?? '';
$tutor_filter = $_GET['tutor'] ?? '';
$status_filter = $_GET['status'] ?? '';

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

if ($date_filter) {
    $query .= " AND DATE(b.booking_date) = ?";
    $params[] = $date_filter;
}

if ($module_filter) {
    $query .= " AND b.module_id = ?";
    $params[] = $module_filter;
}

if ($tutor_filter) {
    $query .= " AND b.tutor_id = ?";
    $params[] = $tutor_filter;
}

if ($status_filter) {
    $query .= " AND b.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY b.booking_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get filter options
$modules = $pdo->query("SELECT module_id, module_code, module_name FROM modules ORDER BY module_name")->fetchAll();
$tutors = $pdo->query("SELECT tutor_id, CONCAT(first_name, ' ', last_name) as name FROM tutors ORDER BY last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Report - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <a href="../admin/reports.php" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Reports</a>
                    <h1>Booking and Session Report</h1>
                    <p>Detailed booking information with filters</p>
                </div>
                
                <!-- Filter Form -->
                <div class="card">
                    <div class="card-header">
                        <h2>Filter Options</h2>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="module">Module</label>
                                    <select id="module" name="module" class="form-control">
                                        <option value="">All Modules</option>
                                        <?php foreach ($modules as $module): ?>
                                            <option value="<?php echo $module['module_id']; ?>" <?php echo $module_filter == $module['module_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($module['module_code'] . ' - ' . $module['module_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tutor">Tutor</label>
                                    <select id="tutor" name="tutor" class="form-control">
                                        <option value="">All Tutors</option>
                                        <?php foreach ($tutors as $tutor): ?>
                                            <option value="<?php echo $tutor['tutor_id']; ?>" <?php echo $tutor_filter == $tutor['tutor_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tutor['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <?php if ($date_filter || $module_filter || $tutor_filter || $status_filter): ?>
                                <a href="booking_report.php" class="btn btn-secondary">Clear Filters</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Booking Results -->
                <div class="card">
                    <div class="card-header">
                        <h2>Report Results (<?php echo count($bookings); ?> bookings)</h2>
                        <button onclick="window.print()" class="btn btn-secondary" style="float: right;">Print Report</button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <p>No bookings found matching your criteria.</p>
                        <?php else: ?>
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
