<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    // Get JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    // Validate required fields
    if (empty($data['product_id'])) {
        throw new Exception('Product ID is required');
    }
    // No quantity check or logic here
    // Fetch product details
    $product_query = "SELECT product, brand, model, storage FROM products WHERE product_id = ?";
    $product_stmt = $pdo->prepare($product_query);
    $product_stmt->execute([$data['product_id']]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        throw new Exception('Product not found');
    }
    $customer_name = $data['customer_name'] ?? null;
    $contact_number = $data['contact_number'] ?? null;
    $returned_to = $data['returned_to'] ?? null;
    $reason = $data['reason'] ?? '';
    $returned_by = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Unknown');
    $returns_query = "INSERT INTO returns (
        product_id, product_name, brand, model, storage,
        reason, returned_by, returned_to, customer_name, contact_number, return_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $returns_stmt = $pdo->prepare($returns_query);
    $returns_stmt->execute([
        $data['product_id'],
        $product['product'],
        $product['brand'],
        $product['model'],
        $product['storage'],
        $reason,
        $returned_by,
        $returned_to,
        $customer_name,
        $contact_number
    ]);
    echo json_encode([
        'success' => true,
        'message' => 'Return recorded successfully.'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in process_return.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 