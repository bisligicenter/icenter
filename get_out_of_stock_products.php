<?php
session_start();

// Check if user is logged in (either admin or staff)
if ((!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) && 
    (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'db.php';

try {
    $pdo = getConnection();
    
    // Get products with 0 items in stock
    $query = "SELECT product_id, product as product_name, brand, model, storage, stock_quantity,
                     DATE_FORMAT(NOW(), '%Y-%m-%d') as date_out_of_stock,
                     DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s') as last_updated
              FROM products 
              WHERE (archived IS NULL OR archived = 0) 
              AND stock_quantity = 0 
              ORDER BY product ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $outOfStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['data' => $outOfStockProducts]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 