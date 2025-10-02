<?php
// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id']) && isset($_POST['status'])) {
    try {
        $reservation_id = $_POST['reservation_id'];
        $status = $_POST['status'];
        
        
        error_log("update_status.php: Request received");
        error_log("update_status.php: POST data: " . print_r($_POST, true));
        error_log("update_status.php: reservation_id type: " . gettype($reservation_id) . ", value: '$reservation_id'");
        error_log("update_status.php: status type: " . gettype($status) . ", value: '$status'");
        error_log("update_status.php: Updating reservation ID: $reservation_id to status: $status");
        
        // Start transaction
        $conn->beginTransaction();
        
        // First, check if reservation exists and get current status
        $check_stmt = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
        error_log("update_status.php: Executing query: SELECT status FROM reservations WHERE reservation_id = '$reservation_id'");
        $check_stmt->execute([$reservation_id]);
        $current_status = $check_stmt->fetchColumn();
        
        error_log("update_status.php: Query result - current_status: " . ($current_status === false ? 'FALSE' : "'$current_status'"));
        
        if ($current_status === false) {
            // Reservation not found
            $conn->rollBack();
            error_log("update_status.php: Reservation $reservation_id not found");
            http_response_code(404);
            echo "Reservation not found";
            exit;
        }
        
        error_log("update_status.php: Current status for reservation $reservation_id: $current_status");
        
        // Check if status is already the same
        if (strtolower(trim($current_status)) === strtolower(trim($status))) {
            $conn->rollBack();
            error_log("update_status.php: Status already $status for reservation $reservation_id");
            echo "success"; // Return success even if no change needed
            exit;
        }
        
        // Update the reservation status
        $update_stmt = $conn->prepare("
            UPDATE reservations 
            SET status = ? 
            WHERE reservation_id = ?
        ");
        
        $update_result = $update_stmt->execute([
            $status,
            $reservation_id
        ]);
        
        error_log("update_status.php: Update executed, result: " . ($update_result ? 'true' : 'false') . ", rows affected: " . $update_stmt->rowCount());
        
        // Verify the update
        $verify_stmt = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
        $verify_stmt->execute([$reservation_id]);
        $new_status = $verify_stmt->fetchColumn();
        error_log("update_status.php: New status for reservation $reservation_id: $new_status");
        
        // Check if the update was successful by comparing the new status
        if (strtolower(trim($new_status)) === strtolower(trim($status))) {
            // Commit the transaction
            $conn->commit();
            error_log("update_status.php: Transaction committed successfully");
            echo "success";
        } else {
            // Rollback the transaction
            $conn->rollBack();
            error_log("update_status.php: Update failed - expected: $status, got: $new_status");
            http_response_code(500);
            echo "Update failed - status mismatch";
        }
    } catch (PDOException $e) {
        // Rollback the transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("update_status.php: Database error: " . $e->getMessage());
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {

    http_response_code(400);
    echo "Invalid request";
}
?> 