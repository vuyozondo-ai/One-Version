<?php
/**
 * Tutors Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * CRUD operations for tutors
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';
$search = $_GET['search'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $tutor_number = sanitize($_POST['tutor_number'] ?? '');
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = sanitize($_POST['phone'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');
        
        if (empty($tutor_number) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            $message = 'Please fill in all required fields.';
        } elseif (!validateEmail($email)) {
            $message = 'Please enter a valid email address.';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO tutors (tutor_number, first_name, last_name, email, password, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$tutor_number, $first_name, $last_name, $email, $hashed_password, $phone, $status]);
                $message = 'Tutor added successfully.';
            } catch (PDOException $e) {
                $message = 'Error adding tutor. Tutor number or email may already exist.';
            }
        }
    } elseif ($action === 'edit') {
        $tutor_id = intval($_POST['tutor_id'] ?? 0);
        $tutor_number = sanitize($_POST['tutor_number'] ?? '');
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');
        $password = $_POST['password'] ?? '';
        
        if ($tutor_id === 0 || empty($tutor_number) || empty($first_name) || empty($last_name) || empty($email)) {
            $message = 'Please fill in all required fields.';
        } elseif (!validateEmail($email)) {
            $message = 'Please enter a valid email address.';
        } else {
            try {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE tutors SET tutor_number = ?, first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, status = ? WHERE tutor_id = ?");
                    $stmt->execute([$tutor_number, $first_name, $last_name, $email, $hashed_password, $phone, $status, $tutor_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE tutors SET tutor_number = ?, first_name = ?, last_name = ?, email = ?, phone = ?, status = ? WHERE tutor_id = ?");
                    $stmt->execute([$tutor_number, $first_name, $last_name, $email, $phone, $status, $tutor_id]);
                }
                $message = 'Tutor updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating tutor.';
            }
        }
    } elseif ($action === 'delete') {
        $tutor_id = intval($_POST['tutor_id'] ?? 0);
        if ($tutor_id === 0) {
            $message = 'Invalid tutor ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM tutors WHERE tutor_id = ?");
                $stmt->execute([$tutor_id]);
                $message = 'Tutor deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting tutor. Tutor may have related records.';
            }
        }
    }
}

// Get tutors with search
if ($search) {
    $stmt = $pdo->prepare("
        SELECT * FROM tutors 
        WHERE tutor_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?
        ORDER BY last_name, first_name
    ");
    $searchParam = "%$search%";
    $stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam]);
} else {
    $stmt = $pdo->query("SELECT * FROM tutors ORDER BY last_name, first_name");
}
$tutors = $stmt->fetchAll();

// Get tutor for editing
$edit_tutor = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tutors WHERE tutor_id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_tutor = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tutors - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Tutors</h1>
                    <p>View, add, edit, and delete tutor records</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (_strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <form method="GET" action="" style="display: flex; gap: 1rem; width: 100%;">
                        <input type="text" name="search" class="form-control" placeholder="Search by tutor number, name, or email..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search): ?>
                            <a href="tutors.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Add/Edit Form -->
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo $edit_tutor ? 'Edit Tutor' : 'Add New Tutor'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_tutor): ?>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="tutor_id" value="<?php echo $edit_tutor['tutor_id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="action" value="add">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="tutor_number">Tutor Number</label>
                                    <input type="text" id="tutor_number" name="tutor_number" class="form-control" required value="<?php echo $edit_tutor ? htmlspecialchars($edit_tutor['tutor_number']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="form-control" required>
                                        <option value="active" <?php echo $edit_tutor && $edit_tutor['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $edit_tutor && $edit_tutor['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required value="<?php echo $edit_tutor ? htmlspecialchars($edit_tutor['first_name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required value="<?php echo $edit_tutor ? htmlspecialchars($edit_tutor['last_name']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required value="<?php echo $edit_tutor ? htmlspecialchars($edit_tutor['email']) : ''; ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $edit_tutor ? htmlspecialchars($edit_tutor['phone']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">Password <?php echo $edit_tutor ? '(leave blank to keep current)' : ''; ?></label>
                                    <input type="password" id="password" name="password" class="form-control" <?php echo $edit_tutor ? '' : 'required'; ?> minlength="6">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><?php echo $edit_tutor ? 'Update Tutor' : 'Add Tutor'; ?></button>
                            <?php if ($edit_tutor): ?>
                                <a href="tutors.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Tutors Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Tutors List (<?php echo count($tutors); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tutor Number</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tutors as $tutor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tutor['tutor_number']); ?></td>
                                        <td><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($tutor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($tutor['phone'] ?? 'N/A'); ?></td>
                                        <td><span class="badge <?php echo $tutor['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo ucfirst($tutor['status']); ?></span></td>
                                        <td>
                                            <a href="tutors.php?edit=<?php echo $tutor['tutor_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this tutor?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="tutor_id" value="<?php echo $tutor['tutor_id']; ?>">
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
