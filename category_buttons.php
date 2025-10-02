<?php
$categories = getAllCategories($conn);
echo '<div id="categoryButtons">';
// Add "All Products" button first
echo '<button class="category-btn active" data-category="all">All Products</button>';

// Handle inconsistent cache format which may return a structured array
if (isset($categories['categories']) && is_array($categories['categories'])) {
    $categories = $categories['categories'];
}

foreach ($categories as $category) {
  if (is_string($category) && !empty($category)) {
    echo '<button class="category-btn" data-category="'.htmlspecialchars($category).'">'.htmlspecialchars($category).'</button>';
  }
}
echo '</div>';
?>