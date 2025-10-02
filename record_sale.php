<?php
require_once 'db.php';

header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("=== RECORD SALE PROCESS STARTED ===");

// Get raw JSON data from POST request
$input = file_get_contents('php://input');
error_log("Received raw input: " . $input);

$data = json_decode($input, true);
error_log("Decoded data: " . print_r($data, true));

error_log("Validating input data fields");
if (!$data) {
    error_log("No data received or JSON decode failed");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}
$requiredFields = ['stock_id', 'selling_price', 'quantity_sold', 'date_of_sale'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        error_log("Missing required field: $field");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

try {
    error_log("Starting database transaction");
    // Begin transaction
    $conn->beginTransaction();
    
    // Get product_id from stocks table
    $getProductStmt = $conn->prepare("SELECT product_id FROM stocks WHERE stock_id = :stock_id");
    $getProductStmt->bindParam(':stock_id', $data['stock_id']);
    $getProductStmt->execute();
    $stockInfo = $getProductStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stockInfo) {
        error_log("Stock record not found for stock_id: " . $data['stock_id']);
        throw new Exception("Stock record not found");
    }
    
    $product_id = $stockInfo['product_id'];
    $quantity_sold = intval($data['quantity_sold']);
    
    error_log("Processing sale for product_id: $product_id, quantity: $quantity_sold");
    
    // Check current stock quantity
    $checkStockStmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = :product_id");
    $checkStockStmt->bindParam(':product_id', $product_id);
    $checkStockStmt->execute();
    $currentStock = $checkStockStmt->fetchColumn();
    
    error_log("Current stock quantity: $currentStock");
    
    if ($currentStock < $quantity_sold) {
        error_log("Insufficient stock. Required: $quantity_sold, Available: $currentStock");
        throw new Exception("Insufficient stock quantity");
    }
    
    // Deduct stock quantity
    $newStock = $currentStock - $quantity_sold;
    error_log("Updating stock to new quantity: $newStock");
    
    $updateStockStmt = $conn->prepare("UPDATE products SET stock_quantity = :new_stock WHERE product_id = :product_id");
    $updateStockStmt->bindParam(':new_stock', $newStock);
    $updateStockStmt->bindParam(':product_id', $product_id);
    $updateStockStmt->execute();
    
    // Verify stock was updated
    $verifyStockStmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = :product_id");
    $verifyStockStmt->bindParam(':product_id', $product_id);
    $verifyStockStmt->execute();
    $updatedStock = $verifyStockStmt->fetchColumn();
    
    error_log("Updated stock quantity: $updatedStock");
    
    if ($updatedStock != $newStock) {
        throw new Exception("Stock update failed");
    }
    
    // Calculate stock revenue and profits
    $stock_revenue = $data['selling_price'] * $quantity_sold;
    $total_cost = $data['purchase_price'] * $quantity_sold;
    $gross_profit = $stock_revenue - $total_cost;
    
    error_log("Calculated metrics - Revenue: $stock_revenue, Cost: $total_cost, Gross Profit: $gross_profit");
    
    // Get any existing expenses for this date
    $expenseStmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses WHERE date = :date_of_sale");
    $expenseStmt->bindParam(':date_of_sale', $data['date_of_sale']);
    $expenseStmt->execute();
    $expenses = $expenseStmt->fetchColumn();
    
    $net_profit = $gross_profit - $expenses;
    error_log("Calculated net profit: $net_profit (Expenses: $expenses)");
    
    // Insert into sales table
    $stmt = $conn->prepare("INSERT INTO sales (sales_id, stock_id, product_id, selling_price, quantity_sold, stock_revenue, date_of_sale, purchase_price, gross_profit, net_profit)
                           VALUES (NULL, :stock_id, :product_id, :selling_price, :quantity_sold, :stock_revenue, :date_of_sale, :purchase_price, :gross_profit, :net_profit)");
    
    $stmt->bindParam(':stock_id', $data['stock_id']);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':selling_price', $data['selling_price']);
    $stmt->bindParam(':quantity_sold', $quantity_sold);
    $stmt->bindParam(':stock_revenue', $stock_revenue);
    $stmt->bindParam(':date_of_sale', $data['date_of_sale']);
    $stmt->bindParam(':purchase_price', $data['purchase_price']);
    $stmt->bindParam(':gross_profit', $gross_profit);
    $stmt->bindParam(':net_profit', $net_profit);
    
    try {
        $stmt->execute();
        $sales_id = $conn->lastInsertId();
        error_log("Sales record created successfully with ID: $sales_id");
    } catch (PDOException $e) {
        error_log("Error creating sales record: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Error Info: " . print_r($stmt->errorInfo(), true));
        throw $e;
    }
    
    // Insert into profit table
    $profitStmt = $conn->prepare("INSERT INTO profit (sales_id, total_sales, gross_profit, expenses, net_profit) 
                                VALUES (:sales_id, :total_sales, :gross_profit, :expenses, :net_profit)");
    
    $profitStmt->bindParam(':sales_id', $sales_id);
    $profitStmt->bindParam(':total_sales', $stock_revenue);
    $profitStmt->bindParam(':gross_profit', $gross_profit);
    $profitStmt->bindParam(':expenses', $expenses);
    $profitStmt->bindParam(':net_profit', $net_profit);
    
    $profitStmt->execute();
    
    error_log("Profit record created");
    
    // Update stock record
    $updateStockStmt = $conn->prepare("UPDATE stocks SET quantity_sold = quantity_sold + :quantity_sold 
                                     WHERE stock_id = :stock_id");
    $updateStockStmt->bindParam(':quantity_sold', $quantity_sold);
    $updateStockStmt->bindParam(':stock_id', $data['stock_id']);
    $updateStockStmt->execute();
    
    error_log("Stock record updated");
    
    // Commit transaction
    $conn->commit();
    
    error_log("Transaction committed successfully");
    
    // Return success response with detailed information
    echo json_encode([
        'success' => true, 
        'message' => 'Sale recorded successfully',
        'data' => [
            'sales_id' => $sales_id,
            'stock_id' => $data['stock_id'],
            'product_id' => $product_id,
            'quantity_sold' => $quantity_sold,
            'remaining_stock' => $newStock,
            'total_sales' => $stock_revenue,
            'gross_profit' => $gross_profit,
            'expenses' => $expenses,
            'net_profit' => $net_profit
        ]
    ]);
    
} catch (PDOException $e) {
    // Roll back the transaction if anything failed
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Database error recording sale: " . $e->getMessage());
    error_log("Error details: " . print_r($e->errorInfo, true));
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'error_details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Roll back the transaction if anything failed
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error recording sale: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

error_log("=== RECORD SALE PROCESS ENDED ===");
?>