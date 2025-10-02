<?php
// Function to get all collections
function getCollections() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM collections ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCollections: " . $e->getMessage());
        return [];
    }
}

// Function to get a specific collection by ID
function getCollectionById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCollectionById: " . $e->getMessage());
        return null;
    }
}

// Function to get products in a collection
function getCollectionProducts($collectionId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM products p 
            INNER JOIN collection_products cp ON p.id = cp.product_id 
            WHERE cp.collection_id = :collection_id
        ");
        $stmt->bindParam(':collection_id', $collectionId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCollectionProducts: " . $e->getMessage());
        return [];
    }
}
?> 