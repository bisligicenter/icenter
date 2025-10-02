<?php
// Enhanced Notification Counts API
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Include database connection
require_once 'db.php';

try {
    // Initialize response array
    $response = [
        'pending_reservations' => 0,
        'pending_users' => 0,
        'low_stock_count' => 0,
        'out_of_stock_count' => 0,
        'total_products' => 0,
        'recent_sales' => 0,
        'timestamp' => time()
    ];
    
    // Get pending reservations count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['pending_reservations'] = (int)$result['count'];
    
    // Get pending users count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['pending_users'] = (int)$result['count'];
    
    // Get low stock count (products with stock <= 5)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE stock_quantity > 0 AND stock_quantity <= 5 AND archived = 0");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['low_stock_count'] = (int)$result['count'];
    
    // Get out of stock count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE stock_quantity = 0 AND archived = 0");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['out_of_stock_count'] = (int)$result['count'];
    
    // Get total products count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE archived = 0");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['total_products'] = (int)$result['count'];
    
    // Get recent sales count (last 24 hours)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM sales WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['recent_sales'] = (int)$result['count'];
    
    // Add system status
    $response['system_status'] = 'healthy';
    $response['last_updated'] = date('Y-m-d H:i:s');
    
    // Return JSON response
    echo json_encode($response);
    
} catch (PDOException $e) {
    // Log error and return error response
    error_log("Database error in get_notification_counts.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => 'Database connection failed',
        'pending_reservations' => 0,
        'pending_users' => 0,
        'low_stock_count' => 0,
        'out_of_stock_count' => 0,
        'total_products' => 0,
        'recent_sales' => 0,
        'system_status' => 'error',
        'timestamp' => time()
    ]);
} catch (Exception $e) {
    // Log error and return error response
    error_log("General error in get_notification_counts.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => 'An unexpected error occurred',
        'pending_reservations' => 0,
        'pending_users' => 0,
        'low_stock_count' => 0,
        'out_of_stock_count' => 0,
        'total_products' => 0,
        'recent_sales' => 0,
        'system_status' => 'error',
        'timestamp' => time()
    ]);
}
?> 