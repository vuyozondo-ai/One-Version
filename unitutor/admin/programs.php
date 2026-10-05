<?php
/**
 * Programs Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * CRUD operations for programs
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $program_name = sanitize($_POST['program_name'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        
        if (empty($program_name) || $department_id === 0) {
            $message = 'Please fill in all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO programs (program_name, department_id) VALUES (?, ?)");
                $stmt->execute([$program_name, $department_id]);
                $message = 'Program added successfully.';
            } catch (PDOException $e) {
                $message = 'Error adding program.';
            }
        }
    } elseif ($action === 'edit') {
        $program_id = intval($_POST['program_id'] ?? 0);
        $program_name = sanitize($_POST['program_name'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        
        if ($program_id === 0 || empty($program_name) || $department_id === 0) {
            $message = 'Please fill in all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE programs SET program_name = ?, department_id = ? WHERE program_id = ?");
                $stmt->execute([$program_name, $department_id, $program_id]);
                $message = 'Program updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating program.';
            }
        }
    } elseif ($action === 'delete') {
        $program_id = intval($_POST['program_id'] ?? 0);
        if ($program_id === 0) {
            $message = 'Invalid program ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM programs WHERE program_id = ?");
                $stmt->execute([$program_id]);
                $message = 'Program deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting program. Program may have related records.';
            }
        }
    }
}

// Get programs
$programs = $pdo->query("
    SELECT p.*, d.department_name,
    (SELECT COUNT(*) FROM modules WHERE program_id = p.program_id) as module_count,
    (SELECT COUNT(*) FROM students WHERE program_id = p.program_id) as student_count
    FROM programs p
    JOIN departments d ON p.department_id = d.department_id
    ORDER BY d.department_name, p.program_name
")->fetchAll();

// Get departments for dropdown
$departments = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();

// Get program for editing
$edit_program = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE program_id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_program = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Programs</h1>
                    <p>View, add, edit, and delete program records</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Add/Edit Form -->
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo $edit_program ? 'Edit Program' : 'Add New Program'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_program): ?>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="program_id" value="<?php echo $edit_program['program_id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="action" value="add">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="program_name">Program Name</label>
                                <input type="text" id="program_name" name="program_name" class="form-control" required value="<?php echo $edit_program ? htmlspecialchars($edit_program['program_name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="department_id">Department</label>
                                <select id="department_id" name="department_id" class="form-control" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>" <?php echo $edit_program && $edit_program['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><?php echo $edit_program ? 'Update Program' : 'Add Program'; ?></button>
                            <?php if ($edit_program): ?>
                                <a href="programs.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Programs Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Programs List (<?php echo count($programs); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Department</th>
                                        <th>Modules</th>
                                        <th>Students</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($programs as $program): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($program['program_name']); ?></td>
                                        <td><?php echo htmlspecialchars($program['department_name']); ?></td>
                                        <td><?php echo $program['module_count']; ?></td>
                                        <td><?php echo $program['student_count']; ?></td>
                                        <td>
                                            <a href="programs.php?edit=<?php echo $program['program_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this program?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="program_id" value="<?php echo $program['program_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
