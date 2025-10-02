<?php 
session_start();
require_once 'db.php'; 

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Cache categories for 5 minutes to reduce database queries
$cache_file = 'cache/categories_cache.json';
$cache_duration = 300; // 5 minutes

function getCachedCategories($conn, $cache_file, $cache_duration) {
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        $cached_data = json_decode(file_get_contents($cache_file), true);
        if ($cached_data && isset($cached_data['categories'])) {
            return $cached_data['categories'];
        }
    }
    
    // Create cache directory if it doesn't exist
    $cache_dir = dirname($cache_file);
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    try {
        $stmt = $conn->query("SELECT DISTINCT product FROM products ORDER BY product ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Cache the results
        file_put_contents($cache_file, json_encode([
            'categories' => $categories,
            'timestamp' => time()
        ]));
        
        return $categories;
    } catch (PDOException $e) {
        return [];
    }
}

// Optimized query to get all data in one go
function getOptimizedData($conn, $search = '', $category = '', $limit = 12, $offset = 0) {
    $whereClauses = ["(archived IS NULL OR archived = 0)"];
    $params = [];
    
    if ($search !== '') {
        $whereClauses[] = "(model LIKE :search OR brand LIKE :search OR storage LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($category !== '') {
        $whereClauses[] = "TRIM(LOWER(product)) = :category";
        $params[':category'] = strtolower($category);
    }
    
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
    
    // Get total count and products in one optimized query
    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM products $whereSQL ORDER BY product_id ASC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count using FOUND_ROWS()
    $totalStmt = $conn->query("SELECT FOUND_ROWS()");
    $totalProducts = $totalStmt->fetchColumn();
    
    return ['products' => $products, 'total' => $totalProducts];
}

