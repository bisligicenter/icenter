<?php
require_once 'db.php';

function fetchSalesMetrics($conn, $recordsPerPage = 20, $offset = 0) {
    try {
        if (!$conn) {
            throw new PDOException("Database connection not available");
        }

        // Get total revenue
        $totalRevenueStmt = $conn->query("
            SELECT COALESCE(SUM(stock_revenue), 0) AS total_revenue 
            FROM sales
        ");
        $totalRevenue = $totalRevenueStmt->fetchColumn();

        // Get total orders (count of unique sales)
        $totalOrdersStmt = $conn->query("SELECT COUNT(*) AS total_orders FROM sales");
        $totalOrders = $totalOrdersStmt->fetchColumn();

        // Calculate average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Calculate conversion rate (assuming 1000 visitors as a placeholder)
        $totalVisitors = 1000; // Placeholder value
        $conversionRate = $totalVisitors > 0 ? ($totalOrders / $totalVisitors) * 100 : 0;

        // Get sales trend data (last 7 days)
        $salesTrendStmt = $conn->query("
            SELECT 
                DATE(date_of_sale) AS sale_date,
                COALESCE(SUM(stock_revenue), 0) AS daily_total
            FROM sales
            WHERE date_of_sale >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(date_of_sale)
            ORDER BY sale_date ASC
        ");

        $salesTrendData = $salesTrendStmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare data for the charts
        $salesTrendDates = [];
        $salesTrendTotals = [];

        // Fill in dates for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $salesTrendDates[] = date('M d', strtotime($date));

            // Find if there's data for this date
            $found = false;
            foreach ($salesTrendData as $data) {
                if ($data['sale_date'] == $date) {
                    $salesTrendTotals[] = (float)$data['daily_total'];
                    $found = true;
                    break;
                }
            }

            // If no data for this date, set to 0
            if (!$found) {
                $salesTrendTotals[] = 0;
            }
        }

        // Get category revenue data
        $categoryRevenueStmt = $conn->query("
            SELECT 
                COALESCE(p.brand, 'Unbranded') AS category,
                COALESCE(SUM(s.stock_revenue), 0) AS revenue
            FROM sales s
            JOIN stocks st ON s.stock_id = st.stock_id
            JOIN products p ON st.product_id = p.product_id
            GROUP BY p.brand
            ORDER BY revenue DESC
            LIMIT 5
        ");

        $categoryRevenueData = [];
        while ($row = $categoryRevenueStmt->fetch(PDO::FETCH_ASSOC)) {
            $categoryRevenueData[] = [
                'value' => (float)$row['revenue'],
                'name' => $row['category']
            ];
        }

        // First, let's get basic sales data to verify it exists
        try {
            // Simple query to get sales data first with pagination
            $recentSalesStmt = $conn->prepare("
                SELECT 
                    s.*,
                    pr.product as product_name,
                    pr.brand,
                    pr.model,
                    (s.stock_revenue - (s.quantity_sold * s.purchase_price)) as calculated_gross_profit,
                    ((s.stock_revenue - (s.quantity_sold * s.purchase_price)) - COALESCE(p.expenses, 0)) as calculated_net_profit,
                    p.expenses
                FROM sales s
                LEFT JOIN products pr ON s.product_id = pr.product_id
                LEFT JOIN profit p ON s.sales_id = p.sales_id
                ORDER BY s.date_of_sale DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $recentSalesStmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
            $recentSalesStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $recentSalesStmt->execute();

            if ($recentSalesStmt === false) {
                throw new PDOException("Query failed: " . print_r($conn->errorInfo(), true));
            }

            $recentSales = $recentSalesStmt->fetchAll(PDO::FETCH_ASSOC);
            
        
            error_log("Number of sales records found: " . count($recentSales));
            if (count($recentSales) === 0) {
                error_log("No sales records found in the database");
            } else {
            
            }

            // Calculate total profits
            $totalGrossProfit = 0;
            $totalNetProfit = 0;
            foreach ($recentSales as $sale) {
                $totalGrossProfit += $sale['calculated_gross_profit'];
                $totalNetProfit += $sale['calculated_net_profit'];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $recentSales = [];
        }

        // Get profit data separately
        $profitData = [];
        if (!empty($recentSales)) {
            $salesIds = array_column($recentSales, 'sales_id');
            $placeholders = str_repeat('?,', count($salesIds) - 1) . '?';
            $profitStmt = $conn->prepare("
                SELECT sales_id, gross_profit, expenses, net_profit 
                FROM profit 
                WHERE sales_id IN ($placeholders)
            ");
            $profitStmt->execute($salesIds);
            while ($row = $profitStmt->fetch(PDO::FETCH_ASSOC)) {
                $profitData[$row['sales_id']] = $row;
            }
        }

        // Calculate average profit per order
        $avgProfitPerOrder = $totalOrders > 0 ? $totalGrossProfit / $totalOrders : 0;

        return [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'conversionRate' => $conversionRate,
            'totalGrossProfit' => $totalGrossProfit,
            'totalNetProfit' => $totalNetProfit,
            'avgProfitPerOrder' => $avgProfitPerOrder,
            'salesTrendDates' => $salesTrendDates,
            'salesTrendTotals' => $salesTrendTotals,
            'categoryRevenueData' => $categoryRevenueData,
            'recentSales' => $recentSales
        ];
    } catch (PDOException $e) {
        error_log("Error fetching sales data: " . $e->getMessage());
        return [
            'totalRevenue' => 0,
            'totalOrders' => 0,
            'avgOrderValue' => 0,
            'conversionRate' => 0,
            'totalGrossProfit' => 0,
            'totalNetProfit' => 0,
            'avgProfitPerOrder' => 0,
            'salesTrendDates' => array_map(function($i) { return date('M d', strtotime("-$i days")); }, range(6, 0)),
            'salesTrendTotals' => array_fill(0, 7, 0),
            'categoryRevenueData' => [],
            'recentSales' => []
        ];
    }
}

// Pagination setup
$recordsPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Get total count for pagination
$totalCountStmt = $conn->query("SELECT COUNT(*) as total FROM sales");
$totalRecords = $totalCountStmt->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

// Initialize metrics
$metrics = fetchSalesMetrics($conn, $recordsPerPage, $offset);
$totalRevenue = $metrics['totalRevenue'];
$totalOrders = $metrics['totalOrders'];
$avgOrderValue = $metrics['avgOrderValue'];
$conversionRate = $metrics['conversionRate'];
$totalGrossProfit = $metrics['totalGrossProfit'];
$totalNetProfit = $metrics['totalNetProfit'];
$avgProfitPerOrder = $metrics['avgProfitPerOrder'];
$salesTrendDates = $metrics['salesTrendDates'];
$salesTrendTotals = $metrics['salesTrendTotals'];
$categoryRevenueData = $metrics['categoryRevenueData'];
$recentSales = $metrics['recentSales'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Sales - Inventory System</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <style>
        :root {
            --primary-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --border-color: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: var(--text-primary);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: var(--bg-primary);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #000000;
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), #1d4ed8);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .metric-card:hover::before {
            transform: scaleX(1);
        }

        .metric-title {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }

        .metric-value {
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 0.5rem;
        }

        .metric-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }

        .profit-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }

        .profit-indicator.positive {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
        }

        .profit-indicator.negative {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-section {
            background: var(--bg-primary);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .table-header {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-secondary);
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .controls-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: var(--bg-primary);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .date-range-filter {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .date-input {
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .date-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-button {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 2px solid var(--border-color);
        }

        .filter-button:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        .filter-button.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .table-container {
            overflow-x: auto;
            max-height: 70vh;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background: var(--bg-secondary);
            padding: 1.25rem 1rem;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 1.25rem 1rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: var(--bg-secondary);
            transform: scale(1.01);
        }

        .summary-row {
            background: linear-gradient(135deg, var(--bg-secondary), #f1f5f9);
            font-weight: 700;
            border-top: 2px solid var(--border-color);
        }

        .profit-positive {
            color: var(--success-color);
            font-weight: 700;
        }

        .profit-negative {
            color: var(--danger-color);
            font-weight: 700;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .chart-legend {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            background: var(--bg-secondary);
            border-radius: 8px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in {
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-container {
                max-width: none;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
            .metric-card {
                break-inside: avoid;
            }
        }

        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #000000;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }

        .scroll-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            background: #1a1a1a;
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 768px) {
            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--bg-primary);
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        .pagination-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            
            .pagination-controls {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .pagination-btn {
                width: 35px;
                height: 35px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" title="Scroll to top">
        <i class="ri-arrow-up-line"></i>
    </button>
    
    <div class="container">
        <!-- Enhanced Header -->
        <div class="header fade-in">
            <a href="admin.php" class="back-button no-print">
                <i class="ri-arrow-left-line"></i>
                Back to Dashboard
            </a>
        </div>
        
        <!-- Enhanced Metrics Dashboard -->
        <div class="metrics-grid">
            <div class="metric-card slide-in" style="animation-delay: 0.1s;">
                <h3 class="metric-title">
                    <i class="ri-money-dollar-circle-line"></i>
                    Total Revenue
                </h3>
                <p class="metric-value">₱<?php echo number_format($totalRevenue, 2); ?></p>
                <p class="metric-subtitle">All-time sales revenue</p>
            </div>
            
            <div class="metric-card slide-in" style="animation-delay: 0.2s;">
                <h3 class="metric-title">
                    <i class="ri-shopping-cart-line"></i>
                    Total Orders
                </h3>
                <p class="metric-value"><?php echo number_format($totalOrders); ?></p>
                <p class="metric-subtitle">Completed transactions</p>
            </div>
            
            <div class="metric-card slide-in" style="animation-delay: 0.3s;">
                <h3 class="metric-title">
                    <i class="ri-bar-chart-line"></i>
                    Average Order Value
                </h3>
                <p class="metric-value">₱<?php echo number_format($avgOrderValue, 2); ?></p>
                <p class="metric-subtitle">Per transaction average</p>
            </div>
            
            <div class="metric-card slide-in" style="animation-delay: 0.4s;">
                <h3 class="metric-title">
                    <i class="ri-trending-up-line"></i>
                    Total Gross Profit
                </h3>
                <p class="metric-value">
                    ₱<?php echo number_format($totalGrossProfit, 2); ?>
                    <span class="profit-indicator <?php echo $totalGrossProfit >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="ri-<?php echo $totalGrossProfit >= 0 ? 'arrow-up' : 'arrow-down'; ?>-line"></i>
                        <?php echo $totalRevenue > 0 ? number_format(($totalGrossProfit / $totalRevenue) * 100, 1) : '0'; ?>%
                    </span>
                </p>
                <p class="metric-subtitle">Before expenses</p>
            </div>
            
            <div class="metric-card slide-in" style="animation-delay: 0.5s;">
                <h3 class="metric-title">
                    <i class="ri-profit-line"></i>
                    Total Net Profit
                </h3>
                <p class="metric-value">
                    ₱<?php echo number_format($totalNetProfit, 2); ?>
                    <span class="profit-indicator <?php echo $totalNetProfit >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="ri-<?php echo $totalNetProfit >= 0 ? 'arrow-up' : 'arrow-down'; ?>-line"></i>
                        <?php echo $totalRevenue > 0 ? number_format(($totalNetProfit / $totalRevenue) * 100, 1) : '0'; ?>%
                    </span>
                </p>
                <p class="metric-subtitle">After expenses</p>
            </div>
            
            <div class="metric-card slide-in" style="animation-delay: 0.6s;">
                <h3 class="metric-title">
                    <i class="ri-percent-line"></i>
                    Profit Margin
                </h3>
                <p class="metric-value"><?php echo $totalRevenue > 0 ? number_format(($totalGrossProfit / $totalRevenue) * 100, 2) : '0.00'; ?>%</p>
                <p class="metric-subtitle">Revenue to profit ratio</p>
            </div>
        </div>

        <!-- Enhanced Charts Section -->
        <div class="charts-grid">
            <div class="chart-container fade-in" style="animation-delay: 0.7s;">
                <h3 class="chart-title">
                    <i class="ri-line-chart-line"></i>
                    Sales Trend (Last 7 Days)
                </h3>
                <div id="salesTrendChart" style="height: 350px;"></div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #3b82f6;"></div>
                        <span>Daily Revenue</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: rgba(59, 130, 246, 0.2);"></div>
                        <span>Trend Area</span>
                    </div>
                </div>
            </div>
            
            <div class="chart-container fade-in" style="animation-delay: 0.8s;">
                <h3 class="chart-title">
                    <i class="ri-pie-chart-line"></i>
                    Revenue by Category
                </h3>
                <div id="categoryPieChart" style="height: 350px;"></div>
            </div>
        </div>

        <!-- Enhanced Sales Table -->
        <div class="table-section fade-in" style="animation-delay: 0.9s;">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="ri-file-list-line"></i>
                    Sales History
                </h3>
                
                <div class="controls-row">
                    <div class="search-container">
                        <i class="ri-search-line search-icon"></i>
                        <input type="text" id="searchInput" placeholder="Search products, brands, or dates..." class="search-input" />
                    </div>
                    
                    <div class="date-range-filter">
                        <input type="date" id="startDate" class="date-input" placeholder="Start Date">
                        <span style="color: var(--text-secondary);">to</span>
                        <input type="date" id="endDate" class="date-input" placeholder="End Date">
                    </div>
                </div>
                
                <div class="filter-buttons">
                    <button class="filter-button active" data-filter="all">
                        <i class="ri-list-check"></i> All Sales
                    </button>
                    <button class="filter-button" data-filter="profitable">
                        <i class="ri-trending-up-line"></i> Profitable
                    </button>
                    <button class="filter-button" data-filter="loss">
                        <i class="ri-trending-down-line"></i> Loss
                    </button>
                    <button class="filter-button" data-filter="recent">
                        <i class="ri-time-line"></i> Last 7 Days
                    </button>
                </div>
            </div>
            
            <div class="table-container">
                <table id="salesTable">
                    <thead>
                        <tr>
                            <th>Sales ID</th>
                            <th>Stock ID</th>
                            <th>Date of Sale</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Selling Price</th>
                            <th>Purchase Price</th>
                            <th>Stock Revenue</th>
                            <th>Gross Profit</th>
                            <th>Expenses</th>
                            <th>Net Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSales)): ?>
                            <tr>
                                <td colspan="11" class="no-results">
                                    <i class="ri-inbox-line"></i>
                                    <p>No sales data available</p>
                                    <?php if (isset($e)): ?>
                                        <p style="color: var(--danger-color); margin-top: 0.5rem;">Error: <?php echo htmlspecialchars($e->getMessage()); ?></p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $totalQuantity = 0;
                            $totalRevenue = 0;
                            $totalGrossProfit = 0;
                            $totalExpenses = 0;
                            $totalNetProfit = 0;
                            
                            foreach ($recentSales as $index => $sale): 
                                $totalQuantity += $sale['quantity_sold'];
                                $totalRevenue += $sale['stock_revenue'];
                                $totalGrossProfit += $sale['calculated_gross_profit'];
                                $totalExpenses += $sale['expenses'] ?? 0;
                                $totalNetProfit += $sale['calculated_net_profit'];
                            endforeach;
                            ?>
                            <!-- Summary Row -->
                            <tr class="summary-row">
                                <td colspan="4"><strong>Total</strong></td>
                                <td><strong><?php echo number_format($totalQuantity); ?></strong></td>
                                <td>-</td>
                                <td>-</td>
                                <td><strong>₱<?php echo number_format($totalRevenue, 2); ?></strong></td>
                                <td class="<?php echo $totalGrossProfit >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                    <strong><?php echo $totalGrossProfit >= 0 ? '+' : ''; ?>₱<?php echo number_format($totalGrossProfit, 2); ?></strong>
                                </td>
                                <td><strong>₱<?php echo number_format($totalExpenses, 2); ?></strong></td>
                                <td class="<?php echo $totalNetProfit >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                    <strong><?php echo $totalNetProfit >= 0 ? '+' : ''; ?>₱<?php echo number_format($totalNetProfit, 2); ?></strong>
                                </td>
                            </tr>
                            <?php foreach ($recentSales as $index => $sale): ?>
                                <tr style="animation-delay: <?php echo $index * 0.05; ?>s;">
                                    <td class="font-medium">#<?php echo htmlspecialchars($sale['sales_id']); ?></td>
                                    <td>#<?php echo htmlspecialchars($sale['stock_id']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($sale['date_of_sale'])); ?></td>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($sale['product_name'] ?? 'N/A'); ?></div>
                                        <div style="color: var(--text-secondary); font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($sale['brand'] ?? 'N/A'); ?> 
                                            <?php echo htmlspecialchars($sale['model'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($sale['quantity_sold']); ?></td>
                                    <td>₱<?php echo number_format($sale['selling_price'], 2); ?></td>
                                    <td>₱<?php echo number_format($sale['purchase_price'], 2); ?></td>
                                    <td>₱<?php echo number_format($sale['stock_revenue'], 2); ?></td>
                                    <td class="<?php echo $sale['calculated_gross_profit'] > 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                        <?php echo $sale['calculated_gross_profit'] > 0 ? '+' : ''; ?>₱<?php echo number_format($sale['calculated_gross_profit'], 2); ?>
                                    </td>
                                    <td>₱<?php echo number_format($sale['expenses'] ?? 0, 2); ?></td>
                                    <td class="<?php echo $sale['calculated_net_profit'] > 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                        <?php echo $sale['calculated_net_profit'] > 0 ? '+' : ''; ?>₱<?php echo number_format($sale['calculated_net_profit'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $recordsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> records
                </div>
                <div class="pagination-controls">
                    <?php if ($page > 1): ?>
                        <a href="?page=1" class="pagination-btn">
                            <i class="ri-double-left-line"></i>
                        </a>
                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                            <i class="ri-arrow-left-line"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                            <i class="ri-arrow-right-line"></i>
                        </a>
                        <a href="?page=<?php echo $totalPages; ?>" class="pagination-btn">
                            <i class="ri-double-right-line"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Show loading overlay
            const loadingOverlay = document.querySelector('.loading-overlay');
            loadingOverlay.classList.add('active');

            // Initialize charts only if their containers exist
            const salesTrendChartContainer = document.getElementById('salesTrendChart');
            const categoryPieChartContainer = document.getElementById('categoryPieChart');
            
            let salesTrendChart = null;
            let categoryPieChart = null;

            if (salesTrendChartContainer) {
                salesTrendChart = echarts.init(salesTrendChartContainer);
                const salesTrendOption = {
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        borderColor: 'transparent',
                        borderRadius: 8,
                        textStyle: { color: '#ffffff' },
                        formatter: function(params) {
                            return params[0].name + '<br/>' + 
                                   params[0].seriesName + ': ₱' + params[0].value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: <?php echo json_encode($salesTrendDates); ?>,
                        axisLine: { lineStyle: { color: '#cbd5e1' } },
                        axisLabel: { color: '#64748b' }
                    },
                    yAxis: {
                        type: 'value',
                        axisLine: { show: false },
                        axisTick: { show: false },
                        splitLine: { lineStyle: { color: '#e2e8f0' } },
                        axisLabel: { 
                            color: '#64748b',
                            formatter: function(value) {
                                return '₱' + value.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                            }
                        }
                    },
                    series: [{
                        name: 'Revenue',
                        data: <?php echo json_encode($salesTrendTotals); ?>,
                        type: 'line',
                        smooth: true,
                        lineStyle: { width: 4, color: '#3b82f6' },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(59, 130, 246, 0.3)' },
                                { offset: 1, color: 'rgba(59, 130, 246, 0.05)' }
                            ])
                        },
                        symbol: 'circle',
                        symbolSize: 10,
                        itemStyle: {
                            color: '#3b82f6',
                            borderColor: '#ffffff',
                            borderWidth: 3
                        }
                    }]
                };
                salesTrendChart.setOption(salesTrendOption);
            }

            if (categoryPieChartContainer) {
                categoryPieChart = echarts.init(categoryPieChartContainer);
                const categoryData = <?php echo json_encode($categoryRevenueData); ?>;
                const categoryPieOption = {
                    tooltip: {
                        trigger: 'item',
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        borderColor: 'transparent',
                        borderRadius: 8,
                        textStyle: { color: '#ffffff' },
                        formatter: function(params) {
                            return params.name + '<br/>' + 
                                   'Revenue: ₱' + params.value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + 
                                   ' (' + params.percent + '%)';
                        }
                    },
                    legend: {
                        bottom: 0,
                        left: 'center',
                        textStyle: { color: '#64748b' },
                        formatter: function(name) {
                            return name.length > 12 ? name.substring(0, 10) + '...' : name;
                        }
                    },
                    series: [{
                        name: 'Revenue',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: { 
                            borderRadius: 8, 
                            borderColor: '#ffffff', 
                            borderWidth: 2 
                        },
                        label: { show: false },
                        emphasis: { 
                            label: { 
                                show: true, 
                                fontSize: '12', 
                                fontWeight: 'bold' 
                            } 
                        },
                        labelLine: { show: false },
                        data: categoryData.length > 0 ? categoryData : [{ value: 0, name: 'No Data' }]
                    }]
                };
                categoryPieChart.setOption(categoryPieOption);
            }

            // Enhanced search and filter functionality
            const searchInput = document.getElementById('searchInput');
            const salesTable = document.getElementById('salesTable');
            const filterButtons = document.querySelectorAll('.filter-button');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            
            function applyFilters() {
                const searchFilter = searchInput.value.toLowerCase().trim();
                const dateFilter = {
                    start: startDate.value ? new Date(startDate.value) : null,
                    end: endDate.value ? new Date(endDate.value) : null
                };
                const activeFilter = document.querySelector('.filter-button.active').dataset.filter;
                
                const rows = salesTable.tBodies[0].rows;
                let hasResults = false;
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    if (row.classList.contains('summary-row')) continue;
                    
                    const product = row.cells[3].textContent.toLowerCase();
                    const date = new Date(row.cells[2].textContent);
                    const grossProfit = parseFloat(row.cells[8].textContent.replace(/[^0-9.-]+/g, ''));
                    const isRecent = (new Date() - date) <= 7 * 24 * 60 * 60 * 1000;
                    
                    let show = true;
                    
                    // Apply search filter
                    if (searchFilter && !product.includes(searchFilter)) {
                        show = false;
                    }
                    
                    // Apply date filter
                    if (dateFilter.start && date < dateFilter.start) show = false;
                    if (dateFilter.end && date > dateFilter.end) show = false;
                    
                    // Apply category filter
                    switch (activeFilter) {
                        case 'profitable':
                            if (grossProfit <= 0) show = false;
                            break;
                        case 'loss':
                            if (grossProfit >= 0) show = false;
                            break;
                        case 'recent':
                            if (!isRecent) show = false;
                            break;
                    }
                    
                    row.style.display = show ? '' : 'none';
                    if (show) hasResults = true;
                }
                
                // Update no results message
                const noResultsRow = salesTable.querySelector('.no-results');
                if (!hasResults && !noResultsRow) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="11" class="no-results"><i class="ri-search-line"></i><p>No matching results found</p></td>';
                    salesTable.tBodies[0].appendChild(tr);
                } else if (hasResults && noResultsRow) {
                    noResultsRow.parentNode.removeChild(noResultsRow);
                }
            }
            
            // Event listeners for filters
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    applyFilters();
                });
            });
            
            [searchInput, startDate, endDate].forEach(element => {
                if (element) {
                    element.addEventListener('input', () => {
                        clearTimeout(window.searchTimeout);
                        window.searchTimeout = setTimeout(applyFilters, 300);
                    });
                }
            });

            // Resize charts when window size changes
            const resizeCharts = () => {
                if (salesTrendChart) salesTrendChart.resize();
                if (categoryPieChart) categoryPieChart.resize();
            };

            window.addEventListener('resize', resizeCharts);
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (salesTrendChart) salesTrendChart.dispose();
                if (categoryPieChart) categoryPieChart.dispose();
            });

            // Hide loading overlay after everything is loaded
            setTimeout(() => {
                loadingOverlay.classList.remove('active');
            }, 800);

            // Scroll to Top functionality
            const scrollToTopBtn = document.getElementById('scrollToTop');
            
            // Show/hide scroll to top button based on scroll position
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.add('show');
                } else {
                    scrollToTopBtn.classList.remove('show');
                }
            });
            
            // Smooth scroll to top when button is clicked
            scrollToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>