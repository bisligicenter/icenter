<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $pdo = getConnection();
    // A product is considered "low stock" if its quantity is between 1 and 5.
    $query = "SELECT product_id, product, brand, model, storage, stock_quantity 
              FROM products 
              WHERE stock_quantity > 0 AND stock_quantity <= 5 AND (archived IS NULL OR archived = 0) 
              ORDER BY stock_quantity ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>