<?php
// This file checks your database connection and structure
// Upload this file to your server and access it via browser to diagnose issues

// Basic PHP configuration check
echo "<h2>PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "<br>";
echo "Error Reporting Level: " . ini_get('error_reporting') . "<br>";
echo "PDO Available: " . (class_exists('PDO') ? 'Yes' : 'No') . "<br>";

// Include database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once 'db.php';
    echo "Connection loaded successfully.<br>";
    
    if (isset($conn) && $conn instanceof PDO) {
        echo "PDO connection established.<br>";
        
        // Try a simple query
        $testStmt = $conn->query("SELECT 1 as test");
        $result = $testStmt->fetch();
        if ($result && isset($result['test']) && $result['test'] == 1) {
            echo "Database query test: <span style='color:green'>PASSED</span><br>";
        } else {
            echo "Database query test: <span style='color:red'>FAILED</span><br>";
        }
    } else {
        echo "<span style='color:red'>Connection variable not available or not a PDO instance!</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color:red'>Connection failed: " . $e->getMessage() . "</span><br>";
}

// Check database tables
echo "<h2>Database Tables Check</h2>";
try {
    if (isset($conn) && $conn instanceof PDO) {
        // Check products table
        $stmt = $conn->query("SHOW TABLES LIKE 'products'");
        echo "Products table exists: " . ($stmt->rowCount() > 0 ? '<span style="color:green">Yes</span>' : '<span style="color:red">No</span>') . "<br>";
        
        if ($stmt->rowCount() > 0) {
            $columns = $conn->query("SHOW COLUMNS FROM products");
            echo "Products table columns:<br><ul>";
            while ($column = $columns->fetch()) {
                echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")" . ($column['Key'] == 'PRI' ? ' - PRIMARY KEY' : '') . "</li>";
            }
            echo "</ul>";
        }
        
        // Check product_images table
        $stmt = $conn->query("SHOW TABLES LIKE 'product_images'");
        echo "Product_images table exists: " . ($stmt->rowCount() > 0 ? '<span style="color:green">Yes</span>' : '<span style="color:red">No</span>') . "<br>";
        
        if ($stmt->rowCount() > 0) {
            $columns = $conn->query("SHOW COLUMNS FROM product_images");
            echo "Product_images table columns:<br><ul>";
            while ($column = $columns->fetch()) {
                echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")" . ($column['Key'] == 'PRI' ? ' - PRIMARY KEY' : '') . "</li>";
            }
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color:red'>Tables check failed: " . $e->getMessage() . "</span><br>";
}

// Directory permissions check
echo "<h2>Directory Permissions Check</h2>";
$uploadDir = 'product_images';
echo "Product images directory: " . $uploadDir . "<br>";
if (file_exists($uploadDir)) {
    echo "Directory exists: <span style='color:green'>Yes</span><br>";
    echo "Is writable: " . (is_writable($uploadDir) ? "<span style='color:green'>Yes</span>" : "<span style='color:red'>No</span>") . "<br>";
} else {
    echo "Directory exists: <span style='color:red'>No</span><br>";
    echo "Attempting to create directory...<br>";
    if (mkdir($uploadDir, 0755, true)) {
        echo "Directory created: <span style='color:green'>Yes</span><br>";
        echo "Is writable: " . (is_writable($uploadDir) ? "<span style='color:green'>Yes</span>" : "<span style='color:red'>No</span>") . "<br>";
    } else {
        echo "Directory creation failed: <span style='color:red'>Insufficient permissions</span><br>";
    }
}

// Create table SQL for reference
echo "<h2>SQL to Create Required Tables</h2>";
echo "<pre>
CREATE TABLE IF NOT EXISTS products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  category_id VARCHAR(50) NOT NULL,
  product_code VARCHAR(100) NOT NULL UNIQUE,
  brand VARCHAR(100) NOT NULL,
  model VARCHAR(100) NOT NULL,
  storage VARCHAR(100) NOT NULL,
  status VARCHAR(50) NOT NULL,
  stock_quantity INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS product_images (
  image_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);
</pre>";