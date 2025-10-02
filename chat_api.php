  <?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';
$conn = getConnection();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'start_session':
            startChatSession($conn);
            break;
        case 'send_message':
            sendMessage($conn);
            break;
        case 'get_messages':
            getMessages($conn);
            break;
        case 'get_sessions':
            getSessions($conn);
            break;
        case 'mark_read':
            markAsRead($conn);
            break;
        case 'close_session':
            closeSession($conn);
            break;
        case 'update_admin_status':
            updateAdminStatus($conn);
            break;
        case 'update_typing_status':
            updateTypingStatus($conn);
            break;
        case 'update_session_info':
            updateSessionInfo($conn);
            break;
        case 'check_read_status':
            checkReadStatus($conn);
            break;
        case 'validate_session':
            validateSession($conn);
            break;
        case 'delete_session':
            deleteSession($conn);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function startChatSession($conn) {
    $customerName = $_POST['customer_name'] ?? 'Anonymous';
    $customerEmail = $_POST['customer_email'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Also set last_message_at to the current time so new sessions appear at the top
    $stmt = $conn->prepare("
        INSERT INTO chat_sessions (customer_name, customer_email, ip_address, user_agent, last_message_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$customerName, $customerEmail, $ipAddress, $userAgent]);
    
    $sessionId = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'message' => 'Chat session started'
    ]);
}

function sendMessage($conn) {
    $sessionId = $_POST['session_id'] ?? 0;
    $senderType = $_POST['sender_type'] ?? 'customer';
    $senderName = $_POST['sender_name'] ?? 'Anonymous';
    $messageText = $_POST['message_text'] ?? '';
    
    if (empty($messageText)) {
        echo json_encode(['error' => 'Message cannot be empty']);
        return;
    }

    // Ensure columns exist for last customer message tracking
    try {
        $conn->exec("ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS last_customer_message_id INT NULL");
        $conn->exec("ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS last_customer_message_at DATETIME NULL");
    } catch (Exception $e) {
        // ignore
    }

    // Deduplication: avoid inserting identical recent message (same session, sender, text)
    $dedupeWindowSeconds = 15; // adjustable window
    $dedupeStmt = $conn->prepare("
        SELECT message_id
        FROM chat_messages
        WHERE session_id = ?
          AND sender_type = ?
          AND sender_name = ?
          AND message_text = ?
          AND created_at >= (NOW() - INTERVAL {$dedupeWindowSeconds} SECOND)
        ORDER BY message_id DESC
        LIMIT 1
    ");
    $dedupeStmt->execute([$sessionId, $senderType, $senderName, $messageText]);
    $existingId = $dedupeStmt->fetchColumn();

    if ($existingId) {
        // Update session last message time since it's effectively a send
        $stmt = $conn->prepare("UPDATE chat_sessions SET last_message_at = CURRENT_TIMESTAMP WHERE session_id = ?");
        $stmt->execute([$sessionId]);

        // Track last customer message for customer-side dedupe if applicable
        if ($senderType === 'customer') {
            $trackStmt = $conn->prepare("UPDATE chat_sessions SET last_customer_message_id = ?, last_customer_message_at = CURRENT_TIMESTAMP WHERE session_id = ?");
            $trackStmt->execute([(int)$existingId, $sessionId]);
        }

        echo json_encode([
            'success' => true,
            'message_id' => (int)$existingId,
            'message' => 'Duplicate suppressed; returning existing message'
        ]);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_name, message_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sessionId, $senderType, $senderName, $messageText]);
    $newMessageId = (int)$conn->lastInsertId();
    
    // Update session last message time
    $stmt = $conn->prepare("UPDATE chat_sessions SET last_message_at = CURRENT_TIMESTAMP WHERE session_id = ?");
    $stmt->execute([$sessionId]);

    // Track last customer message to enable server-side dedupe on first customer poll
    if ($senderType === 'customer') {
        $trackStmt = $conn->prepare("UPDATE chat_sessions SET last_customer_message_id = ?, last_customer_message_at = CURRENT_TIMESTAMP WHERE session_id = ?");
        $trackStmt->execute([$newMessageId, $sessionId]);
    }
    
    echo json_encode([
        'success' => true,
        'message_id' => $newMessageId,
        'message' => 'Message sent successfully'
    ]);
}

