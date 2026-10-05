<?php
/**
 * Modules Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * CRUD operations for modules
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
        $module_code = sanitize($_POST['module_code'] ?? '');
        $module_name = sanitize($_POST['module_name'] ?? '');
        $program_id = intval($_POST['program_id'] ?? 0);
        
        if (empty($module_code) || empty($module_name) || $program_id === 0) {
            $message = 'Please fill in all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO modules (module_code, module_name, program_id) VALUES (?, ?, ?)");
                $stmt->execute([$module_code, $module_name, $program_id]);
                $message = 'Module added successfully.';
            } catch (PDOException $e) {
                $message = 'Error adding module. Module code may already exist.';
            }
        }
    } elseif ($action === 'edit') {
        $module_id = intval($_POST['module_id'] ?? 0);
        $module_code = sanitize($_POST['module_code'] ?? '');
        $module_name = sanitize($_POST['module_name'] ?? '');
        $program_id = intval($_POST['program_id'] ?? 0);
        
        if ($module_id === 0 || empty($module_code) || empty($module_name) || $program_id === 0) {
            $message = 'Please fill in all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE modules SET module_code = ?, module_name = ?, program_id = ? WHERE module_id = ?");
                $stmt->execute([$module_code, $module_name, $program_id, $module_id]);
                $message = 'Module updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating module.';
            }
        }
    } elseif ($action === 'delete') {
        $module_id = intval($_POST['module_id'] ?? 0);
        if ($module_id === 0) {
            $message = 'Invalid module ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM modules WHERE module_id = ?");
                $stmt->execute([$module_id]);
                $message = 'Module deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting module. Module may have related records.';
            }
        }
    }
}

// Get modules
$modules = $pdo->query("
    SELECT m.*, p.program_name, d.department_name,
    (SELECT COUNT(*) FROM tutor_modules WHERE module_id = m.module_id) as tutor_count
    FROM modules m
    JOIN programs p ON m.program_id = p.program_id
    JOIN departments d ON p.department_id = d.department_id
    ORDER BY d.department_name, p.program_name, m.module_code
")->fetchAll();

// Get programs for dropdown
$programs = $pdo->query("
    SELECT p.program_id, p.program_name, d.department_name 
    FROM programs p 
    JOIN departments d ON p.department_id = d.department_id 
    ORDER BY d.department_name, p.program_name
")->fetchAll();

// Get module for editing
$edit_module = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE module_id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_module = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Modules - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Modules</h1>
                    <p>View, add, edit, and delete module records</p>
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
                        <h2><?php echo $edit_module ? 'Edit Module' : 'Add New Module'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_module): ?>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="module_id" value="<?php echo $edit_module['module_id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="action" value="add">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="module_code">Module Code</label>
                                    <input type="text" id="module_code" name="module_code" class="form-control" required value="<?php echo $edit_module ? htmlspecialchars($edit_module['module_code']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="program_id">Program</label>
                                    <select id="program_id" name="program_id" class="form-control" required>
                                        <option value="">Select Program</option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?php echo $program['program_id']; ?>" <?php echo $edit_module && $edit_module['program_id'] == $program['program_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($program['department_name'] . ' - ' . $program['program_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="module_name">Module Name</label>
                                <input type="text" id="module_name" name="module_name" class="form-control" required value="<?php echo $edit_module ? htmlspecialchars($edit_module['module_name']) : ''; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><?php echo $edit_module ? 'Update Module' : 'Add Module'; ?></button>
                            <?php if ($edit_module): ?>
                                <a href="modules.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Modules Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Modules List (<?php echo count($modules); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Module Code</th>
                                        <th>Module Name</th>
                                        <th>Program</th>
                                        <th>Department</th>
                                        <th>Tutors</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $module): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($module['module_code']); ?></td>
                                        <td><?php echo htmlspecialchars($module['module_name']); ?></td>
                                        <td><?php echo htmlspecialchars($module['program_name']); ?></td>
                                        <td><?php echo htmlspecialchars($module['department_name']); ?></td>
                                        <td><?php echo $module['tutor_count']; ?></td>
                                        <td>
                                            <a href="modules.php?edit=<?php echo $module['module_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this module?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="module_id" value="<?php echo $module['module_id']; ?>">
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
