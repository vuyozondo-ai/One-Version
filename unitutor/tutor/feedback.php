<?php
/**
 * Tutor Feedback Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * View feedback received from students
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('tutor');

$tutor_id = getCurrentUserId();

// Get feedback for tutor
$stmt = $pdo->prepare("
    SELECT f.*, s.first_name as student_name, s.last_name as student_last_name,
    b.booking_id
    FROM feedback f
    JOIN students s ON f.student_id = s.student_id
    LEFT JOIN bookings b ON f.booking_id = b.booking_id
    WHERE f.tutor_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$tutor_id]);
$feedback = $stmt->fetchAll();

// Calculate average rating
$avg_rating = getTutorAverageRating($pdo, $tutor_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>My Feedback</h1>
                    <p>View feedback received from students</p>
                </div>
                
                <!-- Rating Summary -->
                <div class="card">
                    <div class="card-header">
                        <h2>Overall Rating</h2>
                    </div>
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 4rem; color: var(--primary-color); font-weight: 700;">
                            <?php echo number_format($avg_rating, 1); ?>
                        </div>
                        <div class="stars" style="font-size: 2rem; margin: 1rem 0;">
                            <?php echo generateStarRating(round($avg_rating)); ?>
                        </div>
                        <p style="color: var(--text-muted);">Based on <?php echo count($feedback); ?> reviews</p>
                    </div>
                </div>
                
                <!-- Feedback List -->
                <div class="card">
                    <div class="card-header">
                        <h2>Feedback History (<?php echo count($feedback); ?> reviews)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($feedback)): ?>
                            <p>No feedback received yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Booking ID</th>
                                            <th>Rating</th>
                                            <th>Comments</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($feedback as $fb): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fb['student_name'] . ' ' . $fb['student_last_name']); ?></td>
                                            <td><?php echo $fb['booking_id'] ? '#' . $fb['booking_id'] : 'N/A'; ?></td>
                                            <td>
                                                <span class="stars"><?php echo generateStarRating($fb['rating']); ?></span>
                                                (<?php echo $fb['rating']; ?>/5)
                                            </td>
                                            <td><?php echo truncateText(htmlspecialchars($fb['comments'] ?? 'N/A'), 50); ?></td>
                                            <td><?php echo formatDateTime($fb['created_at']); ?></td>
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
