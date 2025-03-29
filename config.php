<?php
// config.php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'blog_cms');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');

// Base URL for the app (adjust as needed)
define('BASE_URL', 'http://localhost/blog-cms/');

// Set default timezone
date_default_timezone_set('UTC');

// Create a PDO instance
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database " . DB_NAME . ": " . $e->getMessage());
}
?>
