<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

/**
 * Sends an SMS message.
 * In a real application, this function would be in a shared utility file
 * and would integrate with a real SMS provider like Twilio.
 * @param string $phone The recipient's phone number.
 * @param string $message The message to send.
 * @return bool True on success, false on failure.
 */
function sendSMS($phone, $message) {
    // This is a placeholder function.
    // Replace this with your actual SMS provider's API integration.
    error_log("Simulating SMS send to $phone: $message");
    // For demonstration, we'll just simulate success.
    return true;
}

// Get JSON input from the request body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$message = trim($input['message'] ?? '');
$sendToAll = $input['sendToAll'] ?? false;
$selectedCustomers = $input['selectedCustomers'] ?? []; // These are contact numbers

if (empty($message)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit();
}

try {
    $numbers = [];
    if ($sendToAll) {
        $stmt = $conn->query("SELECT DISTINCT contact_number FROM reservations WHERE contact_number IS NOT NULL AND contact_number != ''");
        $numbers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $numbers = $selectedCustomers;
    }

    if (empty($numbers)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No valid phone numbers found']);
        exit();
    }

    $sentCount = 0;
    $failedCount = 0;

    foreach ($numbers as $number) {
        if (sendSMS($number, $message)) {
            $sentCount++;
        } else {
            $failedCount++;
            error_log("Failed to send SMS to $number");
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => "SMS campaign finished. Sent: $sentCount, Failed: $failedCount"]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>