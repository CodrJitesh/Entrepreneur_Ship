<?php
header('Content-Type: application/json');
include 'connect.php';

try {
    $stmt = $pdo->query("SELECT * FROM mentors");
    $mentors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($mentors);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>