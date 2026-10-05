<?php
/**
 * Payments Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * Manage all payments
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
    
    if ($action === 'add') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $payment_date = sanitize($_POST['payment_date'] ?? '');
        $reference = sanitize($_POST['reference'] ?? '');
        
        if ($student_id === 0 || $amount === 0 || empty($payment_date)) {
            $message = 'Please fill in all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO payments (student_id, admin_id, amount, payment_date, payment_status, reference) VALUES (?, ?, ?, ?, 'pending', ?)");
                $stmt->execute([$student_id, getCurrentUserId(), $amount, $payment_date, $reference]);
                $message = 'Payment added successfully.';
            } catch (PDOException $e) {
                $message = 'Error adding payment.';
            }
        }
    } elseif ($action === 'update_status') {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        
        if ($payment_id === 0 || empty($status)) {
            $message = 'Invalid payment ID or status.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE payments SET payment_status = ?, admin_id = ? WHERE payment_id = ?");
                $stmt->execute([$status, getCurrentUserId(), $payment_id]);
                $message = 'Payment status updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating payment status.';
            }
        }
    } elseif ($action === 'delete') {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        if ($payment_id === 0) {
            $message = 'Invalid payment ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM payments WHERE payment_id = ?");
                $stmt->execute([$payment_id]);
                $message = 'Payment deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting payment.';
            }
        }
    }
}

// Get payments
$query = "
    SELECT p.*, s.first_name as student_name, s.last_name as student_last_name, s.student_number,
    a.first_name as admin_first_name, a.last_name as admin_last_name
    FROM payments p
    JOIN students s ON p.student_id = s.student_id
    LEFT JOIN admins a ON p.admin_id = a.admin_id
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $query .= " AND p.payment_status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY p.payment_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Get students for dropdown
$students = $pdo->query("SELECT student_id, CONCAT(student_number, ' - ', first_name, ' ', last_name) as name FROM students ORDER BY last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Payments</h1>
                    <p>View and manage all payments</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Add Payment Form -->
                <div class="card">
                    <div class="card-header">
                        <h2>Add New Payment</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="student_id">Student</label>
                                    <select id="student_id" name="student_id" class="form-control" required>
                                        <option value="">Select Student</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['student_id']; ?>">
                                                <?php echo htmlspecialchars($student['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <input type="number" id="amount" name="amount" class="form-control" required step="0.01" min="0">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="payment_date">Payment Date</label>
                                    <input type="date" id="payment_date" name="payment_date" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="reference">Reference</label>
                                    <input type="text" id="reference" name="reference" class="form-control">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Payment</button>
                        </form>
                    </div>
                </div>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <form method="GET" action="" style="display: flex; gap: 1rem; width: 100%;">
                        <div class="filter-group">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <?php if ($status_filter): ?>
                            <a href="payments.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Payments List (<?php echo count($payments); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Student</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Processed By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>#<?php echo $payment['payment_id']; ?></td>
                                        <td><?php echo htmlspecialchars($payment['student_name'] . ' ' . $payment['student_last_name']); ?> (<?php echo htmlspecialchars($payment['student_number']); ?>)</td>
                                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo formatDate($payment['payment_date']); ?></td>
                                        <td><span class="badge <?php echo getPaymentStatusBadge($payment['payment_status']); ?>"><?php echo ucfirst($payment['payment_status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($payment['reference'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['admin_first_name'] . ' ' . $payment['admin_last_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if ($payment['payment_status'] === 'pending'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-sm btn-success">Complete</button>
                                                </form>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                                    <input type="hidden" name="status" value="refunded">
                                                    <button type="submit" class="btn btn-sm btn-danger">Refund</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
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