function getMessages($conn) {
    $sessionId = $_GET['session_id'] ?? 0;
    $lastMessageId = $_GET['last_message_id'] ?? 0;

    // Optional: avoid duplicating the sender's first local message on initial fetch
    $excludeSenderOnFirstFetch = isset($_GET['exclude_sender_on_first_fetch']) ? (int)$_GET['exclude_sender_on_first_fetch'] : 0;
    $requestingSenderType = $_GET['sender_type'] ?? '';
    $shouldExcludeSender = ($lastMessageId == 0) && $excludeSenderOnFirstFetch === 1 && in_array($requestingSenderType, ['customer', 'admin'], true);

    // Optional: explicitly exclude a list of message IDs (to hide duplicates you already rendered)
    $excludeIdsRaw = $_GET['exclude_ids'] ?? '';
    $excludeIds = array_filter(array_map('intval', array_filter(explode(',', $excludeIdsRaw))), function($v){ return $v > 0; });
    $hasExcludeIds = count($excludeIds) > 0;

    // Fetch session metadata to detect customer-side fetch
    $sessionMetaStmt = $conn->prepare("SELECT ip_address, user_agent, last_customer_message_id, last_customer_message_at FROM chat_sessions WHERE session_id = ?");
    $sessionMetaStmt->execute([$sessionId]);
    $sessionMeta = $sessionMetaStmt->fetch(PDO::FETCH_ASSOC);

    $requestIp = $_SERVER['REMOTE_ADDR'] ?? '';
    $requestUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isLikelyCustomer = $sessionMeta && ($requestIp === ($sessionMeta['ip_address'] ?? '')) && ($requestUa === ($sessionMeta['user_agent'] ?? ''));

    // Build dynamic WHERE clause
    $where = ["session_id = ?", "message_id > ?"];
    $params = [$sessionId, $lastMessageId];

    if ($shouldExcludeSender) {
        $where[] = "sender_type != ?";
        $params[] = $requestingSenderType;
    }

    if ($hasExcludeIds) {
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $where[] = "message_id NOT IN ($placeholders)";
        $params = array_merge($params, $excludeIds);
    }

    // Exclude the last customer message for the very first customer-side fetch to prevent duplication
    if ((int)$lastMessageId === 0 && $isLikelyCustomer && !empty($sessionMeta['last_customer_message_id'])) {
        $where[] = "message_id != ?";
        $params[] = (int)$sessionMeta['last_customer_message_id'];
    }

    $sql = "
        SELECT message_id, sender_type, sender_name, message_text, created_at, is_read
        FROM chat_messages
        WHERE " . implode(' AND ', $where) . "
        ORDER BY created_at ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also fetch typing status for both customer and admin
    $typingStmt = $conn->prepare("SELECT customer_is_typing, admin_is_typing FROM chat_sessions WHERE session_id = ?");
    $typingStmt->execute([$sessionId]);
    $typingStatus = $typingStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'customer_is_typing' => $typingStatus ? (bool)($typingStatus['customer_is_typing'] ?? 0) : false,
        'admin_is_typing' => $typingStatus ? (bool)($typingStatus['admin_is_typing'] ?? 0) : false
    ]);
}

function getSessions($conn) {
    $status = $_GET['status'] ?? 'active';
    $adminId = $_GET['admin_id'] ?? null;
    
    $sql = "SELECT cs.*, 
            (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id = cs.session_id AND cm.is_read = 0 AND cm.sender_type = 'customer') as unread_count,
            (SELECT message_text FROM chat_messages cm WHERE cm.session_id = cs.session_id ORDER BY cm.created_at DESC LIMIT 1) as last_message,
            (SELECT sender_type FROM chat_messages cm WHERE cm.session_id = cs.session_id ORDER BY cm.created_at DESC LIMIT 1) as last_sender,
            (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id = cs.session_id) as total_messages
            FROM chat_sessions cs";
    
    $params = [];
    
    // Handle different status filters
    if ($status === 'all') {
        // Show all sessions (active and closed)
        $sql .= " WHERE cs.session_status IN ('active', 'closed')";
    } else {
        // Show only sessions with specific status
        $sql .= " WHERE cs.session_status = ?";
        $params[] = $status;
    }
    
    if ($adminId) {
        $sql .= " AND cs.admin_id = ?";
        $params[] = $adminId;
    }
    
    $sql .= " ORDER BY cs.last_message_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'sessions' => $sessions
    ]);
}

