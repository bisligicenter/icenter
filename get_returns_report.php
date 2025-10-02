<?php
session_start();

// Check if user is logged in (either admin or staff)
if ((!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) && 
    (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');
require_once 'db.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT return_id, product_id, product_name, brand, model, storage, reason, returned_by, returned_to, customer_name, contact_number, return_date FROM returns ORDER BY return_date DESC");
    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $returns]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 