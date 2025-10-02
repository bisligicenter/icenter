<?php
require_once 'db.php';
require_once 'functions.php';
$conn = getConnection();

try {
    $itemsPerPage = 12;
    $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Get model brand filter from GET parameter
    $modelBrandFilter = isset($_GET['model_brand']) ? strtolower(trim($_GET['model_brand'])) : '';

    // Get storage filter from GET parameter
    $storageFilter = isset($_GET['storage']) ? strtolower(trim($_GET['storage'])) : '';

    // Determine sorting order based on GET parameter
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : '';
    $orderByClause = 'product ASC'; // default order
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

    // Fetch distinct model brands for laptops
    $modelStmt = $conn->prepare("SELECT DISTINCT LOWER(model) AS model FROM products WHERE LOWER(product) = 'laptop' ORDER BY model ASC");
    $modelStmt->execute();
    $modelBrands = $modelStmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch distinct storage options for laptops that have products
    $storageStmt = $conn->prepare("SELECT DISTINCT LOWER(storage) AS storage FROM products WHERE LOWER(product) = 'laptop' ORDER BY storage ASC");
    $storageStmt->execute();
    $storageOptions = $storageStmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch all laptop products for dropdowns
    $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE LOWER(product) = 'laptop' ORDER BY product_id ASC");
    $allProductsStmt->execute();
    $allProducts = $allProductsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build count query with filters
    $countQuery = "SELECT COUNT(*) FROM products WHERE LOWER(product) = 'laptop'";
    $countParams = [];
    if ($modelBrandFilter !== '' && $modelBrandFilter !== 'all models') {
        $countQuery .= " AND LOWER(model) = :model_brand";
        $countParams[':model_brand'] = $modelBrandFilter;
    }
    if ($storageFilter !== '' && $storageFilter !== 'all storages') {
        $countQuery .= " AND LOWER(storage) = :storage";
        $countParams[':storage'] = $storageFilter;
    }
    $countStmt = $conn->prepare($countQuery);
    foreach ($countParams as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalProductsCount = $countStmt->fetchColumn();

    // Build select query with filters
    $selectQuery = "SELECT * FROM products WHERE LOWER(product) = 'laptop'";
    $selectParams = [];
    if ($modelBrandFilter !== '' && $modelBrandFilter !== 'all models') {
        $selectQuery .= " AND LOWER(model) = :model_brand";
        $selectParams[':model_brand'] = $modelBrandFilter;
    }
    if ($storageFilter !== '' && $storageFilter !== 'all storages') {
        $selectQuery .= " AND LOWER(storage) = :storage";
        $selectParams[':storage'] = $storageFilter;
    }
    $selectQuery .= " ORDER BY $orderByClause LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($selectQuery);
    foreach ($selectParams as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = ceil($totalProductsCount / $itemsPerPage);
} catch (PDOException $e) {
    echo '<div class="text-red-500">Error loading laptop products: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $products = [];
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BiSLIG iCENTER</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="icon" type="image/png" href="images/iCenter.png">
  <link rel="shortcut icon" type="image/png" href="images/iCenter.png">
  <link rel="apple-touch-icon" href="images/iCenter.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <link rel="stylesheet" href="kiosk.css">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #ffffff;
      color: #111827;
      min-height: 100vh;
      margin: 0;
      padding: 0;
    }
    .product-card {
      background-color: #f9fafb;
      color: #111827;
      padding: 1.5rem;
      border-radius: 1rem;
      border: 1px solid #d1d5db;
      box-shadow: 0 6px 20px rgba(0,0,0,0.05);
      transition: all 0.4s ease;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    .product-card:hover {
      border-color: #000000;
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.1);
      transform: translateY(-10px);
      z-index: 10;
    }
    .product-card img {
      width: 200px;
      height: 300px;
      object-fit: cover;
      border-radius: 0.75rem;
      margin-bottom: 1.25rem;
      transition: transform 0.4s ease;
      border: none;
    }
    /* Slant glimmer effect keyframes */
    @keyframes slantGlimmer {
      0% {
        transform: translateX(-150%) skewX(-20deg);
        opacity: 0;
      }
      50% {
        transform: translateX(50%) skewX(-20deg);
        opacity: 0.6;
      }
      100% {
        transform: translateX(150%) skewX(-20deg);
        opacity: 0;
      }
    }

    .product-card img {
      display: block;
      width: 100%;
      height: auto;
      border-radius: 0.75rem;
    }

    .image-wrapper {
      position: relative;
      width: 200px;
      height: 300px;
      margin-bottom: 1.25rem;
      overflow: hidden;
      border-radius: 0.75rem;
    }

    .image-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .image-wrapper::after {
      content: '';
      position: absolute;
      top: 0;
      left: -150%;
      width: 50%;
      height: 100%;
      background: linear-gradient(
        120deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.8) 50%,
        rgba(255, 255, 255, 0) 100%
      );
      filter: blur(10px);
      pointer-events: none;
      z-index: 10;
      border-radius: 0.75rem;
      animation: none;
      transition: none;
    }

    .image-wrapper:hover::after {
      animation: slantGlimmer 2s ease-in-out infinite;
    }
    .product-card h3 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
      color: #111827;
      text-shadow: none;
    }
    .product-card p {
      margin: 0.5rem 0;
      font-size: 1.1rem;
      color: #4b5563;
      line-height: 1.5;
    }
    .product-card p strong {
      color: #000000;
      font-weight: 600;
    }
    .product-card .price {
      font-size: 1.5rem;
      font-weight: 700;
      color: #dc2626; /* red-600 */
      margin: 0.75rem 0;
      text-shadow: none;
    }
    .product-card .brand {
      font-size: 1.25rem;
      font-weight: 700;
      color: #000000;
      background-color: rgba(0, 0, 0, 0.05);
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      margin: 0.5rem 0;
    }
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 700;
      display: inline-block;
      margin-top: 1rem;
      font-size: 1rem;
      position: relative;
      cursor: default;
      user-select: none;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2.5rem;
      margin-bottom: 3.5rem;
      padding: 1.5rem 2.5rem;
      transition: all 0.3s ease;
    }
    /* Model Brand Buttons - Using category button style from kiosk.php */
    #modelBrandButtons a, #storageButtons a {
      background: linear-gradient(135deg, #000 0%, #222 100%);
      color: #fff;
      border: 2px solid transparent;
      border-radius: 20px;
      padding: 10px 24px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      margin-bottom: 8px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.25);
      position: relative;
      overflow: hidden;
      letter-spacing: 0.6px;
      text-decoration: none;
      user-select: none;
    }
    
    #modelBrandButtons a:hover, #storageButtons a:hover,
    #modelBrandButtons a.bg-blue-600, #storageButtons a.bg-green-600 {
      background: linear-gradient(135deg, #222 0%, #000 100%);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.2);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    /* Pagination */
    nav.flex a {
      font-weight: 700;
      padding: 0.6rem 0.85rem;
      border-radius: 0.5rem;
      text-decoration: none;
      color: #ffffff;
      background-color: #000000;
      box-shadow: 0 3px 8px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
      border: 1px solid #000000;
    }
    nav.flex a:hover {
      background-color: #333333;
      color: #ffffff;
      box-shadow: 0 6px 16px rgba(51, 51, 51, 0.4);
    }
    nav.flex a.bg-blue-600 {
      background-color: #000000 !important;
      color: #ffffff !important;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4) !important;
    }
    /* Compare Section */
    .compare-select {
      padding: 0.75rem;
      border: 1px solid #000000;
      border-radius: 0.5rem;
      font-size: 1rem;
      width: 300px;
      margin: 0 0.5rem;
      background-color: #ffffff;
      color: #111827;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .compare-select:focus {
      outline: none;
      border-color: #333333;
      box-shadow: 0 0 0 2px rgba(51, 51, 51, 0.3);
    }
    .compare-button {
      padding: 0.75rem 1.5rem;
      background-color: #000000;
      color: #ffffff;
      border-radius: 0.5rem;
      font-weight: 600;
      border: none;
      transition: all 0.3s ease;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .compare-button:hover:not(:disabled) {
      background-color: #333333;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(51, 51, 51, 0.4);
    }
    .compare-button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      background-color: #666666;
    }
    /* Header */
    @media (min-width: 1025px) {
    header {
      background: transparent !important;
      box-shadow: none !important;
      border-bottom: none !important;
    }
    }
    header a {
      background: #000000;
      color: #ffffff;
      padding: 0.75rem 1.5rem;
      border-radius: 0.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    header a:hover {
      background: #333333;
      transform: translateY(-2px);
    }
    /* Sort Options */
    .sort-container {
      background: #f9fafb;
      border: 1px solid #d1d5db;
      padding: 1rem;
      border-radius: 0.75rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .sort-container select {
      background: #ffffff;
      color: #111827;
      border: 1px solid #d1d5db;
      padding: 0.5rem;
      border-radius: 0.375rem;
    }
    .sort-container select:focus {
      outline: none;
      border-color: #333333;
    }
    /* Comparison Container */
    .comparison-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1rem;
      background-color: #f9fafb;
      padding: 1.5rem;
      border-radius: 1rem;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      border: 1px solid #e5e7eb;
    }
    .product-compare-card {
      background-color: #ffffff;
      border-radius: 0.75rem;
      padding: 1.5rem;
      width: 300px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      border: 1px solid #e5e7eb;
      transition: all 0.3s ease;
    }
    .product-compare-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0,0,0,0.1);
    }
    .placeholder-card {
      background-color: #f3f4f6;
      border-radius: 0.75rem;
      padding: 1.5rem;
      width: 300px;
      height: 400px;
      border: 2px dashed #d1d5db;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #9ca3af;
    }
    .product-compare-img {
      width: 350px;
      height: 350px;
      object-fit: contain;
      margin: 0 auto 0.5rem;
      display: block;
    }
    .compare-heading {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 1rem;
      text-align: center;
    }
    .compare-detail {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
      border-bottom: 1px solid #f3f4f6;
    }
    .compare-detail-label {
      color: #6b7280;
      font-weight: 500;
    }
    .compare-detail-value {
      font-weight: 600;
      color: #111827;
    }
    .compare-price {
      font-size: 1.25rem;
      font-weight: 700;
      color: #dc2626;
      text-align: center;
      display: block;
    }
    .compare-stock-good {
      color: #10b981;
      font-weight: 600;
    }
    .compare-stock-low {
      color: #f59e0b;
      font-weight: 600;
    }
    .compare-stock-none {
      color: #ef4444;
      font-weight: 600;
    }
    
    /* Hide search bar in header for laptop page only */
    .search-container {
      display: none !important;
    }
    
    /* Center the navigation menu items */
    .menu-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      flex: 1;
    }
    
    .menu-wrapper ul {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 32px;
      margin: 0;
      padding: 0;
      list-style: none;
    }
    
    .menu-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      height: 70px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      background: #000;
    }
    
    /* Make the logo bigger */
    .menu-bar .logo {
      height: 60px;
      margin-right: 30px;
      border: 2px solid #ffffff;
      border-radius: 15px;
      padding: 8px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      background: rgba(255, 255, 255, 0.08);
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0, 125, 209, 0.15);
    }
    
    .menu-bar .logo:hover { 
      transform: scale(1.08) translateY(-3px);
      box-shadow: none;
      border-color: #fff;
      background: rgba(255, 255, 255, 0.12);
    }
    
    .compare-checkbox {
      appearance: auto !important;
      -webkit-appearance: checkbox !important;
      width: 18px;
      height: 18px;
      z-index: 1000 !important;
      position: relative !important;
      pointer-events: auto !important;
      background: #fff;
    }
  </style>