// Fetch pending user count for notification
$stmt = $conn->query("SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$pending_count = $row ? (int)$row['pending_count'] : 0;

// Fetch pending reservation count for notification
$reservationStmt = $conn->query("SELECT COUNT(*) AS pending_reservation_count FROM reservations WHERE status = 'pending' AND (archived IS NULL OR archived = 0)");
$reservationRow = $reservationStmt->fetch(PDO::FETCH_ASSOC);
$pending_reservation_count = $reservationRow ? (int)$reservationRow['pending_reservation_count'] : 0;

// Get cached categories
$categories = getCachedCategories($conn, $cache_file, $cache_duration);

// Count products per category
$categoryProductCounts = [];
foreach ($categories as $cat) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE TRIM(LOWER(product)) = :category AND (archived IS NULL OR archived = 0)");
    $stmt->execute([':category' => strtolower($cat)]);
    $categoryProductCounts[$cat] = $stmt->fetchColumn();
}
?>
<!DOCTYPE html
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bislig iCenter - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="shortcut icon" type="image/png" href="images/iCenter.png">
    <link rel="apple-touch-icon" href="images/iCenter.png">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: { primary: "#1a1a1a", secondary: "#404040" },
            borderRadius: {
              none: "0px",
              sm: "4px",
              DEFAULT: "8px",
              md: "12px",
              lg: "16px",
              xl: "20px",
              "2xl": "24px",
              "3xl": "32px",
              full: "9999px",
              button: "8px",
            },
          },
        },
      };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <link rel="stylesheet" href="sidebar_enhancements_v2.css">
    <script src="sidebar_enhancements_v2.js"></script>
    <style>
      :where([class^="ri-"])::before { content: "\f3c2"; }
      body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
      }
      
      /* Enhanced Card Hover Effects */
      .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Enhanced Product Card */
      .product-card {
        min-height: 280px;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        background: white;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        border: 2px solid #e5e7eb;
      }
      .product-card p,
      .product-card a {
        margin-bottom: 0.25rem;
      }
      
      /* Enhanced Sidebar */
      #sidebar {
        background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
        box-shadow: 12px 0 32px 0 rgba(0,0,0,0.18), 0 1.5px 0 0 rgba(255,255,255,0.08) inset;
        border-right: 2px solid rgba(255,255,255,0.12);
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        backdrop-filter: blur(12px) saturate(1.2);
        -webkit-backdrop-filter: blur(12px) saturate(1.2);
        /* glassmorphism */
        background-color: rgba(26,26,26,0.85);
        transform: translateX(0);
        will-change: transform, width;
        position: relative;
        overflow: hidden;
      }
      #sidebar::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        box-shadow: 0 8px 32px 0 rgba(0,0,0,0.18);
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      #sidebar.collapsed {
        width: 80px;
        min-width: 80px;
        max-width: 80px;
        box-shadow: 8px 0 32px 0 rgba(0,0,0,0.18);
        background: linear-gradient(180deg, #1a1a1a 0%, #23232d 50%, #1a1a1a 100%);
        background-color: rgba(26,26,26,0.92);
        border-right: none;
        transform: translateX(0);
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      
      /* Add sliding animation for sidebar */
      #sidebar.sliding {
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      
      /* Ensure main content slides smoothly */
      #mainContent {
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        will-change: margin-left;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
        position: relative;
      }
      
      /* Prevent any gaps during transitions */
      #mainContent::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        z-index: -1;
        pointer-events: none;
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      
      /* Ensure perfect alignment between sidebar and main content */
      #sidebar, #mainContent {
        transform: translateZ(0);
        backface-visibility: hidden;
        perspective: 1000px;
      }
      
      /* Prevent any white spaces during transitions */
      .flex {
        overflow-x: hidden;
      }
      
      /* Prevent white flash during sidebar transitions */
      #mainContent.sliding {
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      
      /* Smooth header transitions */
      header {
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        will-change: transform, margin-left;
      }
      
      /* Ensure smooth background transition */
      body {
        overflow-x: hidden;
      }
      #sidebar.collapsed::after {
        display: none;
      }
      #sidebar .nav-icon {
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      
      #sidebar a:hover .nav-icon {
        box-shadow: 0 4px 16px rgba(0,0,0,0.20), 0 0 20px rgba(255,255,255,0.1);
        transform: scale(1.05);
      }

      #sidebar.collapsed .nav-item {
        justify-content: center;
        padding: 1.2rem 0.5rem;
        min-height: 3.2rem;
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border: none;
        box-shadow: none;
        background: transparent;
      }
      #sidebar.collapsed .nav-icon {
        margin-right: 0;
        width: 2.5rem;
        height: 2.5rem;
        border: none;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.07);
        border-radius: 12px;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        transform: scale(1);
      }
      
      /* Different colors for each icon in collapsed mode */
      #sidebar.collapsed .nav-item:nth-child(1) .nav-icon {
        background: rgba(59, 130, 246, 0.2);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      #sidebar.collapsed .nav-item:nth-child(2) .nav-icon {
        background: rgba(34, 197, 94, 0.2);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      #sidebar.collapsed .nav-item:nth-child(3) .nav-icon {
        background: rgba(245, 158, 11, 0.2);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      #sidebar.collapsed .nav-item:nth-child(4) .nav-icon {
        background: rgba(168, 85, 247, 0.2);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }

      #sidebar.collapsed .sidebar-text {
        opacity: 0;
        transform: translateX(-24px);
        pointer-events: none;
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        width: 0;
        overflow: hidden;
        white-space: nowrap;
      }
      #sidebar.collapsed .nav-item,
      #sidebar.collapsed a {
        position: relative;
      }
      
      #sidebar.collapsed .nav-item:hover .nav-icon {
        box-shadow: 0 4px 16px rgba(0,0,0,0.20);
        background: rgba(255,255,255,0.15);
        transform: scale(1.1);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }

      #sidebar a {
        border-radius: 16px;
        margin: 8px 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1.5px 0 0 rgba(255,255,255,0.04) inset;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        white-space: nowrap;
      }
      
      #sidebar a:hover {
        background: rgba(255,255,255,0.08);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
        border: 1px solid rgba(255,255,255,0.2);
      }
      #sidebar.collapsed a {
        box-shadow: none;
        border: none;
      }


      #sidebar a.active {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.32), rgba(99, 102, 241, 0.22));
        color: #fff;
        box-shadow: 0 4px 18px rgba(99,102,241,0.18);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      
      /* Enhanced Header */
      header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
      }
      
      /* Enhanced Stats Cards */
      .bg-\[\#1a1a1a\] {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
      }
      .bg-\[\#1a1a1a\]:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      }
      
      /* Enhanced Button Styles */
      .rounded-button {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
      }
      
      /* Ensure edit button has proper black styling */
      .product-card a[href*="edit_products.php"] {
        background: linear-gradient(135deg, #374151 0%, #111827 100%) !important;
        color: white !important;
        border: none !important;
        box-shadow: none !important;
      }
      
      .product-card a[href*="edit_products.php"]:hover {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: none !important;
      }
      .rounded-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
      }
      .rounded-button:hover::before {
        left: 100%;
      }
      .rounded-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      }
      
      /* Enhanced Status Badge Styles */
      .status-badge {
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
        margin: 8px 0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }
      .status-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
      }
      .status-badge:hover::before {
        transform: translateX(100%);
      }
      .status-badge.in-stock {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.1));
        color: #22c55e;
        font-size: 1.0rem;
        font-weight: 700;
        border: 2px solid rgba(34, 197, 94, 0.3);
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
      }
      .status-badge.low-stock {
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(234, 179, 8, 0.1));
        color: #eab308;
        border: 2px solid rgba(234, 179, 8, 0.3);
        box-shadow: 0 4px 15px rgba(234, 179, 8, 0.2);
      }
      .status-badge.out-of-stock {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.1));
        color: #ef4444;
        font-size: 1.0rem;
        font-weight: 700;
        border: 2px solid rgba(239, 68, 68, 0.3);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
      }
      
      /* Enhanced Pagination Styles */
      #paginationControls a {
        border-radius: 12px;
        position: relative;
        overflow: hidden;
      }
      #paginationControls a::before {
        display: none;
      }
      #paginationControls a:hover::before {
        display: none;
      }
      #paginationControls a:hover {
        transform: none;
        box-shadow: none;
      }
      
      /* Enhanced Category Buttons */
      #categoryButtons a {
        border: 2px solid transparent;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        -webkit-tap-highlight-color: transparent;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }
      #categoryButtons a::before {
        display: none;
      }
      #categoryButtons a:hover {
        border-color:rgb(2, 1, 17);
      }
      #categoryButtons a.bg-blue-600 {
        background: linear-gradient(135deg,rgb(1, 1, 15) 0%,rgb(1, 1, 21) 100%) !important;
        border-color:rgb(2, 1, 16) !important;
        color: #fff !important;
      }
      
      /* Enhanced Form Elements */
      input[type="number"]::-webkit-inner-spin-button,
      input[type="number"]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
      }
      
      /* Enhanced Custom Checkbox */
      .custom-checkbox {
      position: relative;
      display: inline-block;
        width: 22px;
        height: 22px;
      cursor: pointer;
      }
      .custom-checkbox input {
      opacity: 0;
      width: 0;
      height: 0;
      }
      .checkmark {
      position: absolute;
      top: 0;
      left: 0;
        width: 22px;
        height: 22px;
      background-color: #fff;
      border: 2px solid #d1d5db;
        border-radius: 6px;
        transition: all 0.3s ease;
      }
      .custom-checkbox input:checked ~ .checkmark {
        background: linear-gradient(135deg,rgb(12, 12, 12),rgb(12, 12, 12));
      border-color:rgb(12, 12, 13);
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
      }
      .checkmark:after {
      content: "";
      position: absolute;
      display: none;
      }
      .custom-checkbox input:checked ~ .checkmark:after {
      display: block;
      }
      .custom-checkbox .checkmark:after {
        left: 8px;
        top: 4px;
      width: 6px;
      height: 10px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
      }
      
      /* Enhanced Custom Switch */
      .custom-switch {
      position: relative;
      display: inline-block;
        width: 48px;
        height: 26px;
      }
      .custom-switch input {
      opacity: 0;
      width: 0;
      height: 0;
      }
      .switch-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #e5e7eb;
      transition: .4s;
      border-radius: 34px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .switch-slider:before {
      position: absolute;
      content: "";
        height: 20px;
        width: 20px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      }
      .custom-switch input:checked + .switch-slider {
        background: linear-gradient(135deg,rgb(0, 0, 0),rgb(10, 10, 11));
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .custom-switch input:checked + .switch-slider:before {
        transform: translateX(22px);
      }
      
      /* Enhanced Custom Range */
      .custom-range {
      width: 100%;
        height: 8px;
        border-radius: 6px;
        background: linear-gradient(90deg, #e5e7eb 0%, #d1d5db 100%);
      outline: none;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .custom-range::-webkit-slider-thumb {
      appearance: none;
        width: 22px;
        height: 22px;
      border-radius: 50%;
        background: linear-gradient(135deg,rgb(9, 9, 9),rgb(8, 8, 8));
      cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        border: 2px solid white;
      }
      .custom-range::-moz-range-thumb {
        width: 22px;
        height: 22px;
      border-radius: 50%;
        background: linear-gradient(135deg,rgb(13, 13, 13),rgb(12, 12, 13));
      cursor: pointer;
      border: none;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
      }
      
      /* Enhanced Dropdown */
      .dropdown-content {
      display: none;
      position: absolute;
      background-color: white;
        min-width: 180px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      z-index: 1;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
      }
      .dropdown-content a {
      color: black;
        padding: 14px 18px;
      text-decoration: none;
      display: block;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 2px;
      }
      .dropdown-content a:hover {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        transform: translateX(4px);
      }
      .show {
      display: block;
        animation: fadeInUp 0.3s ease;
      }
      
      /* Enhanced Product Card Images */
      .product-card img {
        border: none !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Enhanced Animations */
      @keyframes fadeIn {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }

      @keyframes slideInRight {
        from {
          opacity: 0;
          transform: translateX(30px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }

      @keyframes slideInLeft {
        from {
          opacity: 0;
          transform: translateX(-30px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }

      @keyframes pulse {
        0%, 100% {
          opacity: 1;
        }
        50% {
          opacity: 0.5;
        }
      }

      @keyframes scaleIn {
        from {
          opacity: 0;
          transform: scale(0.8);
        }
        to {
          opacity: 1;
          transform: scale(1);
        }
      }

      @keyframes bounceIn {
        0% {
          opacity: 0;
          transform: scale(0.3);
        }
        50% {
          opacity: 1;
          transform: scale(1.05);
        }
        70% {
          transform: scale(0.9);
        }
        100% {
          opacity: 1;
          transform: scale(1);
        }
      }

      .animate-fadeIn {
        animation: fadeIn 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }

      .animate-slideInRight {
        animation: slideInRight 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }

      .animate-slideInLeft {
        animation: slideInLeft 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }

      .animate-scaleIn {
        animation: scaleIn 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }

      .animate-bounceIn {
        animation: bounceIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }

      .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
      }
      
      /* Enhanced Scrollbar */
      ::-webkit-scrollbar {
        width: 8px;
      }
      ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
      }
      ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg,rgba(128, 128, 130, 0.93),rgb(127, 127, 130));
        border-radius: 4px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg,rgb(135, 135, 137),rgb(129, 129, 129));
      }
      
      /* Enhanced Focus States */
      *:focus {
        outline: 2px solidrgb(2, 1, 20);
        outline-offset: 2px;
      }
      
      /* Enhanced Text Selection */
      ::selection {
        background: rgba(79, 70, 229, 0.2);
        color: #1a1a1a;
      }
      
      #categoryButtons a:hover:not(.bg-blue-600) {
        background: #000 !important;
        color: #fff !important;
      }
      
      #categoryButtons a.bg-blue-600:hover {
        background: linear-gradient(135deg,rgb(1, 1, 15) 0%,rgb(1, 1, 21) 100%) !important;
        color: #fff !important;
      }
      
      #categoryButtons a:active {
        /* No animation effects */
      }
      
      #categoryButtons a.bg-blue-600:active {
        background: linear-gradient(135deg,rgb(1, 1, 15) 0%,rgb(1, 1, 21) 100%) !important;
        color: #fff !important;
      }
      
      /* Enhanced Sidebar Toggle Buttons */
      #sidebarToggle, #sidebarToggleOuter {
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.18);
        border: 2px solid rgba(255,255,255,0.18);
        background: rgba(26,26,26,0.92);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      #sidebarToggle:hover, #sidebarToggleOuter:hover {
        background: rgba(99,102,241,0.18);
        border-color: rgba(99,102,241,0.32);
        transform: scale(1.12);
        box-shadow: 0 6px 24px rgba(99,102,241,0.18);
      }
      
      /* Toggle button visibility management */
      #sidebarToggleOuter {
        display: block;
      }
      
      #sidebarToggle {
        display: block;
      }
      
      /* Hide outer toggle when sidebar is expanded */
      .sidebar-open #sidebarToggleOuter {
        display: none;
      }
      
      /* Hide inner toggle when sidebar is collapsed */
      .sidebar-closed #sidebarToggle {
        display: none;
      }
      
      /* Prevent white flash during sidebar transitions */
      #mainContent.sliding {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Ensure smooth background transition */
      body {
        overflow-x: hidden;
      }
      .sidebar-tooltip {
        position: absolute;
        left: 50%;
        top: 100%;
        transform: translate(-50%, 0) scale(0.95);
        transform-origin: top;
        background: rgba(30, 41, 59, 0.95);
        color: #fff;
        padding: 6px 16px;
        border-radius: 8px;
        white-space: nowrap;
        font-size: 0.95rem;
        font-weight: 500;
        opacity: 0;
        pointer-events: none;
        margin-top: 12px;
        z-index: 100;
        box-shadow: 0 4px 16px rgba(0,0,0,0.18);
        transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      .sidebar-tooltip::after {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-bottom-color: rgba(30, 41, 59, 0.95);
      }
      #sidebar.collapsed .nav-item:hover .sidebar-tooltip {
        opacity: 1;
        transform: translate(-50%, 0) scale(1);
        transition: all 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      }
      #sidebar:not(.collapsed) .sidebar-tooltip {
        display: none;
      }

      /* Hide scrollbar but keep functionality */
      .custom-scrollbar::-webkit-scrollbar {
        display: none; /* for Chrome, Safari, and Opera */
      }
      .custom-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* for Firefox */
      }

      /* ===== RESPONSIVE DESIGN ===== */
      
      /* Mobile First Approach */
      
      /* Extra Small devices (phones, 576px and down) */
      @media (max-width: 575.98px) {
        /* Sidebar adjustments for mobile */
        #sidebar {
          transform: translateX(-100%);
          z-index: 1000;
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          transition: transform 0.3s ease-in-out;
        }
        
        .sidebar-overlay.active {
          opacity: 1;
          visibility: visible;
        }
        
        /* Sidebar content adjustments for mobile */
        #sidebar .p-6 {
          padding: 1rem !important;
        }
        
        #sidebar .nav-item {
          padding: 0.75rem 1rem !important;
          margin: 0.25rem 0 !important;
        }
        
        #sidebar .nav-icon {
          width: 2rem !important;
          height: 2rem !important;
          font-size: 1.1rem !important;
        }
        
        #sidebar .sidebar-text {
          font-size: 0.95rem !important;
        }
        
        /* Header adjustments */
        header {
          padding: 0.5rem 0.5rem 0.5rem 0.5rem !important;
        }
        header .flex {
          flex-direction: column !important;
          align-items: center !important;
          gap: 0.5rem !important;
        }


        /* Date and time centered below logo */
        header .text-sm.text-white {
          text-align: center !important;
          margin-top: 0.5rem !important;
        }
        /* Date and time text consistent sizing */
        header .font-semibold.text-lg {
          font-size: 1.3rem !important;
          line-height: 1.4 !important;
        }
        header .text-white\/80.text-sm {
          font-size: 1.1rem !important;
          line-height: 1.4 !important;
        }
        .group .text-xs {
          font-size: 0.8rem !important;
        }
        /* Category bar responsive */
        #categoryButtons {
          flex-wrap: nowrap !important;
          overflow-x: auto !important;
          -webkit-overflow-scrolling: touch;
          gap: 0.5rem !important;
          padding-bottom: 0.5rem;
          margin-bottom: 1rem;
          scrollbar-width: thin;
        }
        #categoryButtons::-webkit-scrollbar {
          height: 4px;
        }
        #categoryButtons a {
          font-size: 0.85rem !important;
          padding: 0.5rem 1rem !important;
          min-width: max-content;
          border-radius: 10px !important;
        }
        
        /* Product grid adjustments */
        #productGrid {
          grid-template-columns: 1fr !important;
          gap: 1rem !important;
        }
        
        .product-card {
          flex-direction: column !important;
          min-height: auto !important;
        }
        
        .product-card > div:first-child {
          width: 100% !important;
          height: 140px !important;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #f3f4f6;
        }
        
        .product-card > div:last-child {
          width: 100% !important;
          padding: 1rem !important;
        }
        
        .product-card img {
          width: auto !important;
          max-width: 100% !important;
          height: 100% !important;
          max-height: 120px !important;
          object-fit: contain !important;
          margin: 0 auto;
          display: block;
        }
        
        /* Main content padding */
        .p-8 {
          padding: 1rem !important;
        }
        
        /* Modal adjustments */
        .modal-content {
          margin: 1rem !important;
          max-width: calc(100% - 2rem) !important;
        }
        
        /* Status badges */
        .status-badge {
          font-size: 0.75rem;
          padding: 0.5rem 1rem;
        }
        
        /* Product card text */
        .product-card h3 {
          font-size: 1.25rem !important;
        }
        
        .product-card p {
          font-size: 0.875rem !important;
        }
        
        /* Sold button layout */
        .product-card .flex {
          flex-direction: column;
          gap: 0.5rem;
        }
        
        .sold-quantity-input {
          width: 100% !important;
          max-width: 120px;
        }
        /* Admin icon responsive */
        header .w-12.h-12 {
          width: 2.5rem !important;
          height: 2.5rem !important;
        }
        header .w-12.h-12 i {
          font-size: 1rem !important;
        }
        header .text-xs.font-semibold {
          font-size: 0.7rem !important;
        }
        /* Category bar responsive */
        #categoryButtons {
          flex-wrap: nowrap !important;
          overflow-x: auto !important;
          -webkit-overflow-scrolling: touch;
          gap: 0.5rem !important;
          padding-bottom: 0.5rem;
          margin-bottom: 1rem;
          scrollbar-width: thin;
        }
        #categoryButtons::-webkit-scrollbar {
          height: 4px;
        }
        #categoryButtons a {
          font-size: 0.85rem !important;
          padding: 0.5rem 1rem !important;
          min-width: max-content;
          border-radius: 10px !important;
        }
      }
      
      /* Small devices (landscape phones, 576px and up) */
      @media (min-width: 576px) and (max-width: 767.98px) {
        /* Sidebar content adjustments */
        #sidebar .nav-item {
          padding: 0.875rem 1.25rem !important;
          margin: 0.375rem 0 !important;
        }
        
        #sidebar .nav-icon {
          width: 2.25rem !important;
          height: 2.25rem !important;
          font-size: 1.2rem !important;
        }
        
        #sidebar .sidebar-text {
          font-size: 1rem !important;
        }
        
        /* Header adjustments */
        header {
          padding: 0.75rem 1rem !important;
        }
        header .flex {
          flex-direction: column !important;
          align-items: flex-start !important;
          gap: 0.75rem !important;
        }

        /* Date and time text consistent sizing */
        header .font-semibold.text-lg {
          font-size: 1.3rem !important;
          line-height: 1.4 !important;
        }
        header .text-white\/80.text-sm {
          font-size: 1.1rem !important;
          line-height: 1.4 !important;
        }
        /* Category bar responsive */
        #categoryButtons {
          flex-wrap: wrap !important;
          gap: 0.5rem !important;
          margin-bottom: 1rem;
        }
        #categoryButtons a {
          font-size: 0.95rem !important;
          padding: 0.5rem 1.2rem !important;
          border-radius: 12px !important;
        }
        
        #productGrid {
          grid-template-columns: repeat(2, 1fr) !important;
          gap: 1rem !important;
        }
        
        .product-card {
          flex-direction: column !important;
        }
        
        .product-card > div:first-child {
          width: 100% !important;
          height: 180px !important;
        }
        
        .product-card > div:last-child {
          width: 100% !important;
          padding: 1rem !important;
        }
        
        .p-8 {
          padding: 1.5rem !important;
        }
        
        /* Admin icon responsive */
        header .w-12.h-12 {
          width: 3rem !important;
          height: 3rem !important;
        }
        header .w-12.h-12 i {
          font-size: 1.1rem !important;
        }
        header .text-xs.font-semibold {
          font-size: 0.75rem !important;
        }
      }

      /* Combined Mobile & Small Tablet Styles (up to 767.98px) */
      @media (max-width: 767.98px) {
        #sidebar {
          width: 280px; /* Default mobile width */
          min-width: 280px;
          max-width: 280px;
          transform: translateX(-100%);
          z-index: 1000;
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          transition: transform 0.3s ease-in-out;
        }

        @media (min-width: 576px) {
          #sidebar {
            width: 300px; /* Width for small tablets */
            min-width: 300px;
            max-width: 300px;
          }
        }

        #sidebar.mobile-open {
          transform: translateX(0);
        }

        .sidebar-overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.5);
          z-index: 999;
          opacity: 0;
          visibility: hidden;
          transition: all 0.3s ease-in-out;
        }

        #mainContent {
          margin-left: 0 !important;
          width: 100%;
        }

        #sidebarToggleOuter { display: block !important; top: 1rem; left: 1rem; z-index: 1001; }
        #sidebarToggle { display: none !important; }
        body.sidebar-open { overflow: hidden; }
      }
      
      /* Medium devices (tablets, 768px and up) */
      @media (min-width: 768px) and (max-width: 991.98px) {
        #sidebar {
          width: 240px;
          min-width: 240px;
          max-width: 240px;
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          transform: translateX(0);
        }
        
        #sidebar.collapsed {
          width: 70px;
          min-width: 70px;
          max-width: 70px;
        }
        
        #mainContent {
          margin-left: 240px !important;
        }
        
        #mainContent.ml-20 {
          margin-left: 70px !important;
        }
        
        /* Sidebar content adjustments */
        #sidebar .nav-item {
          padding: 1rem 1.5rem !important;
          margin: 0.5rem 0 !important;
        }
        
        #sidebar .nav-icon {
          width: 2.5rem !important;
          height: 2.5rem !important;
          font-size: 1.3rem !important;
        }
        
        #sidebar .sidebar-text {
          font-size: 1.05rem !important;
        }
        
        /* Show both toggle buttons on tablet */
        #sidebarToggleOuter {
          display: none !important;
        }
        
        #sidebarToggle {
          display: block !important;
        }
        
        /* Header adjustments */
        header {
          padding: 1rem 2rem !important;
        }
        /* Date and time text consistent sizing */
        header .font-semibold.text-lg {
          font-size: 1.3rem !important;
          line-height: 1.4 !important;
        }
        header .text-white\/80.text-sm {
          font-size: 1.1rem !important;
          line-height: 1.4 !important;
        }
        /* Category bar responsive */
        #categoryButtons {
          flex-wrap: wrap !important;
          gap: 0.75rem !important;
        }
        #categoryButtons a {
          font-size: 1rem !important;
          padding: 0.6rem 1.3rem !important;
        }
        
        #productGrid {
          grid-template-columns: repeat(2, 1fr) !important;
          gap: 1.5rem !important;
        }
        
        .product-card {
          flex-direction: column !important;
        }
        
        .product-card > div:first-child {
          width: 100% !important;
          height: 200px !important;
        }
        
        .product-card > div:last-child {
          width: 100% !important;
          padding: 1.5rem !important;
        }
        
        .p-8 {
          padding: 2rem !important;
        }
        
        /* Admin icon responsive */
        header .w-12.h-12 {
          width: 3rem !important;
          height: 3rem !important;
        }
        header .w-12.h-12 i {
          font-size: 1.1rem !important;
        }
        header .text-xs.font-semibold {
          font-size: 0.75rem !important;
        }
      }
      
      /* Large devices (desktops, 992px and up) */
      @media (min-width: 992px) and (max-width: 1199.98px) {
        #sidebar {
          width: 256px;
          min-width: 256px;
          max-width: 256px;
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          transform: translateX(0);
        }
        
        #sidebar.collapsed {
          width: 80px;
          min-width: 80px;
          max-width: 80px;
        }
        
        #mainContent {
          margin-left: 256px !important;
        }
        
        #mainContent.ml-20 {
          margin-left: 80px !important;
        }
        
        /* Header adjustments */
        header {
          padding: 1.5rem 3rem !important;
        }
        /* Category bar responsive */
        #categoryButtons {
          flex-wrap: wrap !important;
          gap: 1rem !important;
        }
        #categoryButtons a {
          font-size: 1.05rem !important;
          padding: 0.7rem 1.5rem !important;
        }
        
        #productGrid {
          grid-template-columns: repeat(2, 1fr) !important;
        }
        
        .product-card {
          flex-direction: row !important;
        }
        
        .product-card > div:first-child {
          width: 40% !important;
          height: auto !important;
        }
        
        .product-card > div:last-child {
          width: 60% !important;
        }
      }
      
      /* Extra large devices (large desktops, 1200px and up) */
      @media (min-width: 1200px) {
        #sidebar {
          width: 256px;
          min-width: 256px;
          max-width: 256px;
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          transform: translateX(0);
        }
        
        #sidebar.collapsed {
          width: 80px;
          min-width: 80px;
          max-width: 80px;
        }
        
        #mainContent {
          margin-left: 256px !important;
        }
        
        #mainContent.ml-20 {
          margin-left: 80px !important;
        }
        
        /* Header adjustments */
        header {
          padding: 1.5rem 3rem !important;
        }
        /* iCenter logo consistent size */

        /* Category bar responsive */
        #categoryButtons {
          flex-wrap: wrap !important;
          gap: 1rem !important;
        }
        #categoryButtons a {
          font-size: 1.05rem !important;
          padding: 0.7rem 1.5rem !important;
        }
        
        #productGrid {
          grid-template-columns: repeat(2, 1fr) !important;
        }
        
        .product-card {
          flex-direction: row !important;
        }
        
        .product-card > div:first-child {
          width: 40% !important;
          height: auto !important;
        }
        
        .product-card > div:last-child {
          width: 60% !important;
        }
        

      }
      .protrude-logo {
  box-shadow: 0 12px 32px rgba(0,0,0,0.25), 0 0 0 8px rgba(99,102,241,0.12);
  transform: scale(1.18) translateY(-8px);
  transition: transform 0.3s cubic-bezier(.25,.46,.45,.94), box-shadow 0.3s;
  background: linear-gradient(135deg, #fff 60%, #e2e8f0 100%);
  z-index: 10;
  position: relative;
}
.protrude-logo:hover {
  transform: scale(1.22) translateY(-12px);
  box-shadow: 0 20px 48px rgba(0,0,0,0.32), 0 0 0 12px rgba(99,102,241,0.18);
}
    </style>
  </head>
      <body class="min-h-screen">
        <div class="flex">
      <!-- Enhanced Sidebar -->
          <div
            id="sidebar"
            class="w-64 h-screen fixed shadow-md flex flex-col z-10 transition-all duration-300"
        style="background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);"
          >
        <div class="p-6 flex items-center justify-center relative">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-300"></div>
          
          <!-- Sidebar Toggle Button -->
          <button id="sidebarToggle" class="absolute top-4 right-4 z-50 text-white bg-[#1a1a1a] border-2 border-white p-1 rounded-md shadow-md focus:outline-none transition-all duration-300">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>
        </div>
        
        <!-- Admin Profile Section -->
        <div class="px-6 py-4 border-b border-white/10">
          <div class="flex flex-col items-center cursor-pointer">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-black font-medium shadow-lg transition-all duration-300 mb-2">
              <i class="ri-user-line text-lg m-0 p-0 leading-none"></i>
            </div>
            <span class="text-white text-xs font-semibold transition-colors duration-300">ADMIN</span>
          </div>
        </div>
        
            <div class="flex-1 overflow-y-auto custom-scrollbar">
              <nav class="px-4 py-2">
            <div class="space-y-2">
                  <!-- Notification Icon for Pending Approvals in Sidebar -->
                  <a href="approve_users.php" class="nav-item flex items-center px-4 py-4 text-base font-medium rounded-xl text-gray-300 group relative" data-title="Pending Account Approvals">
                    <div class="nav-icon w-8 h-8 flex items-center justify-center mr-4 bg-gradient-to-br from-yellow-400/20 to-red-500/20 rounded-lg">
                      <i class="ri-notification-3-line text-xl text-yellow-400"></i>
                    </div>
                    <span class="sidebar-text">Approvals</span>
                    <?php if ($pending_count > 0): ?>
                      <span style="position: absolute; right: 12px; background: #ff4c4c; color: #fff; border-radius: 50%; padding: 2px 7px; font-size: 0.85em; font-weight: bold; border: 2px solid #fff; min-width: 22px; text-align: center; line-height: 1;">
                        <?php echo $pending_count; ?>
                      </span>
                    <?php endif; ?>
                    <span class="sidebar-tooltip">Pending Account Approvals</span>
                  </a>
                  <?php
// Sidebar navigation items array
$sidebarItems = [
  [
    'href' => 'add_products.php',
    'icon' => '<i class="ri-add-line text-xl text-blue-500"></i>',
    'text' => 'Add Product',
    'tooltip' => 'Add Product',
    'iconBg' => 'bg-gradient-to-br from-blue-500/20 to-purple-500/20',
  ],
  [
    'href' => 'inventory_stocks.php',
    'icon' => '<i class="ri-store-2-line text-xl text-orange-500"></i>',
    'text' => 'Inventory Stocks',
    'tooltip' => 'Inventory Stocks',
    'iconBg' => 'bg-gradient-to-br from-orange-500/20 to-red-500/20',
  ],
  [
    'href' => 'view_sales.php',
    'icon' => '<i class="ri-money-dollar-circle-line text-xl text-green-500"></i>',
    'text' => 'Sales',
    'tooltip' => 'View Sales',
    'iconBg' => 'bg-gradient-to-br from-green-500/20 to-emerald-500/20',
  ],
  [
    'href' => 'reserved.php',
    'icon' => '<i class="ri-calendar-check-line text-xl text-purple-500"></i>',
    'text' => 'Reservations',
    'tooltip' => 'Reservations',
    'iconBg' => 'bg-gradient-to-br from-purple-500/20 to-pink-500/20',
    'badge' => $pending_reservation_count,
  ],

  [
    'href' => 'promotional_videos.php',
    'icon' => '<i class="ri-video-camera-line text-xl text-pink-500"></i>',
    'text' => 'Promotional Videos',
    'tooltip' => 'Manage Promotional Videos',
    'iconBg' => 'bg-gradient-to-br from-pink-500/20 to-yellow-500/20',
  ],

  [
    'href' => 'reset_password.php',
    'icon' => '<i class="ri-lock-password-line text-xl text-green-500"></i>',
    'text' => 'Change Password',
    'tooltip' => 'Change your password',
    'iconBg' => 'bg-gradient-to-br from-green-500/20 to-blue-500/20',
  ],

];
?>
                  <?php foreach ($sidebarItems as $item): ?>
      <a
        href="<?= $item['href'] ?>"
        class="nav-item flex items-center px-4 py-4 text-base font-medium rounded-xl text-gray-300 group"
        data-title="<?= htmlspecialchars($item['text']) ?>"
      >
        <div class="nav-icon w-8 h-8 flex items-center justify-center mr-4 <?= $item['iconBg'] ?> rounded-lg">
          <?= $item['icon'] ?>
        </div>
        <span class="sidebar-text"><?= htmlspecialchars($item['text']) ?></span>
        <?php if (isset($item['badge']) && $item['badge'] > 0): ?>
          <span style="position: absolute; right: 12px; background: #ff4c4c; color: #fff; border-radius: 50%; padding: 2px 7px; font-size: 0.85em; font-weight: bold; border: 2px solid #fff; min-width: 22px; text-align: center; line-height: 1;">
            <?= $item['badge'] ?>
          </span>
        <?php endif; ?>
        <span class="sidebar-tooltip"><?= htmlspecialchars($item['tooltip']) ?></span>
      </a>
    <?php endforeach; ?>
              </nav>
            </div>
        <div class="p-4 border-t border-white/10">
              <button id="logoutButton" type="button"
            class="nav-item flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 rounded-xl hover:from-red-600 hover:to-red-700 whitespace-nowrap transition-all duration-300 group shadow-lg hover:shadow-xl"
            data-title="Log Out">
            <div class="nav-icon w-6 h-6 flex items-center justify-center mr-3 bg-white/20 rounded-lg group-hover:bg-white/30 transition-all duration-300 overflow-hidden">
              <i class="ri-logout-box-line text-lg"></i>
                </div>
            <span class="sidebar-text">Log Out</span>
              </button>

        </div>
      </div>
      
      <!-- Sidebar Overlay for Mobile -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      
                <!-- Sidebar Toggle Button (Always Visible) -->
      <button id="sidebarToggleOuter" class="fixed top-4 left-4 z-50 text-white bg-[#1a1a1a] border-2 border-white p-2 rounded-md shadow-md focus:outline-none transition-all duration-300 hover:scale-110 hover:shadow-lg">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>
      </button>
      
          <!-- Main content -->
          <div id="mainContent" class="flex-1 ml-64 transition-all duration-300">
        <!-- Enhanced Header -->
        <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
          <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6 space-x-2 lg:space-x-4">
            <div class="flex items-center space-x-3 lg:space-x-6">
              <img src="images/iCenter.png" alt="Logo" class="h-24 w-auto border-1 border-white rounded-2xl shadow-2xl protrude-logo p-3 bg-white" style="margin-left:8px; margin-top:8px;" />
              <div class="text-xs lg:text-sm text-white flex flex-col space-y-1">
                <span class="font-semibold text-sm lg:text-lg" id="currentDate"></span>
                <div class="text-white/80 text-xs lg:text-sm">
                  <i class="ri-time-line mr-1 lg:mr-2"></i>
                  <span id="currentTime"></span>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons Group -->
            <div class="flex items-center space-x-2 sm:space-x-4">
              <!-- Email Icon - Mail Customers Button -->
              <a href="email_customers.php" class="flex flex-col items-center justify-center text-center transition-all duration-300 group text-white w-20">
                <div class="bg-blue-500/20 hover:bg-blue-500/30 border border-blue-400/30 rounded-xl p-3 transition-all duration-300">
                  <i class="ri-mail-line text-blue-500 text-2xl"></i>
                </div>
                <span class="text-xs font-semibold mt-2 group-hover:text-blue-400 transition-colors duration-300">Email</span>
              </a>
              
              <!-- Admin Chat Icon - Live Chat Button -->
              <button id="admin-chat-toggle-header" class="flex flex-col items-center justify-center text-center transition-all duration-300 group text-white w-20">
                <div class="relative bg-green-500/20 hover:bg-green-500/30 border border-green-400/30 rounded-xl p-3 transition-all duration-300">
                  <i class="fas fa-headset text-green-600 text-xl"></i>
                  <span class="admin-chat-badge-header" id="admin-chat-badge-header" style="display: none; position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; border: 2px solid white;">0</span>
                </div>
                <span class="text-xs font-semibold mt-2 group-hover:text-green-400 transition-colors duration-300">Chat</span>
              </button>
              
              <!-- Archive Icon - Archive Products Button -->
              <a href="archived_products.php" class="flex flex-col items-center justify-center text-center transition-all duration-300 group text-white w-20">
                <div class="bg-red-500/20 hover:bg-red-500/30 border border-red-400/30 rounded-xl p-3 transition-all duration-300">
                  <i class="ri-archive-line text-red-500 text-2xl"></i>
                </div>
                <span class="text-xs font-semibold mt-2 group-hover:text-red-400 transition-colors duration-300">Archived</span>
              </a>
            </div>
          </div>
         </header>

        <!-- Dashboard content -->
        <div class="p-8">
          
          
          <!-- Enhanced Product inventory section -->
          <div class="mb-10">
            <?php
// Helper function for category button
function renderCategoryButton($cat, $currentCategory, $currentModelBrand, $search) {
                $catLower = strtolower($cat);
                $activeClass = ($catLower === $currentCategory)
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 text-gray-800 hover:bg-black hover:text-white';
                $urlParams = [];
  if ($catLower !== '' && $catLower !== 'all products') {
                $urlParams['category'] = $catLower;
  }
  if ($search !== '') {
    $urlParams['search'] = $search;
                }
                if ($currentModelBrand !== 'all models') {
                    $urlParams['model_brand'] = $currentModelBrand;
                }
                $urlParams['page'] = 1;
                $queryString = http_build_query($urlParams);
                echo '<a href="?' . $queryString . '" class="px-4 py-2 rounded-xl border border-transparent cursor-pointer ' . $activeClass . '" onclick="handleCategoryClick(event)">' . htmlspecialchars($cat) . '</a>';
}
?>
            <div class="flex flex-wrap gap-3 mb-8 justify-center" id="categoryButtons">
            <?php
            $currentCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
            $currentModelBrand = isset($_GET['model_brand']) ? strtolower(trim($_GET['model_brand'])) : 'all models';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            // Add "All Products" button
            renderCategoryButton('All Products', '', $currentModelBrand, $search);
             foreach ($categories as $cat) {
    if (!empty($categoryProductCounts[$cat])) { // Only show if count > 0
        renderCategoryButton($cat, $currentCategory, $currentModelBrand, $search);
    }
}
            ?>
          </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" id="productGrid">
              <?php
              // Helper function to get stock status
              function getStockStatus($stockQuantity) {
                  $stockQuantity = (int)$stockQuantity;
                  if ($stockQuantity == 0) {
                      return ['status' => 'out-of-stock', 'text' => 'Out of Stock'];
                  } elseif ($stockQuantity > 0 && $stockQuantity <= 5) {
                      return ['status' => 'low-stock', 'text' => 'Low Stock'];
                  } else {
                      return ['status' => 'in-stock', 'text' => 'In Stock'];
                  }
              }

              // Enhanced helper function to render product card
              function renderProductCard($product) {
                  $stockInfo = getStockStatus($product['stock_quantity']);
                  $categoryLower = strtolower($product['brand']);
                  $mainImage = $product['image1'] ?? '';
                  $storageDisplay = !empty($product['storage']) ? htmlspecialchars($product['storage']) : 'Not Available';
                  
                  echo '<div class="product-card text-center border-2 border-gray-200 rounded-2xl relative" data-category="' . htmlspecialchars($categoryLower) . '">';
                  
                  // Enhanced image container - now on the left side
                  if (!empty($mainImage)) {
                      echo '<div class="relative w-2/5 overflow-hidden bg-gray-100">';
                      echo '<img src="' . htmlspecialchars($mainImage) . '" alt="Main Product Image" class="w-full h-full object-cover" />';
                      echo '<div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>';
                      echo '</div>';
                  }
                  
                  echo '<div class="flex-1 p-6 flex flex-col justify-between">';
                  echo '<div class="mt-8">';
                  echo '<h3 class="font-bold text-2xl mb-3 text-gray-800 group-hover:text-blue-600 transition-colors duration-300">' . htmlspecialchars($product['brand'] . ' ' . $product['model']) . '</h3>';
                  echo '<p class="font-semibold text-blue-600 text-xl mb-4">' . htmlspecialchars($product['brand']) . '</p>';
                  
                  echo '<div class="mt-4 mb-4 text-gray-700 text-base flex justify-center gap-x-6">';
                  echo '<span><span class="font-medium">Model: </span>' . htmlspecialchars($product['model']) . '</span>';
                  echo '<span><span class="font-medium">Storage: </span>' . $storageDisplay . '</span>';
                  echo '</div>';
                  
                  echo '<div class="grid grid-cols-2 gap-3 text-gray-600 mb-6">';
                  echo '<div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">';
                  echo '<span class="font-medium">Purchase:</span>';
                  echo '<span class="text-gray-800">₱' . number_format($product['purchase_price'], 2) . '</span>';
                  echo '</div>';
                  echo '<div class="flex justify-between items-center py-2 px-3 bg-red-50 rounded-lg border border-red-200">';
                  echo '<span class="font-medium text-red-700">Selling:</span>';
                  echo '<span class="text-red-700 font-semibold">₱' . number_format($product['selling_price'], 2) . '</span>';
                  echo '</div>';
                  echo '</div>';
                  echo '<p class="status-badge ' . $stockInfo['status'] . ' w-full block text-center px-6 py-3 rounded-xl font-semibold">' . $stockInfo['text'] . ' (' . $product['stock_quantity'] . ')</p>';
                  // Add Edit button below the status badge
                  $editUrl = 'edit_products.php?product_id=' . urlencode($product['product_id']);
                  echo '<a href="' . $editUrl . '" class="mt-4 w-full inline-block px-6 py-3 bg-gradient-to-r from-gray-800 to-black text-white font-semibold rounded-xl hover:from-gray-900 hover:to-gray-800 transition-all duration-300 text-center">';
                  echo '<i class="ri-edit-2-line mr-2"></i>Edit</a>';
                  // Insert Sold button and quantity input (copied from view_products.php)
                  echo '<div class="flex flex-col items-center space-y-3 mt-2 w-full justify-center">';
                  echo '<div class="flex items-center space-x-3">';
                  echo '<input type="number" min="1" value="1" max="' . (int)$product['stock_quantity'] . '" class="sold-quantity-input border border-gray-300 rounded-lg px-3 py-2 w-24 text-center focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" data-product-id="' . htmlspecialchars($product['product_id']) . '" />';
                  echo '<button class="sold-button bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg" data-product-id="' . htmlspecialchars($product['product_id']) . '"><i class="fas fa-shopping-cart mr-2"></i>Sold</button>';
                  echo '</div>';
                  echo '</div>';
                  // Add delete button
                  echo '<button class="trash-can-button" data-product-id="' . htmlspecialchars($product['product_id']) . '" title="Delete Product" style="position: absolute; top: 15px; right: 15px; background: rgba(220, 53, 69, 0.1); border: none; cursor: pointer; color: #dc3545; font-size: 20px; padding:  8px; border-radius: 50%; transition: all 0.3s ease;">';
                  echo '<i class="fas fa-box"></i>';
                  echo '</button>';
                  echo '</div>';
                  echo '</div>';
                  echo '</div>';
              }

              // Helper function to render pagination
              function renderPagination($totalPages, $page, $search, $category) {
                  if ($totalPages <= 1) return;
                  
                  echo '<div class="col-span-full flex justify-center mt-6 space-x-2" id="paginationControls">';
                  $urlParams = [];
                  if ($search !== '') {
                      $urlParams['search'] = $search;
                  }
                  if ($category !== '') {
                      $urlParams['category'] = $category;
                  }
                  
                  // Previous page link
                  if ($page > 1) {
                      $urlParams['page'] = $page - 1;
                      echo '<a href="?' . http_build_query($urlParams) . '" class="px-4 py-2 rounded-full border border-black bg-white text-black font-semibold transition-colors duration-200 hover:bg-gray-200">Previous</a>';
                  } else {
                      echo '<span class="px-4 py-2 rounded-full border border-gray-300 bg-gray-100 text-gray-400 font-semibold cursor-not-allowed">Previous</span>';
                  }
                  
                  // Page number links
                  for ($i = 1; $i <= $totalPages; $i++) {
                      if ($i == $page) {
                          echo '<span class="px-4 py-2 rounded-full bg-black text-white font-semibold border border-black">' . $i . '</span>';
              } else {
                          $urlParams['page'] = $i;
                          echo '<a href="?' . http_build_query($urlParams) . '" class="px-4 py-2 rounded-full border border-black bg-white text-black font-semibold transition-colors duration-200 hover:bg-gray-200">' . $i . '</a>';
                      }
                  }
                  
                  // Next page link
                  if ($page < $totalPages) {
                      $urlParams['page'] = $page + 1;
                      echo '<a href="?' . http_build_query($urlParams) . '" class="px-4 py-2 rounded-full border border-black bg-white text-black font-semibold transition-colors duration-200 hover:bg-gray-200">Next</a>';
                  } else {
                      echo '<span class="px-4 py-2 rounded-full border border-gray-300 bg-gray-100 text-gray-400 font-semibold cursor-not-allowed">Next</span>';
                  }
                  echo '</div>';
              }

              // Helper function to render product grid and pagination
              function renderProductGridAndPagination($products, $totalProducts, $limit, $page, $search, $category) {
                  if (count($products) === 0) {
                      echo '<div class="col-span-full text-center py-8">
                              <p class="text-gray-500">No products found in the database.</p>
                            </div>';
                  } else {
                      foreach ($products as $product) {
                          renderProductCard($product);
                      }
                  }
                  renderPagination(ceil($totalProducts / $limit), $page, $search, $category);
              }

              $limit = 12;
                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                try {
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $category = isset($_GET['category']) ? trim($_GET['category']) : '';
                $ajax = isset($_GET['ajax']) ? true : false;

                // Use optimized data fetching
                $data = getOptimizedData($conn, $search, $category, $limit, $offset);
                $products = $data['products'];
                $totalProducts = $data['total'];

                    if ($ajax) {
                        // If AJAX request, return only product grid and pagination HTML
                        ob_start();
                    renderProductGridAndPagination($products, $totalProducts, $limit, $page, $search, $category);
                        $output = ob_get_clean();
                        echo $output;
                        exit;
                    }

                renderProductGridAndPagination($products, $totalProducts, $limit, $page, $search, $category);
                  
                } catch (PDOException $e) {
                    echo '<div class="col-span-full text-center py-8">
                            <p class="text-red-500">Error loading products: ' . htmlspecialchars($e->getMessage()) . '</p>
                          </div>';
                }
                ?>
          </div>
          </div>
        </div>
          </div>
  </div>
  
  <!-- Admin Chat Interface -->
  <?php include 'admin_chat_interface.php'; ?>
  

  <!-- Enhanced Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center hidden z-[9999]">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
        <div class="text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="ri-logout-box-line text-2xl text-white"></i>
          </div>
          <h2 class="text-2xl font-bold text-gray-800 mb-4">Confirm Logout</h2>
          <p class="text-gray-600 mb-8">Are you sure you want to log out of your account?</p>
          <div class="flex space-x-4">
            <button id="cancelLogout" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-300 font-semibold focus:outline-none focus:ring-2 focus:ring-gray-300">Cancel</button>
            <a href="logout.php" id="confirmLogout" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 font-semibold shadow-lg hover:shadow-xl text-center inline-block focus:outline-none focus:ring-2 focus:ring-red-300">Logout</a>
          </div>
        </div>
      </div>
    </div>

          <script>
      // Enhanced logout modal functionality
      document.addEventListener('DOMContentLoaded', function() {
        const logoutButton = document.getElementById('logoutButton');
        const logoutModal = document.getElementById('logoutModal');
        const cancelLogout = document.getElementById('cancelLogout');
        const confirmLogout = document.getElementById('confirmLogout');

        if (logoutButton && logoutModal && cancelLogout) {
          logoutButton.addEventListener('click', () => {
            logoutModal.classList.remove('hidden');
            setTimeout(() => {
              const modalContent = document.getElementById('modalContent');
              if (modalContent) {
                modalContent.style.transform = 'scale(1)';
                modalContent.style.opacity = '1';
              }
            }, 10);
          });

          function hideModal() {
            const modalContent = document.getElementById('modalContent');
            if (modalContent) {
              modalContent.style.transform = 'scale(0.95)';
              modalContent.style.opacity = '0';
            }
            setTimeout(() => {
              logoutModal.classList.add('hidden');
            }, 300);
          }

          cancelLogout.addEventListener('click', hideModal);

          logoutModal.addEventListener('click', (e) => {
            if (e.target === logoutModal) {
              hideModal();
            }
          });

          // Add keyboard support (ESC key to close modal)
          document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !logoutModal.classList.contains('hidden')) {
              hideModal();
            }
          });

          // Ensure confirm logout button works properly
          if (confirmLogout) {
            confirmLogout.addEventListener('click', (e) => {
              // The link will handle the redirect automatically
              // No need to prevent default or add extra logic
            });
          }
        }
      });

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
        // Date
        const dateString = now.toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        const dateElement = document.getElementById('currentDate');
        if (dateElement) {
          dateElement.textContent = dateString;
        }
      }

      // Update time and date every second
      setInterval(updateTime, 1000);
      updateTime(); // Initial call

          // Sidebar toggle functionality
          const sidebarToggle = document.getElementById("sidebarToggle");
          const sidebarToggleOuter = document.getElementById("sidebarToggleOuter");
          const sidebar = document.getElementById("sidebar");
          const mainContent = document.getElementById("mainContent");
          const sidebarOverlay = document.getElementById("sidebarOverlay");
          const body = document.body;

          function toggleSidebar() {
            // Check if we're on mobile (screen width <= 767px)
            const isMobile = window.innerWidth <= 767;
            
            if (isMobile) {
              // Mobile behavior - slide in/out with overlay
              sidebar.classList.toggle("mobile-open");
              if (sidebarOverlay) {
                sidebarOverlay.classList.toggle("active");
              }
              body.classList.toggle("sidebar-open");
            } else {
              // Desktop behavior - collapse/expand
              // Immediately add sliding class for instant response
              sidebar.classList.add("sliding");
              mainContent.classList.add("sliding");
              
              // Get header element and add sliding class immediately
              const header = document.querySelector('header');
              if (header) {
                header.classList.add("sliding");
              }
              
              // Toggle collapsed state immediately
              sidebar.classList.toggle("collapsed");
              const collapsed = sidebar.classList.contains("collapsed");
              
              // Apply changes immediately without delay
              if (collapsed) {
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-20");
                body.classList.remove("sidebar-open");
                body.classList.add("sidebar-closed");
              } else {
                mainContent.classList.remove("ml-20");
                mainContent.classList.add("ml-64");
                body.classList.remove("sidebar-closed");
                body.classList.add("sidebar-open");
              }
              
                        // Remove sliding class after animation completes
          setTimeout(() => {
            sidebar.classList.remove("sliding");
            mainContent.classList.remove("sliding");
            if (header) {
              header.classList.remove("sliding");
            }
          }, 500);
            }
          }

          sidebarToggle.addEventListener("click", toggleSidebar);
          sidebarToggleOuter.addEventListener("click", toggleSidebar);
          
          // Close sidebar when overlay is clicked (mobile only)
          if (sidebarOverlay) {
            sidebarOverlay.addEventListener("click", () => {
              if (window.innerWidth <= 767) {
                sidebar.classList.remove("mobile-open");
                sidebarOverlay.classList.remove("active");
                body.classList.remove("sidebar-open");
              }
            });
          }
          
          // Close sidebar when clicking outside on mobile
          document.addEventListener("click", (e) => {
            if (window.innerWidth <= 767 && 
                !sidebar.contains(e.target) && 
                !sidebarToggleOuter.contains(e.target) &&
                sidebar.classList.contains("mobile-open")) {
              sidebar.classList.remove("mobile-open");
              if (sidebarOverlay) {
                sidebarOverlay.classList.remove("active");
              }
              body.classList.remove("sidebar-open");
            }
          });
          
          // Handle window resize
          window.addEventListener("resize", () => {
            const isMobile = window.innerWidth <= 767;
            
            if (isMobile) {
              // Reset to mobile state
              sidebar.classList.remove("collapsed", "sliding");
              mainContent.classList.remove("ml-64", "ml-20", "sliding");
              body.classList.remove("sidebar-closed", "sidebar-open");
              
              // Close mobile sidebar if open
              sidebar.classList.remove("mobile-open");
              if (sidebarOverlay) {
                sidebarOverlay.classList.remove("active");
              }
            } else {
              // Reset to desktop state
              sidebar.classList.remove("mobile-open");
              if (sidebarOverlay) {
                sidebarOverlay.classList.remove("active");
              }
              body.classList.remove("sidebar-open");
              
              // Initialize desktop state (collapsed by default)
              if (!sidebar.classList.contains("collapsed")) {
                sidebar.classList.add("collapsed");
                mainContent.classList.add("ml-20");
                body.classList.add("sidebar-closed");
              }
            }
          });
          
          // Initialize sidebar state based on screen size
          const isMobile = window.innerWidth <= 767;
          if (!isMobile) {
            // Desktop: collapsed by default
            sidebar.classList.add("collapsed");
            mainContent.classList.add("ml-20");
            body.classList.add("sidebar-closed");
          } else {
            // Mobile: hidden by default
            sidebar.classList.remove("collapsed", "mobile-open");
            mainContent.classList.remove("ml-64", "ml-20");
            body.classList.remove("sidebar-closed", "sidebar-open");
          }

          // Sidebar hover functionality (desktop only)
          let sidebarHoverTimeout;
          sidebar.addEventListener('mouseenter', function() {
            if (window.innerWidth > 767) { // Desktop only
              clearTimeout(sidebarHoverTimeout);
              if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('ml-20');
                mainContent.classList.add('ml-64');
                body.classList.remove('sidebar-closed');
                body.classList.add('sidebar-open');
              }
            }
          });
          sidebar.addEventListener('mouseleave', function() {
            if (window.innerWidth > 767) { // Desktop only
                        // Add a slight delay to avoid flicker if user moves quickly
          sidebarHoverTimeout = setTimeout(function() {
            if (!sidebar.classList.contains('collapsed')) {
              sidebar.classList.add('collapsed');
              mainContent.classList.remove('ml-64');
              mainContent.classList.add('ml-20');
              body.classList.remove('sidebar-open');
              body.classList.add('sidebar-closed');
            }
          }, 200);
            }
          });

      // Category click handler
      function handleCategoryClick(event) {
        event.preventDefault();
        const url = event.currentTarget.href;
        const categoryButtons = document.getElementById('categoryButtons');
        const productGridContainer = document.getElementById('productGrid');

        categoryButtons.style.transition = 'opacity 0.3s ease';
        categoryButtons.style.opacity = '0.5';
        productGridContainer.style.transition = 'opacity 0.3s ease';
        productGridContainer.style.opacity = '0.5';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(response => response.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newProductGrid = doc.getElementById('productGrid');
            if (newProductGrid && productGridContainer) {
              productGridContainer.innerHTML = newProductGrid.innerHTML;
              productGridContainer.style.opacity = '1';
            }

            const oldPagination = document.querySelector('#paginationControls');
            const newPagination = doc.querySelector('#paginationControls');
            if (oldPagination && newPagination) {
              oldPagination.innerHTML = newPagination.innerHTML;
            }

            const newCategoryButtons = doc.getElementById('categoryButtons');
            if (newCategoryButtons && categoryButtons) {
              categoryButtons.innerHTML = newCategoryButtons.innerHTML;
            }

            attachPaginationHandlers();
            attachCategoryButtonHandlers();
            categoryButtons.style.opacity = '1';
          })
          .catch(err => {
            console.error('Error loading category:', err);
            categoryButtons.style.opacity = '1';
            productGridContainer.style.opacity = '1';
          });
      }

      // Search handler
      function handleSearch(event) {
        event.preventDefault();
        const searchInput = document.getElementById('searchInput');
        const searchTerm = searchInput.value.trim();
        const urlParams = new URLSearchParams(window.location.search);

        if (searchTerm) {
          urlParams.set('search', searchTerm);
        } else {
          urlParams.delete('search');
        }
        urlParams.set('page', 1);

        window.location.href = window.location.pathname + '?' + urlParams.toString();
        return false;
      }

      // Optimized AJAX pagination functionality with debouncing
      let fetchTimeout;
      let isFetching = false;
      
      document.addEventListener('DOMContentLoaded', () => {
        const productGridContainer = document.getElementById('productGrid');
        const categoryButtons = document.getElementById('categoryButtons');

        function fetchPage(url, pushState = true) {
          if (!productGridContainer || isFetching) return;
          
          // Clear any pending requests
          if (fetchTimeout) {
            clearTimeout(fetchTimeout);
          }
          
          // Debounce requests to prevent rapid successive calls
          fetchTimeout = setTimeout(() => {
            isFetching = true;
            productGridContainer.style.transition = 'opacity 0.1s ease';
            productGridContainer.style.opacity = '0';

            fetch(url, { 
              headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
              }
            })
            .then(response => {
              if (!response.ok) throw new Error('Network response was not ok');
              return response.text();
            })
            .then(html => {
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, 'text/html');

              const newProductGrid = doc.getElementById('productGrid');
              const newPagination = doc.querySelector('#paginationControls');

              if (newProductGrid && productGridContainer.parentNode) {
                // Use requestAnimationFrame for smoother transitions
                requestAnimationFrame(() => {
                  productGridContainer.innerHTML = newProductGrid.innerHTML;
                  productGridContainer.style.transition = 'opacity 0.1s ease';
                  productGridContainer.style.opacity = '1';
                  
                  // Re-initialize event listeners for Sold buttons
                  if (typeof initializeSaleEventListeners === 'function') {
                    initializeSaleEventListeners();
                  }
                });
              }

              const oldPagination = document.querySelector('#paginationControls');
              if (oldPagination && newPagination) {
                oldPagination.innerHTML = newPagination.innerHTML;
              }

              attachPaginationHandlers();
              if (pushState) {
                history.pushState(null, '', url);
              }
            })
            .catch(err => {
              console.error('Error loading page:', err);
              productGridContainer.style.opacity = '1';
            })
            .finally(() => {
              isFetching = false;
            });
          }, 150); // 150ms debounce delay
        }

            function attachPaginationHandlers() {
              const paginationLinks = document.querySelectorAll('#paginationControls a');
              paginationLinks.forEach(link => {
                link.addEventListener('click', e => {
                  e.preventDefault();
              fetchPage(link.href);
                });
              });
            }

            function attachCategoryButtonHandlers() {
              if (!categoryButtons) return;
              const categoryLinks = categoryButtons.querySelectorAll('a');
              categoryLinks.forEach(link => {
                link.removeEventListener('click', categoryButtonClickHandler);
                link.addEventListener('click', categoryButtonClickHandler);
              });
            }

            function categoryButtonClickHandler(e) {
              e.preventDefault();
          fetchPage(e.currentTarget.href);
            }

            attachCategoryButtonHandlers();
