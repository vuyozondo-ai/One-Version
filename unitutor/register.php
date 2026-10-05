<?php
/**
 * Registration Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Handles new user registration for students
 */
require_once 'config/database.php';
require_once 'includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(getDashboardUrl());
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = sanitize($_POST['student_number'] ?? '');
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $program_id = intval($_POST['program_id'] ?? 0);
    
    // Validate input
    if (empty($student_number) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif ($program_id === 0) {
        $error = 'Please select a program.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Check if student number already exists
            $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_number = ?");
            $stmt->execute([$student_number]);
            if ($stmt->fetch()) {
                $error = 'Student number already registered.';
            } else {
                // Insert new student
                try {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO students (student_number, first_name, last_name, email, password, program_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$student_number, $first_name, $last_name, $email, $hashed_password, $program_id]);
                    $success = 'Registration successful! You can now login.';
                } catch (PDOException $e) {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

// Get programs for dropdown
try {
    $programs = $pdo->query("SELECT program_id, program_name FROM programs ORDER BY program_name")->fetchAll();
} catch (PDOException $e) {
    $programs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UniTutor</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">
                    <h1>UniTutor</h1>
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 4rem; margin-bottom: 4rem;">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header">
                <h2>Student Registration</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <?php showError($error); ?>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <?php showSuccess($success); ?>
                    <p style="text-align: center; margin-top: 1rem;">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </p>
                <?php else: ?>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="student_number">Student Number</label>
                            <input type="text" id="student_number" name="student_number" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="program_id">Program</label>
                            <select id="program_id" name="program_id" class="form-control" required>
                                <option value="">Select Program</option>
                                <?php foreach ($programs as $program): ?>
                                    <option value="<?php echo $program['program_id']; ?>">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
                </form>
                
                <p style="margin-top: 1.5rem; text-align: center;">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> UniTutor - University Peer Tutoring Management System</p>
        </div>
    </footer>
</body>
</html>
