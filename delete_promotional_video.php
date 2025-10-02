<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    jsonError('Unauthorized access');
}

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['slot'])) {
    jsonError('Missing slot');
}
$slot = validateSlot($data['slot']);
if (!$slot) {
    jsonError('Invalid slot');
}

try {
    // Fetch current filename for this slot
    $stmt = $conn->prepare('SELECT filename FROM promotional_videos WHERE slot = :slot AND is_archived = 0');
    $stmt->execute([':slot' => $slot]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['filename'])) {
        jsonError('No active video found for this slot');
    }
    $filename = $row['filename'];
    $filePath = __DIR__ . '/promotional_videos/' . $filename;

    // Delete the file if it exists
    $fileDeleted = true;
    if (file_exists($filePath)) {
        $fileDeleted = unlink($filePath);
    }

    // Remove the row for this slot from the database
    $stmt = $conn->prepare('DELETE FROM promotional_videos WHERE slot = :slot AND is_archived = 0');
    $stmt->execute([':slot' => $slot]);

    if ($stmt->rowCount() > 0) {
        if ($fileDeleted) {
            echo json_encode(['success' => true, 'message' => 'Video deleted successfully']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Video deleted from database', 'notice' => 'File not found or could not be deleted from server']);
        }
    } else {
        jsonError('Failed to delete video from database');
    }
} catch (Exception $e) {
    jsonError('Error: ' . $e->getMessage());
} 