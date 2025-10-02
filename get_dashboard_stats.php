<?php
// This file is used for the AJAX request to get dashboard stats
require_once 'db.php';
header('Content-Type: application/json');

try {
    // Get product fields (e.g., id, brand, model, stock_quantity)
    $stmt = $conn->query("SELECT product_id, brand, model, stock_quantity FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get low stock count (products with stock between 1-5)
    $lowStock = $conn->query("SELECT COUNT(*) FROM products WHERE stock_quantity > 0 AND stock_quantity <= 5")->fetchColumn();

    // Get out of stock count
    $outOfStock = $conn->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0")->fetchColumn();

    // Calculate total sales amount
    $totalSales = 0;
    $stmtSales = $conn->query("SELECT SUM(stock_revenue) AS total_sales FROM sales");
    if ($stmtSales !== false) {
        $result = $stmtSales->fetch(PDO::FETCH_ASSOC);
        if ($result && $result['total_sales'] !== null) {
            $totalSales = $result['total_sales'];
        }
    }

    // Return the stats and product fields as JSON
    echo json_encode([
        'products' => $products,
        'low_stock' => $lowStock,
        'out_of_stock' => $outOfStock,
        'total_sales' => $totalSales
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
