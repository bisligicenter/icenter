<?php
require_once 'db.php';

$currentCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
$currentModelBrand = isset($_GET['model_brand']) ? strtolower(trim($_GET['model_brand'])) : '';

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

// Get categories for the buttons (moved here to avoid duplication)
try {
    $stmt = $conn->query("SELECT DISTINCT product FROM products ORDER BY product ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}
$activeCategory = $currentCategory === '' ? 'all products' : $currentCategory;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Products</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <style>
      body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
      }
      
      /* Enhanced Header */
      header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
      }
    </style>
</head>
<body class="min-h-screen">
    <!-- Enhanced Header -->
    <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
        <div class="flex justify-between items-center px-8 py-6 space-x-4">
            <div class="flex items-center space-x-6">
                <div class="ml-2 mr-10 text-sm text-white flex items-center space-x-6">
                    <img src="images/iCenter.png" alt="Logo" class="h-20 w-auto border-2 border-white rounded-lg shadow-lg mr-4" />
                    <div class="flex flex-col space-y-1">
                        <span class="font-semibold text-lg"><?php echo date('l, F j, Y'); ?></span>
                        <div class="text-white/80 text-sm">
                            <i class="ri-time-line mr-2"></i>
                            <span id="currentTime"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-8">
                <div class="flex items-center space-x-4">
                    <div class="flex flex-col items-center group cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-black font-medium shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                            <i class="ri-user-line text-lg"></i>
                        </div>
                        <span class="text-white text-xs font-semibold mt-2 group-hover:text-blue-300 transition-colors duration-300">ADMIN</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto py-8">
        <div class="flex justify-between items-center mb-8">
            <a href="admin.php" class="inline-flex items-center text-sm text-white bg-black border-2 border-black rounded-lg px-6 py-3 transition-all duration-300 font-medium shadow-md hover:shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
            <a href="archived_products.php" class="inline-block px-6 py-3 bg-yellow-500 text-white rounded-full hover:bg-yellow-600 transition-all duration-300 font-medium shadow-md hover:shadow-lg">
                <i class="fas fa-archive mr-2"></i>
                Archived Products
            </a>
        </div>

        <div class="flex justify-center mb-6 flex-wrap gap-3" id="categoryButtons">
            <?php
            // Add "All Products" button
            $activeClass = ($activeCategory === 'all products') ? 'bg-black text-white shadow-lg' : 'bg-white text-gray-800 hover:bg-gray-100 border border-gray-300 shadow-md hover:shadow-lg';
            $urlParams = [];
            if (isset($_GET['search']) && $_GET['search'] !== '') {
                $urlParams['search'] = $_GET['search'];
            }
            if ($currentModelBrand !== 'all models') {
                $urlParams['model_brand'] = $currentModelBrand;
            }
            $urlParams['page'] = 1;
            $queryString = http_build_query($urlParams);
            echo '<a href="?' . $queryString . '" class="px-6 py-3 rounded-full border cursor-pointer transition-all duration-300 font-medium ' . $activeClass . '" onclick="handleCategoryClick(event)">All Products</a>';

            foreach ($categories as $cat) {
                $catLower = strtolower($cat);
                $activeClass = ($catLower === $activeCategory) ? 'bg-black text-white shadow-lg' : 'bg-white text-gray-800 hover:bg-gray-100 border border-gray-300 shadow-md hover:shadow-lg';
                $urlParams = [];
                $urlParams['category'] = $catLower;
                if (isset($_GET['search']) && $_GET['search'] !== '') {
                    $urlParams['search'] = $_GET['search'];
                }
                if ($currentModelBrand !== 'all models') {
                    $urlParams['model_brand'] = $currentModelBrand;
                }
                $urlParams['page'] = 1;
                $queryString = http_build_query($urlParams);
                echo '<a href="?' . $queryString . '" class="px-6 py-3 rounded-full border cursor-pointer transition-all duration-300 font-medium ' . $activeClass . '" onclick="handleCategoryClick(event)">' . htmlspecialchars($cat) . '</a>';
            }
            ?>
        </div>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-6 text-center">
                Error loading products: <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (empty($products)): ?>
            <div class="text-center text-gray-500 text-xl">No products found.</div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8" id="productGrid">
            <?php foreach ($products as $product): ?>
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
                    </div>
                    <button class="trash-can-button" data-product-id="<?= htmlspecialchars($product['product_id']) ?>" title="Delete Product" style="position: absolute; top: 15px; right: 15px; background: rgba(220, 53, 69, 0.1); border: none; cursor: pointer; color: #dc3545; font-size: 20px; padding: 8px; border-radius: 50%; transition: all 0.3s ease;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

