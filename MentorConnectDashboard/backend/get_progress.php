<?php
header('Content-Type: application/json');
include 'connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 1; // Default to user_id 1 for demo

try {
    $stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($progress);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>