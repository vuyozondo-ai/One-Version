<?php
/**
 * Tutor Profile Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Shows detailed tutor profile and available slots
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$tutor_id = intval($_GET['tutor_id'] ?? 0);
$message = '';

if ($tutor_id === 0) {
    setFlashMessage('error', 'Invalid tutor ID.');
    redirect('find_tutor.php');
}

// Get tutor information
$stmt = $pdo->prepare("SELECT * FROM tutors WHERE tutor_id = ?");
$stmt->execute([$tutor_id]);
$tutor = $stmt->fetch();

if (!$tutor) {
    setFlashMessage('error', 'Tutor not found.');
    redirect('find_tutor.php');
}

// Get tutor's modules
$stmt = $pdo->prepare("
    SELECT m.module_code, m.module_name, tm.proficiency_level, p.program_name, d.department_name
    FROM tutor_modules tm
    JOIN modules m ON tm.module_id = m.module_id
    JOIN programs p ON m.program_id = p.program_id
    JOIN departments d ON p.department_id = d.department_id
    WHERE tm.tutor_id = ?
");
$stmt->execute([$tutor_id]);
$tutor_modules = $stmt->fetchAll();

// Get tutor's average rating
$avg_rating = getTutorAverageRating($pdo, $tutor_id);

// Get tutor's session count
$session_count = getTutorSessionCount($pdo, $tutor_id);

// Get available slots
$stmt = $pdo->prepare("
    SELECT aslot.*, m.module_code, m.module_name
    FROM available_slots aslot
    JOIN modules m ON aslot.module_id = m.module_id
    WHERE aslot.tutor_id = ? AND aslot.availability_status = 'available' AND aslot.slot_date >= CURDATE()
    ORDER BY aslot.slot_date ASC, aslot.start_time ASC
");
$stmt->execute([$tutor_id]);
$available_slots = $stmt->fetchAll();

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot_id = intval($_POST['slot_id'] ?? 0);
    $reason = sanitize($_POST['reason'] ?? '');
    $student_id = getCurrentUserId();
    
    if ($slot_id === 0) {
        $message = 'Please select a time slot.';
    } elseif (empty($reason)) {
        $message = 'Please provide a reason for the tutoring session.';
    } else {
        try {
            // Check if slot is still available
            $stmt = $pdo->prepare("SELECT * FROM available_slots WHERE slot_id = ? AND availability_status = 'available'");
            $stmt->execute([$slot_id]);
            $slot = $stmt->fetch();
            
            if (!$slot) {
                $message = 'Sorry, this time slot is no longer available.';
            } else {
                // Create booking
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (student_id, tutor_id, module_id, slot_id, reason, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$student_id, $tutor_id, $slot['module_id'], $slot_id, $reason]);
                
                // Update slot status
                $stmt = $pdo->prepare("UPDATE available_slots SET availability_status = 'booked' WHERE slot_id = ?");
                $stmt->execute([$slot_id]);
                
                $message = 'Booking submitted successfully! The tutor will review your request.';
                $available_slots = array_filter($available_slots, function($s) use ($slot_id) {
                    return $s['slot_id'] != $slot_id;
                });
            }
        } catch (PDOException $e) {
            $message = 'Error creating booking. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Profile - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <a href="find_tutor.php" class="btn btn-secondary" style="margin-bottom: 1rem;">← Back to Find Tutor</a>
                    <h1>Tutor Profile</h1>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Tutor Information -->
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 250px;">
                                <div style="text-align: center; margin-bottom: 1.5rem;">
                                    <div class="tutor-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 1rem;">
                                        <?php echo strtoupper(substr($tutor['first_name'], 0, 1) . substr($tutor['last_name'], 0, 1)); ?>
                                    </div>
                                    <div class="tutor-rating" style="justify-content: center;">
                                        <span class="stars" style="font-size: 1.5rem;"><?php echo generateStarRating(round($avg_rating)); ?></span>
                                        <span style="font-size: 1.25rem; margin-left: 0.5rem;"><?php echo number_format($avg_rating, 1); ?>/5</span>
                                    </div>
                                    <p style="margin-top: 0.5rem; color: var(--text-muted);"><?php echo $session_count; ?> completed sessions</p>
                                </div>
                            </div>
                            
                            <div style="flex: 2; min-width: 300px;">
                                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Contact Information</h3>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($tutor['email']); ?></p>
                                <?php if ($tutor['phone']): ?>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($tutor['phone']); ?></p>
                                <?php endif; ?>
                                <p><strong>Status:</strong> <span class="badge <?php echo $tutor['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo ucfirst($tutor['status']); ?></span></p>
                                
                                <h3 style="color: var(--primary-color); margin: 1.5rem 0 1rem;">Modules Taught</h3>
                                <div class="table-responsive">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Module Code</th>
                                                <th>Module Name</th>
                                                <th>Department</th>
                                                <th>Proficiency</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tutor_modules as $tm): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($tm['module_code']); ?></td>
                                                <td><?php echo htmlspecialchars($tm['module_name']); ?></td>
                                                <td><?php echo htmlspecialchars($tm['department_name']); ?></td>
                                                <td><span class="badge badge-info"><?php echo ucfirst($tm['proficiency_level']); ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Available Slots -->
                <div class="card">
                    <div class="card-header">
                        <h2>Available Time Slots</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($available_slots)): ?>
                            <p>No available time slots at the moment. Please check back later.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Module</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($available_slots as $slot): ?>
                                        <tr>
                                            <td><?php echo formatDate($slot['slot_date']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($slot['module_code'] . ' - ' . $slot['module_name']); ?></td>
                                            <td>
                                                <button onclick="showBookingForm(<?php echo $slot['slot_id']; ?>, '<?php echo formatDate($slot['slot_date']); ?>', '<?php echo date('g:i A', strtotime($slot['start_time'])); ?>')" class="btn btn-sm btn-primary">Book</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Booking Form Modal -->
                <div id="bookingModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Book Session</h2>
                            <button class="modal-close" onclick="closeBookingForm()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Date:</strong> <span id="modalDate"></span></p>
                            <p><strong>Time:</strong> <span id="modalTime"></span></p>
                            <form method="POST" action="">
                                <input type="hidden" name="slot_id" id="slotId">
                                
                                <div class="form-group">
                                    <label for="reason">Reason for Tutoring</label>
                                    <textarea id="reason" name="reason" class="form-control" rows="4" required placeholder="Please describe what you need help with..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Submit Booking</button>
                                <button type="button" class="btn btn-secondary" onclick="closeBookingForm()">Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showBookingForm(slotId, date, time) {
            document.getElementById('slotId').value = slotId;
            document.getElementById('modalDate').textContent = date;
            document.getElementById('modalTime').textContent = time;
            document.getElementById('bookingModal').classList.add('active');
        }
        
        function closeBookingForm() {
            document.getElementById('bookingModal').classList.remove('active');
        }
    </script>
</body>
</html>
