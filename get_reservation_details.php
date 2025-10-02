<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Check if ID parameter is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Reservation ID is required'
        ]);
        exit;
    }

    $reservationId = intval($_GET['id']);
    
    // Debug logging
    error_log("get_reservation_details.php: Requesting reservation ID: " . $reservationId);
    
    // Use the global database connection
    global $conn;
    if (!$conn) {
        error_log("get_reservation_details.php: Database connection failed");
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit;
    }

    // First, check if proof_of_payment column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM reservations LIKE 'proof_of_payment'");
    if ($checkColumn->rowCount() == 0) {
        error_log("get_reservation_details.php: proof_of_payment column does not exist, adding it");
        try {
            $conn->exec("ALTER TABLE reservations ADD COLUMN proof_of_payment VARCHAR(255) NULL");
            error_log("get_reservation_details.php: proof_of_payment column added successfully");
        } catch (Exception $e) {
            error_log("get_reservation_details.php: Error adding proof_of_payment column: " . $e->getMessage());
        }
    }

    // Fetch reservation details
    $stmt = $conn->prepare("
        SELECT * FROM reservations 
        WHERE reservation_id = ? AND (archived IS NULL OR archived = 0)
    ");
    
    if (!$stmt) {
        error_log("get_reservation_details.php: Database prepare error");
        echo json_encode([
            'success' => false,
            'message' => 'Database prepare error'
        ]);
        exit;
    }

    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        error_log("get_reservation_details.php: Reservation not found for ID: " . $reservationId);
        echo json_encode([
            'success' => false,
            'message' => 'Reservation not found'
        ]);
        exit;
    }

    error_log("get_reservation_details.php: Successfully retrieved reservation ID: " . $reservationId);
    error_log("get_reservation_details.php: Reservation data: " . print_r($reservation, true));
    error_log("get_reservation_details.php: Proof of payment value: " . ($reservation['proof_of_payment'] ?? 'NOT SET'));

    // Return the reservation data
    echo json_encode([
        'success' => true,
        'data' => $reservation
    ]);

} catch (Exception $e) {
    error_log("Error in get_reservation_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred: ' . $e->getMessage()
    ]);
}
?> 