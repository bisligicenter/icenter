<?php
session_start();

// Check if user is logged in (either admin or staff)
if ((!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) && 
    (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'db.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    $start_date = $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_POST['end_date'] ?? date('Y-m-d');
    
    // Query to get stock out records from stock_out table with enhanced data
    $query = "
        SELECT 
            so.id as movement_id,
            DATE_FORMAT(so.created_at, '%Y-%m-%d %H:%i:%s') as date_time,
            DATE(so.created_at) as date,
            p.product_id,
            p.product as product_name,
            p.brand,
            p.model,
            p.storage,
            ABS(so.quantity) as quantity_removed,
            so.previous_stock,
            so.new_stock,
            so.created_by as removed_by,
            COALESCE(so.notes, 'No notes provided') as reason,
            p.stock_quantity as current_stock
        FROM stock_out so
        JOIN products p ON so.product_id = p.product_id
        WHERE DATE(so.created_at) BETWEEN :start_date AND :end_date
        ORDER BY so.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no stock_out table exists or no data, show empty result
    if (empty($results)) {
        // Check if stock_out table exists
        $table_check_query = "SHOW TABLES LIKE 'stock_out'";
        $table_check_stmt = $pdo->prepare($table_check_query);
        $table_check_stmt->execute();
        
        if ($table_check_stmt->rowCount() == 0) {
            // Table doesn't exist, return error message
            echo json_encode([
                'error' => 'Stock out tracking is not set up. Please run the database setup first.',
                'setup_required' => true,
                'data' => [],
                'summary' => [
                    'total_movements' => 0,
                    'total_quantity_removed' => 0,
                    'unique_products' => 0,
                    'date_range' => $start_date . ' to ' . $end_date
                ]
            ]);
            exit;
        } else {
            // Table exists but no data for the date range
            echo json_encode([
                'data' => [],
                'summary' => [
                    'total_movements' => 0,
                    'total_quantity_removed' => 0,
                    'unique_products' => 0,
                    'date_range' => $start_date . ' to ' . $end_date
                ]
            ]);
            exit;
        }
    }
    
    // Calculate summary statistics
    $total_movements = count($results);
    $total_quantity_removed = array_sum(array_column($results, 'quantity_removed'));
    $unique_products = count(array_unique(array_column($results, 'product_id')));
    
    $response = [
        'data' => $results,
        'summary' => [
            'total_movements' => $total_movements,
            'total_quantity_removed' => $total_quantity_removed,
            'unique_products' => $unique_products,
            'date_range' => $start_date . ' to ' . $end_date
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in get_stock_out_report.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error occurred: ' . $e->getMessage(),
        'data' => [],
        'summary' => [
            'total_movements' => 0,
            'total_quantity_removed' => 0,
            'unique_products' => 0,
            'date_range' => 'Error'
        ]
    ]);
}
?> 