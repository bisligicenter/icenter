<?php
// This script fetches product codes for a given category
require_once 'db.php';
header('Content-Type: application/json');

// Check if category_id is provided
if (!isset($_GET['category_id']) || empty($_GET['category_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

$category_id = $_GET['category_id'];

try {
    // First, check if the products table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'products'");
    $tableExists = $tableCheck->rowCount() > 0;
    
    if (!$tableExists) {
        // Create products table if it doesn't exist
        $conn->exec("CREATE TABLE products (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            category_id INT(11) NOT NULL,
            product_code VARCHAR(50) NOT NULL UNIQUE,
            brand VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            storage VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            stock_quantity INT(11) NOT NULL DEFAULT 0,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            image1 VARCHAR(255) NULL,
            image2 VARCHAR(255) NULL,
            image3 VARCHAR(255) NULL,
            image4 VARCHAR(255) NULL,
            image5 VARCHAR(255) NULL,
            image6 VARCHAR(255) NULL,
            image7 VARCHAR(255) NULL,
            image8 VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        echo json_encode([]);
        exit;
    }
    
    // Query product codes by category
    $stmt = $conn->prepare("SELECT product_code, brand, model, storage FROM products WHERE category_id = ? ORDER BY product_code");
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>