<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Database connection
require_once 'db.php';

try {
    // Total reservations (non-archived)
    $totalStmt = $conn->query("SELECT COUNT(*) as total FROM reservations WHERE (archived IS NULL OR archived = 0)");
    $totalCount = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Completed reservations (non-archived)
    $completedStmt = $conn->query("SELECT COUNT(*) as completed FROM reservations WHERE (archived IS NULL OR archived = 0) AND (STATUS = 'completed' OR STATUS = 'complete' OR STATUS = 'done')");
    $completedCount = $completedStmt->fetch(PDO::FETCH_ASSOC)['completed'];
    
    // Pending reservations (non-archived)
    $pendingStmt = $conn->query("SELECT COUNT(*) as pending FROM reservations WHERE (archived IS NULL OR archived = 0) AND (STATUS IS NULL OR STATUS = '' OR STATUS = 'pending' OR STATUS != 'completed' AND STATUS != 'complete' AND STATUS != 'done')");
    $pendingCount = $pendingStmt->fetch(PDO::FETCH_ASSOC)['pending'];
    
    // Total archived reservations
    $totalArchivedStmt = $conn->query("SELECT COUNT(*) as total_archived FROM reservations WHERE archived = 1");
    $totalArchivedCount = $totalArchivedStmt->fetch(PDO::FETCH_ASSOC)['total_archived'];
    
    // Completed archived reservations
    $completedArchivedStmt = $conn->query("SELECT COUNT(*) as completed_archived FROM reservations WHERE archived = 1 AND (STATUS = 'completed' OR STATUS = 'complete' OR STATUS = 'done')");
    $completedArchivedCount = $completedArchivedStmt->fetch(PDO::FETCH_ASSOC)['completed_archived'];
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'total' => (int)$totalCount,
        'completed' => (int)$completedCount,
        'pending' => (int)$pendingCount,
        'total_archived' => (int)$totalArchivedCount,
        'completed_archived' => (int)$completedArchivedCount
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 