<?php
/**
 * Tutor Available Slots Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Manage tutor's available time slots
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('tutor');

$tutor_id = getCurrentUserId();
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $module_id = intval($_POST['module_id'] ?? 0);
        $slot_date = sanitize($_POST['slot_date'] ?? '');
        $start_time = sanitize($_POST['start_time'] ?? '');
        $end_time = sanitize($_POST['end_time'] ?? '');
        
        if ($module_id === 0 || empty($slot_date) || empty($start_time) || empty($end_time)) {
            $message = 'Please fill in all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO available_slots (tutor_id, module_id, slot_date, start_time, end_time, availability_status) VALUES (?, ?, ?, ?, ?, 'available')");
                $stmt->execute([$tutor_id, $module_id, $slot_date, $start_time, $end_time]);
                $message = 'Time slot added successfully!';
            } catch (PDOException $e) {
                $message = 'Error adding time slot.';
            }
        }
    } elseif ($action === 'delete') {
        $slot_id = intval($_POST['slot_id'] ?? 0);
        if ($slot_id === 0) {
            $message = 'Invalid slot ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM available_slots WHERE slot_id = ? AND tutor_id = ?");
                $stmt->execute([$slot_id, $tutor_id]);
                $message = 'Time slot deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting time slot.';
            }
        }
    }
}

// Get tutor's modules for dropdown
$stmt = $pdo->prepare("
    SELECT m.module_id, m.module_code, m.module_name
    FROM tutor_modules tm
    JOIN modules m ON tm.module_id = m.module_id
    WHERE tm.tutor_id = ?
    ORDER BY m.module_code
");
$stmt->execute([$tutor_id]);
$tutor_modules = $stmt->fetchAll();

// Get tutor's available slots
$stmt = $pdo->prepare("
    SELECT aslot.*, m.module_code, m.module_name
    FROM available_slots aslot
    JOIN modules m ON aslot.module_id = m.module_id
    WHERE aslot.tutor_id = ? AND aslot.slot_date >= CURDATE()
    ORDER BY aslot.slot_date ASC, aslot.start_time ASC
");
$stmt->execute([$tutor_id]);
$available_slots = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Slots - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Available Time Slots</h1>
                    <p>Manage your availability for tutoring sessions</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Add Slot Form -->
                <div class="card">
                    <div class="card-header">
                        <h2>Add Available Time Slot</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="module_id">Module</label>
                                    <select id="module_id" name="module_id" class="form-control" required>
                                        <option value="">Select Module</option>
                                        <?php foreach ($tutor_modules as $module): ?>
                                            <option value="<?php echo $module['module_id']; ?>">
                                                <?php echo htmlspecialchars($module['module_code'] . ' - ' . $module['module_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="slot_date">Date</label>
                                    <input type="date" id="slot_date" name="slot_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="start_time">Start Time</label>
                                    <input type="time" id="start_time" name="start_time" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="end_time">End Time</label>
                                    <input type="time" id="end_time" name="end_time" class="form-control" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Time Slot</button>
                        </form>
                    </div>
                </div>
                
                <!-- Available Slots List -->
                <div class="card">
                    <div class="card-header">
                        <h2>My Available Slots (<?php echo count($available_slots); ?>)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($available_slots)): ?>
                            <p>No available time slots. Add your availability above.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Module</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($available_slots as $slot): ?>
                                        <tr>
                                            <td><?php echo formatDate($slot['slot_date']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time'])); ?></td>
                                            <td><?php echo htmlspecialchars($slot['module_code'] . ' - ' . $slot['module_name']); ?></td>
                                            <td><span class="badge <?php echo $slot['availability_status'] == 'available' ? 'badge-success' : 'badge-danger'; ?>"><?php echo ucfirst($slot['availability_status']); ?></span></td>
                                            <td>
                                                <?php if ($slot['availability_status'] === 'available'): ?>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this time slot?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="slot_id" value="<?php echo $slot['slot_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">Booked</span>
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
