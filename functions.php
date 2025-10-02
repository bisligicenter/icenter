 <?php
function getProductsByCategory($conn, $category = null) {
    $sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
    $params = [];
    if ($category !== null) {
        $sql .= " WHERE c.category_name = :category";
        $params[':category'] = $category;
    }
    $sql .= " ORDER BY c.category_name, p.product";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Resolve a product field value using multiple candidate names, case/format-insensitive.
 * Normalizes keys by lowercasing and removing all non-alphanumeric characters so that
 * columns like "Display_output", "display output", or "displayOutput" all match.
 */
function getProductFieldValue(array $product, array $candidateFieldNames) {
    // Build canonical map of product keys → values
    $canonicalToKey = [];
    foreach ($product as $key => $value) {
        $canonical = strtolower(preg_replace('/[^a-z0-9]/i', '', (string)$key));
        if ($canonical !== '') {
            // First occurrence wins
            if (!array_key_exists($canonical, $canonicalToKey)) {
                $canonicalToKey[$canonical] = $key;
            }
        }
    }

    // Try each candidate in order
    foreach ($candidateFieldNames as $candidate) {
        $canonicalCandidate = strtolower(preg_replace('/[^a-z0-9]/i', '', (string)$candidate));
        if ($canonicalCandidate === '') continue;
        if (array_key_exists($canonicalCandidate, $canonicalToKey)) {
            $actualKey = $canonicalToKey[$canonicalCandidate];
            $value = $product[$actualKey];
            if ($value !== null && $value !== '') {
                return $value;
            }
        }
    }

    return '';
}

function getAllProducts($conn) {
    $sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getAllCategories($conn) {
    $cacheFile = __DIR__ . '/cache/categories_cache.json';
    $cacheTime = 3600; // 1 hour cache

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        // Load from cache
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        if ($cachedData !== null) {
            return $cachedData;
        }
    }

    // Fetch from database
    $sql = "SELECT DISTINCT category_name FROM categories ORDER BY category_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Save to cache
    file_put_contents($cacheFile, json_encode($categories));

    return $categories;
}

function getNewArrivals($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            ORDER BY p.product_id DESC 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBestSellers($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            ORDER BY p.selling_price DESC 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSpecialOffers($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.selling_price > 0 
            ORDER BY p.selling_price ASC 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecommendedProducts($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            ORDER BY RAND() 
            LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createProductCard($product, $isSpecialOffer = false) {
    $imagePath = !empty($product['image1']) ? $product['image1'] : 'images/default.png';
    
    $html = '<div class="product-card">';
    
    // For special offers, we'll show a badge if the price is below a certain threshold
    if ($isSpecialOffer && $product['selling_price'] < 10000) {
        $html .= '<div class="discount-badge">Special Offer</div>';
    }
    
    $html .= '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['product']) . '">';
    $html .= '<div class="product-title">' . htmlspecialchars($product['product']) . '</div>';
    $html .= '<div class="product-price">₱' . number_format($product['selling_price'], 2) . '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Enhanced search function with multiple search criteria
 */
function searchProducts($conn, $query, $filters = []) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE (p.archived = 0 OR p.archived IS NULL)";
    
    $params = [];
    $conditions = [];
    
    // Basic search conditions
    if (!empty($query)) {
        $searchConditions = [
            "LOWER(p.product) LIKE LOWER(:search_term)",
            "LOWER(p.brand) LIKE LOWER(:search_term)",
            "LOWER(p.model) LIKE LOWER(:search_term)",
            "LOWER(p.storage) LIKE LOWER(:search_term)",
            "LOWER(c.category_name) LIKE LOWER(:search_term)"
        ];
        
        $searchTerm = '%' . $query . '%';
        $params[':search_term'] = $searchTerm;
        $conditions[] = "(" . implode(" OR ", $searchConditions) . ")";
    }
    
    // Category filter
    if (!empty($filters['category']) && $filters['category'] !== 'all') {
        $conditions[] = "LOWER(c.category_name) = LOWER(:category)";
        $params[':category'] = $filters['category'];
    }
    
    // Price range filter
    if (!empty($filters['price_min'])) {
        $conditions[] = "p.selling_price >= :price_min";
        $params[':price_min'] = $filters['price_min'];
    }
    
    if (!empty($filters['price_max'])) {
        $conditions[] = "p.selling_price <= :price_max";
        $params[':price_max'] = $filters['price_max'];
    }
    
    // Brand filter
    if (!empty($filters['brand']) && $filters['brand'] !== 'all') {
        $conditions[] = "LOWER(p.brand) = LOWER(:brand)";
        $params[':brand'] = $filters['brand'];
    }
    
    // Storage filter
    if (!empty($filters['storage'])) {
        $conditions[] = "LOWER(p.storage) = LOWER(:storage)";
        $params[':storage'] = $filters['storage'];
    }
    
    // Add conditions to SQL
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    // Ordering
    $orderBy = "ORDER BY p.product ASC";
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_asc':
                $orderBy = "ORDER BY p.selling_price ASC";
                break;
            case 'price_desc':
                $orderBy = "ORDER BY p.selling_price DESC";
                break;
            case 'name_asc':
                $orderBy = "ORDER BY p.product ASC";
                break;
            case 'name_desc':
                $orderBy = "ORDER BY p.product DESC";
                break;
            case 'newest':
                $orderBy = "ORDER BY p.product_id DESC";
                break;
        }
    }
    
    $sql .= " " . $orderBy;
    
    $limit = !empty($filters['limit']) ? (int)$filters['limit'] : null;

    // Limit results
    if ($limit !== null) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $conn->prepare($sql);

    // Bind all parameters from the $params array
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }

    if ($limit !== null) {
        // Bind the limit as an integer, which is required for LIMIT clauses
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get search suggestions based on popular searches
 */
function getSearchSuggestions($conn, $query = '', $limit = 10) {
    $sql = "SELECT DISTINCT 
                p.product,
                p.brand,
                p.model,
                c.category_name,
                COUNT(*) as search_count
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE (p.archived = 0 OR p.archived IS NULL)";
    
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (
            LOWER(p.product) LIKE LOWER(:search_term) OR
            LOWER(p.brand) LIKE LOWER(:search_term) OR
            LOWER(p.model) LIKE LOWER(:search_term) OR
            LOWER(c.category_name) LIKE LOWER(:search_term)
        )";
        $params[':search_term'] = '%' . $query . '%';
    }
    
    $sql .= " GROUP BY p.product, p.brand, p.model, c.category_name 
              ORDER BY search_count DESC, p.product ASC 
              LIMIT :limit";
    
    $params[':limit'] = $limit;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get popular brands for search filters
 */
function getPopularBrands($conn) {
    $sql = "SELECT DISTINCT brand, COUNT(*) as product_count 
            FROM products 
            WHERE (archived = 0 OR archived IS NULL) AND brand IS NOT NULL AND brand != ''
            GROUP BY brand 
            ORDER BY product_count DESC, brand ASC 
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get price ranges for search filters
 */
function getPriceRanges($conn) {
    $sql = "SELECT 
                MIN(selling_price) as min_price,
                MAX(selling_price) as max_price,
                AVG(selling_price) as avg_price
            FROM products 
            WHERE (archived = 0 OR archived IS NULL) AND selling_price > 0";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Log search activity for analytics
 */
function logSearchActivity($conn, $query, $results_count, $user_ip = null) {
    try {
        $sql = "INSERT INTO search_logs (query, results_count, user_ip, search_date) 
                VALUES (:query, :results_count, :user_ip, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':query' => $query,
            ':results_count' => $results_count,
            ':user_ip' => $user_ip ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        return true;
    } catch (Exception $e) {
        // Log error but don't break the search functionality
        error_log("Failed to log search activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get search analytics
 */
function getSearchAnalytics($conn, $days = 30) {
    $sql = "SELECT 
                query,
                COUNT(*) as search_count,
                AVG(results_count) as avg_results,
                MAX(search_date) as last_searched
            FROM search_logs 
            WHERE search_date >= DATE_SUB(NOW(), INTERVAL :days DAY)
            GROUP BY query 
            ORDER BY search_count DESC 
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':days' => $days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get stock status for a product
 */
function getStockStatus($stockQuantity) {
    if ($stockQuantity > 10) {
        return ['status' => 'in-stock', 'text' => 'In Stock', 'class' => 'text-green-600'];
    } elseif ($stockQuantity > 0) {
        return ['status' => 'low-stock', 'text' => 'Low Stock', 'class' => 'text-yellow-600'];
    } else {
        return ['status' => 'out-of-stock', 'text' => 'Out of Stock', 'class' => 'text-red-600'];
    }
}

/**
 * Enhanced product card renderer with image container on the left side
 */
function renderProductCard($product, $isCarousel = false) {
    $stockInfo = getStockStatus($product['stock_quantity']);
    $categoryLower = strtolower($product['brand']);
    $mainImage = $product['image1'] ?? '';
    
    // Fallback image path
    $fallbackImage = 'images/default.png';
    if (!empty($mainImage)) {
        $images = explode(',', $mainImage);
        $mainImage = trim($images[0]);
    } else {
        $mainImage = $fallbackImage;
    }
    
    $description = !empty($product['description']) ? $product['description'] : '';
    $price = !empty($product['selling_price']) ? '₱' . number_format($product['selling_price'], 2) : 'Price not available';
    $productName = !empty($product['product']) ? $product['product'] : 'Unnamed Product';
    $productId = !empty($product['product_id']) ? $product['product_id'] : 0;
    $brand = !empty($product['brand']) ? $product['brand'] : '';
    $model = !empty($product['model']) ? $product['model'] : '';
    $categoryName = !empty($product['category_name']) ? $product['category_name'] : 'Uncategorized';
    $storage = !empty($product['storage']) ? $product['storage'] : '';
    
    // New product fields resolved via robust, case/format-insensitive mapping
    $waterResistance = getProductFieldValue($product, [
        'water_resistance', 'water resistant', 'water-resistant', 'waterresistant', 'water_resistent', 'ip_rating', 'ip rating'
    ]);

    $displayOutput = getProductFieldValue($product, [
        'display_output', 'display output', 'display', 'do'
    ]);

    $screenSize = getProductFieldValue($product, [
        'screen_size', 'screen size', 'display_size', 'display size', 'screen'
    ]);

    $chargingPort = getProductFieldValue($product, [
        'charging_port', 'charging port', 'usb_port', 'usb port', 'connector', 'port', 'charging'
    ]);

    $material = getProductFieldValue($product, [
        'material', 'body_material', 'body material', 'build', 'body'
    ]);

    $chip = getProductFieldValue($product, [
        'chip', 'processor', 'soc', 'chipset'
    ]);

    $cameraFeature = getProductFieldValue($product, [
        'camera_feature', 'camera features', 'camera_features', 'camera', 'camera specs', 'camera_specs'
    ]);
    
    $optionName = trim($productName . ' ' . $brand . ' ' . $model . ' (' . $storage . ')');
    
    // Prepare all images for the product (robust to null)
    $imageListRaw = isset($product['image1']) ? (string)$product['image1'] : '';
    $images = $imageListRaw !== '' ? array_map('trim', explode(',', $imageListRaw)) : [];
    if (!empty($product['image2'])) $images[] = $product['image2'];
    if (!empty($product['image3'])) $images[] = $product['image3'];
    if (!empty($product['image4'])) $images[] = $product['image4'];
    if (!empty($product['image5'])) $images[] = $product['image5'];
    $imagesJson = htmlspecialchars(json_encode($images));
    $secondImage = isset($images[1]) ? $images[1] : '';
    $brandModel = trim($brand . ' ' . $model);
    
    // Only output the .card div as direct child with all data attributes
    echo '<div class="card group" style="width: 350px;" data-product-id="'.$productId.'" data-product-name="'.htmlspecialchars($optionName).'" data-description="'.htmlspecialchars($description).'" data-price="'.$price.'" data-brand="'.htmlspecialchars($brand).'" data-model="'.htmlspecialchars($model).'" data-category="'.htmlspecialchars($categoryName).'" data-storage="'.htmlspecialchars($storage).'" data-water-resistance="'.htmlspecialchars($waterResistance).'" data-display-output="'.htmlspecialchars($displayOutput).'" data-screen-size="'.htmlspecialchars($screenSize).'" data-charging-port="'.htmlspecialchars($chargingPort).'" data-material="'.htmlspecialchars($material).'" data-chip="'.htmlspecialchars($chip).'" data-camera-feature="'.htmlspecialchars($cameraFeature).'" data-images=\''.$imagesJson.'\' data-second-image="'.htmlspecialchars($secondImage).'" data-category-filter="'.htmlspecialchars($categoryName).'">';
    // Enhanced image container - now on the left side with hover effects
    if (!empty($mainImage)) {
        echo '<div class="relative w-full overflow-hidden bg-gray-100" style="height: 400px;">';
        echo '<img src="' . htmlspecialchars($mainImage) . '" alt="' . htmlspecialchars($productName) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px; transition: transform 0.3s ease;" class="group-hover:scale-105">';
        echo '<div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" style="border-radius: 15px;"></div>';
        echo '</div>';
    }
    
    echo '<h3 class="card-link" style="margin-top: 10px; margin-bottom: 8px;">'.htmlspecialchars($brandModel).'</h3>';
    echo '<div class="card-price" style="color: #111; margin-bottom: 8px;">'.$price.'</div>';
    
    // Add compare checkbox at the bottom of the card only if not carousel
    if (!$isCarousel) {
        echo '<div style="width: 100%; display: flex; justify-content: center; align-items: center; margin-top: 12px; margin-bottom: 2px;">';
        echo '<input type="checkbox" class="compare-checkbox" id="compare_'.$productId.'" name="compare[]" value="'.$productId.'" data-product-name="'.htmlspecialchars($optionName).'" style="appearance: auto; -webkit-appearance: checkbox; width: 18px; height: 18px;">';
        echo '<label for="compare_'.$productId.'" style="font-size: 1rem; font-weight: 500; color: #333; cursor: pointer; margin-left: 6px;">Compare</label>';
        echo '</div>';
    }

    // Add See Detail button always, centered
    echo '<div style="width: 100%; display: flex; justify-content: center; margin-top: 10px;">';
    echo '<button class="details-btn">See Detail</button>';
    echo '</div>';

    // Add horizontal line at the bottom of the card
    echo '<hr style="width: 90%; margin: 18px auto 0 auto; border: 0; border-top: 1.5px solid #e5e7eb;">';

    echo '</div>'; // close .card
}
?>