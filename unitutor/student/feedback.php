<?php
/**
 * Student Feedback Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * View and submit feedback
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$student_id = getCurrentUserId();
$message = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tutor_id = intval($_POST['tutor_id'] ?? 0);
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comments = sanitize($_POST['comments'] ?? '');
    
    if ($tutor_id === 0 || $rating < 1 || $rating > 5) {
        $message = 'Please select a valid rating.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (student_id, tutor_id, booking_id, rating, comments) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $tutor_id, $booking_id ?: null, $rating, $comments]);
            $message = 'Feedback submitted successfully!';
        } catch (PDOException $e) {
            $message = 'Error submitting feedback.';
        }
    }
}

// Get student's feedback
$feedback = $pdo->prepare("
    SELECT f.*, t.first_name as tutor_name, t.last_name as tutor_last_name,
    b.booking_id
    FROM feedback f
    JOIN tutors t ON f.tutor_id = t.tutor_id
    LEFT JOIN bookings b ON f.booking_id = b.booking_id
    WHERE f.student_id = ?
    ORDER BY f.created_at DESC
");
$feedback->execute([$student_id]);
$feedback = $feedback->fetchAll();

// Get completed sessions without feedback
$completed_sessions = $pdo->prepare("
    SELECT s.session_id, s.booking_id, t.tutor_id, t.first_name as tutor_name, t.last_name as tutor_last_name,
    m.module_code, m.module_name
    FROM sessions s
    JOIN bookings b ON s.booking_id = b.booking_id
    JOIN tutors t ON b.tutor_id = t.tutor_id
    JOIN modules m ON b.module_id = m.module_id
    WHERE b.student_id = ? AND s.status = 'completed'
    AND s.booking_id NOT IN (SELECT booking_id FROM feedback WHERE student_id = ? AND booking_id IS NOT NULL)
");
$completed_sessions->execute([$student_id, $student_id]);
$completed_sessions = $completed_sessions->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Feedback - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>My Feedback</h1>
                    <p>View your feedback history and submit new feedback</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Submit Feedback -->
                <?php if (!empty($completed_sessions)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Submit Feedback</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="session_id">Select Completed Session</label>
                                    <select id="session_id" name="booking_id" class="form-control" required onchange="updateTutorId(this)">
                                        <option value="">Select a session...</option>
                                        <?php foreach ($completed_sessions as $session): ?>
                                            <option value="<?php echo $session['booking_id']; ?>" data-tutor-id="<?php echo $session['tutor_id']; ?>">
                                                <?php echo formatDate($session['session_date']); ?> - <?php echo htmlspecialchars($session['tutor_name'] . ' ' . $session['tutor_last_name']); ?> - <?php echo htmlspecialchars($session['module_code']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <input type="hidden" name="tutor_id" id="tutor_id" value="">
                                
                                <div class="form-group">
                                    <label for="rating">Rating (1-5)</label>
                                    <select id="rating" name="rating" class="form-control" required>
                                        <option value="">Select rating...</option>
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="3">3 - Good</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="1">1 - Poor</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="comments">Comments</label>
                                    <textarea id="comments" name="comments" class="form-control" rows="4" placeholder="Share your experience..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Feedback History -->
                <div class="card">
                    <div class="card-header">
                        <h2>Feedback History (<?php echo count($feedback); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($feedback)): ?>
                            <p>No feedback submitted yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tutor</th>
                                            <th>Booking ID</th>
                                            <th>Rating</th>
                                            <th>Comments</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($feedback as $fb): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fb['tutor_name'] . ' ' . $fb['tutor_last_name']); ?></td>
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
    
    <script>
        function updateTutorId(select) {
            const selectedOption = select.options[select.selectedIndex];
            const tutorId = selectedOption.getAttribute('data-tutor-id');
            document.getElementById('tutor_id').value = tutorId;
        }
    </script>
</body>
</html>
