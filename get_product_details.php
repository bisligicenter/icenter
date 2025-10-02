<?php
require_once 'db.php';
require_once 'functions.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();
    
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Product ID is required');
    }
    
    $productId = $_GET['id'];
    
    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? AND (archived IS NULL OR archived = 0)");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Product not found');
    }

    // Ensure image_path is always set (fallback to null if missing)
    if (!isset($product['image_path']) || empty($product['image_path'])) {
        $product['image_path'] = null;
    }
    // Optionally, set default for description and category
    if (!isset($product['description'])) {
        $product['description'] = '';
    }
    if (!isset($product['category'])) {
        $product['category'] = '';
    }

    echo json_encode($product);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}