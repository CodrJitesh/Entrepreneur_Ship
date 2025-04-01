<?php
$host = 'localhost';
$dbname = 'mentorconnect_dashboard_db';
$username = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password (empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>