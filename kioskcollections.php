<?php
// Enhanced CSS styles for better UX
echo '<style>
/* Enhanced Collection Cards with Better UX */
.collection-card {
    text-decoration: none;
    color: inherit;
    position: relative;
    overflow: hidden;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
    opacity: 1;
}

.collection-card.loading {
    opacity: 0.7;
    transform: translateY(20px);
}

.collection-card.loaded {
    animation: cardSlideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes cardSlideIn {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.collection-title {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #000000 !important;
    position: relative;
}

.collection-title::after {
    content: "";
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #007bff, #0056b3);
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 1px;
}

.collection-title:hover {
    color: #007bff !important;
    transform: translateX(3px);
}

.collection-title:hover::after {
    width: 100%;
}

.collection-title i {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    color: #000000 !important;
    opacity: 0.7;
}

.collection-title:hover i {
    transform: translateX(8px) scale(1.1);
    color: #007bff !important;
    opacity: 1;
}

/* Enhanced Image Loading */
.collection-image {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.collection-image.loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.collection-image.loaded {
    animation: imageFadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes imageFadeIn {
    0% {
        opacity: 0;
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

/* Enhanced Price Display */
.collection-price {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.collection-price::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.6s ease;
}

.collection-card:hover .collection-price::before {
    left: 100%;
}

/* Enhanced Description */
.collection-description {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.collection-card:hover .collection-description {
    color: #333 !important;
    transform: translateY(-2px);
}



/* Enhanced Focus States for Accessibility */
.collection-card:focus {
    outline: 3px solid #007bff;
    outline-offset: 2px;
    transform: translateY(-8px);
}

/* Loading State */
.collection-card.loading .collection-image {
    filter: blur(2px);
}

/* Success State Animation */
.collection-card.success {
    animation: successPulse 0.6s ease-in-out;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Enhanced Hover Effects */
.collection-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
}

.collection-card:hover .collection-image {
    transform: scale(1.05);
    filter: brightness(1.1);
}

/* Responsive Enhancements */
@media (max-width: 768px) {
    .collection-card:hover {
        transform: translateY(-8px) scale(1.01);
    }
    
    .collection-title:hover {
        transform: translateX(2px);
    }
    
    .collection-title:hover i {
        transform: translateX(6px) scale(1.05);
    }
}
</style>';

$items = [
  'IPHONE' => [
    'image' => 'images/Iphonegif.gif',
    'description' => 'Apple iPhones with cutting-edge technology.',
    'price' => '₱20,000 - ₱80,000',
    'category' => 'Smartphones'
  ],
  'IPAD' => [
    'image' => 'images/Ipadgif.gif',
    'description' => 'Powerful tablets for work and play.',
    'price' => '₱15,000 - ₱60,000',
    'category' => 'Tablets'
  ],
  'AIRPODS' => [
    'image' => 'images/airpodsgif.gif',
    'description' => 'High-quality wireless earbuds for immersive sound.',
    'price' => '₱3,000 - ₱15,000',
    'category' => 'Audio'
  ],
  'ACCESSORIES' => [
    'image' => 'images/Accessoriesgif.gif',
    'description' => 'Essential accessories to complement your devices.',
    'price' => '₱100 - ₱5,000',
    'category' => 'Accessories'
  ],
  'ANDROID' => [
    'image' => 'images/Androidgif.gif',
    'description' => 'Latest Android smartphones and gadgets.',
    'price' => '₱5,000 - ₱50,000',
    'category' => 'Smartphones'
  ],
  'LAPTOP' => [
    'image' => 'images/Laptopgif.gif',
    'description' => 'Portable laptops for productivity on the go.',
    'price' => '₱15,000 - ₱90,000',
    'category' => 'Computers'
  ],
  'COMPUTERS' => [
    'image' => 'images/pc.png',
    'description' => 'Complete PC setups for home and office.',
    'price' => '₱10,000 - ₱100,000',
    'category' => 'Computers'
  ],
  'PRINTER' => [
    'image' => 'images/Printergif.gif',
    'description' => 'Reliable printers for all your printing needs.',
    'price' => '₱3,000 - ₱25,000',
    'category' => 'Peripherals'
  ],
];

foreach ($items as $item => $details):
  $linkMap = [
    'IPHONE' => 'iphone.php',
    'IPAD' => 'ipad.php',
    'AIRPODS' => 'airpods.php',
    'COMPUTERS' => 'pcset.php',
    'LAPTOP' => 'laptop.php',
    'PRINTER' => 'printer.php',
    'ANDROID' => 'android.php',
    'ACCESSORIES' => 'accessories.php',
  ];
  $link = isset($linkMap[$item]) ? $linkMap[$item] : '#';
  $serverImagePath = __DIR__ . '/' . $details['image'];
  if (!file_exists($serverImagePath)):
    $details['image'] = 'uploads/default_thumbnail.jpg';
    $serverImagePath = __DIR__ . '/' . $details['image'];
    if (!file_exists($serverImagePath)):
      $details['image'] = 'uploads/default.jpg';
      $serverImagePath = __DIR__ . '/' . $details['image'];
      if (!file_exists($serverImagePath)):
        $details['image'] = '';
      endif;
    endif;
  endif;
  
  $cardClass = 'collection-card';
?>
<a href="<?php echo htmlspecialchars($link); ?>" 
   class="<?php echo $cardClass; ?>" 
   data-category="<?php echo htmlspecialchars($details['category']); ?>"
   aria-label="View <?php echo htmlspecialchars($item); ?> collection">
  <?php if ($details['image'] !== ''): ?>
    <img src="<?php echo htmlspecialchars($details['image']); ?>" 
         alt="<?php echo htmlspecialchars($item); ?> collection" 
         class="collection-image loading"
         loading="lazy"
         onload="this.classList.remove('loading'); this.classList.add('loaded');"
         onerror="this.src='images/default-collection.png'; this.classList.remove('loading'); this.classList.add('loaded');">
  <?php endif; ?>
  <div class="collection-content">
    <h3 class="collection-title">
      <?php echo htmlspecialchars($item); ?>
      <i class="fas fa-arrow-right" aria-hidden="true"></i>
    </h3>
    <p class="collection-description"><?php echo htmlspecialchars($details['description']); ?></p>
          <div class="collection-price">
        <i class="fas fa-tag" aria-hidden="true"></i>
        <span><?php echo htmlspecialchars($details['price']); ?></span>
      </div>
  </div>
</a>
<?php endforeach; ?>