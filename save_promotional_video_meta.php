<?php
ini_set('post_max_size', '100M');
ini_set('max_input_time', 300);
header('Content-Type: application/json');
require_once 'db.php';
require_once 'functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    jsonError('Unauthorized access');
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['slot'], $data['title'], $data['description'])) {
    jsonError('Missing data. Input: ' . json_encode($data));
}

$slot = validateSlot($data['slot'] ?? 0);
if (!$slot) {
    jsonError('Invalid slot: ' . $data['slot']);
}

$title = trim($data['title']);
$description = trim($data['description']);

if ($title === '' || $description === '') {
    jsonError('Title and description cannot be empty.');
}

// Sanitize inputs
$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

// Limit lengths
if (strlen($title) > 255) {
    jsonError('Title is too long. Maximum 255 characters allowed.');
}

if (strlen($description) > 1000) {
    jsonError('Description is too long. Maximum 1000 characters allowed.');
}

try {
    $stmt = $conn->prepare("
        INSERT INTO promotional_videos (slot, title, description)
        VALUES (:slot, :title, :description)
        ON DUPLICATE KEY UPDATE title = :title_update, description = :description_update, uploaded_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([
        ':slot' => $slot,
        ':title' => $title,
        ':description' => $description,
        ':title_update' => $title,
        ':description_update' => $description
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    jsonError('DB error: ' . $e->getMessage());
}
