<?php
require_once 'db.php';

try {
    $stmt = $conn->query("SELECT COUNT(*) AS count FROM reservations WHERE status = 'pending'");
    $count = $stmt->fetchColumn();
    echo json_encode(['count' => (int)$count]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching reservation count']);
}
?>