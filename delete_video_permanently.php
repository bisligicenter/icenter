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
$id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
$filename = basename($data['filename'] ?? '');

if (!$id || !$filename) {
    jsonError('Invalid ID or filename provided.');
}

try {
    // First, verify the video exists and is archived
    $stmt = $conn->prepare("SELECT id, filename FROM promotional_videos WHERE id = :id AND filename = :filename AND is_archived = 1");
    $stmt->execute([':id' => $id, ':filename' => $filename]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$video) {
        jsonError('Archived video not found or invalid parameters.');
    }

    // Delete the database record
    $stmt = $conn->prepare("DELETE FROM promotional_videos WHERE id = :id AND filename = :filename AND is_archived = 1");
    $stmt->execute([':id' => $id, ':filename' => $filename]);

    if ($stmt->rowCount() > 0) {
        // If DB deletion was successful, delete the file
        $filePath = __DIR__ . '/promotional_videos/' . $filename;
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                echo json_encode(['success' => true, 'message' => 'Video deleted permanently']);
            } else {
                // This case is tricky. The DB record is gone but the file remains.
                // For now, we'll report an error to the user.
                jsonError('Failed to delete the video file from the server.');
            }
        } else {
            // The file was already gone, but the DB record was deleted, so we'll call it a success.
            echo json_encode(['success' => true, 'message' => 'Video deleted permanently', 'notice' => 'File was not found on server, but database record was removed.']);
        }
    } else {
        jsonError('Failed to delete video from database.');
    }
} catch (Exception $e) {
    jsonError('Database error: ' . $e->getMessage());
}
?>