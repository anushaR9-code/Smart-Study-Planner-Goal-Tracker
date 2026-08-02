<?php
/**
 * StudySync - Database Connection
 * Uses PDO with prepared statements for security.
 * Update credentials below to match your XAMPP MySQL setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'studysync');
define('DB_USER', 'root');
define('DB_PASS', '');       // default XAMPP password is empty

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
