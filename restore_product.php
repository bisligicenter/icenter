<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['product_id'])) {
        $productId = $input['product_id'];

        try {
            // Restore the product by setting archived to 0
            $stmt = $conn->prepare("UPDATE products SET archived = 0 WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Product restored successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to restore the product.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Product ID not provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
