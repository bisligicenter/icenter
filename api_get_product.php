<?php
header('Content-Type: application/json');
require_once 'db.php';

$response = ['success' => false, 'product' => null, 'message' => ''];

try {
    $productId = isset($_GET['product_id']) ? trim($_GET['product_id']) : '';
    
    if (empty($productId)) {
        $response['message'] = 'Product ID is required.';
        echo json_encode($response);
        exit;
    }

    $conn = getConnection();
    
    // Fetch complete product details
    $sql = "SELECT product_id, product, brand, model, description, selling_price, stock_quantity,
                   image1, image2, image3, image4, image5, image6, image7, image8,
                   water_resistance, display_output, screen_size, charging_port, 
                   material, chip, camera_feature, storage
            FROM products 
            WHERE product_id = :product_id 
            AND (archived IS NULL OR archived = 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $response['success'] = true;
        $response['product'] = $product;
    } else {
        $response['message'] = 'Product not found.';
    }

} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    error_log("Get Product API Error: " . $e->getMessage());
}

echo json_encode($response);
?>
