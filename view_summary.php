<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admin";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sales summary
$sales_sql = "SELECT COUNT(*) AS total_sales, SUM(selling_price * quantity_sold) AS total_revenue FROM sales";
$sales_result = $conn->query($sales_sql);
$sales_data = $sales_result ? $sales_result->fetch_assoc() : ['total_sales' => 0, 'total_revenue' => 0];

// Stock summary
$stock_sql = "SELECT COUNT(*) AS total_products, SUM(stock_quantity) AS total_stock FROM products";
$stock_result = $conn->query($stock_sql);
$stock_data = $stock_result ? $stock_result->fetch_assoc() : ['total_products' => 0, 'total_stock' => 0];

// Stock per category/brand/model
$stock_per_category_sql = "SELECT c.category_name, SUM(p.stock_quantity) AS total_stock
                           FROM products p
                           JOIN categories c ON p.category_id = c.category_id
                           GROUP BY c.category_name
                           ORDER BY c.category_name ASC";
$stock_per_category_result = $conn->query($stock_per_category_sql);
$stock_per_category = [];
if ($stock_per_category_result) {
    while ($row = $stock_per_category_result->fetch_assoc()) {
        $stock_per_category[] = $row;
    }
}

$stock_per_brand_sql = "SELECT brand, SUM(stock_quantity) AS total_stock FROM products GROUP BY brand ORDER BY brand ASC";
$stock_per_brand_result = $conn->query($stock_per_brand_sql);
$stock_per_brand = [];
if ($stock_per_brand_result) {
    while ($row = $stock_per_brand_result->fetch_assoc()) {
        $stock_per_brand[] = $row;
    }
}

$stock_per_model_sql = "SELECT model, SUM(stock_quantity) AS total_stock FROM products GROUP BY model ORDER BY model ASC";
$stock_per_model_result = $conn->query($stock_per_model_sql);
$stock_per_model = [];
if ($stock_per_model_result) {
    while ($row = $stock_per_model_result->fetch_assoc()) {
        $stock_per_model[] = $row;
    }
}

// Profit summary
$profit_sql = "SELECT 
    SUM(total_sales) AS total_sales,
    SUM(total_cogs) AS total_cogs,
    SUM(gross_profit) AS gross_profit,
    SUM(expenses) AS expenses,
    SUM(net_profit) AS net_profit
FROM profit";
$profit_result = $conn->query($profit_sql);
$profit_data = $profit_result ? $profit_result->fetch_assoc() : [
    'total_sales' => 0,
    'total_cogs' => 0,
    'gross_profit' => 0,
    'expenses' => 0,
    'net_profit' => 0
];

// Top 5 best-selling products
$top_products_sql = "SELECT p.product_name, SUM(s.quantity_sold) AS total_sold
                     FROM sales s
                     JOIN products p ON s.product_id = p.product_id
                     GROUP BY s.product_id
                     ORDER BY total_sold DESC
                     LIMIT 5";
$top_products_result = $conn->query($top_products_sql);
$top_products = [];
if ($top_products_result) {
    while ($row = $top_products_result->fetch_assoc()) {
        $top_products[] = $row;
    }
}

