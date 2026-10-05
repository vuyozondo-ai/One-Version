<?php
/**
 * Tutor Modules Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * View modules assigned to tutor
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('tutor');

$tutor_id = getCurrentUserId();

// Get tutor's modules
$stmt = $pdo->prepare("
    SELECT tm.*, m.module_code, m.module_name, p.program_name, d.department_name
    FROM tutor_modules tm
    JOIN modules m ON tm.module_id = m.module_id
    JOIN programs p ON m.program_id = p.program_id
    JOIN departments d ON p.department_id = d.department_id
    WHERE tm.tutor_id = ?
    ORDER BY d.department_name, p.program_name, m.module_code
");
$stmt->execute([$tutor_id]);
$tutor_modules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Modules - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>My Modules</h1>
                    <p>View the modules you are assigned to tutor</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Assigned Modules (<?php echo count($tutor_modules); ?>)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tutor_modules)): ?>
                            <p>You are not assigned to any modules yet. Please contact an administrator.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Module Code</th>
                                            <th>Module Name</th>
                                            <th>Department</th>
                                            <th>Program</th>
                                            <th>Proficiency Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tutor_modules as $tm): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tm['module_code']); ?></td>
                                            <td><?php echo htmlspecialchars($tm['module_name']); ?></td>
                                            <td><?php echo htmlspecialchars($tm['department_name']); ?></td>
                                            <td><?php echo htmlspecialchars($tm['program_name']); ?></td>
                                            <td><span class="badge badge-info"><?php echo ucfirst($tm['proficiency_level']); ?></span></td>
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
