<?php
/**
 * Database Connection
 * 
 * This file handles the database connection for the Anime Library application
 */

// Database configuration
$host = 'localhost';
$dbname = '24152367'; // Changed from anime_library
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// Connection options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Create a PDO instance
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        $options
    );

    // Test connection by running a simple query
    $pdo->query("SELECT 1");

} catch (PDOException $e) {
    // If there is an error with the connection, stop the script and display the error
    // Log the error to a file
    error_log("Database connection error: " . $e->getMessage(), 0);
    die("Database connection failed: " . $e->getMessage());
}