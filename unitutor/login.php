<?php
/**
 * Login Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Handles user authentication for all user roles (admin, student, tutor)
 */
require_once 'config/database.php';
require_once 'includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect(getDashboardUrl());
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check admin login
        $stmt = $pdo->prepare("SELECT admin_id, admin_number, first_name, last_name, email, password FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION['user_number'] = $admin['admin_number'];
            setFlashMessage('success', 'Welcome back, ' . $admin['first_name'] . '!');
            redirect('admin/dashboard.php');
        }
        
        // Check student login
        $stmt = $pdo->prepare("SELECT student_id, student_number, first_name, last_name, email, password FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();
        
        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['user_id'] = $student['student_id'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['user_name'] = $student['first_name'] . ' ' . $student['last_name'];
            $_SESSION['user_number'] = $student['student_number'];
            setFlashMessage('success', 'Welcome back, ' . $student['first_name'] . '!');
            redirect('student/dashboard.php');
        }
        
        // Check tutor login
        $stmt = $pdo->prepare("SELECT tutor_id, tutor_number, first_name, last_name, email, password FROM tutors WHERE email = ?");
        $stmt->execute([$email]);
        $tutor = $stmt->fetch();
        
        if ($tutor && password_verify($password, $tutor['password'])) {
            $_SESSION['user_id'] = $tutor['tutor_id'];
            $_SESSION['user_role'] = 'tutor';
            $_SESSION['user_name'] = $tutor['first_name'] . ' ' . $tutor['last_name'];
            $_SESSION['user_number'] = $tutor['tutor_number'];
            setFlashMessage('success', 'Welcome back, ' . $tutor['first_name'] . '!');
            redirect('tutor/dashboard.php');
        }
        
        // If no match found
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UniTutor</title>
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
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 4rem; margin-bottom: 4rem;">
        <div class="card" style="max-width: 500px; margin: 0 auto;">
            <div class="card-header">
                <h2>Login</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <?php showError($error); ?>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </form>
                
                <p style="margin-top: 1.5rem; text-align: center;">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </div>
        </div>
        
        <div class="card" style="max-width: 500px; margin: 2rem auto;">
            <div class="card-body">
                <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Test Accounts</h3>
                <p style="margin-bottom: 0.5rem;"><strong>Admin:</strong> admin@unitutor.test / Admin123</p>
                <p style="margin-bottom: 0.5rem;"><strong>Student:</strong> student@unitutor.test / Student123</p>
                <p><strong>Tutor:</strong> tutor@unitutor.test / Tutor123</p>
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