</head>
<body>
<?php include 'kioskheader.php'; ?>

  <main class="p-8">
    <!-- Model Brand Buttons -->
    <div class="flex flex-wrap gap-2 mb-4 justify-center" id="modelBrandButtons">
      <?php
        $currentModelBrand = $modelBrandFilter !== '' ? $modelBrandFilter : 'all models';

        // Always show "All Models" button
        $urlParams = [
          'model_brand' => 'all models',
          'page' => 1,
          'sort' => $sortOption,
          'storage' => $storageFilter
        ];
        $queryString = http_build_query($urlParams);
        $activeClass = ($currentModelBrand === 'all models') ? 'bg-gradient-to-r from-blue-700 to-blue-900 text-white shadow-lg scale-105' : 'bg-gray-200 text-gray-900 hover:bg-blue-200 hover:scale-105';
        echo '<a href="?' . $queryString . '" class="px-6 py-3 rounded-full border-2 border-blue-700 font-bold cursor-pointer transition-all duration-200 shadow-md m-1 ' . $activeClass . '" title="All Models" aria-label="All Models" style="user-select:none;">All Models</a>';

        // Show all available models (except "Not Available" and empty)
        foreach ($modelBrands as $model) {
          $modelLower = strtolower(trim($model));
          if ($modelLower === '' || $modelLower === 'not available') continue;
          $urlParams['model_brand'] = $modelLower;
          $queryString = http_build_query($urlParams);
          $activeClass = ($modelLower === $currentModelBrand) ? 'bg-gradient-to-r from-blue-700 to-blue-900 text-white shadow-lg scale-105' : 'bg-gray-200 text-gray-900 hover:bg-blue-200 hover:scale-105';
          echo '<a href="?' . $queryString . '" class="px-6 py-3 rounded-full border-2 border-blue-700 font-bold cursor-pointer transition-all duration-200 shadow-md m-1 ' . $activeClass . '" title="Filter by ' . htmlspecialchars($model) . '" aria-label="Filter by ' . htmlspecialchars($model) . '" style="user-select:none;">' . htmlspecialchars($model) . '</a>';
        }
      ?>
    </div>

    <hr class="my-4 border-gray-300" />

    <!-- Storage Buttons -->
    <div class="flex flex-wrap gap-2 mb-6 justify-center" id="storageButtons">
      <?php
      $currentStorage = $storageFilter !== '' ? $storageFilter : 'all storages';
      $activeClassStorage = ($currentStorage === 'all storages') ? 'bg-green-700 text-white shadow-lg' : 'bg-gray-300 text-gray-900 hover:bg-gray-400 hover:border-gray-600';
      $urlParamsStorage = [];
      $urlParamsStorage['page'] = 1;
      $urlParamsStorage['sort'] = $sortOption;
      $urlParamsStorage['model_brand'] = $modelBrandFilter;
      $queryStringStorage = http_build_query($urlParamsStorage);
      echo '<a href="?' . $queryStringStorage . '" class="px-5 py-3 rounded-full border border-transparent cursor-pointer transition-colors duration-300 shadow-md" title="All Storages" aria-label="Filter by All Storages" style="user-select:none;">All Storages</a>';

      // Filter out null, empty, and 'not available' storage values
      $filteredStorageOptions = array_filter($storageOptions, function($storage) {
          $storageLower = strtolower($storage);
          return $storageLower !== '' && $storageLower !== 'not available' && $storageLower !== null;
      });

      foreach ($filteredStorageOptions as $storage) {
          $storageLower = strtolower($storage);
          $activeClassStorage = ($storageLower === $currentStorage) ? 'bg-green-700 text-white shadow-lg' : 'bg-gray-300 text-gray-900 hover:bg-gray-400 hover:border-gray-600';
          $urlParamsStorage = [];
          $urlParamsStorage['storage'] = $storageLower;
          $urlParamsStorage['page'] = 1;
          $urlParamsStorage['sort'] = $sortOption;
          $urlParamsStorage['model_brand'] = $modelBrandFilter;
          $queryStringStorage = http_build_query($urlParamsStorage);
          echo '<a href="?' . $queryStringStorage . '" class="px-5 py-3 rounded-full border border-transparent cursor-pointer transition-colors duration-300 shadow-md" title="Filter by ' . htmlspecialchars($storage) . '" aria-label="Filter by ' . htmlspecialchars($storage) . '" style="user-select:none;">' . htmlspecialchars($storage) . '</a>';
      }
      ?>
    </div>

    <!-- Sort Options -->
    <div class="mb-6 flex justify-center">
      <div class="sort-container">
        <label for="sortOption" class="font-semibold mr-2 text-blue-400">Sort by:</label>
        <select id="sortOption" class="border border-gray-300 rounded px-2 py-1" onchange="changeSorting(this.value)">
          <option value="" <?= $sortOption === '' ? 'selected' : '' ?>>Default</option>
          <option value="price_asc" <?= $sortOption === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
          <option value="price_desc" <?= $sortOption === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
          <option value="stock_asc" <?= $sortOption === 'stock_asc' ? 'selected' : '' ?>>Stock (Low to High)</option>
          <option value="stock_desc" <?= $sortOption === 'stock_desc' ? 'selected' : '' ?>>Stock (High to Low)</option>
        </select>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="dropdown-product-grid">
      <?php
      if (count($products) === 0) {
          echo '<p class="col-span-full text-center text-gray-500 text-xl">No Laptop products found.</p>';
      } else {
          foreach ($products as &$product) { // Use reference to modify
              // Add default technical specifications for Laptops if not already set
              $specs = [
                  'water_resistance' => 'Not Rated',
                  'display_output' => 'Full HD Display',
                  'screen_size' => '13-inch to 16-inch',
                  'charging_port' => 'USB-C / Proprietary',
                  'material' => 'Aluminum / Plastic',
                  'chip' => 'Intel / AMD / Apple Silicon',
                  'camera_feature' => '720p/1080p Webcam'
              ];
              foreach ($specs as $key => $value) {
                  $product[$key] = empty($product[$key]) ? $value : $product[$key];
              }
              renderProductCard($product);
          }
          unset($product); // Unset reference
      }
      ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="flex justify-center space-x-2 mt-6">
      <?php for ($page = 1; $page <= $totalPages; $page++): ?>
        <a href="?page=<?= $page ?>&sort=<?= htmlspecialchars($sortOption) ?>&model_brand=<?= htmlspecialchars($modelBrandFilter) ?>&storage=<?= htmlspecialchars($storageFilter) ?>" class="px-3 py-1 rounded <?= $page === $currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
          <?= $page ?>
        </a>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>
  </main>

  <?php include 'kioskmodals.php'; ?>

  <script>
    // Utility function to get element by ID with error handling
    function getElement(id) {
      const el = document.getElementById(id);
      if (!el) {
        console.error(`Element with ID '${id}' not found.`);
      }
      return el;
    }

    // Add transition effects
    document.addEventListener('DOMContentLoaded', function() {
      // Animate product cards
      const cards = document.querySelectorAll('.product-card');
      cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
          card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, index * 50);
      });
      
      // Set up compare selects event listeners
      const compareSelect1 = getElement('compareSelect1');
      const compareSelect2 = getElement('compareSelect2');
      const compareSelect3 = getElement('compareSelect3');
      
      if (compareSelect1 && compareSelect2 && compareSelect3) {
        function updateDropdownOptions() {
          const selectedValues = [
            compareSelect1.value,
            compareSelect2.value,
            compareSelect3.value
          ];
          [compareSelect1, compareSelect2, compareSelect3].forEach((select, index) => {
            Array.from(select.options).forEach(option => {
              if (option.value === "") {
                option.disabled = false;
                return;
              }
              // Disable option if selected in other dropdowns
              option.disabled = selectedValues.includes(option.value) && option.value !== select.value;
            });
          });
        }

        compareSelect1.addEventListener('change', function() {
          fetchProductDetails(1, this.value);
          updateDropdownOptions();
        });
        compareSelect2.addEventListener('change', function() {
          fetchProductDetails(2, this.value);
          updateDropdownOptions();
        });
        compareSelect3.addEventListener('change', function() {
          fetchProductDetails(3, this.value);
          updateDropdownOptions();
        });

        // Initialize disabled options on page load
        updateDropdownOptions();
      }
    });

    // Cache for product details to avoid redundant fetches
    const productCache = {};

    // Function to clear compare selection
    function clearCompareSelection(slotNumber) {
      const select = document.getElementById(`compareSelect${slotNumber}`);
      if (select) {
        const removedProductId = select.value;
        select.value = '';
        select.dispatchEvent(new Event('change'));
        // Uncheck the corresponding checkbox
        if (removedProductId) {
          const checkbox = document.querySelector(`.compare-checkbox[value="${removedProductId}"]`);
          if (checkbox) {
            checkbox.checked = false;
          }
        }
      }
    }

    // Function to fetch and display product details
    function fetchProductDetails(slotNumber, productId) {
      if (!productId) {
        // If no product is selected, show placeholder
        const placeholder = getElement(`placeholder${slotNumber}`);
        if (placeholder) {
          placeholder.style.display = 'flex';
        }
        
        // Remove any existing product card
        const existingCard = getElement(`product-compare-${slotNumber}`);
        if (existingCard) {
          existingCard.remove();
        }
        return;
      }
      
      // Check if product details are already cached
      if (productCache[productId]) {
        displayProductDetails(slotNumber, productCache[productId]);
        return;
      }
      
      // Show loading state in placeholder
      const placeholder = getElement(`placeholder${slotNumber}`);
      if (placeholder) {
        placeholder.innerHTML = `
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400 mx-auto mb-4"></div>
          <p class="text-center text-lg">Loading...</p>
        `;
        placeholder.style.display = 'flex';
      }
      
      // Add timestamp to prevent caching
      const timestamp = Date.now();
      
      // Fetch product details
      fetch(`get_product_details.php?product_id=${encodeURIComponent(productId)}&t=${timestamp}`)
        .then(res => {
          if (!res.ok) throw new Error(`Failed to fetch product ${productId}`);
          return res.json();
        })
        .then(product => {
          // Cache the product details
          productCache[productId] = product;
          // Display the product details
          displayProductDetails(slotNumber, product);
        })
        .catch(error => {
          if (placeholder) {
            placeholder.innerHTML = `
              <svg class="w-20 h-20 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-center text-lg">Failed to load product details.</p>
            `;
            placeholder.style.display = 'flex';
          }
          console.error(error);
        });
    }

    function displayProductDetails(slotNumber, product) {
      // Remove previous card if exists
      const existingCard = document.getElementById(`product-compare-${slotNumber}`);
      if (existingCard) existingCard.remove();

      const placeholder = getElement(`placeholder${slotNumber}`);
      if (placeholder) placeholder.style.display = 'none';

      // If product is archived or missing, show error
      if (!product || product.archived) {
        if (placeholder) {
          placeholder.innerHTML = `
            <svg class="w-20 h-20 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-center text-lg">Product not available.</p>
          `;
          placeholder.style.display = 'flex';
        }
        return;
      }

      // Build product card
      const card = document.createElement('div');
      card.className = 'product-compare-card';
      card.id = `product-compare-${slotNumber}`;
      card.innerHTML = `
        <img src="${product.image1 ? product.image1 : 'uploads/default.jpg'}" class="product-compare-img" alt="Product Image" />
        <div class="compare-heading">${product.brand} ${product.model} (${product.storage})</div>
        <div class="compare-detail see-details" style="text-align: center; font-weight: 600; font-size: 1.25rem; margin-top: 0.75rem; color: #2563eb; cursor: pointer; user-select: none;" onclick="openDetailsModal(${product.product_id})">See Details</div>
      `;

      // Insert card after placeholder
      const container = document.getElementById('comparisonContainer');
      const placeholderDiv = document.getElementById(`placeholder${slotNumber}`);
      if (container && placeholderDiv) {
        container.insertBefore(card, placeholderDiv.nextSibling);
      }
    }

    // Function to change sorting
    function changeSorting(sortOption) {
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.set('sort', sortOption);
      urlParams.set('page', 1); // Reset to first page
      window.location.search = urlParams.toString();
    }

    // Function to add product to first available compare dropdown
    function addToCompare(productId) {
      const compareSelects = [
        document.getElementById('compareSelect1'),
        document.getElementById('compareSelect2'),
        document.getElementById('compareSelect3')
      ];
      for (const select of compareSelects) {
        if (select.value === '') {
          select.value = productId.toString();
          select.dispatchEvent(new Event('change'));
          break;
        }
      }
    }

    // Handle checkbox change to add or remove product from compare dropdowns
    function handleCompareCheckboxChange(checkbox) {
      const productId = checkbox.value;
      const checkboxes = document.querySelectorAll('.compare-checkbox');
      const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

      if (checkbox.checked) {
        if (checkedCount > 3) {
          // Prevent checking more than 3
          checkbox.checked = false;
          alert('You can only compare up to 3 products.');
          return;
        }
        // Add to first available compare dropdown
        addToCompare(productId);
      } else {
        // Remove from compare dropdown if selected
        const compareSelects = [
          document.getElementById('compareSelect1'),
          document.getElementById('compareSelect2'),
          document.getElementById('compareSelect3')
        ];
        for (const select of compareSelects) {
          if (select.value === productId) {
            select.value = '';
            select.dispatchEvent(new Event('change'));
            break;
          }
        }
      }

      // Disable unchecked checkboxes if 3 are checked
      const newCheckedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
      if (newCheckedCount >= 3) {
        checkboxes.forEach(cb => {
          if (!cb.checked) {
            cb.disabled = true;
          }
        });
      } else {
        checkboxes.forEach(cb => {
          cb.disabled = false;
        });
      }
    }

    // Update checkboxes based on compare dropdown selections
    function updateCheckboxesFromCompare() {
      const selectedValues = [
        document.getElementById('compareSelect1').value,
        document.getElementById('compareSelect2').value,
        document.getElementById('compareSelect3').value
      ];
      const checkboxes = document.querySelectorAll('.compare-checkbox');
      checkboxes.forEach(checkbox => {
        checkbox.checked = selectedValues.includes(checkbox.value);
      });

      // Disable unchecked checkboxes if 3 are checked
      const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
      if (checkedCount >= 3) {
        checkboxes.forEach(cb => {
          if (!cb.checked) {
            cb.disabled = true;
          }
        });
      } else {
        checkboxes.forEach(cb => {
          cb.disabled = false;
        });
      }
    }

    // Update dropdown options and checkboxes on page load and on compare dropdown change
    document.addEventListener('DOMContentLoaded', function() {
      updateDropdownOptions();
      updateCheckboxesFromCompare();

      const compareSelects = [
        document.getElementById('compareSelect1'),
        document.getElementById('compareSelect2'),
        document.getElementById('compareSelect3')
      ];
      compareSelects.forEach(select => {
        select.addEventListener('change', function() {
          updateDropdownOptions();
          updateCheckboxesFromCompare();
        });
      });

      // Also disable checkboxes if 3 products are already selected on page load
      const checkboxes = document.querySelectorAll('.compare-checkbox');
      const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
      if (checkedCount >= 3) {
        checkboxes.forEach(cb => {
          if (!cb.checked) {
            cb.disabled = true;
          }
        });
      }
    });
    function clearAllCompareSelections() {
      const compareSelects = [
        document.getElementById('compareSelect1'),
        document.getElementById('compareSelect2'),
        document.getElementById('compareSelect3')
      ];
      compareSelects.forEach(select => {
        select.value = '';
        select.dispatchEvent(new Event('change'));
      });
    }
    // Function to open details modal and fetch product details
    function openDetailsModal(productId) {
      const modal = document.getElementById('detailsModal');
      const modalContent = document.getElementById('modalContent');
      if (modal && modalContent) {
        modal.classList.remove('hidden');
        modalContent.innerHTML = '<p>Loading...</p>';

        // Fetch product details
        fetch(`get_product_details.php?product_id=${encodeURIComponent(productId)}`)
          .then(res => {
            if (!res.ok) throw new Error('Failed to fetch product details');
            return res.json();
          })
          .then(product => {
            // Build detailed product info HTML
            let detailsHtml = `
              <h3 class="text-xl font-bold mb-4">${product.brand} ${product.model} (${product.storage})</h3>
              <img src="${product.image1 ? product.image1 : 'uploads/default.jpg'}" alt="Product Image" class="w-full h-auto mb-4" />
              <p><strong>Price:</strong> ₱${Number(product.selling_price).toLocaleString()}</p>
              <p><strong>Stock:</strong> ${product.stock_quantity}</p>
              <p><strong>Color:</strong> ${product.color || 'N/A'}</p>
              <p><strong>Status:</strong> ${product.archived ? 'Archived' : 'Active'}</p>
              <p><strong>Description:</strong> ${product.description || 'No description available.'}</p>
            `;
            modalContent.innerHTML = detailsHtml;
          })
          .catch(error => {
            modalContent.innerHTML = '<p class="text-red-600">Failed to load product details.</p>';
            console.error(error);
          });
      }
    }

    // Function to close details modal
    function closeDetailsModal() {
      const modal = document.getElementById('detailsModal');
      if (modal) {
        modal.classList.add('hidden');
      }
    }
  </script>

  <style>
    /* BULLETPROOF Customer Care Modal Styles */
    .care-modal.show,
    .care-modal.customer-care-active {
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
      z-index: 100000 !important;
    }
    
    .care-modal.show .modal-content,
    .care-modal.customer-care-active .modal-content {
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }
    
    /* Force Contact Us modal to stay open */
    #contactUsModal.show,
    #contactUsModal.customer-care-active {
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
      z-index: 100000 !important;
      pointer-events: auto !important;
    }
  </style>

  <script>
  // Local handler to ensure See Details works on this page
  document.addEventListener('click', function(e) {
    const target = e.target;
    if (target.classList.contains('details-btn') || target.closest('.details-btn')) {
      e.preventDefault();
      e.stopPropagation();
      const btn = target.classList.contains('details-btn') ? target : target.closest('.details-btn');
      const card = btn.closest('.card');
      if (!card) return;

      const detailsModal = document.getElementById('detailsModal');
      if (!detailsModal) return;

      const modalImage = detailsModal.querySelector('.modal-product-image');
      const modalName = detailsModal.querySelector('.modal-product-name');
      const modalDescription = detailsModal.querySelector('.modal-product-description');
      const modalPrice = detailsModal.querySelector('.modal-product-price');
      const modalBrand = detailsModal.querySelector('.modal-product-brand');
      const modalModel = detailsModal.querySelector('.modal-product-model');
      const modalStorage = detailsModal.querySelector('.modal-product-storage');
      const modalWaterResistance = detailsModal.querySelector('.modal-product-water-resistance');
      const modalDisplayOutput = detailsModal.querySelector('.modal-product-display-output');
      const modalScreenSize = detailsModal.querySelector('.modal-product-screen-size');
      const modalChargingPort = detailsModal.querySelector('.modal-product-charging-port');
      const modalMaterial = detailsModal.querySelector('.modal-product-material');
      const modalChip = detailsModal.querySelector('.modal-product-chip');
      const modalCameraFeature = detailsModal.querySelector('.modal-product-camera-feature');

      const imagesJson = card.getAttribute('data-images') || '[]';
      let images = [];
      try { images = JSON.parse(imagesJson); } catch (err) { images = []; }

      const mainImageSrc = images.length > 0 ? images[0] : '';
      if (modalImage) modalImage.src = mainImageSrc;
      if (modalName) modalName.textContent = card.getAttribute('data-product-name') || '';
      if (modalDescription) modalDescription.textContent = card.getAttribute('data-description') || '';
      if (modalPrice) modalPrice.textContent = card.getAttribute('data-price') || '';
      if (modalBrand) modalBrand.textContent = card.getAttribute('data-brand') || '';
      if (modalModel) modalModel.textContent = card.getAttribute('data-model') || '';
      if (modalStorage) modalStorage.textContent = card.getAttribute('data-storage') || '';
      if (modalWaterResistance) modalWaterResistance.textContent = card.getAttribute('data-water-resistance') || '';
      if (modalDisplayOutput) modalDisplayOutput.textContent = card.getAttribute('data-display-output') || '';
      if (modalScreenSize) modalScreenSize.textContent = card.getAttribute('data-screen-size') || '';
      if (modalChargingPort) modalChargingPort.textContent = card.getAttribute('data-charging-port') || '';
      if (modalMaterial) modalMaterial.textContent = card.getAttribute('data-material') || '';
      if (modalChip) modalChip.textContent = card.getAttribute('data-chip') || '';
      if (modalCameraFeature) modalCameraFeature.textContent = card.getAttribute('data-camera-feature') || '';

      const thumbnailsContainer = document.getElementById('modalThumbnails');
      if (thumbnailsContainer) {
        thumbnailsContainer.innerHTML = '';
        images.forEach((src, index) => {
          const thumbnail = document.createElement('img');
          thumbnail.src = src;
          if (index === 0) thumbnail.classList.add('active');
          thumbnail.addEventListener('click', () => {
            if (modalImage) modalImage.src = src;
            thumbnailsContainer.querySelectorAll('img').forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
          });
          thumbnailsContainer.appendChild(thumbnail);
        });
      }

      const modalPanel = detailsModal.querySelector('.details-modal-content');
      if (modalPanel) modalPanel.style.visibility = 'hidden';
      detailsModal.style.display = 'block';
      detailsModal.classList.add('show');

      if (modalPanel) {
        const finalize = () => { modalPanel.style.visibility = 'visible'; };
        if (modalImage && typeof modalImage.complete !== 'undefined') {
          if (modalImage.complete) {
            finalize();
          } else {
            modalImage.addEventListener('load', finalize, { once: true });
          }
        } else {
          finalize();
        }
      }
    }
  });
  </script>

  <script src="kiosk.js"></script>
  <script src="compare.js"></script>
  <?php include 'customer_chat_widget.php'; ?>
</body>
</html>