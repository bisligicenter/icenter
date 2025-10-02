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
    // Check if the slot is already occupied by an active video
    $stmt = $conn->prepare("SELECT slot FROM promotional_videos WHERE slot = :slot AND is_archived = 0");
    $stmt->execute([':slot' => $slot]);
    if ($stmt->fetch()) {
        jsonError('This slot is already occupied by an active video. Please archive it first.');
    }

    // Check if there's an archived video in this slot
    $stmt = $conn->prepare("SELECT slot, filename FROM promotional_videos WHERE slot = :slot AND is_archived = 1");
    $stmt->execute([':slot' => $slot]);
    $archivedVideo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$archivedVideo) {
        jsonError('No archived video found in slot ' . $slot);
    }

    $stmt = $conn->prepare("UPDATE promotional_videos SET is_archived = 0 WHERE slot = :slot AND is_archived = 1");
    $stmt->execute([':slot' => $slot]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Video unarchived successfully']);
    } else {
        jsonError('Failed to unarchive video. Video may have been unarchived by another user.');
    }
} catch (Exception $e) {
    jsonError('Database error: ' . $e->getMessage());
}
?>