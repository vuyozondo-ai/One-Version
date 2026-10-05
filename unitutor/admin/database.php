<?php
/**
 * Database Management
 * UniTutor - University Peer Tutoring Management System
 * 
 * Database table management for administrators
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';
$selected_table = $_GET['table'] ?? '';

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// Get table record counts
$table_counts = [];
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $table_counts[$table] = $count;
}

// Get table data if selected
$table_data = [];
$table_columns = [];
if ($selected_table && in_array($selected_table, $tables)) {
    $table_columns = $pdo->query("SHOW COLUMNS FROM `$selected_table`")->fetchAll(PDO::FETCH_COLUMN);
    $table_data = $pdo->query("SELECT * FROM `$selected_table` LIMIT 50")->fetchAll();
}

// Handle table deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_table') {
    $table_to_delete = sanitize($_POST['table_name'] ?? '');
    if ($table_to_delete && in_array($table_to_delete, $tables)) {
        try {
            $pdo->exec("DROP TABLE `$table_to_delete`");
            $message = "Table '$table_to_delete' deleted successfully.";
            header("Location: database.php");
            exit();
        } catch (PDOException $e) {
            $message = "Error deleting table: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Database Management</h1>
                    <p>View and manage database tables</p>
                </div>
                
                <?php if ($message): ?>
                    <?php if (strpos($message, 'successfully') !== false): ?>
                        <?php showSuccess($message); ?>
                    <?php else: ?>
                        <?php showError($message); ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Tables Overview -->
                <div class="card">
                    <div class="card-header">
                        <h2>Database Tables (<?php echo count($tables); ?> tables)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Records</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tables as $table): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($table); ?></code></td>
                                        <td><?php echo number_format($table_counts[$table]); ?></td>
                                        <td>
                                            <a href="database.php?table=<?php echo htmlspecialchars($table); ?>" class="btn btn-sm btn-primary">View Data</a>
                                            <?php if (!in_array($table, ['admins', 'students', 'tutors', 'departments', 'programs', 'modules', 'bookings', 'sessions', 'feedback', 'payments', 'applications', 'available_slots', 'tutor_modules'])): ?>
                                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this table? This action cannot be undone.');">
                                                    <input type="hidden" name="action" value="delete_table">
                                                    <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($table); ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php if ($selected_table): ?>
                    <!-- Table Data -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Table: <?php echo htmlspecialchars($selected_table); ?> (<?php echo count($table_data); ?> records shown)</h2>
                            <a href="database.php" class="btn btn-sm btn-secondary">Back to Tables</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($table_data)): ?>
                                <p>This table is empty.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table>
                                        <thead>
                                            <tr>
                                                <?php foreach ($table_columns as $column): ?>
                                                    <th><?php echo htmlspecialchars($column); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($table_data as $row): ?>
                                            <tr>
                                                <?php foreach ($table_columns as $column): ?>
                                                    <td><?php echo htmlspecialchars($row[$column] ?? ''); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p style="margin-top: 1rem; color: var(--text-muted);">Showing first 50 records.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Database Information -->
                <div class="card">
                    <div class="card-header">
                        <h2>Database Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Database Name</label>
                                <input type="text" class="form-control" value="unitutor_db" readonly>
                            </div>
                            <div class="form-group">
                                <label>Total Tables</label>
                                <input type="text" class="form-control" value="<?php echo count($tables); ?>" readonly>
                            </div>
                        </div>
                        
                        <h3 style="color: var(--primary-color); margin: 1.5rem 0 1rem;">Important Notes</h3>
                        <ul style="color: var(--text-muted); line-height: 1.8;">
                            <li>This interface allows you to view database table structures and data.</li>
                            <li>Core system tables (students, tutors, bookings, etc.) cannot be deleted for safety.</li>
                            <li>Only custom tables can be deleted through this interface.</li>
                            <li>For full database management, use phpMyAdmin or MySQL Workbench.</li>
                            <li>Always backup your database before making structural changes.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
