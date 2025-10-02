<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$whereClauses = [];
$params = [];

if ($startDate) {
    $whereClauses[] = "s.date_of_sale >= :start_date";
    $params[':start_date'] = $startDate;
}
if ($endDate) {
    $whereClauses[] = "s.date_of_sale <= :end_date";
    $params[':end_date'] = $endDate;
}

$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

$sql = "SELECT s.sales_id, s.stock_id, s.product_id, s.selling_price, s.quantity_sold, s.cogs, s.stock_revenue, s.date_of_sale,
                   st.purchase_price, st.date_of_purchase,
                   p.product, p.brand, p.model
            FROM sales s
            JOIN stocks st ON s.stock_id = st.stock_id
            JOIN products p ON st.product_id = p.product_id
            $whereSQL
            ORDER BY s.date_of_sale DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate summary totals
    $totalSales = 0;
    $totalQuantity = 0;
    $totalRevenue = 0;

    foreach ($sales as $sale) {
        $totalSales += $sale['selling_price'] * $sale['quantity_sold'];
        $totalQuantity += $sale['quantity_sold'];
        $totalRevenue += $sale['stock_revenue'];
    }

    echo json_encode([
        'success' => true,
        'sales' => $sales,
        'summary' => [
            'total_sales' => $totalSales,
            'total_quantity' => $totalQuantity,
            'total_revenue' => $totalRevenue
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
