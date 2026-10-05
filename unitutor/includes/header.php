<?php
/**
 * Header File
 * UniTutor - University Peer Tutoring Management System
 * 
 * Contains the HTML head section and navigation
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTutor - University Peer Tutoring Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    <main>
        <?php displayFlashMessage(); ?>
