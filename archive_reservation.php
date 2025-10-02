<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    try {
        $reservation_id = $_POST['reservation_id'];
        
        // First, check if the archived column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM reservations LIKE 'archived'");
        $columnExists = $checkColumn->rowCount() > 0;
        
        if ($columnExists) {
            // Update the archive status to 1 (Yes/Archived) in the reservations table
            $update_stmt = $conn->prepare("UPDATE reservations SET archived = 1 WHERE reservation_id = ? AND (archived = 0 OR archived IS NULL)");
            $update_stmt->execute([$reservation_id]);
        } else {
            // If archived column doesn't exist, change status to 'archived' instead
            $update_stmt = $conn->prepare("UPDATE reservations SET status = 'archived' WHERE reservation_id = ?");
            $update_stmt->execute([$reservation_id]);
        }
        
        if ($update_stmt->rowCount() > 0) {
            echo "success";
        } else {
            http_response_code(404);
            echo "Reservation not found or already archived";
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