// Low stock products (less than 10)
$low_stock_sql = "SELECT product_name, stock_quantity FROM products WHERE stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 5";
$low_stock_result = $conn->query($low_stock_sql);
$low_stock_products = [];
if ($low_stock_result) {
    while ($row = $low_stock_result->fetch_assoc()) {
        $low_stock_products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Summary Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: #f4f7f8;
            color: #333;
        }
        h1 {
            text-align: center;
            font-weight: 700;
            font-size: 2.5em;
            color: #2c3e50;
            margin: 30px 0 20px 0;
        }
        .summary-container {
            display: flex;
            flex-direction: column;
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto 40px auto;
        }
        .summary-box {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(44,62,80,0.07);
            padding: 32px 28px;
            margin-bottom: 0;
            width: 100%;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
        }
        .summary-box:not(:last-child):hover {
            box-shadow: 0 8px 32px rgba(44,62,80,0.13);
            transform: translateY(-4px) scale(1.01);
        }
        .summary-box h2 {
            text-align: left;
            color: #34495e;
            font-weight: 600;
            font-size: 1.5em;
            margin-bottom: 18px;
            border-left: 4px solid #3498db;
            padding-left: 12px;
        }
        .summary-box h3 {
            font-size: 1.1em;
            color: #2980b9;
            margin: 18px 0 8px 0;
        }
        .summary-box p {
            font-size: 1.2em;
            margin: 10px 0;
            color: #555;
        }
        .summary-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 200px;
            overflow-y: auto;
        }
        .summary-box ul li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 1.08em;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .summary-box ul li:last-child {
            border-bottom: none;
        }
        .low-stock {
            background: #fffbe6;
            border-left: 4px solid #f39c12;
        }
        .top-seller-rank {
            background: #3498db;
            color: #fff;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .sold-count {
            color: #27ae60;
            font-weight: 600;
        }
        .stock-count {
            color: #e67e22;
            font-weight: 600;
        }
        .icon {
            margin-right: 8px;
            vertical-align: middle;
        }
        a.back-link {
            text-decoration: none;
            color: #2980b9;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin: 24px 0 0 24px;
            transition: color 0.3s;
        }
        a.back-link:hover {
            color: #1c5980;
        }
        a.back-link svg {
            margin-right: 8px;
            transition: transform 0.3s;
        }
        a.back-link:hover svg {
            transform: translateX(-4px);
        }
        @media (max-width: 900px) {
            .summary-container { padding: 0 10px; }
        }
        @media (max-width: 600px) {
            .summary-box { padding: 18px 8px; }
            h1 { font-size: 2em; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <a href="admin.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#2980b9" class="bi bi-arrow-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 1-.5.5H2.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 1 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z"/>
        </svg>
        Back to Admin
    </a>
    <h1>General Summary Report</h1>
    <div class="summary-container">
        <!-- Sales Summary -->
        <div class="summary-box">
            <h2><span class="icon">💰</span>Sales Summary</h2>
            <p>Total Sales: <b><?php echo $sales_data['total_sales'] ?? 0; ?></b></p>
            <p>Total Revenue: <b>₱<?php echo number_format($sales_data['total_revenue'] ?? 0, 2); ?></b></p>
            <canvas id="salesChart" style="max-width: 600px; margin: 0 auto;"></canvas>
        </div>
        <!-- Stock Summary -->
        <div class="summary-box">
            <h2><span class="icon">📦</span>Stock Summary</h2>
            <p>Total Products: <b><?php echo $stock_data['total_products'] ?? 0; ?></b></p>
            <p>Total Stock Quantity: <b><?php echo $stock_data['total_stock'] ?? 0; ?></b></p>
            <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
                <div style="flex: 1; min-width: 220px;">
                    <h3>Stock per Category</h3>
                    <canvas id="stockCategoryChart"></canvas>
                </div>
                <div style="flex: 1; min-width: 220px;">
                    <h3>Stock per Brand</h3>
                    <canvas id="stockBrandChart"></canvas>
                </div>
            </div>
            <div style="margin-top: 30px; max-width: 900px; margin-left: auto; margin-right: auto;">
                <h3>Stock per Model</h3>
                <canvas id="stockModelChart" style="width: 100%; height: 400px;"></canvas>
            </div>
            <div style="margin-top: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
                <h3>Stock Distribution by Category</h3>
                <canvas id="stockCategoryPieChart"></canvas>
            </div>
        </div>
        <!-- Profit Summary -->
        <div class="summary-box" style="background-color: #e0f7fa;">
            <h2><span class="icon">📈</span>Profit Summary</h2>
            <p>Total Sales: <b>₱<?php echo number_format($profit_data['total_sales'] ?? 0, 2); ?></b></p>
            <p>Total COGS: <b>₱<?php echo number_format($profit_data['total_cogs'] ?? 0, 2); ?></b></p>
            <p>Gross Profit: <b>₱<?php echo number_format($profit_data['gross_profit'] ?? 0, 2); ?></b></p>
            <p>Expenses: <b>₱<?php echo number_format($profit_data['expenses'] ?? 0, 2); ?></b></p>
            <p><strong>Net Profit: <span style="color:#27ae60;">₱<?php echo number_format($profit_data['net_profit'] ?? 0, 2); ?></span></strong></p>
            <canvas id="profitChart" style="max-width: 600px; margin: 20px auto 0;"></canvas>
        </div>
        <!-- Top Sellers -->
        <div class="summary-box">
            <h2><span class="icon">🏆</span>Top 5 Best-Selling Products</h2>
            <?php if (empty($top_products)): ?>
                <p class="text-center" style="color:#888;">No sales data available.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($top_products as $i => $prod): ?>
                        <li>
                            <span>
                                <span class="top-seller-rank"><?= $i+1 ?></span>
                                <?= htmlspecialchars($prod['product_name']) ?>
                            </span>
                            <span class="sold-count">Sold: <?= (int)$prod['total_sold'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <!-- Low Stock -->
        <div class="summary-box low-stock">
            <h2><span class="icon">⚠️</span>Low Stock Products (Less than 10)</h2>
            <?php if (empty($low_stock_products)): ?>
                <p class="text-center" style="color:#888;">No low stock products.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($low_stock_products as $prod): ?>
                        <li>
                            <?= htmlspecialchars($prod['product_name']) ?>
                            <span class="stock-count">Stock: <?= (int)$prod['stock_quantity'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Helper: fixed color palette for charts
        function getPalette(n) {
            const palette = [
                '#3498db','#e67e22','#2ecc71','#9b59b6','#f39c12',
                '#1abc9c','#e74c3c','#34495e','#95a5a6','#16a085'
            ];
            let arr = [];
            for(let i=0;i<n;i++) arr.push(palette[i%palette.length]);
            return arr;
        }

        // Sales Chart
        const salesData = {
            labels: ['Total Sales', 'Total Revenue'],
            datasets: [{
                label: 'Sales Summary',
                data: [<?php echo $sales_data['total_sales'] ?? 0; ?>, <?php echo $sales_data['total_revenue'] ?? 0; ?>],
                backgroundColor: getPalette(2)
            }]
        };
        const salesConfig = {
            type: 'bar',
            data: salesData,
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Sales Summary' }
                },
                scales: { y: { beginAtZero: true } }
            }
        };
        new Chart(document.getElementById('salesChart'), salesConfig);

        // Stock Charts
        const stockCategoryLabels = <?php echo json_encode(array_column($stock_per_category, 'category_name')); ?>;
        const stockCategoryData = <?php echo json_encode(array_map('intval', array_column($stock_per_category, 'total_stock'))); ?>;
        const stockBrandLabels = <?php echo json_encode(array_column($stock_per_brand, 'brand')); ?>;
        const stockBrandData = <?php echo json_encode(array_map('intval', array_column($stock_per_brand, 'total_stock'))); ?>;
        const stockModelLabels = <?php echo json_encode(array_column($stock_per_model, 'model')); ?>;
        const stockModelData = <?php echo json_encode(array_map('intval', array_column($stock_per_model, 'total_stock'))); ?>;

        new Chart(document.getElementById('stockCategoryChart'), {
            type: 'bar',
            data: {
                labels: stockCategoryLabels,
                datasets: [{
                    label: 'Stock per Category',
                    data: stockCategoryData,
                    backgroundColor: getPalette(stockCategoryLabels.length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Stock per Category' }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

        new Chart(document.getElementById('stockBrandChart'), {
            type: 'bar',
            data: {
                labels: stockBrandLabels,
                datasets: [{
                    label: 'Stock per Brand',
                    data: stockBrandData,
                    backgroundColor: getPalette(stockBrandLabels.length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Stock per Brand' }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

        new Chart(document.getElementById('stockModelChart'), {
            type: 'bar',
            data: {
                labels: stockModelLabels,
                datasets: [{
                    label: 'Stock per Model',
                    data: stockModelData,
                    backgroundColor: getPalette(stockModelLabels.length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Stock per Model' }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Pie Chart for Stock Distribution by Category
        new Chart(document.getElementById('stockCategoryPieChart'), {
            type: 'pie',
            data: {
                labels: stockCategoryLabels,
                datasets: [{
                    data: stockCategoryData,
                    backgroundColor: getPalette(stockCategoryLabels.length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    title: { display: true, text: 'Stock Distribution by Category' }
                }
            }
        });

        // Profit Chart
        const profitData = {
            labels: ['Total Sales', 'Total COGS', 'Gross Profit', 'Expenses', 'Net Profit'],
            datasets: [{
                label: 'Profit Summary',
                data: [
                    <?php echo $profit_data['total_sales'] ?? 0; ?>,
                    <?php echo $profit_data['total_cogs'] ?? 0; ?>,
                    <?php echo $profit_data['gross_profit'] ?? 0; ?>,
                    <?php echo $profit_data['expenses'] ?? 0; ?>,
                    <?php echo $profit_data['net_profit'] ?? 0; ?>
                ],
                backgroundColor: getPalette(5)
            }]
        };
        const profitConfig = {
            type: 'bar',
            data: profitData,
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Profit Summary' }
                },
                scales: { y: { beginAtZero: true } }
            }
        };
        new Chart(document.getElementById('profitChart'), profitConfig);
    </script>
</body>
</html>