function markAsRead($conn) {
    $sessionId = $_POST['session_id'] ?? 0;
    $senderType = $_POST['sender_type'] ?? 'customer';
    
    $stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender_type = ?");
    $stmt->execute([$sessionId, $senderType]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Messages marked as read'
    ]);
}

function closeSession($conn) {
    $sessionId = $_POST['session_id'] ?? 0;
    
    $stmt = $conn->prepare("UPDATE chat_sessions SET session_status = 'closed' WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Session closed'
    ]);
}

function updateAdminStatus($conn) {
    $adminId = $_POST['admin_id'] ?? 1;
    $isOnline = $_POST['is_online'] ?? false;
    
    $stmt = $conn->prepare("UPDATE chat_admins SET is_online = ?, last_activity = CURRENT_TIMESTAMP WHERE admin_id = ?");
    $stmt->execute([$isOnline ? 1 : 0, $adminId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin status updated'
    ]);
}

function updateTypingStatus($conn) {
    $sessionId = $_POST['session_id'] ?? 0;
    $senderType = $_POST['sender_type'] ?? '';
    $isTyping = $_POST['is_typing'] ?? 0;

    if (!$sessionId || !$senderType) {
        echo json_encode(['error' => 'Session ID and sender type are required']);
        return;
    }

    $column = ($senderType === 'customer') ? 'customer_is_typing' : 'admin_is_typing';
    
    // Add columns if they don't exist (for robustness)
    try {
        $conn->exec("ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS customer_is_typing TINYINT(1) DEFAULT 0");
        $conn->exec("ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS admin_is_typing TINYINT(1) DEFAULT 0");
    } catch (Exception $e) {
        // Ignore if columns already exist
    }

    $stmt = $conn->prepare("UPDATE chat_sessions SET $column = ? WHERE session_id = ?");
    $stmt->execute([$isTyping, $sessionId]);

    echo json_encode(['success' => true, 'message' => 'Typing status updated']);
}

function checkReadStatus($conn) {
    $sessionId = $_POST['session_id'] ?? 0;
    $messageIds = $_POST['message_ids'] ?? '';

    if (!$sessionId || empty($messageIds)) {
        echo json_encode(['success' => true, 'read_ids' => []]);
        return;
    }

    // Sanitize IDs to prevent SQL injection
    $ids = array_map('intval', explode(',', $messageIds));
    if (empty($ids)) {
        echo json_encode(['success' => true, 'read_ids' => []]);
        return;
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("
        SELECT message_id 
        FROM chat_messages 
        WHERE session_id = ? AND message_id IN ($placeholders) AND is_read = 1
    ");
    $params = array_merge([$sessionId], $ids);
    $stmt->execute($params);
    $read_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'read_ids' => $read_ids]);
}

function updateSessionInfo($conn) {
    $sessionId = $_POST['session_id'] ?? 0;
    $additionalInfo = $_POST['additional_info'] ?? '';
    
    // Add additional_info column to chat_sessions if it doesn't exist
    try {
        $conn->exec("ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS additional_info TEXT");
    } catch (Exception $e) {
        // Column might already exist
    }
    
    $stmt = $conn->prepare("UPDATE chat_sessions SET additional_info = ? WHERE session_id = ?");
    $stmt->execute([$additionalInfo, $sessionId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Session info updated'
    ]);
}

function validateSession($conn) {
    $sessionId = $_GET['session_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT session_id, session_status FROM chat_sessions WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session && $session['session_status'] === 'active') {
        echo json_encode([
            'success' => true,
            'valid' => true,
            'session_id' => $session['session_id']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'valid' => false,
            'message' => 'Session not found or closed'
        ]);
    }
}

function deleteSession($conn) {
    $sessionId = $_POST['session_id'] ?? 0;
    
    if (!$sessionId) {
        echo json_encode(['error' => 'Session ID is required']);
        return;
    }
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Delete all messages for this session
        $stmt = $conn->prepare("DELETE FROM chat_messages WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        
        // Delete the session
        $stmt = $conn->prepare("DELETE FROM chat_sessions WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Session and all messages deleted successfully'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode([
            'error' => 'Failed to delete session: ' . $e->getMessage()]
        );
    }
}
?> 