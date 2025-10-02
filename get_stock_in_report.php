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
    
    // Query to get stock in records from stock_in table with enhanced data
    $query = "
        SELECT 
            si.id as movement_id,
            DATE_FORMAT(si.created_at, '%Y-%m-%d %H:%i:%s') as date_time,
            DATE(si.created_at) as date,
            p.product_id,
            p.product as product_name,
            p.brand,
            p.model,
            p.storage,
            si.quantity as quantity_added,
            si.previous_stock,
            si.new_stock,
            si.created_by as added_by,
            COALESCE(si.notes, 'No notes provided') as notes,
            p.stock_quantity as current_stock
        FROM stock_in si
        JOIN products p ON si.product_id = p.product_id
        WHERE DATE(si.created_at) BETWEEN :start_date AND :end_date
        ORDER BY si.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no stock_in table exists or no data, show empty result
    if (empty($results)) {
        // Check if stock_in table exists
        $table_check_query = "SHOW TABLES LIKE 'stock_in'";
        $table_check_stmt = $pdo->prepare($table_check_query);
        $table_check_stmt->execute();
        
        if ($table_check_stmt->rowCount() == 0) {
            // Table doesn't exist, return error message
            echo json_encode([
                'error' => 'Stock in tracking is not set up. Please run the database setup first.',
                'setup_required' => true,
                'data' => [],
                'summary' => [
                    'total_movements' => 0,
                    'total_quantity_added' => 0,
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
                    'total_quantity_added' => 0,
                    'unique_products' => 0,
                    'date_range' => $start_date . ' to ' . $end_date
                ]
            ]);
            exit;
        }
    }
    
    // Calculate summary statistics
    $total_movements = count($results);
    $total_quantity_added = array_sum(array_column($results, 'quantity_added'));
    $unique_products = count(array_unique(array_column($results, 'product_id')));
    
    $response = [
        'data' => $results,
        'summary' => [
            'total_movements' => $total_movements,
            'total_quantity_added' => $total_quantity_added,
            'unique_products' => $unique_products,
            'date_range' => $start_date . ' to ' . $end_date
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in get_stock_in_report.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error occurred: ' . $e->getMessage(),
        'data' => [],
        'summary' => [
            'total_movements' => 0,
            'total_quantity_added' => 0,
            'unique_products' => 0,
            'date_range' => 'Error'
        ]
    ]);
}
?> 