<script>
  function handleCategoryClick(event) {
    event.preventDefault();
    const url = event.currentTarget.href;
    const categoryButtons = document.getElementById('categoryButtons');
    const productGrid = document.getElementById('productGrid');
    
    // Add fade out transition
    categoryButtons.style.transition = 'opacity 0.3s ease';
    categoryButtons.style.opacity = '0.5';
    productGrid.style.transition = 'opacity 0.3s ease';
    productGrid.style.opacity = '0.5';
    
    // Update active button state
    document.querySelectorAll('#categoryButtons a').forEach(btn => {
      btn.classList.remove('bg-black', 'text-white', 'shadow-lg');
      btn.classList.add('bg-white', 'text-gray-800', 'hover:bg-gray-100', 'border', 'border-gray-300', 'shadow-md', 'hover:shadow-lg');
    });
    event.currentTarget.classList.remove('bg-white', 'text-gray-800', 'hover:bg-gray-100', 'border', 'border-gray-300', 'shadow-md', 'hover:shadow-lg');
    event.currentTarget.classList.add('bg-black', 'text-white', 'shadow-lg');
    
    // Parse URL parameters
    const urlParams = new URLSearchParams(url.split('?')[1] || '');
    const category = urlParams.get('category') || '';
    const search = urlParams.get('search') || '';
    const modelBrand = urlParams.get('model_brand') || '';
    const sort = urlParams.get('sort') || '';
    
    // Make AJAX request
    fetch(`get_products.php?category=${encodeURIComponent(category)}&search=${encodeURIComponent(search)}&model_brand=${encodeURIComponent(modelBrand)}&sort=${encodeURIComponent(sort)}&ajax=1`)
      .then(response => response.text())
      .then(html => {
        // Update product grid
        productGrid.innerHTML = html;
        
        // Restore opacity
        categoryButtons.style.opacity = '1';
        productGrid.style.opacity = '1';
        
        // Update URL without reloading
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.pushState({}, '', newUrl);
        
        // Reinitialize event listeners for new content
        initializeEventListeners();
      })
      .catch(error => {
        console.error('Error:', error);
        // Restore opacity on error
        categoryButtons.style.opacity = '1';
        productGrid.style.opacity = '1';
        alert('Error loading products. Please try again.');
      });
  }

  // Current time display
  function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
      hour12: true, 
      hour: '2-digit', 
      minute: '2-digit', 
      second: '2-digit' 
    });
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
      timeElement.textContent = timeString;
    }
  }

  // Update time every second
  setInterval(updateTime, 1000);
  updateTime(); // Initial call
  </script>

<!-- Modal HTML -->
<div id="archiveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
    <div class="text-center">
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
        <i class="fas fa-archive text-red-600 text-xl"></i>
      </div>
      <h3 class="text-xl font-bold mb-4 text-gray-900">Confirm Archive</h3>
      <p class="mb-8 text-gray-600">Are you sure you want to archive this product? This action can be undone later.</p>
      <div class="flex justify-center space-x-4">
        <button id="cancelArchive" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-all duration-300 font-medium">Cancel</button>
        <button id="confirmArchive" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg">Archive</button>
      </div>
    </div>
  </div>
</div>

<!-- Sale Confirmation Modal -->
<div id="saleSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
      <i class="fas fa-check text-green-600 text-xl"></i>
    </div>
    <h3 class="text-xl font-bold mb-4 text-green-700">Sale Successful</h3>
    <p class="mb-8 text-gray-600">The product stock was successfully deducted and the sale was recorded.</p>
    <button id="closeSaleSuccessModal" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg">OK</button>
  </div>
</div>

<!-- Sale Confirmation Prompt Modal -->
<div id="saleConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
      <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
    </div>
    <h3 class="text-xl font-bold mb-4 text-gray-900">Confirm Sale</h3>
    <p class="mb-8 text-gray-600">Are you sure you want to mark this quantity as sold?</p>
    <div class="flex justify-center space-x-4">
      <button id="cancelSaleConfirm" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-all duration-300 font-medium">Cancel</button>
      <button id="confirmSaleConfirm" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg">Confirm</button>
    </div>
  </div>
