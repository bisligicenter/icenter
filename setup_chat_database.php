<?php
// Setup script for the custom chat system
require_once 'db.php';

try {
    $conn = getConnection();
    
    // Read and execute the SQL file
    $sql = file_get_contents('create_chat_tables.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }
    
    echo "✅ Chat system database tables created successfully!\n";
    echo "📋 Tables created:\n";
    echo "   - chat_sessions\n";
    echo "   - chat_messages\n";
    echo "   - chat_admins\n";
    echo "\n🚀 Your custom live chat system is now ready!\n";
    echo "\nFeatures available:\n";
    echo "   - Customer chat widget on kiosk page\n";
    echo "   - Admin chat interface in dashboard\n";
    echo "   - Real-time messaging\n";
    echo "   - Session management\n";
    echo "   - Unread message notifications\n";
    
} catch (PDOException $e) {
    echo "❌ Error setting up chat database: " . $e->getMessage() . "\n";
}
?> 