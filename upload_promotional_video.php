<?php
// filepath: c:\xampp\htdocs\admin\upload_promotional_video.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);

header('Content-Type: application/json');
require_once 'db.php';
require_once 'functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    jsonError('Unauthorized access');
}

if (!isset($_FILES['video'])) {
    jsonError('No video uploaded. $_FILES: ' . json_encode($_FILES));
}

if ($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];
    $errorMsg = $uploadErrors[$_FILES['video']['error']] ?? 'Unknown upload error';
    jsonError('Upload error: ' . $errorMsg);
}

$slot = validateSlot($_POST['slot'] ?? 0);
if (!$slot) {
    jsonError('Invalid slot');
}

// Enhanced file validation
$allowedExts = ['mp4', 'webm', 'mov', 'avi', 'mkv'];
$allowedMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];
$originalName = basename($_FILES['video']['name']);
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$mimeType = $_FILES['video']['type'];

// Check file extension
if (!in_array($ext, $allowedExts)) {
    jsonError('Invalid video format. Allowed formats: ' . implode(', ', $allowedExts));
}

// Check MIME type
if (!in_array($mimeType, $allowedMimes)) {
    jsonError('Invalid file type detected');
}

// Check file size (100MB limit)
$maxSize = 100 * 1024 * 1024; // 100MB in bytes
if ($_FILES['video']['size'] > $maxSize) {
    jsonError('File size exceeds 100MB limit');
}

// Server-side check for duplicate filename
try {
    $stmt = $conn->prepare("SELECT slot FROM promotional_videos WHERE filename = :filename");
    $stmt->execute([':filename' => $originalName]);
    $existingVideo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingVideo && $existingVideo['slot'] != $slot) {
        jsonError('This video already exists in slot ' . $existingVideo['slot'] . '. Please upload a different video.');
    }
} catch (Exception $e) {
    jsonError('DB error during duplicate check: ' . $e->getMessage());
}

$uploadDir = __DIR__ . '/promotional_videos';
ensureDirectory($uploadDir);
if (is_dir($uploadDir)) {
    chmod($uploadDir, 0755);
}

// Generate unique filename to prevent conflicts
$filename = $originalName;
$counter = 1;
$targetPath = $uploadDir . '/' . $filename;

// If file exists, add counter to filename
while (file_exists($targetPath)) {
    $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $filename = $nameWithoutExt . '_' . $counter . '.' . $ext;
    $targetPath = $uploadDir . '/' . $filename;
    $counter++;
}

if (move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO promotional_videos (slot, filename, title, description)
            VALUES (:slot, :filename, :title, :description)
            ON DUPLICATE KEY UPDATE filename = VALUES(filename), uploaded_at = CURRENT_TIMESTAMP
        ");
        
        // Fetch existing title/desc or use defaults
        $meta_stmt = $conn->prepare("SELECT title, description FROM promotional_videos WHERE slot = :slot");
        $meta_stmt->execute([':slot' => $slot]);
        $meta = $meta_stmt->fetch(PDO::FETCH_ASSOC);

        $title = $meta['title'] ?? 'Video ' . $slot;
        $description = $meta['description'] ?? 'Upload a promotional video for your store or product.';

        $stmt->execute([
            ':slot' => $slot,
            ':filename' => $filename,
            ':title' => $title,
            ':description' => $description
        ]);
        
        $publicUrl = 'promotional_videos/' . $filename;
        echo json_encode(['success' => true, 'url' => $publicUrl, 'filename' => $filename]);
    } catch (Exception $e) {
        // If something goes wrong with DB, delete the orphaned file
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }
        jsonError('DB error: ' . $e->getMessage());
    }
} else {
    $errorMsg = 'Failed to move uploaded file. ';
    $errorMsg .= 'Target path: ' . $targetPath . '. ';
    $errorMsg .= 'Is uploaded file: ' . (is_uploaded_file($_FILES['video']['tmp_name']) ? 'yes' : 'no') . '. ';
    $errorMsg .= 'File size: ' . $_FILES['video']['size'] . ' bytes. ';
    $errorMsg .= 'Folder writable: ' . (is_writable($uploadDir) ? 'yes' : 'no') . '. ';
    jsonError($errorMsg);
}
?>