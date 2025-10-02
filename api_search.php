<?php

require_once 'db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'products' => [], 'message' => ''];

try {
    $conn = getConnection();
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (strlen($query) < 2) {
        // Return empty products for short queries, no error message needed
        $response['success'] = true;
        echo json_encode($response);
        exit;
    }

    $likeQuery = '%' . $query . '%';
    
    // Fetch full product details for suggestions
    $sql = "SELECT product_id, product, brand, model, selling_price, image1 
            FROM products 
            WHERE (archived IS NULL OR archived = 0) 
            AND (
                product LIKE :q_product OR 
                brand LIKE :q_brand OR 
                model LIKE :q_model
            )
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':q_product', $likeQuery, PDO::PARAM_STR);
    $stmt->bindValue(':q_brand', $likeQuery, PDO::PARAM_STR);
    $stmt->bindValue(':q_model', $likeQuery, PDO::PARAM_STR);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['products'] = $products;

} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    error_log("Search API Error: " . $e->getMessage());
}

echo json_encode($response);