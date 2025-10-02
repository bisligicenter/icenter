<?php
header('Content-Type: application/json');
require_once 'db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query is too short.']);
    exit;
}

try {
    $conn = getConnection();
    
    // Search across brand, model, and product name. Prioritize matches starting with the query.
    $sql = "SELECT product_id, brand, model, selling_price, image1 
            FROM products 
            WHERE (archived IS NULL OR archived = 0) 
              AND (
                  brand LIKE :query 
                  OR model LIKE :query 
                  OR product LIKE :query
              )
            ORDER BY 
                CASE 
                    WHEN brand LIKE :query_exact THEN 1
                    WHEN model LIKE :query_exact THEN 2
                    ELSE 3
                END,
                brand, model
            LIMIT 8";

    $stmt = $conn->prepare($sql);
    $searchTerm = '%' . $query . '%';
    $stmt->bindValue(':query', $searchTerm, PDO::PARAM_STR);
    $stmt->bindValue(':query_exact', $query . '%', PDO::PARAM_STR);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'products' => $products]);

} catch (PDOException $e) {
    error_log("Search Suggestion Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while searching.']);
}

$conn = null;
?>