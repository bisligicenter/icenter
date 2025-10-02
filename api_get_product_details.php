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
    
    // Fetch complete product details with all fields
    $sql = "SELECT product_id, product, brand, model, description, selling_price, stock_quantity,
                   image1, image2, image3, image4, image5, image6, image7, image8,
                   water_resistance, display_output, screen_size, charging_port, 
                   material, chip, camera_feature, storage, category_id,
                   created_at, updated_at
            FROM products 
            WHERE product_id = :product_id 
            AND (archived IS NULL OR archived = 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Format the product data for better frontend consumption
        $product['images'] = array_filter([
            $product['image1'], $product['image2'], $product['image3'], $product['image4'],
            $product['image5'], $product['image6'], $product['image7'], $product['image8']
        ]);
        
        // Format price
        if ($product['selling_price']) {
            $product['formatted_price'] = '₱' . number_format($product['selling_price'], 2);
        }
        
        // Determine stock status
        $stock = intval($product['stock_quantity']);
        if ($stock <= 0) {
            $product['stock_status'] = 'out_of_stock';
            $product['stock_text'] = 'Out of Stock';
        } elseif ($stock <= 5) {
            $product['stock_status'] = 'low_stock';
            $product['stock_text'] = "Low Stock ({$stock})";
        } else {
            $product['stock_status'] = 'in_stock';
            $product['stock_text'] = 'In Stock';
        }
        
        $response['success'] = true;
        $response['product'] = $product;
    } else {
        $response['message'] = 'Product not found or has been archived.';
    }

} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    error_log("Get Product Details API Error: " . $e->getMessage());
}

echo json_encode($response);
?>
