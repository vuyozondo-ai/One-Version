<?php
/**
 * Feedback Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * Manage all feedback
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $feedback_id = intval($_POST['feedback_id'] ?? 0);
        if ($feedback_id === 0) {
            $message = 'Invalid feedback ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM feedback WHERE feedback_id = ?");
                $stmt->execute([$feedback_id]);
                $message = 'Feedback deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting feedback.';
            }
        }
    }
}

// Get feedback
$feedback = $pdo->query("
    SELECT f.*, s.first_name as student_name, s.last_name as student_last_name,
    t.first_name as tutor_name, t.last_name as tutor_last_name,
    b.booking_id
    FROM feedback f
    JOIN students s ON f.student_id = s.student_id
    JOIN tutors t ON f.tutor_id = t.tutor_id
    LEFT JOIN bookings b ON f.booking_id = b.booking_id
    ORDER BY f.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Feedback</h1>
                    <p>View and manage all student feedback</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Feedback Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Feedback List (<?php echo count($feedback); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Feedback ID</th>
                                        <th>Student</th>
                                        <th>Tutor</th>
                                        <th>Booking ID</th>
                                        <th>Rating</th>
                                        <th>Comments</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feedback as $fb): ?>
                                    <tr>
                                        <td>#<?php echo $fb['feedback_id']; ?></td>
                                        <td><?php echo htmlspecialchars($fb['student_name'] . ' ' . $fb['student_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($fb['tutor_name'] . ' ' . $fb['tutor_last_name']); ?></td>
                                        <td><?php echo $fb['booking_id'] ? '#' . $fb['booking_id'] : 'N/A'; ?></td>
                                        <td>
                                            <span class="stars"><?php echo generateStarRating($fb['rating']); ?></span>
                                            (<?php echo $fb['rating']; ?>/5)
                                        </td>
                                        <td><?php echo truncateText(htmlspecialchars($fb['comments'] ?? 'N/A'), 50); ?></td>
                                        <td><?php echo formatDateTime($fb['created_at']); ?></td>
                                        <td>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="feedback_id" value="<?php echo $fb['feedback_id']; ?>">
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
