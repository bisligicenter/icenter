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

    try {
        $pdo = getConnection();
        
        // Get low stock count (1-5 items)
        $lowStockQuery = "SELECT COUNT(*) as count 
                        FROM products 
                        WHERE (archived IS NULL OR archived = 0) 
                        AND stock_quantity > 0 
                        AND stock_quantity <= 5";
        
        $stmt = $pdo->prepare($lowStockQuery);
        $stmt->execute();
        $lowStockResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $lowStockCount = $lowStockResult['count'] ?? 0;
        
        // Get out of stock count (0 items)
        $outOfStockQuery = "SELECT COUNT(*) as count 
                            FROM products 
                            WHERE (archived IS NULL OR archived = 0) 
                            AND stock_quantity = 0";
        
        $stmt = $pdo->prepare($outOfStockQuery);
        $stmt->execute();
        $outOfStockResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $outOfStockCount = $outOfStockResult['count'] ?? 0;
        
        // Get total products count
        $totalQuery = "SELECT COUNT(*) as count 
                    FROM products 
                    WHERE (archived IS NULL OR archived = 0)";
        
        $stmt = $pdo->prepare($totalQuery);
        $stmt->execute();
        $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCount = $totalResult['count'] ?? 0;
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'low_stock_count' => (int)$lowStockCount,
            'out_of_stock_count' => (int)$outOfStockCount,
            'total_products' => (int)$totalCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
    ?> 