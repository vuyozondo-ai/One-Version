<?php
/**
 * Departments Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * CRUD operations for departments
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
        $department_name = sanitize($_POST['department_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $admin_id = intval($_POST['admin_id'] ?? 0);
        
        if (empty($department_name)) {
            $message = 'Please enter department name.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO departments (department_name, description, admin_id) VALUES (?, ?, ?)");
                $stmt->execute([$department_name, $description, $admin_id ?: null]);
                $message = 'Department added successfully.';
            } catch (PDOException $e) {
                $message = 'Error adding department. Department name may already exist.';
            }
        }
    } elseif ($action === 'edit') {
        $department_id = intval($_POST['department_id'] ?? 0);
        $department_name = sanitize($_POST['department_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $admin_id = intval($_POST['admin_id'] ?? 0);
        
        if ($department_id === 0 || empty($department_name)) {
            $message = 'Please enter department name.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, description = ?, admin_id = ? WHERE department_id = ?");
                $stmt->execute([$department_name, $description, $admin_id ?: null, $department_id]);
                $message = 'Department updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating department.';
            }
        }
    } elseif ($action === 'delete') {
        $department_id = intval($_POST['department_id'] ?? 0);
        if ($department_id === 0) {
            $message = 'Invalid department ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM departments WHERE department_id = ?");
                $stmt->execute([$department_id]);
                $message = 'Department deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting department. Department may have related records.';
            }
        }
    }
}

// Get departments
$departments = $pdo->query("
    SELECT d.*, a.first_name as admin_first_name, a.last_name as admin_last_name,
    (SELECT COUNT(*) FROM programs WHERE department_id = d.department_id) as program_count
    FROM departments d
    LEFT JOIN admins a ON d.admin_id = a.admin_id
    ORDER BY d.department_name
")->fetchAll();

// Get admins for dropdown
$admins = $pdo->query("SELECT admin_id, CONCAT(first_name, ' ', last_name) as name FROM admins ORDER BY first_name")->fetchAll();

// Get department for editing
$edit_department = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_department = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Departments</h1>
                    <p>View, add, edit, and delete department records</p>
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
                        <h2><?php echo $edit_department ? 'Edit Department' : 'Add New Department'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_department): ?>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="department_id" value="<?php echo $edit_department['department_id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="action" value="add">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="department_name">Department Name</label>
                                <input type="text" id="department_name" name="department_name" class="form-control" required value="<?php echo $edit_department ? htmlspecialchars($edit_department['department_name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_department ? htmlspecialchars($edit_department['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_id">Assigned Admin</label>
                                <select id="admin_id" name="admin_id" class="form-control">
                                    <option value="">No Admin Assigned</option>
                                    <?php foreach ($admins as $admin): ?>
                                        <option value="<?php echo $admin['admin_id']; ?>" <?php echo $edit_department && $edit_department['admin_id'] == $admin['admin_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($admin['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><?php echo $edit_department ? 'Update Department' : 'Add Department'; ?></button>
                            <?php if ($edit_department): ?>
                                <a href="departments.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Departments Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Departments List (<?php echo count($departments); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Department Name</th>
                                        <th>Description</th>
                                        <th>Assigned Admin</th>
                                        <th>Programs</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dept['department_name']); ?></td>
                                        <td><?php echo truncateText(htmlspecialchars($dept['description'] ?? 'N/A'), 50); ?></td>
                                        <td><?php echo htmlspecialchars($dept['admin_first_name'] . ' ' . $dept['admin_last_name'] ?? 'Not assigned'); ?></td>
                                        <td><?php echo $dept['program_count']; ?></td>
                                        <td>
                                            <a href="departments.php?edit=<?php echo $dept['department_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this department?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="department_id" value="<?php echo $dept['department_id']; ?>">
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
