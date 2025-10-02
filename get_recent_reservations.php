<?php
require_once 'db.php';

try {
    // Get the 5 most recent reservations with the new multi-product structure
    $stmt = $conn->query("
        SELECT 
            reservation_id,
            name,
            reservation_date,
            reservation_time,
            status,
            product_count,
            reservation_fee,
            remaining_reservation_fee,
            -- Product 1
            product_name_1, product_brand_1, product_model_1, product_price_1,
            -- Product 2
            product_name_2, product_brand_2, product_model_2, product_price_2,
            -- Product 3
            product_name_3, product_brand_3, product_model_3, product_price_3,
            -- Product 4
            product_name_4, product_brand_4, product_model_4, product_price_4,
            -- Product 5
            product_name_5, product_brand_5, product_model_5, product_price_5
        FROM reservations
        ORDER BY reservation_date DESC, reservation_time DESC
        LIMIT 5
    ");
    
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates and prepare response
    foreach ($reservations as &$res) {
        $date = new DateTime($res['reservation_date']);
        $res['reservation_date'] = $date->format('M d, Y');
        
        // Create products array
        $products = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($res["product_name_$i"])) {
                $products[] = [
                    'name' => $res["product_name_$i"],
                    'brand' => $res["product_brand_$i"],
                    'model' => $res["product_model_$i"],
                    'price' => $res["product_price_$i"]
                ];
            }
        }
        $res['products'] = $products;
        
        // Remove individual product fields
        for ($i = 1; $i <= 5; $i++) {
            unset($res["product_name_$i"]);
            unset($res["product_brand_$i"]);
            unset($res["product_model_$i"]);
            unset($res["product_price_$i"]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'reservations' => $reservations
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error fetching recent reservations'
    ]);
}
?> 