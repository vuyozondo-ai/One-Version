<?php
/**
 * Admin Reports Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Main reports page for administrators
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Reports</h1>
                    <p>View and generate system reports</p>
                </div>
                
                <div class="stats-grid">
                    <div class="card" style="text-align: center; cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-body">
                            <h2 style="color: var(--primary-color); margin-bottom: 1rem;">📊</h2>
                            <h3 style="margin-bottom: 0.5rem;">Student Activity</h3>
                            <p style="color: var(--text-muted);">View student tutoring activity and statistics</p>
                            <a href="../reports/student_activity.php" class="btn btn-primary" style="margin-top: 1rem;">View Report</a>
                        </div>
                    </div>
                    
                    <div class="card" style="text-align: center; cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-body">
                            <h2 style="color: var(--primary-color); margin-bottom: 1rem;">👨‍🏫</h2>
                            <h3 style="margin-bottom: 0.5rem;">Tutor Performance</h3>
                            <p style="color: var(--text-muted);">View tutor performance and ratings</p>
                            <a href="../reports/tutor_performance.php" class="btn btn-primary" style="margin-top: 1rem;">View Report</a>
                        </div>
                    </div>
                    
                    <div class="card" style="text-align: center; cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-body">
                            <h2 style="color: var(--primary-color); margin-bottom: 1rem;">📚</h2>
                            <h3 style="margin-bottom: 0.5rem;">Module Demand</h3>
                            <p style="color: var(--text-muted);">View module demand and popularity</p>
                            <a href="../reports/module_demand.php" class="btn btn-primary" style="margin-top: 1rem;">View Report</a>
                        </div>
                    </div>
                    
                    <div class="card" style="text-align: center; cursor: pointer; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="card-body">
                            <h2 style="color: var(--primary-color); margin-bottom: 1rem;">📅</h2>
                            <h3 style="margin-bottom: 0.5rem;">Booking Report</h3>
                            <p style="color: var(--text-muted);">View detailed booking information</p>
                            <a href="../reports/booking_report.php" class="btn btn-primary" style="margin-top: 1rem;">View Report</a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Report Information</h2>
                    </div>
                    <div class="card-body">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Available Reports</h3>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <h4 style="margin-bottom: 0.5rem;">1. Student Activity Report</h4>
                            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Shows the number of bookings, completed sessions, and cancelled sessions for each student.</p>
                            <a href="../reports/student_activity.php" class="btn btn-sm btn-primary">Generate</a>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <h4 style="margin-bottom: 0.5rem;">2. Tutor Performance Report</h4>
                            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Shows the number of sessions, completed sessions, average rating, and number of students helped for each tutor.</p>
                            <a href="../reports/tutor_performance.php" class="btn btn-sm btn-primary">Generate</a>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <h4 style="margin-bottom: 0.5rem;">3. Module Demand Report</h4>
                            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Shows the number of bookings, completed sessions, and students requesting tutoring for each module, sorted by demand.</p>
                            <a href="../reports/module_demand.php" class="btn btn-sm btn-primary">Generate</a>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <h4 style="margin-bottom: 0.5rem;">4. Booking Report</h4>
                            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Shows detailed booking information with filtering options by date, module, tutor, and status.</p>
                            <a href="../reports/booking_report.php" class="btn btn-sm btn-primary">Generate</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
