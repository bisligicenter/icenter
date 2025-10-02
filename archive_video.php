<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    jsonError('Unauthorized access');
}

$data = json_decode(file_get_contents('php://input'), true);
$slot = validateSlot($data['slot'] ?? 0);

if (!$slot) {
    jsonError('Invalid slot provided.');
}

try {
    // Check if video exists and is not already archived
    $stmt = $conn->prepare("SELECT slot, filename FROM promotional_videos WHERE slot = :slot AND is_archived = 0");
    $stmt->execute([':slot' => $slot]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$video) {
        jsonError('No active video found in slot ' . $slot . ' or video is already archived.');
    }

    $stmt = $conn->prepare("UPDATE promotional_videos SET is_archived = 1 WHERE slot = :slot AND is_archived = 0");
    $stmt->execute([':slot' => $slot]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Video archived successfully']);
    } else {
        jsonError('Failed to archive video. Video may have been archived by another user.');
    }
} catch (Exception $e) {
    jsonError('Database error: ' . $e->getMessage());
}
?>