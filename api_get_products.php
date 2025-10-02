<?php
require_once 'db.php';
require_once 'functions.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();

    // --- PARAMETERS ---
    $category = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 12; // Or make this a parameter
    $offset = ($page - 1) * $limit;

    // --- SQL QUERY BUILDING ---
    $baseQuery = "FROM products WHERE (archived IS NULL OR archived = 0)";
    $whereConditions = [];
    $queryParams = [];

    // Category filter
    if ($category !== 'all' && $category !== '') {
        $whereConditions[] = "LOWER(product) = :category";
        $queryParams[':category'] = $category;
    }

    // Search filter
    if ($search !== '') {
        $whereConditions[] = "(LOWER(product) LIKE :search OR LOWER(brand) LIKE :search OR LOWER(model) LIKE :search)";
        $queryParams[':search'] = '%' . $search . '%';
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = ' AND ' . implode(' AND ', $whereConditions);
    }

    // Sorting
    $orderByClause = 'ORDER BY product_id DESC'; // Default
    switch ($sort) {
        case 'price_asc': $orderByClause = 'ORDER BY selling_price ASC'; break;
        case 'price_desc': $orderByClause = 'ORDER BY selling_price DESC'; break;
        case 'name_asc': $orderByClause = 'ORDER BY product ASC, brand ASC, model ASC'; break;
        case 'name_desc': $orderByClause = 'ORDER BY product DESC, brand DESC, model DESC'; break;
    }

    // --- EXECUTE QUERIES ---
    // Count query
    $countQuery = "SELECT COUNT(*) " . $baseQuery . $whereClause;
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($queryParams);
    $totalProducts = $countStmt->fetchColumn();

    // Data query
    $dataQuery = "SELECT * " . $baseQuery . $whereClause . " " . $orderByClause . " LIMIT :limit OFFSET :offset";
    $dataStmt = $conn->prepare($dataQuery);

    // Bind params for data query
    foreach ($queryParams as $key => $val) {
        $dataStmt->bindValue($key, $val);
    }
    $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();
    $products = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- RESPONSE ---
    echo json_encode([
        'success' => true,
        'products' => $products,
        'pagination' => [
            'total' => (int)$totalProducts,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($totalProducts / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>