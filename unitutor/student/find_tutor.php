<?php
/**
 * Find Tutor Page
 * UniTutor - University Peer Tutoring Management System
 * 
 * Allows students to search and find tutors
 */
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$search = $_GET['search'] ?? '';
$module_filter = $_GET['module'] ?? '';
$department_filter = $_GET['department'] ?? '';

// Build query
$query = "
    SELECT DISTINCT t.*, 
    (SELECT AVG(rating) FROM feedback WHERE tutor_id = t.tutor_id) as avg_rating,
    (SELECT COUNT(*) FROM sessions s JOIN bookings b ON s.booking_id = b.booking_id WHERE b.tutor_id = t.tutor_id AND s.status = 'completed') as session_count
    FROM tutors t
    JOIN tutor_modules tm ON t.tutor_id = tm.tutor_id
    JOIN modules m ON tm.module_id = m.module_id
    JOIN programs p ON m.program_id = p.program_id
    JOIN departments d ON p.department_id = d.department_id
    WHERE t.status = 'active'
";

$params = [];

if ($search) {
    $query .= " AND (t.first_name LIKE ? OR t.last_name LIKE ? OR m.module_name LIKE ? OR m.module_code LIKE ?)";
    $searchParam = "%$search%";
    $params = array_fill(0, 4, $searchParam);
}

if ($module_filter) {
    $query .= " AND m.module_id = ?";
    $params[] = $module_filter;
}

if ($department_filter) {
    $query .= " AND d.department_id = ?";
    $params[] = $department_filter;
}

$query .= " ORDER BY avg_rating DESC, t.last_name, t.first_name";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tutors = $stmt->fetchAll();

// Get modules for filter
$modules = $pdo->query("SELECT m.module_id, m.module_code, m.module_name FROM modules m ORDER BY m.module_name")->fetchAll();

// Get departments for filter
$departments = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Tutor - UniTutor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid">
                <div class="page-header">
                    <h1>Find a Tutor</h1>
                    <p>Search for peer tutors by module, name, or department</p>
                </div>
                
                <!-- Search and Filter -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" id="search" name="search" class="form-control" placeholder="Tutor name, module, or code..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="module">Module</label>
                                    <select id="module" name="module" class="form-control">
                                        <option value="">All Modules</option>
                                        <?php foreach ($modules as $module): ?>
                                            <option value="<?php echo $module['module_id']; ?>" <?php echo $module_filter == $module['module_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($module['module_code'] . ' - ' . $module['module_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select id="department" name="department" class="form-control">
                                        <option value="">All Departments</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['department_id']; ?>" <?php echo $department_filter == $dept['department_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept['department_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if ($search || $module_filter || $department_filter): ?>
                                <a href="find_tutor.php" class="btn btn-secondary">Clear Filters</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Tutor Results -->
                <div class="card">
                    <div class="card-header">
                        <h2>Available Tutors (<?php echo count($tutors); ?> found)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tutors)): ?>
                            <p>No tutors found matching your criteria. Try adjusting your search filters.</p>
                        <?php else: ?>
                            <div class="tutor-grid">
                                <?php foreach ($tutors as $tutor): ?>
                                    <?php
                                    // Get tutor's modules
                                    $stmt = $pdo->prepare("
                                        SELECT m.module_code, m.module_name, tm.proficiency_level
                                        FROM tutor_modules tm
                                        JOIN modules m ON tm.module_id = m.module_id
                                        WHERE tm.tutor_id = ?
                                    ");
                                    $stmt->execute([$tutor['tutor_id']]);
                                    $tutor_modules = $stmt->fetchAll();
                                    ?>
                                    
                                    <div class="tutor-card">
                                        <div class="tutor-card-header">
                                            <div class="tutor-avatar">
                                                <?php echo strtoupper(substr($tutor['first_name'], 0, 1) . substr($tutor['last_name'], 0, 1)); ?>
                                            </div>
                                            <div class="tutor-info">
                                                <h3><?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></h3>
                                                <p>Peer Tutor</p>
                                            </div>
                                        </div>
                                        
                                        <div class="tutor-card-body">
                                            <div class="tutor-rating">
                                                <span class="stars"><?php echo generateStarRating(round($tutor['avg_rating'])); ?></span>
                                                <span><?php echo number_format($tutor['avg_rating'], 1); ?>/5</span>
                                                <span style="color: var(--text-muted);">(<?php echo $tutor['session_count']; ?> sessions)</span>
                                            </div>
                                            
                                            <p><strong>Modules:</strong></p>
                                            <div class="tutor-modules">
                                                <?php foreach ($tutor_modules as $tm): ?>
                                                    <span class="module-tag"><?php echo htmlspecialchars($tm['module_code']); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="tutor-card-footer">
                                            <a href="tutor_profile.php?tutor_id=<?php echo $tutor['tutor_id']; ?>" class="btn btn-primary" style="width: 100%;">View Profile</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
