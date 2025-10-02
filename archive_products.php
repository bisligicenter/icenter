<?php
require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['product_id'])) {
    $productId = $input['product_id'];

    try {
        // Archive the product by updating its status
        $stmt = $conn->prepare("UPDATE products SET archived = 1 WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product archived successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to archive the product.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
