<?php
require_once 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("=== PROCESS SOLD ITEM STARTED ===");

// Handle stock deduction form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deduct_stock'])) {
    error_log("POST data received: " . print_r($_POST, true));
    
    $productId = $_POST['product_id'] ?? '';
    $quantitySold = $_POST['quantity_sold'] ?? 0;
    $quantitySold = intval($quantitySold);

    error_log("Processing sale - Product ID: $productId, Quantity: $quantitySold");

    if ($productId && $quantitySold > 0) {
        try {
            // Get current stock and price information using PDO
            $stmt = $conn->prepare("SELECT stock_quantity, purchase_price, selling_price FROM products WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Product data retrieved: " . print_r($product, true));
            
            if ($product !== false) {
                $currentStock = $product['stock_quantity'];
                error_log("Current stock quantity: $currentStock");
                
                if ($quantitySold <= $currentStock) {
                    // Begin transaction
                    $conn->beginTransaction();
                    error_log("Transaction started");
                    
                    try {
                        // Deduct stock
                        $newStock = $currentStock - $quantitySold;
                        error_log("Updating stock to new quantity: $newStock");
                        
                        $updateStmt = $conn->prepare("UPDATE products SET stock_quantity = :new_stock WHERE product_id = :product_id");
                        $updateStmt->bindParam(':new_stock', $newStock, PDO::PARAM_INT);
                        $updateStmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
                        $updateStmt->execute();
                        
                        // Check if a stock entry exists for this product
                        $stmtStock = $conn->prepare("SELECT stock_id FROM stocks WHERE product_id = :product_id ORDER BY date_of_purchase DESC LIMIT 1");
                        $stmtStock->bindParam(':product_id', $productId, PDO::PARAM_STR);
                        $stmtStock->execute();
                        $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);
                        
                        error_log("Stock record found: " . print_r($stock, true));
                        
                        // If no stock entry exists, create one
                        if (!$stock) {
                            error_log("Creating new stock record");
                            $stmtInsertStock = $conn->prepare("INSERT INTO stocks (product_id, purchase_price, date_of_purchase) VALUES (:product_id, :purchase_price, :date_of_purchase)");
                            $stmtInsertStock->bindParam(':product_id', $productId, PDO::PARAM_STR);
                            $stmtInsertStock->bindParam(':purchase_price', $product['purchase_price'], PDO::PARAM_STR);
                            $purchaseDate = date('Y-m-d');
                            $stmtInsertStock->bindParam(':date_of_purchase', $purchaseDate, PDO::PARAM_STR);
                            $stmtInsertStock->execute();
                            
                            $stockId = $conn->lastInsertId();
                            error_log("New stock record created with ID: $stockId");
                        } else {
                            $stockId = $stock['stock_id'];
                            error_log("Using existing stock record with ID: $stockId");
                        }
                        
                        // Prepare sale data
                        $saleData = [
                            'stock_id' => $stockId,
                            'selling_price' => $product['selling_price'],
                            'quantity_sold' => $quantitySold,
                            'date_of_sale' => date('Y-m-d')
                        ];
                        
                        error_log("Prepared sale data: " . print_r($saleData, true));
                        
                        // Make the API call to record_sale.php
                        $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/admin/record_sale.php');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saleData));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        
                        error_log("Sending request to record_sale.php");
                        $response = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        
                        if (curl_errno($ch)) {
                            throw new Exception("cURL error: " . curl_error($ch));
                        }
                        
                        curl_close($ch);
                        
                        error_log("Response from record_sale.php - HTTP Code: $httpCode, Response: $response");
                        
                        if ($httpCode != 200) {
                            throw new Exception("Failed to record sale. HTTP status: $httpCode");
                        }
                        
                        $responseData = json_decode($response, true);
                        if (!$responseData || !isset($responseData['success']) || !$responseData['success']) {
                            throw new Exception($responseData['message'] ?? 'Failed to record sale');
                        }
                        
                        // Commit the transaction
                        $conn->commit();
                        error_log("Transaction committed successfully");
                        
                        // Set success message
                        $_SESSION['success_message'] = "Product sold successfully. Stock updated.";
                        
                        // Redirect back to products page
                        header("Location: view_products.php?success=true");
                        exit();
                        
                    } catch (Exception $e) {
                        // Roll back the transaction if anything failed
                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                            error_log("Transaction rolled back due to error: " . $e->getMessage());
                        }
                        throw $e;
                    }
                } else {
                    error_log("Quantity sold ($quantitySold) exceeds current stock ($currentStock)");
                    $_SESSION['error_message'] = "Quantity sold exceeds current stock.";
                    header("Location: view_products.php?error=stock_exceeded");
                    exit();
                }
            } else {
                error_log("Product not found for ID: $productId");
                $_SESSION['error_message'] = "Product not found.";
                header("Location: view_products.php?error=product_not_found");
                exit();
            }
        } catch (Exception $e) {
            error_log("Error processing sale: " . $e->getMessage());
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header("Location: view_products.php?error=processing_error");
            exit();
        }
    } else {
        error_log("Invalid input - Product ID: $productId, Quantity: $quantitySold");
        $_SESSION['error_message'] = "Invalid input.";
        header("Location: view_products.php?error=invalid_input");
        exit();
    }
}

error_log("=== PROCESS SOLD ITEM ENDED ===");
?>