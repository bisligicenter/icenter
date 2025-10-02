<?php
require_once 'db.php';
require_once 'functions.php';

$conn = getDBConnection();

$category = isset($_GET['category']) ? $_GET['category'] : '';

if ($category === '') {
    // If no category specified, show message or redirect
    echo "<h2>No category specified.</h2>";
    exit;
}

// Fetch products for the specified category
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE c.category_name = ? AND (p.archived IS NULL OR p.archived = 0)
        ORDER BY p.product";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Products in Category: <?php echo htmlspecialchars($category); ?></title>
  <link rel="stylesheet" href="kiosk.css" />
</head>
<body>
  <header>
    <h1>Category: <?php echo htmlspecialchars($category); ?></h1>
    <nav>
      <a href="kiosk.php">Home</a>
    </nav>
  </header>
  <main>
    <?php if (count($products) === 0): ?>
      <p>No products found in this category.</p>
    <?php else: ?>
      <div id="productGrid" style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php foreach ($products as $product): ?>
          <?php
            $imagePath = 'images/default.png';
            if (!empty($product['image1'])) {
              $images = explode(',', $product['image1']);
              $imagePath = trim($images[0]);
            }
            $price = !empty($product['selling_price']) ? '₱' . number_format($product['selling_price'], 2) : 'Price not available';
            $productName = !empty($product['product']) ? $product['product'] : 'Unnamed Product';
            $productId = !empty($product['product_id']) ? $product['product_id'] : 0;
          ?>
          <div class="card" style="width: 300px; text-align: center;">
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($productName); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px;">
            <h3><?php echo htmlspecialchars($productName); ?></h3>
            <p><?php echo $price; ?></p>
            <a href="reservations.php?product_id=<?php echo $productId; ?>" class="reserve-btn">Reserve</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
