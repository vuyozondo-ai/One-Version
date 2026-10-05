<?php
/**
 * Applications Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * Manage tutor applications
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $application_id = intval($_POST['application_id'] ?? 0);
        $admin_id = getCurrentUserId();
        
        if ($application_id === 0) {
            $message = 'Invalid application ID.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE applications SET status = 'approved', admin_id = ? WHERE application_id = ?");
                $stmt->execute([$admin_id, $application_id]);
                $message = 'Application approved successfully.';
            } catch (PDOException $e) {
                $message = 'Error approving application.';
            }
        }
    } elseif ($action === 'reject') {
        $application_id = intval($_POST['application_id'] ?? 0);
        $admin_id = getCurrentUserId();
        
        if ($application_id === 0) {
            $message = 'Invalid application ID.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE applications SET status = 'rejected', admin_id = ? WHERE application_id = ?");
                $stmt->execute([$admin_id, $application_id]);
                $message = 'Application rejected successfully.';
            } catch (PDOException $e) {
                $message = 'Error rejecting application.';
            }
        }
    } elseif ($action === 'delete') {
        $application_id = intval($_POST['application_id'] ?? 0);
        if ($application_id === 0) {
            $message = 'Invalid application ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM applications WHERE application_id = ?");
                $stmt->execute([$application_id]);
                $message = 'Application deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting application.';
            }
        }
    }
}

// Get applications
$applications = $pdo->query("
    SELECT a.*, s.first_name as student_first_name, s.last_name as student_last_name, s.student_number,
    ad.first_name as admin_first_name, ad.last_name as admin_last_name
    FROM applications a
    LEFT JOIN students s ON a.student_id = s.student_id
    LEFT JOIN admins ad ON a.admin_id = ad.admin_id
    ORDER BY a.application_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Applications</h1>
                    <p>Review and manage tutor applications</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Applications Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Applications List (<?php echo count($applications); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Student Number</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th>Reviewed By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['student_first_name'] . ' ' . $app['student_last_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($app['student_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $app['application_type'])); ?></td>
                                        <td><?php echo formatDate($app['application_date']); ?></td>
                                        <td><span class="badge <?php echo getApplicationStatusBadge($app['status']); ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                        <td><?php echo truncateText(htmlspecialchars($app['notes'] ?? 'N/A'), 30); ?></td>
                                        <td><?php echo htmlspecialchars($app['admin_first_name'] . ' ' . $app['admin_last_name'] ?? 'Not reviewed'); ?></td>
                                        <td>
                                            <?php if ($app['status'] === 'pending'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this application?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
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
