<?php
/**
 * Students Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * CRUD operations for students
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';
$search = $_GET['search'] ?? '';

// Handle form submissions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $student_number = sanitize($_POST['student_number'] ?? '');
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $program_id = intval($_POST['program_id'] ?? 0);
        
        if (empty($student_number) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            $message = 'Please fill in all required fields.';
        } elseif (!validateEmail($email)) {
            $message = 'Please enter a valid email address.';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO students (student_number, first_name, last_name, email, password, program_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$student_number, $first_name, $last_name, $email, $hashed_password, $program_id]);
                $message = 'Student added successfully.';
            } catch (PDOException $e) {
                $message = 'Error adding student. Student number or email may already exist.';
            }
        }
    } elseif ($action === 'edit') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $student_number = sanitize($_POST['student_number'] ?? '');
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $program_id = intval($_POST['program_id'] ?? 0);
        $password = $_POST['password'] ?? '';
        
        if ($student_id === 0 || empty($student_number) || empty($first_name) || empty($last_name) || empty($email)) {
            $message = 'Please fill in all required fields.';
        } elseif (!validateEmail($email)) {
            $message = 'Please enter a valid email address.';
        } else {
            try {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE students SET student_number = ?, first_name = ?, last_name = ?, email = ?, password = ?, program_id = ? WHERE student_id = ?");
                    $stmt->execute([$student_number, $first_name, $last_name, $email, $hashed_password, $program_id, $student_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE students SET student_number = ?, first_name = ?, last_name = ?, email = ?, program_id = ? WHERE student_id = ?");
                    $stmt->execute([$student_number, $first_name, $last_name, $email, $program_id, $student_id]);
                }
                $message = 'Student updated successfully.';
            } catch (PDOException $e) {
                $message = 'Error updating student.';
            }
        }
    } elseif ($action === 'delete') {
        $student_id = intval($_POST['student_id'] ?? 0);
        if ($student_id === 0) {
            $message = 'Invalid student ID.';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
                $stmt->execute([$student_id]);
                $message = 'Student deleted successfully.';
            } catch (PDOException $e) {
                $message = 'Error deleting student. Student may have related records.';
            }
        }
    }
}

// Get students with search
if ($search) {
    $stmt = $pdo->prepare("
        SELECT s.*, p.program_name 
        FROM students s 
        LEFT JOIN programs p ON s.program_id = p.program_id 
        WHERE s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ?
        ORDER BY s.last_name, s.first_name
    ");
    $searchParam = "%$search%";
    $stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam]);
} else {
    $stmt = $pdo->query("
        SELECT s.*, p.program_name 
        FROM students s 
        LEFT JOIN programs p ON s.program_id = p.program_id 
        ORDER BY s.last_name, s.first_name
    ");
}
$students = $stmt->fetchAll();

// Get programs for dropdown
$programs = $pdo->query("SELECT program_id, program_name FROM programs ORDER BY program_name")->fetchAll();

// Get student for editing
$edit_student = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_student = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php 
        $role = 'admin';
        $active_page = 'students';
        include '../includes/sidebar.php'; 
        ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Manage Students</h1>
                    <p>View, add, edit, and delete student records</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <form method="GET" action="" style="display: flex; gap: 1rem; width: 100%;">
                        <input type="text" name="search" class="form-control" placeholder="Search by student number, name, or email..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($search): ?>
                            <a href="students.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Add/Edit Form -->
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_student): ?>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="student_id" value="<?php echo $edit_student['student_id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="action" value="add">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="student_number">Student Number</label>
                                    <input type="text" id="student_number" name="student_number" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['student_number']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="program_id">Program</label>
                                    <select id="program_id" name="program_id" class="form-control" required>
                                        <option value="">Select Program</option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?php echo $program['program_id']; ?>" <?php echo $edit_student && $edit_student['program_id'] == $program['program_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($program['program_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['first_name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['last_name']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required value="<?php echo $edit_student ? htmlspecialchars($edit_student['email']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password <?php echo $edit_student ? '(leave blank to keep current)' : ''; ?></label>
                                <input type="password" id="password" name="password" class="form-control" <?php echo $edit_student ? '' : 'required'; ?> minlength="6">
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><?php echo $edit_student ? 'Update Student' : 'Add Student'; ?></button>
                            <?php if ($edit_student): ?>
                                <a href="students.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Students Table -->
                <div class="card">
                    <div class="card-header">
                        <h2>Students List (<?php echo count($students); ?> records)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Number</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Program</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['program_name'] ?? 'Not assigned'); ?></td>
                                        <td>
                                            <a href="students.php?edit=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
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
