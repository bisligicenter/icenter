<?php
/**
 * Unarchive Products Script
 * 
 * This script unarchives products so they can be displayed in the kiosk
 */

require_once 'db.php';

try {
    $conn = getConnection();
    
    echo "<h1>Unarchive Products</h1>";
    
    // Check current status
    $stmt = $conn->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as archived FROM products WHERE archived = 1");
    $archivedProducts = $stmt->fetch()['archived'];
    
    $stmt = $conn->query("SELECT COUNT(*) as active FROM products WHERE archived IS NULL OR archived = 0");
    $activeProducts = $stmt->fetch()['active'];
    
    echo "<h2>Current Status</h2>";
    echo "<p><strong>Total products:</strong> $totalProducts</p>";
    echo "<p><strong>Archived products:</strong> $archivedProducts</p>";
    echo "<p><strong>Active products:</strong> $activeProducts</p>";
    
    if ($activeProducts == 0 && $archivedProducts > 0) {
        echo "<h2>Unarchiving Products</h2>";
        echo "<p>No active products found. Unarchiving some products...</p>";
        
        // Unarchive first 10 products
        $updateStmt = $conn->prepare("UPDATE products SET archived = 0 WHERE archived = 1 LIMIT 10");
        $result = $updateStmt->execute();
        
        if ($result) {
            $affectedRows = $updateStmt->rowCount();
            echo "<p style='color: green;'>✅ Successfully unarchived $affectedRows products</p>";
            
            // Show updated status
            $stmt = $conn->query("SELECT COUNT(*) as active FROM products WHERE archived IS NULL OR archived = 0");
            $newActiveProducts = $stmt->fetch()['active'];
            echo "<p><strong>Active products now:</strong> $newActiveProducts</p>";
            
            // Show some unarchived products
            echo "<h3>Recently Unarchived Products:</h3>";
            $stmt = $conn->query("SELECT product_id, product, brand, model FROM products WHERE archived = 0 ORDER BY product_id DESC LIMIT 5");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($products)) {
                echo "<ul>";
                foreach ($products as $product) {
                    echo "<li><strong>{$product['brand']} {$product['model']}</strong> - {$product['product']}</li>";
                }
                echo "</ul>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Failed to unarchive products</p>";
        }
        
    } elseif ($activeProducts > 0) {
        echo "<p style='color: green;'>✅ Active products already exist</p>";
        echo "<p>No need to unarchive products.</p>";
        
        // Show some active products
        echo "<h3>Active Products:</h3>";
        $stmt = $conn->query("SELECT product_id, product, brand, model FROM products WHERE archived IS NULL OR archived = 0 ORDER BY product_id DESC LIMIT 5");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($products)) {
            echo "<ul>";
            foreach ($products as $product) {
                echo "<li><strong>{$product['brand']} {$product['model']}</strong> - {$product['product']}</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ No products found in database</p>";
        echo "<p>You may need to add some products first.</p>";
    }
    
    echo "<h2>Next Steps</h2>";

    echo "<p>3. Visit your kiosk: <a href='kiosk.php'>kiosk.php</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; border: 1px solid #ef4444; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?> 