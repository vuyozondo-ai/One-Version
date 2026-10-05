<?php
/**
 * Landing Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Public landing page with information about the system
 */
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get statistics from database
try {
    $stats = [
        'students' => $pdo->query("SELECT COUNT(*) as count FROM students")->fetch()['count'],
        'tutors' => $pdo->query("SELECT COUNT(*) as count FROM tutors")->fetch()['count'],
        'modules' => $pdo->query("SELECT COUNT(*) as count FROM modules")->fetch()['count'],
        'sessions' => $pdo->query("SELECT COUNT(*) as count FROM sessions")->fetch()['count']
    ];
} catch (PDOException $e) {
    // Use default values if database query fails
    $stats = [
        'students' => 500,
        'tutors' => 100,
        'modules' => 50,
        'sessions' => 1000
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTutor - University Peer Tutoring Management System</title>
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
                <li><a href="#about">About</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#find-tutor">Find a Tutor</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn btn-primary">Register</a></li>
            </ul>
            
            <div class="mobile-menu-toggle">
                <button class="btn btn-icon" onclick="toggleMobileMenu()">☰</button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Learn Better. Together.</h1>
            <p>Connect with qualified peer tutors from your university and get support in the modules that matter.</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-lg">Find a Tutor</a>
                <a href="register.php" class="btn btn-outline btn-lg">Become a Tutor</a>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section>
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['students']; ?>+</h3>
                    <p>Students</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['tutors']; ?>+</h3>
                    <p>Tutors</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['modules']; ?>+</h3>
                    <p>Modules</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['sessions']; ?>+</h3>
                    <p>Sessions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <div class="container">
            <div class="section-header">
                <h2>About UniTutor</h2>
                <p>Your university's peer tutoring management system designed to help students succeed academically through peer support.</p>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <p>UniTutor is a comprehensive peer tutoring platform that connects students with qualified peer tutors for personalized academic support. Our system makes it easy to find tutors, book sessions, and track your learning progress.</p>
                    <br>
                    <p><strong>For Students:</strong> Find tutors for your modules, book convenient time slots, attend sessions, and provide feedback to help improve the tutoring experience.</p>
                    <br>
                    <p><strong>For Tutors:</strong> Share your knowledge, manage your availability, confirm bookings, and track your tutoring sessions and ratings.</p>
                    <br>
                    <p><strong>For Administrators:</strong> Manage the entire tutoring system, monitor performance, generate reports, and ensure smooth operations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>How UniTutor Works</h2>
                <p>Getting started with peer tutoring is simple. Follow these steps to begin your learning journey.</p>
            </div>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Find a Tutor</h3>
                    <p>Search for tutors by module, department, or availability.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Select a Module</h3>
                    <p>Choose the module you need help with from available options.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Choose a Slot</h3>
                    <p>Select an available time slot that fits your schedule.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Book a Session</h3>
                    <p>Submit your booking request with your learning goals.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">5</div>
                    <h3>Attend Session</h3>
                    <p>Meet with your tutor at the scheduled time and location.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">6</div>
                    <h3>Give Feedback</h3>
                    <p>Rate your session and provide feedback to help improve.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Find a Tutor Section -->
    <section id="find-tutor">
        <div class="container">
            <div class="section-header">
                <h2>Find a Tutor</h2>
                <p>Browse our available peer tutors and find the perfect match for your learning needs.</p>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <p style="margin-bottom: 1.5rem; font-size: 1.1rem;">Ready to find a tutor? Login to access our full tutor directory and book sessions.</p>
                    <a href="login.php" class="btn btn-primary btn-lg">Login to Find Tutors</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> UniTutor - University Peer Tutoring Management System</p>
            <p>A Database Systems Project</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