attachPaginationHandlers();

            // Handle browser back/forward buttons
            window.addEventListener('popstate', () => {
              fetchPage(location.href, false);
            });
          });
          </script>

<!-- Archive Confirmation Modal -->
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
<!-- Sale Success Modal -->
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

<!-- Sale Error Modal -->
<div id="saleErrorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
      <i class="fas fa-times text-red-600 text-xl"></i>
    </div>
    <h3 class="text-xl font-bold mb-4 text-red-700">Sale Error</h3>
    <p class="mb-8 text-gray-600" id="saleErrorMessage">Quantity exceeds current stock.</p>
    <button id="closeSaleErrorModal" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg">OK</button>
  </div>
</div>



  <script>
  // --- Archive/Delete Button Logic (copied from view_products.php) ---
function initializeArchiveEventListeners() {
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

  if (cancelBtn) {
    cancelBtn.addEventListener('click', closeModal);
  }

  if (confirmBtn) {
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
  }

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
}

// --- Sale Button Logic (copied from view_products.php) ---
function initializeSaleEventListeners() {
  // Sale confirmation modal elements and functions
  const saleSuccessModal = document.getElementById('saleSuccessModal');
  const closeSaleSuccessModalBtn = document.getElementById('closeSaleSuccessModal');

  if (closeSaleSuccessModalBtn) {
    closeSaleSuccessModalBtn.addEventListener('click', function () {
      saleSuccessModal.classList.add('hidden');
    });
  }

  // Sale confirmation prompt modal elements and functions
  const saleConfirmModal = document.getElementById('saleConfirmModal');
  const cancelSaleConfirmBtn = document.getElementById('cancelSaleConfirm');
  const confirmSaleConfirmBtn = document.getElementById('confirmSaleConfirm');

  let pendingSale = null;

  if (cancelSaleConfirmBtn) {
    cancelSaleConfirmBtn.addEventListener('click', function () {
      saleConfirmModal.classList.add('hidden');
      pendingSale = null;
    });
  }

  if (confirmSaleConfirmBtn) {
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
          if (closeSaleSuccessModalBtn) {
            closeSaleSuccessModalBtn.onclick = () => {
              saleSuccessModal.classList.add('hidden');
              location.reload();
            };
          }
        } else {
          showSaleErrorModal(data.message || 'Quantity exceeds current stock.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the sale.');
      });
    });
  }

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

function showSaleErrorModal(message) {
  const saleErrorModal = document.getElementById('saleErrorModal');
  const saleErrorMessage = document.getElementById('saleErrorMessage');
  if (saleErrorMessage) saleErrorMessage.textContent = message;
  if (saleErrorModal) saleErrorModal.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
  initializeArchiveEventListeners();
  initializeSaleEventListeners();
  // Sale error modal close button
  const closeSaleErrorModalBtn = document.getElementById('closeSaleErrorModal');
  if (closeSaleErrorModalBtn) {
    closeSaleErrorModalBtn.addEventListener('click', function () {
      document.getElementById('saleErrorModal').classList.add('hidden');
    });
  }
});
</script>

<!-- Chat Notification Sound (Temporarily Disabled) -->
<!-- 
<script>
  function playChatNotification() {
    const audio = document.getElementById('chatNotificationSound');
    if (audio) {
      audio.currentTime = 0;
      audio.play();
    }
  }
</script> 
-->
<!-- Add this to your HTML, preferably near the end of <body> -->
<audio id="chatNotificationSound" src="sounds/notif.mp3" preload="auto"></audio>
      </body>
    </html>
