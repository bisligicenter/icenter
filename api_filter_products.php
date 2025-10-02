<?php
require_once 'db.php';
require_once 'functions.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();

    // Get search and filter parameters from URL
    $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';
    $brandFilter = isset($_GET['brand']) ? $_GET['brand'] : 'all';
    $priceMin = isset($_GET['price_min']) && is_numeric($_GET['price_min']) ? (float)$_GET['price_min'] : null;
    $priceMax = isset($_GET['price_max']) && is_numeric($_GET['price_max']) ? (float)$_GET['price_max'] : null;

    // Build filters array for searchProducts function
    $filters = [
        'sort' => $sort,
        'category' => $categoryFilter,
        'brand' => $brandFilter,
        'price_min' => $priceMin,
        'price_max' => $priceMax,
    ];

    // Fetch products using the advanced search function
    $products = searchProducts($conn, $searchTerm, $filters);
    $totalProducts = count($products);

    // Generate HTML for the product cards
    ob_start();
    if (empty($products) && !empty($searchTerm)) {
        echo '<p class="col-span-full text-center text-gray-500 py-10">No products found matching your search.</p>';
    } else {
        foreach ($products as $product) {
            renderProductCard($product);
        }
    }
    $html = ob_get_clean();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => $totalProducts
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>