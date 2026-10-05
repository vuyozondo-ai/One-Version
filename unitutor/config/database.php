<?php
/**
 * Database Configuration File
 * UniTutor - University Peer Tutoring Management System
 * 
 * This file handles the PDO database connection for the entire application.
 * All database queries should use this connection with prepared statements.
 */

// Database configuration settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'unitutor_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create PDO database connection
 * Uses PDO for secure database operations with prepared statements
 */
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Display error if connection fails
    die("Database Connection Failed: " . $e->getMessage());
}
?>
