<?php
session_start();
require_once 'db.php';

// It's good practice to use the Composer autoloader, but for this case, we'll require the files directly.
require 'phpmailer/PHPMailer-master/src/Exception.php';
require 'phpmailer/PHPMailer-master/src/PHPMailer.php';
require 'phpmailer/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

function sendLowStockAlert($pdo, $productId, $newStock) {
    // Fetch product details for the email
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stmt->execute([':product_id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        return; // Product not found
    }

    // --- IMPORTANT: CONFIGURE YOUR EMAIL SETTINGS HERE ---
    $adminEmail = 'bisligicenter@gmail.com'; // Set the admin's email address

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-smtp-username@example.com'; // Your SMTP username
        $mail->Password   = 'your-smtp-password'; // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('no-reply@icenter.com', 'iCenter Stock Alert');
        $mail->addAddress($adminEmail, 'Admin');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Low Stock Alert: ' . $product['brand'] . ' ' . $product['model'];
        $mail->Body    = "
            <h1>Low Stock Alert</h1>
            <p>This is an automated notification to inform you that a product's stock is running low.</p>
            <table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>
                <tr><td style='background-color: #f2f2f2;'><strong>Product ID:</strong></td><td>{$product['product_id']}</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Product Type:</strong></td><td>" . htmlspecialchars($product['product']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Brand:</strong></td><td>" . htmlspecialchars($product['brand']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Model:</strong></td><td>" . htmlspecialchars($product['model']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Storage:</strong></td><td>" . htmlspecialchars($product['storage']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>New Stock Quantity:</strong></td><td style='color: red; font-weight: bold;'>{$newStock}</td></tr>
            </table>
            <p>Please consider restocking this item soon.</p>
        ";
        $mail->AltBody = "Low Stock Alert for " . htmlspecialchars($product['brand'] . ' ' . $product['model']) . ". New stock is {$newStock}.";

        $mail->send();
    } catch (Exception $e) {
        // Optional: Log the error to a file instead of exposing it.
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}

function sendOutOfStockAlert($pdo, $productId) {
    // Fetch product details for the email
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stmt->execute([':product_id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        return; // Product not found
    }

    // --- IMPORTANT: CONFIGURE YOUR EMAIL SETTINGS HERE ---
    $adminEmail = 'bisligicenter@gmail.com'; // Set the admin's email address

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-smtp-username@example.com'; // Your SMTP username
        $mail->Password   = 'your-smtp-password'; // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('no-reply@icenter.com', 'iCenter Stock Alert');
        $mail->addAddress($adminEmail, 'Admin');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'URGENT: Out of Stock Alert - ' . $product['brand'] . ' ' . $product['model'];
        $mail->Body    = "
            <h1 style='color: #d9534f;'>Out of Stock Alert</h1>
            <p>This is an automated notification to inform you that a product is now <strong>out of stock</strong>.</p>
            <table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>
                <tr><td style='background-color: #f2f2f2;'><strong>Product ID:</strong></td><td>{$product['product_id']}</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Product Type:</strong></td><td>" . htmlspecialchars($product['product']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Brand:</strong></td><td>" . htmlspecialchars($product['brand']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Model:</strong></td><td>" . htmlspecialchars($product['model']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Storage:</strong></td><td>" . htmlspecialchars($product['storage']) . "</td></tr>
                <tr><td style='background-color: #f2f2f2;'><strong>Stock Quantity:</strong></td><td style='color: #d9534f; font-weight: bold;'>0</td></tr>
            </table>
            <p>Please restock this item immediately to avoid losing sales.</p>
        ";
        $mail->AltBody = "URGENT: Out of Stock Alert for " . htmlspecialchars($product['brand'] . ' ' . $product['model']) . ". Stock is now 0.";

        $mail->send();
    } catch (Exception $e) {
        // Optional: Log the error to a file instead of exposing it.
        error_log("Mailer Error (Out of Stock): {$mail->ErrorInfo}");
    }
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['product_id']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$productId = filter_var($data['product_id'], FILTER_VALIDATE_INT);
$quantitySold = filter_var($data['quantity'], FILTER_VALIDATE_INT);

if ($productId === false || $quantitySold === false || $quantitySold <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity.']);
    exit;
}

try {
    $conn->beginTransaction();

    // Get current stock and lock the row
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = :product_id FOR UPDATE");
    $stmt->execute([':product_id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found.');
    }

    $currentStock = (int)$product['stock_quantity'];

    if ($quantitySold > $currentStock) {
        throw new Exception('Quantity exceeds current stock.');
    }

    // Update stock quantity
    $newStock = $currentStock - $quantitySold;
    $updateStmt = $conn->prepare("UPDATE products SET stock_quantity = :new_stock WHERE product_id = :product_id");
    $updateStmt->execute([':new_stock' => $newStock, ':product_id' => $productId]);

    // Record the sale
    $saleStmt = $conn->prepare("INSERT INTO sales (product_id, quantity_sold, sale_date) VALUES (:product_id, :quantity, NOW())");
    $saleStmt->execute([':product_id' => $productId, ':quantity' => $quantitySold]);

    $conn->commit();

    // Check for out of stock or low stock and send email AFTER the transaction is committed
    $lowStockThreshold = 5;
    if ($newStock == 0 && $currentStock > 0) {
        sendOutOfStockAlert($conn, $productId);
    } elseif ($newStock <= $lowStockThreshold && $currentStock > $lowStockThreshold) {
        sendLowStockAlert($conn, $productId, $newStock);
    }

    echo json_encode(['success' => true, 'message' => 'Sale recorded successfully.']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>