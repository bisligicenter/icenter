<?php
require_once 'db.php';
require_once 'functions.php';
$conn = getConnection();

try {
    // Only show accessories, airpods, android, ipad, iphone, laptop, pcset, or printer if category is set
    $categoryFilter = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
    if ($categoryFilter === 'accessories') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'accessories' ORDER BY product, brand, model, storage, product_id ASC");
    } else if ($categoryFilter === 'airpods') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'airpods' ORDER BY product, brand, model, storage, product_id ASC");
    } else if ($categoryFilter === 'android') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'android' ORDER BY product, brand, model, storage, product_id ASC");
    } else if ($categoryFilter === 'ipad') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'ipad' ORDER BY product, brand, model, storage, product_id ASC");
    } else if ($categoryFilter === 'iphone') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'iphone' ORDER BY product, brand, model, storage, product_id ASC");
    } else if ($categoryFilter === 'laptop') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'laptop' ORDER BY product, brand, model, storage, product_id ASC");
    } else if ($categoryFilter === 'pcset') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'pc set' ORDER BY product, brand, model, storage, product_id ASC");
    } else if ($categoryFilter === 'printer') {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE (archived IS NULL OR archived = 0) AND LOWER(product) = 'printer' ORDER BY product, brand, model, storage, product_id ASC");
    } else {
        $allProductsStmt = $conn->prepare("SELECT * FROM products WHERE archived IS NULL OR archived = 0 ORDER BY product, brand, model, storage, product_id ASC");
    }
    $allProductsStmt->execute();
    $allProducts = $allProductsStmt->fetchAll(PDO::FETCH_ASSOC);
    // Generate product options once
    $productOptions = '';
    foreach ($allProducts as $product) {
        $productOptions .= '<option value="' . htmlspecialchars($product['product_id']) . '">' .
            htmlspecialchars($product['product'] . ' ' . $product['brand'] . ' ' . $product['model'] . ' (' . $product['storage'] . ')') .
            '</option>';
    }
} catch (PDOException $e) {
    echo '<div class="text-red-500">Error loading products: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $allProducts = [];
    $productOptions = '';
}

