<?php
require_once 'db.php';
require_once 'functions.php';

$conn = getConnection();

// Get search and filter parameters from URL
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';
$brandFilter = isset($_GET['brand']) ? $_GET['brand'] : 'all';
$priceMin = isset($_GET['price_min']) && is_numeric($_GET['price_min']) ? (float)$_GET['price_min'] : null;
$priceMax = isset($_GET['price_max']) && is_numeric($_GET['price_max']) ? (float)$_GET['price_max'] : null;

// Fetch filter options
$categories = getAllCategories($conn);
$brands = getPopularBrands($conn);

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Search Results for "<?= htmlspecialchars($searchTerm) ?>" - BISLIG iCENTER</title>
  <link rel="icon" type="image/png" href="images/iCenter.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <link rel="stylesheet" href="kiosk.css">
  <style>
    body {
        background: #f8f9fa;
    }
    .search-results-header {
        padding: 2.5rem 1rem;
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
        margin-bottom: 2.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .search-results-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #111827;
    }
    .search-results-header p {
        font-size: 1.1rem;
        color: #6b7280;
        margin-top: 0.5rem;
    }
    .product-grid-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    #productGrid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        justify-content: center;
    }
    .filter-sort-container {
        max-width: 1400px;
        margin: 0 auto 2.5rem auto;
        padding: 1.5rem;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: center;
        justify-content: center;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .filter-group label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #4b5563;
    }
    .filter-group select, .filter-group input {
        padding: 0.6rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #f9fafb;
        min-width: 180px;
    }
    .filter-buttons {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
        padding-top: 1.5rem; /* Align with labels */
    }
  </style>
</head>
<body>
  <?php include 'kioskheader.php'; ?>

  <main>
    <div class="search-results-header">
        <h1>Search Results</h1>
        <?php if (!empty($searchTerm)): ?>
            <p>Showing results for "<strong><?= htmlspecialchars($searchTerm) ?></strong>" (<?= $totalProducts ?> found)</p>
        <?php else: ?>
            <p>Please enter a search term to find products.</p>
        <?php endif; ?>
    </div>

    <!-- Filter and Sort Controls -->
    <div class="filter-sort-container">
        <form id="filterForm" class="flex flex-wrap items-end gap-6">
            <input type="hidden" name="q" value="<?= htmlspecialchars($searchTerm) ?>">

            <div class="filter-group">
                <label for="sort">Sort By</label>
                <select name="sort" id="sort">
                    <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Relevance</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="category">Category</label>
                <select name="category" id="category">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="brand">Brand</label>
                <select name="brand" id="brand">
                    <option value="all">All Brands</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= htmlspecialchars($b['brand']) ?>" <?= $brandFilter === $b['brand'] ? 'selected' : '' ?>><?= htmlspecialchars($b['brand']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="price_min">Min Price</label>
                <input type="number" name="price_min" id="price_min" placeholder="e.g., 500" value="<?= htmlspecialchars($priceMin ?? '') ?>">
            </div>

            <div class="filter-group">
                <label for="price_max">Max Price</label>
                <input type="number" name="price_max" id="price_max" placeholder="e.g., 5000" value="<?= htmlspecialchars($priceMax ?? '') ?>">
            </div>

            <div class="filter-buttons"><a href="search_results.php?q=<?= htmlspecialchars($searchTerm) ?>" class="text-gray-600 hover:text-blue-600 font-semibold px-4 py-2.5">Reset Filters</a></div>
        </form>
    </div>

    <div class="product-grid-container">
        <div id="productGrid">
            <?php
            if (isset($error)) {
                echo '<p class="col-span-full text-center text-red-500">' . $error . '</p>';
            } elseif (empty($products) && !empty($searchTerm)) {
                echo '<p class="col-span-full text-center text-gray-500 py-10">No products found matching your search.</p>';
            } else {
                foreach ($products as $product) {
                    renderProductCard($product);
                }
            }
            ?>
        </div>
    </div>
  </main>

  <?php include 'kioskmodals.php'; ?>
  <script src="kiosk.js"></script>
  <?php include 'customer_chat_widget.php'; ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('filterForm');
        const productGrid = document.getElementById('productGrid');
        const resultCountElement = document.querySelector('.search-results-header p');
        const originalSearchTerm = "<?= htmlspecialchars($searchTerm) ?>";
        let debounceTimer;

        function fetchFilteredProducts() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const queryString = params.toString();

            // Show loading state
            productGrid.style.opacity = '0.5';
            productGrid.style.transition = 'opacity 0.3s ease';

            fetch(`api_filter_products.php?${queryString}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update product grid
                        productGrid.innerHTML = data.html;
                        
                        // Update result count
                        if (resultCountElement) {
                            resultCountElement.innerHTML = `Showing results for "<strong>${originalSearchTerm}</strong>" (${data.count} found)`;
                        }

                        // Update URL without reloading
                        const newUrl = `${window.location.pathname}?${queryString}`;
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    } else {
                        productGrid.innerHTML = '<p class="col-span-full text-center text-red-500">Error loading products.</p>';
                    }
                })
                .catch(error => {
                    console.error('Filter error:', error);
                    productGrid.innerHTML = '<p class="col-span-full text-center text-red-500">An error occurred. Please try again.</p>';
                })
                .finally(() => {
                    // Remove loading state
                    productGrid.style.opacity = '1';
                });
        }

        // Attach event listeners to all form inputs
        form.querySelectorAll('select, input[type="number"]').forEach(input => {
            input.addEventListener('change', fetchFilteredProducts);
        });

        // Add debounce for price inputs to avoid too many requests while typing
        form.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchFilteredProducts, 500);
            });
        });

        // Prevent default form submission on Enter key
        form.addEventListener('submit', function (e) {
            e.preventDefault();
        });
    });
  </script>
</body>
</html>