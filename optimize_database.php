<?php
require_once 'db.php';

echo "Starting database optimization...\n";

// Check if database connection is available
if (!$conn) {
    echo "✗ Database connection failed. Please check your database configuration.\n";
    echo "Make sure:\n";
    echo "1. MySQL server is running\n";
    echo "2. Database 'admin2' exists\n";
    echo "3. PDO MySQL driver is installed\n";
    echo "4. Database credentials are correct in db.php\n";
    exit(1);
}

try {
    // Test connection first
    $conn->query("SELECT 1");
    echo "✓ Database connection successful\n";
    
    // Add indexes for frequently queried columns
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_product_archived ON products(archived)",
        "CREATE INDEX IF NOT EXISTS idx_product_brand ON products(brand)",
        "CREATE INDEX IF NOT EXISTS idx_product_model ON products(model)",
        "CREATE INDEX IF NOT EXISTS idx_product_storage ON products(storage)",
        "CREATE INDEX IF NOT EXISTS idx_product_product ON products(product)",
        "CREATE INDEX IF NOT EXISTS idx_product_id ON products(product_id)",
        "CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)",
        "CREATE INDEX IF NOT EXISTS idx_reservations_status ON reservations(status)",
        "CREATE INDEX IF NOT EXISTS idx_reservations_archived ON reservations(archived)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $conn->exec($index);
            echo "✓ Index created successfully\n";
        } catch (PDOException $e) {
            echo "⚠ Index may already exist: " . $e->getMessage() . "\n";
        }
    }
    
    // Analyze table for better query optimization
    try {
        $conn->exec("ANALYZE TABLE products");
        $conn->exec("ANALYZE TABLE users");
        $conn->exec("ANALYZE TABLE reservations");
        echo "✓ Table analysis completed\n";
    } catch (PDOException $e) {
        echo "⚠ Table analysis failed: " . $e->getMessage() . "\n";
    }
    
    echo "✓ Database optimization completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error optimizing database: " . $e->getMessage() . "\n";
}
?> 