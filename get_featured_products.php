<?php
// Function to get featured products
function getFeaturedProducts($limit = 6) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE is_featured = 1 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getFeaturedProducts: " . $e->getMessage());
        return [];
    }
}

// Function to get latest products
function getLatestProducts($limit = 6) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getLatestProducts: " . $e->getMessage());
        return [];
    }
}

// Function to get best-selling products
function getBestSellingProducts($limit = 6) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, COUNT(oi.product_id) as order_count 
            FROM products p 
            LEFT JOIN order_items oi ON p.id = oi.product_id 
            GROUP BY p.id 
            ORDER BY order_count DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getBestSellingProducts: " . $e->getMessage());
        return [];
    }
}
?> 