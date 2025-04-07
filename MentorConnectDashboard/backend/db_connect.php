<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';  // Default XAMPP username
$db_pass = '';      // Default XAMPP password
$db_name = 'mentorship_db';

// First, connect without selecting a database
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($db_name);
    
    // Create tables if they don't exist
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        user_type ENUM('mentor', 'mentee') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS profiles (
        user_id INT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        headline VARCHAR(255),
        bio TEXT,
        industry VARCHAR(255),
        skills_or_needs TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->query($sql);
} else {
    die("Error creating database: " . $conn->error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to safely close the database connection
function closeConnection() {
    global $conn;
    if (isset($conn)) {
        $conn->close();
    }
}

// Register the closeConnection function to be called when the script ends
register_shutdown_function('closeConnection');
?> 