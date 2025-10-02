<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    try {
        $reservation_id = $_POST['reservation_id'];
        
        // Update the archive status to 0 (Not Archived) in the reservations table
        $update_stmt = $conn->prepare("UPDATE reservations SET archived = 0 WHERE reservation_id = ? AND archived = 1");
        $update_stmt->execute([$reservation_id]);
        
        if ($update_stmt->rowCount() > 0) {
            echo "success";
        } else {
            http_response_code(404);
            echo "Reservation not found or not archived";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}
?> 