</div>

<script>
// Function to initialize all event listeners
function initializeEventListeners() {
  // Archive modal elements and functions
  const modal = document.getElementById('archiveModal');
  const confirmBtn = document.getElementById('confirmArchive');
  const cancelBtn = document.getElementById('cancelArchive');
  let currentProductId = null;

  function openModal(productId) {
    currentProductId = productId;
    modal.classList.remove('hidden');
  }

  function closeModal() {
    currentProductId = null;
    modal.classList.add('hidden');
  }

  cancelBtn.addEventListener('click', closeModal);

  confirmBtn.addEventListener('click', function () {
    if (!currentProductId) return;
    fetch('archive_products.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: currentProductId })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show local confirmation message instead of alert
        const confirmationMessage = document.createElement('div');
        confirmationMessage.textContent = data.message;
        confirmationMessage.style.position = 'fixed';
        confirmationMessage.style.top = '20px';
        confirmationMessage.style.right = '20px';
        confirmationMessage.style.backgroundColor = '#4caf50';
        confirmationMessage.style.color = 'white';
        confirmationMessage.style.padding = '10px 20px';
        confirmationMessage.style.borderRadius = '5px';
        confirmationMessage.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
        confirmationMessage.style.zIndex = '1000';
        document.body.appendChild(confirmationMessage);
        setTimeout(() => {
          confirmationMessage.remove();
          location.reload();
        }, 2000);
      } else {
        // Show alert only if there is an error message
        alert(data.message);
      }
      closeModal();
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while archiving the product.');
      closeModal();
    });
  });

  document.querySelectorAll('.trash-can-button').forEach(button => {
    button.addEventListener('click', function () {
      const productId = this.dataset.productId;
      openModal(productId);
    });
    
    // Add hover effects
    button.addEventListener('mouseenter', function() {
      this.style.background = 'rgba(220, 53, 69, 0.2)';
      this.style.transform = 'scale(1.1)';
    });
    
    button.addEventListener('mouseleave', function() {
      this.style.background = 'rgba(220, 53, 69, 0.1)';
      this.style.transform = 'scale(1)';
    });
  });

  // Sale confirmation modal elements and functions
  const saleSuccessModal = document.getElementById('saleSuccessModal');
  const closeSaleSuccessModalBtn = document.getElementById('closeSaleSuccessModal');

  closeSaleSuccessModalBtn.addEventListener('click', function () {
    saleSuccessModal.classList.add('hidden');
  });

  // Sale confirmation prompt modal elements and functions
  const saleConfirmModal = document.getElementById('saleConfirmModal');
  const cancelSaleConfirmBtn = document.getElementById('cancelSaleConfirm');
  const confirmSaleConfirmBtn = document.getElementById('confirmSaleConfirm');

  let pendingSale = null;

  cancelSaleConfirmBtn.addEventListener('click', function () {
    saleConfirmModal.classList.add('hidden');
    pendingSale = null;
  });

  confirmSaleConfirmBtn.addEventListener('click', function () {
    if (!pendingSale) return;
    const { productId, quantity } = pendingSale;
    saleConfirmModal.classList.add('hidden');
    pendingSale = null;

    fetch('sell_product.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId, quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        saleSuccessModal.classList.remove('hidden');
        closeSaleSuccessModalBtn.onclick = () => {
          saleSuccessModal.classList.add('hidden');
          location.reload();
        };
      } else {
        alert(data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while processing the sale.');
    });
  });

  // Handle Sold button click
  document.querySelectorAll('.sold-button').forEach(button => {
    button.addEventListener('click', function () {
      const productId = this.dataset.productId;
      const productCard = this.closest('.product-card');
      if (!productCard) {
        alert('Product card not found.');
        return;
      }
      const input = productCard.querySelector('.sold-quantity-input');
      if (!input) {
        alert('Quantity input not found.');
        return;
      }
      const quantity = parseInt(input.value);
      if (isNaN(quantity) || quantity <= 0) {
        alert('Please enter a valid quantity.');
        return;
      }
      pendingSale = { productId, quantity };
      saleConfirmModal.classList.remove('hidden');
    });
  });
}

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
  initializeEventListeners();
});
</script>

</body>
</html>
