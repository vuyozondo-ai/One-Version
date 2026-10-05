<?php
/**
 * Student Profile Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * View and edit student profile
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$student_id = getCurrentUserId();
$message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $message = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $message = 'Please enter a valid email address.';
    } else {
        try {
            // Check if email is being changed and if it's already taken
            $stmt = $pdo->prepare("SELECT email FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $current_email = $stmt->fetch()['email'];
            
            if ($email !== $current_email) {
                $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
                $stmt->execute([$email, $student_id]);
                if ($stmt->fetch()) {
                    $message = 'Email already in use by another student.';
                }
            }
            
            if (!$message) {
                // Handle password change
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $message = 'Please enter your current password to change your password.';
                    } else {
                        $stmt = $pdo->prepare("SELECT password FROM students WHERE student_id = ?");
                        $stmt->execute([$student_id]);
                        $stored_password = $stmt->fetch()['password'];
                        
                        if (!password_verify($current_password, $stored_password)) {
                            $message = 'Current password is incorrect.';
                        } elseif ($new_password !== $confirm_password) {
                            $message = 'New passwords do not match.';
                        } elseif (strlen($new_password) < 6) {
                            $message = 'New password must be at least 6 characters.';
                        } else {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, password = ? WHERE student_id = ?");
                            $stmt->execute([$first_name, $last_name, $email, $hashed_password, $student_id]);
                            $message = 'Profile updated successfully!';
                        }
                    }
                } else {
                    // Update without password change
                    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ? WHERE student_id = ?");
                    $stmt->execute([$first_name, $last_name, $email, $student_id]);
                    $message = 'Profile updated successfully!';
                }
            }
        } catch (PDOException $e) {
            $message = 'Error updating profile.';
        }
    }
}

// Get student information
$stmt = $pdo->prepare("
    SELECT s.*, p.program_name, d.department_name
    FROM students s
    LEFT JOIN programs p ON s.program_id = p.program_id
    LEFT JOIN departments d ON p.department_id = d.department_id
    WHERE s.student_id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>My Profile</h1>
                    <p>View and edit your profile information</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h2>Profile Information</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="student_number">Student Number</label>
                                    <input type="text" id="student_number" class="form-control" value="<?php echo htmlspecialchars($student['student_number']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="program_name">Program</label>
                                    <input type="text" id="program_name" class="form-control" value="<?php echo htmlspecialchars($student['program_name'] ?? 'Not assigned'); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required value="<?php echo htmlspecialchars($student['first_name']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required value="<?php echo htmlspecialchars($student['last_name']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($student['email']); ?>">
                            </div>
                            
                            <hr style="margin: 2rem 0;">
                            
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Change Password</h3>
                            <p style="color: var(--text-muted); margin-bottom: 1rem;">Leave blank if you don't want to change your password.</p>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" minlength="6">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="6">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
                
                <!-- Account Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h2>Account Statistics</h2>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3><?php echo getStudentBookingCount($pdo, $student_id); ?></h3>
                                <p>Total Bookings</p>
                            </div>
                            <div class="stat-card">
                                <h3><?php echo getStudentCompletedSessionCount($pdo, $student_id); ?></h3>
                                <p>Completed Sessions</p>
                            </div>
                            <div class="stat-card">
                                <h3><?php echo formatDate($student['created_at'], 'M d, Y'); ?></h3>
                                <p>Member Since</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
