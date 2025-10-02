<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// IMPORTANT: Replace with your Facebook Page Access Token
$pageAccessToken = 'PASTE_YOUR_PAGE_ACCESS_TOKEN_HERE';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$message = trim($input['message'] ?? '');
$sendToAll = $input['sendToAll'] ?? false;
$selectedCustomers = $input['selectedCustomers'] ?? []; // These are PSIDs

if (empty($message)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit();
}

if ($pageAccessToken === 'PASTE_YOUR_PAGE_ACCESS_TOKEN_HERE' || empty($pageAccessToken)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Messenger API is not configured. Please set a Page Access Token.']);
    exit();
}

try {
    $psids = [];
    if ($sendToAll) {
        $stmt = $conn->query("SELECT DISTINCT messenger_psid FROM reservations WHERE messenger_psid IS NOT NULL AND messenger_psid != ''");
        $psids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $psids = $selectedCustomers;
    }

    if (empty($psids)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No valid Messenger recipients found']);
        exit();
    }

    $sentCount = 0;
    $failedCount = 0;

    foreach ($psids as $psid) {
        $requestBody = [
            'recipient' => ['id' => $psid],
            'message' => ['text' => $message],
            'messaging_type' => 'MESSAGE_TAG', // Or 'UPDATE' or 'RESPONSE'
            'tag' => 'POST_PURCHASE_UPDATE' // Necessary for sending messages outside the 24-hour window
        ];

        $ch = curl_init('https://graph.facebook.com/v19.0/me/messages?access_token=' . $pageAccessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $sentCount++;
        } else {
            $failedCount++;
            // You might want to log the error response from Facebook for debugging
            error_log("Failed to send to PSID $psid: $response");
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => "Messenger campaign finished. Sent: $sentCount, Failed: $failedCount"]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>