$selectedIds = [];
if (isset($_GET['ids'])) {
    $selectedIds = array_map('urldecode', explode(',', $_GET['ids']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Compare Products - Inventory System</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="kiosk.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background: #fff;
      color: #181818;
      min-height: 100vh;
      overflow-x: hidden;
    }
    .main-container {
      background: #fff;
      border-radius: 24px;
      margin: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), 0 0 0 1px #e5e7eb;
      overflow: hidden;
      animation: slideInUp 0.8s ease-out;
    }
    @keyframes slideInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .header-section {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      padding: 40px 20px;
      text-align: center;
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid #e5e7eb;
    }
    .header-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    .page-title {
      font-size: 3rem;
      font-weight: 800;
      color: #181818;
      margin-bottom: 10px;
      text-shadow: 0 2px 8px rgba(0,0,0,0.04);
      position: relative;
      z-index: 1;
    }
    .page-subtitle {
      font-size: 1.2rem;
      color: #555;
      font-weight: 400;
      position: relative;
      z-index: 1;
    }
    .controls-section {
      padding: 40px;
      background: #fafbfc;
      border-bottom: 1px solid #e5e7eb;
    }
    .controls-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .control-group {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
      position: relative;
    }
    .control-label {
      font-size: 0.9rem;
      font-weight: 600;
      color: #888;
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .control-label i {
      font-size: 0.8rem;
      opacity: 0.7;
    }
    .compare-select {
      width: 100%;
      padding: 15px 20px;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-size: 1rem;
      background: #fff;
      color: #181818;
      transition: all 0.3s ease;
      font-weight: 500;
      position: relative;
    }
    .compare-select:focus {
      outline: none;
      border-color: #181818;
      box-shadow: 0 0 0 3px rgba(24, 24, 24, 0.1);
      transform: scale(1.02);
    }
    .compare-select:hover {
      border-color: #d1d5db;
      transform: translateY(-1px);
    }
    .compare-select.has-selection {
      border-color: #181818;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }
    .action-buttons {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    .btn:hover::before {
      left: 100%;
    }
    .btn-primary {
      background: #181818;
      color: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border: 1px solid #181818;
    }
    .btn-primary:hover {
      background: #fff;
      color: #181818;
      border: 1px solid #181818;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .btn-secondary {
      background: #fff;
      color: #181818;
      border: 1px solid #e5e7eb;
    }
    .btn-secondary:hover {
      background: #181818;
      color: #fff;
      border: 1px solid #181818;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .btn-danger {
      background: #fff;
      color: #181818;
      border: 1px solid #181818;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .btn-danger:hover {
      background: #181818;
      color: #fff;
      border: 1px solid #181818;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .comparison-section {
      padding: 40px;
      background: #fff;
    }
    .comparison-container {
      display: flex;
      gap: 30px;
      justify-content: center;
      align-items: stretch; /* Ensures all columns/cards are the same height */
    }
    .product-compare-card {
      background: #fff;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      border: 1px solid #e5e7eb;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.6s ease-out;
      min-height: 600px;
    }
    .product-compare-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #181818, #4a5568);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    .product-compare-card:hover::before {
      transform: scaleX(1);
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .product-compare-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 16px 32px rgba(0, 0, 0, 0.10);
      border-color: #bdbdbd;
    }
    .placeholder-card {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border-radius: 20px;
      padding: 40px 30px;
      border: 1px solid #e5e7eb;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #bbb;
      transition: all 0.3s ease;
      min-height: 500px;
      position: relative;
      overflow: hidden;
      cursor: pointer;
    }
    .placeholder-card:hover {
      border-color: #181818;
      background: #fff;
      transform: scale(1.02);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .placeholder-card::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100px;
      height: 100px;
      background: radial-gradient(circle, rgba(24, 24, 24, 0.05) 0%, transparent 70%);
      transform: translate(-50%, -50%);
      border-radius: 50%;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    .placeholder-card:hover::before {
      opacity: 1;
    }
    .placeholder-icon {
      width: 80px;
      height: 80px;
      color: #e5e7eb;
      margin-bottom: 20px;
      position: relative;
      z-index: 1;
      transition: all 0.3s ease;
    }
    .placeholder-card:hover .placeholder-icon {
      color: #181818;
      transform: scale(1.1);
    }
    .placeholder-text {
      font-size: 1.1rem;
      font-weight: 600;
      text-align: center;
      position: relative;
      z-index: 1;
      transition: color 0.3s ease;
    }
    .placeholder-card:hover .placeholder-text {
      color: #181818;
    }
    .product-compare-img {
      width: 100%;
      height: 350px;
      object-fit: contain;
      margin: 0 auto 20px;
      display: block;
      border-radius: 12px;
      background: #fafbfc;
      padding: 20px;
      transition: transform 0.3s ease;
      filter: grayscale(1) contrast(1.1);
      position: relative;
    }
    .product-compare-img:hover {
      transform: scale(1.05);
      filter: grayscale(0) contrast(1);
    }
    .compare-heading {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 20px;
      text-align: center;
      color: #181818;
      line-height: 1.3;
    }
    .compare-details {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .compare-detail {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #e5e7eb;
      transition: all 0.3s ease;
    }
    .compare-detail:hover {
      background: #fafbfc;
      border-radius: 8px;
      padding-left: 10px;
      padding-right: 10px;
      transform: translateX(5px);
    }
    .compare-detail-label {
      color: #888;
      font-weight: 500;
      font-size: 0.9rem;
    }
    .compare-detail-value {
      font-weight: 600;
      color: #181818;
      text-align: right;
    }
    .compare-price {
      font-size: 1.5rem;
      font-weight: 800;
      color: #181818;
      text-align: center;
      display: block;
      margin: 20px 0;
      padding: 15px;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      transition: all 0.3s ease;
    }
    .compare-price:hover {
      background: #181818;
      color: #fff;
      transform: scale(1.05);
    }
    .compare-stock-good {
      color: #fff;
      font-weight: 600;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }
    .compare-stock-low {
      color: #fff;
      font-weight: 600;
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
    }
    .compare-stock-none {
      color: #fff;
      font-weight: 600;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    }
    .compare-column {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .color-swatches {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin: 15px 0;
      flex-wrap: wrap;
    }
    .color-swatch {
      width: 60px;
      height: 60px;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: #fff;
      padding: 3px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .color-swatch::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle, rgba(24, 24, 24, 0.1) 0%, transparent 70%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    .color-swatch:hover::before {
      opacity: 1;
    }
    .color-swatch:hover {
      border-color: #181818;
      transform: scale(1.1);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .color-swatch.active {
      border-color: #181818;
      box-shadow: 0 0 0 2px #e5e7eb;
      transform: scale(1.1);
    }
    .color-swatch img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 4px;
    }
    .tooltip {
      position: absolute;
      background: #181818;
      color: #fff;
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 0.8rem;
      white-space: nowrap;
      z-index: 1000;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
      transform: translateY(10px);
    }
    .tooltip.show {
      opacity: 1;
      transform: translateY(0);
    }
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #181818;
      color: #fff;
      padding: 15px 20px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      z-index: 1000;
      transform: translateX(400px);
      transition: transform 0.3s ease;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .notification.show {
      transform: translateX(0);
    }
    .notification.success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .notification.error {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #e5e7eb;
      border-radius: 50%;
      border-top-color: #181818;
      animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .feature-highlight {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      border: 1px solid #f59e0b;
      border-radius: 8px;
      padding: 8px 12px;
      margin: 5px 0;
      font-size: 0.85rem;
      color: #92400e;
      font-weight: 500;
    }

    @media (max-width: 768px) {
      .main-container { margin: 10px; border-radius: 16px; }
      .header-section { padding: 30px 15px; }
      .page-title { font-size: 2rem; }
      .controls-section, .comparison-section { padding: 20px; }
      .controls-grid { grid-template-columns: 1fr; }
      .comparison-container { grid-template-columns: 1fr; gap: 20px; }
      .action-buttons { flex-direction: column; align-items: center; }
      .btn { width: 100%; max-width: 300px; justify-content: center; }
      .notification { right: 10px; left: 10px; transform: translateY(-100px); }
      .notification.show { transform: translateY(0); }
    }
    .accessibility-focus:focus {
      outline: 3px solid #3b82f6;
      outline-offset: 2px;
    }
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }
    .price-calculator {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border-radius: 16px;
      padding: 20px;
      margin: 20px 0;
      border: 1px solid #e5e7eb;
      display: none;
    }
    .price-calculator.show {
      display: block;
      animation: slideInDown 0.4s ease-out;
    }
    @keyframes slideInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .calculator-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: #181818;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .price-differences {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }
    .price-diff-item {
      background: #fff;
      padding: 15px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      text-align: center;
      transition: all 0.3s ease;
    }
    .price-diff-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .diff-label {
      font-size: 0.9rem;
      color: #666;
      font-weight: 500;
      margin-bottom: 8px;
    }
    .diff-value {
      font-size: 1.3rem;
      font-weight: 700;
      color: #181818;
    }
    .diff-positive {
      color: #10b981;
    }
    .diff-negative {
      color: #ef4444;
    }
    .diff-neutral {
      color: #6b7280;
    }
    .calculator-summary {
      background: #fff;
      padding: 15px;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      margin-top: 15px;
      text-align: center;
    }
    .summary-text {
      font-size: 1rem;
      font-weight: 600;
      color: #181818;
    }
    .best-value {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      border: 1px solid #f59e0b;
      color: #92400e;
    }
  </style>
</head>
<body>
<?php include 'kioskheader.php'; ?>
  <div class="main-container">
    <!-- Header Section -->
    <div class="header-section">
      <h1 class="page-title">
        Compare Products
      </h1>
      <p class="page-subtitle">Compare features, prices, and specifications side by side to make informed decisions</p>
    </div>
    <!-- Controls Section -->
    <div class="controls-section">
      <div class="controls-grid">
        <!-- Product 1 dropdown -->
        <div class="control-group">
          <label for="compareSelect1" class="control-label">
            <i class="fas fa-mobile-alt"></i>
            Product 1
          </label>
          <select id="compareSelect1" class="compare-select" aria-label="Select Product 1">
            <option value="">Choose Product 1</option>
            <?php foreach ($allProducts as $product): ?>
              <option value="<?= htmlspecialchars($product['product_id']) ?>"
                <?= (isset($selectedIds[0]) && $selectedIds[0] == $product['product_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($product['product'] . ' ' . $product['brand'] . ' ' . $product['model'] . ' (' . $product['storage'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Product 2 dropdown -->
        <div class="control-group">
          <label for="compareSelect2" class="control-label">
            <i class="fas fa-tablet-alt"></i>
            Product 2
          </label>
          <select id="compareSelect2" class="compare-select" aria-label="Select Product 2">
            <option value="">Choose Product 2</option>
            <?php foreach ($allProducts as $product): ?>
              <option value="<?= htmlspecialchars($product['product_id']) ?>"
                <?= (isset($selectedIds[1]) && $selectedIds[1] == $product['product_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($product['product'] . ' ' . $product['brand'] . ' ' . $product['model'] . ' (' . $product['storage'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Product 3 dropdown -->
        <div class="control-group">
          <label for="compareSelect3" class="control-label">
            <i class="fas fa-laptop"></i>
            Product 3
          </label>
          <select id="compareSelect3" class="compare-select" aria-label="Select Product 3">
            <option value="">Choose Product 3</option>
            <?php foreach ($allProducts as $product): ?>
              <option value="<?= htmlspecialchars($product['product_id']) ?>"
                <?= (isset($selectedIds[2]) && $selectedIds[2] == $product['product_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($product['product'] . ' ' . $product['brand'] . ' ' . $product['model'] . ' (' . $product['storage'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="action-buttons">
        <button type="button" class="btn btn-primary accessibility-focus" onclick="clearAllCompareSelections()" aria-label="Clear all product selections">
          <i class="fas fa-trash-alt"></i>
          Clear All
        </button>
        <button type="button" class="btn btn-secondary accessibility-focus" onclick="clearCompareSelection(1)" aria-label="Remove Product 1">
          <i class="fas fa-times"></i>
          Remove Product 1
        </button>
        <button type="button" class="btn btn-secondary accessibility-focus" onclick="clearCompareSelection(2)" aria-label="Remove Product 2">
          <i class="fas fa-times"></i>
          Remove Product 2
        </button>
        <button type="button" class="btn btn-secondary accessibility-focus" onclick="clearCompareSelection(3)" aria-label="Remove Product 3">
          <i class="fas fa-times"></i>
          Remove Product 3
        </button>
      </div>
    </div>
    <!-- Price Calculator Section -->
    <div class="price-calculator" id="priceCalculator">
      <div class="calculator-title">
        <i class="fas fa-calculator"></i>
        Price Difference Calculator
      </div>
      <div class="price-differences" id="priceDifferences">
        <!-- Price differences will be populated here -->
      </div>
      <div class="calculator-summary" id="calculatorSummary">
        <!-- Summary will be populated here -->
      </div>
    </div>
    
    <!-- Comparison Section -->
    <div class="comparison-section">
      <div class="comparison-container" id="comparisonContainer">
        <!-- Product 1 column -->
        <div class="compare-column">
          <div class="placeholder-card" id="placeholder1" onclick="focusSelect(1)" role="button" tabindex="0" aria-label="Click to select Product 1">
            <i class="fas fa-plus placeholder-icon"></i>
            <p class="placeholder-text">Select Product 1</p>
            <p class="placeholder-text" style="font-size: 0.9rem; opacity: 0.7;">Click to choose a product</p>
          </div>
        </div>
        <!-- Product 2 column -->
        <div class="compare-column">
          <div class="placeholder-card" id="placeholder2" onclick="focusSelect(2)" role="button" tabindex="0" aria-label="Click to select Product 2">
            <i class="fas fa-plus placeholder-icon"></i>
            <p class="placeholder-text">Select Product 2</p>
            <p class="placeholder-text" style="font-size: 0.9rem; opacity: 0.7;">Click to choose a product</p>
          </div>
        </div>
        <!-- Product 3 column -->
        <div class="compare-column">
          <div class="placeholder-card" id="placeholder3" onclick="focusSelect(3)" role="button" tabindex="0" aria-label="Click to select Product 3">
            <i class="fas fa-plus placeholder-icon"></i>
            <p class="placeholder-text">Select Product 3</p>
            <p class="placeholder-text" style="font-size: 0.9rem; opacity: 0.7;">Click to choose a product</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Notification Container -->
  <div id="notificationContainer"></div>

  <?php include 'kioskmodals.php'; ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let selectedProducts = new Set();
      let comparisonData = {};
      
      // Add event listeners for select changes
      for (let i = 1; i <= 3; i++) {
        const select = document.getElementById(`compareSelect${i}`);
        select.addEventListener('change', function() {
          updateComparison(i, this.value);
        });
        
        // Add visual feedback for selection
        select.addEventListener('change', function() {
          if (this.value) {
            this.classList.add('has-selection');
          } else {
            this.classList.remove('has-selection');
          }
        });
      }

      // Automatically load and display selected products on page load
      for (let i = 1; i <= 3; i++) {
        const select = document.getElementById(`compareSelect${i}`);
        if (select.value) {
          select.classList.add('has-selection');
          updateComparison(i, select.value);
        } else {
          showPlaceholder(i);
        }
      }
      
      function updateComparison(position, productId) {
        if (!productId) {
          showPlaceholder(position);
          selectedProducts.delete(productId);
          return;
        }
        
        // Check for duplicate selections
        if (selectedProducts.has(productId)) {
          showNotification('This product is already selected in another column', 'error');
          document.getElementById(`compareSelect${position}`).value = '';
          return;
        }
        
        selectedProducts.add(productId);
        showLoading(position);
        
        // Fetch product data
        fetch(`get_product_details.php?id=${productId}`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(product => {
            showProductCard(position, product);
            comparisonData[position] = product;
            updatePriceCalculator();
            showNotification(`Product ${position} loaded successfully`, 'success');
          })
          .catch(error => {
            console.error('Error fetching product:', error);
            showPlaceholder(position);
            selectedProducts.delete(productId);
            showNotification('Failed to load product. Please try again.', 'error');
          });
      }
      
      function escapeHtml(text) {
        return String(text)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
      }
      
      function safe(v) { return v ?? '-'; }
      
      function showProductCard(position, product) {
        const placeholder = document.getElementById(`placeholder${position}`);
        let price = product.selling_price ?? 0;
        let stock_quantity = product.stock_quantity ?? 0;
        
        // Collect all available images for this product
        const images = [];
        for (let i = 1; i <= 8; i++) {
          const imageKey = `image${i}`;
          let img = product[imageKey];
          if (img && typeof img === 'string' && img.trim() !== '' && img.trim().toLowerCase() !== 'null') {
            img = img.trim();
            images.push(img); // Use as-is, since DB already has the full path
          }
        }
        // Use first image as default, fallback to a generic image
        let imagePath = images.length > 0 ? images[0] : 'images/products/NO_IMAGE_AVAILABLE.jpg';
        
        // Create color swatches if multiple images exist
        let colorSwatches = '';
        if (images.length > 1) {
          colorSwatches = '<div class="color-swatches" role="group" aria-label="Product color options">';
          images.forEach((img, index) => {
            colorSwatches += `
              <button class="color-swatch ${index === 0 ? 'active' : ''}" 
                      onclick="changeProductImage(${position}, ${index}, ${JSON.stringify(images).replace(/"/g, '&quot;')})"
                      data-image-index="${index}"
                      aria-label="Select color option ${index + 1}">
                <img src="${escapeHtml(img)}" alt="Color ${index + 1}" onerror="this.src='images/products/NO_IMAGE_AVAILABLE.jpg'">
              </button>
            `;
          });
          colorSwatches += '</div>';
        }
        
        // Add feature highlights
        let featureHighlights = '';
        if (product.stock_quantity > 0) {
          featureHighlights = '<div class="feature-highlight"><i class="fas fa-check-circle"></i> In Stock</div>';
        }
        
        placeholder.innerHTML = `
          <div class="product-compare-card" data-product-images='${JSON.stringify(images).replace(/'/g, "&#39;")}' data-current-image-index="0">
            <img src="${escapeHtml(imagePath)}" alt="${escapeHtml(product.product)}" class="product-compare-img" onerror="this.src='images/products/NO_IMAGE_AVAILABLE.jpg'">
            ${colorSwatches}
            <h3 class="compare-heading">${escapeHtml(safe(product.product))} ${escapeHtml(safe(product.brand))}</h3>
            ${featureHighlights}
            <div class="compare-details">
              <div class="compare-detail">
                <span class="compare-detail-label">Type</span>
                <span class="compare-detail-value">${escapeHtml(safe(product.model))}</span>
              </div>
              <div class="compare-detail">
                <span class="compare-detail-label">Brand</span>
                <span class="compare-detail-value">${escapeHtml(safe(product.brand))}</span>
              </div>
              <div class="compare-detail">
                <span class="compare-detail-label">Model</span>
                <span class="compare-detail-value">${escapeHtml(safe(product.model))}</span>
              </div>
              <div class="compare-detail">
                <span class="compare-detail-label">Storage</span>
                <span class="compare-detail-value">${escapeHtml(safe(product.storage))}</span>
              </div>
            </div>
            <span class="compare-price">₱${parseFloat(price).toLocaleString()}</span>
          </div>
        `;
      }
      
      function showPlaceholder(position) {
        const placeholder = document.getElementById(`placeholder${position}`);
        placeholder.innerHTML = `
          <i class="fas fa-plus placeholder-icon"></i>
          <p class="placeholder-text">Select Product ${position}</p>
          <p class="placeholder-text" style="font-size: 0.9rem; opacity: 0.7;">Click to choose a product</p>
        `;
        placeholder.onclick = () => focusSelect(position);
        placeholder.setAttribute('role', 'button');
        placeholder.setAttribute('tabindex', '0');
        placeholder.setAttribute('aria-label', `Click to select Product ${position}`);
      }
      
      function showLoading(position) {
        const placeholder = document.getElementById(`placeholder${position}`);
        placeholder.innerHTML = '<div class="loading"></div><p class="placeholder-text">Loading...</p>';
      }
      
      function getStockClass(stock) {
        const stockNum = parseInt(stock);
        if (stockNum === 0) return 'compare-stock-none';
        if (stockNum <= 5) return 'compare-stock-low';
        return 'compare-stock-good';
      }
      
      
      
      function updatePriceCalculator() {
        const products = Object.values(comparisonData);
        const calculator = document.getElementById('priceCalculator');
        const differencesContainer = document.getElementById('priceDifferences');
        const summaryContainer = document.getElementById('calculatorSummary');
        
        if (products.length < 2) {
          calculator.classList.remove('show');
          differencesContainer.innerHTML = '';
          summaryContainer.innerHTML = '';
          return;
        }
        
        calculator.classList.add('show');
        
        // Calculate price differences
        const prices = products.map(p => parseFloat(p.selling_price || 0));
        const productNames = products.map(p => `${p.product} ${p.brand} ${p.model}`);
        
        let differencesHTML = '';
        let totalSavings = 0;
        let bestValueIndex = 0;
        let minPrice = Math.min(...prices);
        
        // Generate comparison pairs
        for (let i = 0; i < products.length; i++) {
          for (let j = i + 1; j < products.length; j++) {
            const price1 = prices[i];
            const price2 = prices[j];
            const diff = price2 - price1;
            const absDiff = Math.abs(diff);
            
            const diffClass = diff > 0 ? 'diff-positive' : diff < 0 ? 'diff-negative' : 'diff-neutral';
            const diffIcon = diff > 0 ? 'fa-arrow-up' : diff < 0 ? 'fa-arrow-down' : 'fa-minus';
            const diffText = diff > 0 ? 'More expensive' : diff < 0 ? 'Less expensive' : 'Same price';
            
            differencesHTML += `
              <div class="price-diff-item">
                <div class="diff-label">${productNames[i]} vs ${productNames[j]}</div>
                <div class="diff-value ${diffClass}">
                  <i class="fas ${diffIcon}"></i>
                  ₱${absDiff.toLocaleString()}
                </div>
                <div class="diff-label">${diffText}</div>
              </div>
            `;
            
            if (diff < 0) {
              totalSavings += absDiff;
            }
          }
        }
        
        differencesContainer.innerHTML = differencesHTML;
        
        // Find best value (lowest price)
        bestValueIndex = prices.indexOf(minPrice);
        
        // Generate summary
        const totalProducts = products.length;
        const avgPrice = prices.reduce((a, b) => a + b, 0) / totalProducts;
        const priceRange = Math.max(...prices) - minPrice;
        
        let summaryHTML = `
          <div class="summary-text">
            <strong>Comparison Summary:</strong><br>
            • ${totalProducts} products compared<br>
            • Price range: ₱${priceRange.toLocaleString()}<br>
            • Average price: ₱${avgPrice.toLocaleString()}<br>
        `;
        
        if (totalSavings > 0) {
          summaryHTML += `• Potential savings: ₱${totalSavings.toLocaleString()}<br>`;
        }
        
        summaryHTML += `• Best value: ${productNames[bestValueIndex]}</div>`;
        
        summaryContainer.innerHTML = summaryHTML;
        summaryContainer.className = 'calculator-summary best-value';
      }
      
      function showNotification(message, type = 'success') {
        const container = document.getElementById('notificationContainer');
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
          <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
          <span>${message}</span>
        `;
        
        container.appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
          notification.classList.remove('show');
          setTimeout(() => container.removeChild(notification), 300);
        }, 3000);
      }
      
      // Make functions globally accessible
      window.showPlaceholder = showPlaceholder;
      window.showLoading = showLoading;
      window.getStockClass = getStockClass;
      window.showNotification = showNotification;
      window.updateComparison = updateComparison;
      
      // Fix Clear All button functionality
      window.clearAllCompareSelections = function() {
        for (let i = 1; i <= 3; i++) {
          const select = document.getElementById(`compareSelect${i}`);
          select.value = '';
          select.classList.remove('has-selection');
          showPlaceholder(i);
        }
        comparisonData = {};
        updatePriceCalculator();
        showNotification('All products cleared', 'success');
      };
    });
    
    // Global functions for button actions
          function clearCompareSelection(position) {
        const select = document.getElementById(`compareSelect${position}`);
        select.value = '';
        select.classList.remove('has-selection');
        showPlaceholder(position);
        delete comparisonData[position];
        updatePriceCalculator();
        showNotification(`Product ${position} removed`, 'success');
      }
    
          function clearAllCompareSelections() {
        for (let i = 1; i <= 3; i++) {
          clearCompareSelection(i);
        }
        comparisonData = {};
        updatePriceCalculator();
        showNotification('All products cleared', 'success');
      }
    
    function focusSelect(position) {
      const select = document.getElementById(`compareSelect${position}`);
      select.focus();
      select.click();
    }
    
    // Function to change product image when color swatch is clicked
    function changeProductImage(position, imageIndex, images) {
      const placeholder = document.getElementById(`placeholder${position}`);
      const productCard = placeholder.querySelector('.product-compare-card');
      const mainImage = productCard.querySelector('.product-compare-img');
      const swatches = productCard.querySelectorAll('.color-swatch');
      
      // Update main image
      if (images[imageIndex]) {
        mainImage.src = images[imageIndex];
      }
      
      // Update active swatch
      swatches.forEach((swatch, index) => {
        if (index === imageIndex) {
          swatch.classList.add('active');
        } else {
          swatch.classList.remove('active');
        }
      });
      
      // Update data attribute
      productCard.setAttribute('data-current-image-index', imageIndex);
      
      // Show notification
      showNotification('Image updated', 'success');
    }
    
    // Keyboard navigation for accessibility
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        const activeElement = document.activeElement;
        if (activeElement.classList.contains('placeholder-card')) {
          e.preventDefault();
          const position = activeElement.id.replace('placeholder', '');
          focusSelect(position);
        }
      }
    });
  </script>
  <script src="kiosk.js"></script>
</body>
</html>