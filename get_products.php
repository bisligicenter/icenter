<?php
require_once 'db.php';

$currentCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
$currentModelBrand = isset($_GET['model_brand']) ? strtolower(trim($_GET['model_brand'])) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Determine sorting order based on GET parameter
$sortOption = isset($_GET['sort']) ? $_GET['sort'] : '';
$orderByClause = 'product_id DESC'; // default order
switch ($sortOption) {
    case 'price_asc':
        $orderByClause = 'selling_price ASC';
        break;
    case 'price_desc':
        $orderByClause = 'selling_price DESC';
        break;
    case 'stock_asc':
        $orderByClause = 'stock_quantity ASC';
        break;
    case 'stock_desc':
        $orderByClause = 'stock_quantity DESC';
        break;
}

try {
    if ($currentCategory !== '' && $currentCategory !== 'all products') {
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE TRIM(LOWER(product)) = :category AND (archived IS NULL OR archived = 0)");
        $countStmt->bindValue(':category', $currentCategory, PDO::PARAM_STR);
        $countStmt->execute();
        $totalProductsCount = $countStmt->fetchColumn();

        $stmt = $conn->prepare("SELECT * FROM products WHERE TRIM(LOWER(product)) = :category AND (archived IS NULL OR archived = 0) ORDER BY $orderByClause");
        $stmt->bindValue(':category', $currentCategory, PDO::PARAM_STR);
    } else {
        $totalProductsCount = $conn->query("SELECT COUNT(*) FROM products WHERE (archived IS NULL OR archived = 0)")->fetchColumn();
        $stmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) ORDER BY $orderByClause");
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    $error = $e->getMessage();
}

// Return HTML for products
if (isset($error)) {
    echo '<div class="bg-red-100 text-red-700 p-4 rounded mb-6 text-center">Error loading products: ' . htmlspecialchars($error) . '</div>';
} elseif (empty($products)) {
    echo '<div class="text-center text-gray-500 text-xl">No products found.</div>';
} else {
    foreach ($products as $product): ?>
        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col items-center border border-gray-100 product-card transform hover:-translate-y-2" style="position: relative;">
            <h2 class="font-bold text-xl text-gray-900 mb-3 text-center"><?= htmlspecialchars($product['brand'] . ' ' . $product['model']) ?></h2>
            <?php $mainImage = $product['image1'] ?? ''; ?>
            <?php if (!empty($mainImage)): ?>
                <img src="<?= htmlspecialchars($mainImage) ?>" alt="Product Image" class="w-48 h-64 object-cover rounded-xl mb-6 shadow-md" />
            <?php else: ?>
                <div class="w-48 h-64 flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl mb-6 text-gray-400 border-2 border-dashed border-gray-200">
                    <i class="fas fa-image text-4xl"></i>
                </div>
            <?php endif; ?>
            <div class="w-full space-y-3 mb-4">
                <p class="text-gray-700 font-semibold text-center text-lg"><?= htmlspecialchars($product['brand']) ?></p>
                <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                    <p class="text-gray-600 text-sm"><span class="font-semibold">Model:</span> <?= htmlspecialchars($product['model']) ?></p>
                    <p class="text-gray-600 text-sm"><span class="font-semibold">Storage:</span> <?= !empty($product['storage']) ? htmlspecialchars($product['storage']) : 'N/A' ?></p>
                    <p class="text-gray-600 text-sm"><span class="font-semibold">Purchase Price:</span> ₱<?= number_format($product['purchase_price'], 2) ?></p>
                    <p class="text-gray-800 font-bold text-lg"><span class="font-semibold">Selling Price:</span> ₱<?= number_format($product['selling_price'], 2) ?></p>
                </div>
            </div>
            <?php
                $stock = (int)$product['stock_quantity'];
                if ($stock == 0) {
                    $stockClass = 'bg-red-100 text-red-700 border border-red-200';
                    $stockText = 'Out of Stock';
                } elseif ($stock > 0 && $stock <= 5) {
                    $stockClass = 'bg-yellow-100 text-yellow-700 border border-yellow-200';
                    $stockText = 'Low Stock (' . $stock . ')';
                } else {
                    $stockClass = 'bg-green-100 text-green-700 border border-green-200';
                    $stockText = 'In Stock (' . $stock . ')';
                }
            ?>
            <span class="px-4 py-2 rounded-full <?= $stockClass ?> text-sm font-semibold mb-4"> <?= $stockText ?> </span>
            <div class="flex flex-col items-center space-y-3 mt-2 w-full justify-center">
                <div class="flex items-center space-x-3">
                    <input type="number" min="1" value="1" max="<?= (int)$product['stock_quantity'] ?>" class="sold-quantity-input border border-gray-300 rounded-lg px-3 py-2 w-24 text-center focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" data-product-id="<?= htmlspecialchars($product['product_id']) ?>" />
                    <button class="sold-button bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg" data-product-id="<?= htmlspecialchars($product['product_id']) ?>">
                        <i class="fas fa-shopping-cart mr-2"></i>Sold
                    </button>
                </div>
                <a href="edit_products.php?product_id=<?= urlencode($product['product_id']) ?>" title="Edit Product" class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </a>
            </div>
            <button class="trash-can-button" data-product-id="<?= htmlspecialchars($product['product_id']) ?>" title="Delete Product" style="position: absolute; top: 15px; right: 15px; background: rgba(220, 53, 69, 0.1); border: none; cursor: pointer; color: #dc3545; font-size: 20px; padding: 8px; border-radius: 50%; transition: all 0.3s ease;">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    <?php endforeach;
}
?> 