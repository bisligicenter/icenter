<?php
ob_start();

// Custom error handler for fatal errors
function handleFatalError() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit;
    }
}
register_shutdown_function('handleFatalError');



// API endpoint for handling reservations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any previous output
    ob_clean();
    header('Content-Type: application/json');
    
    // Enable error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    try {
        // Log the incoming request
        error_log("=== RESERVATION REQUEST STARTED ===");
        
        // Check if required files exist
        if (!file_exists('db.php')) {
            throw new Exception('Database configuration file not found');
        }
        if (!file_exists('functions.php')) {
            throw new Exception('Functions file not found');
        }
        
        require_once 'db.php';
        require_once 'functions.php';
        
        // Handle FormData instead of JSON
        $data = $_POST;
        error_log("Form data received: " . print_r($data, true));
        
        // Handle file upload
        $proofOfPaymentFilename = '';
        if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/proof_of_payment/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $file = $_FILES['proof_of_payment'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
            }
            
            if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                throw new Exception('File size too large. Maximum size is 5MB.');
            }
            
            $proofOfPaymentFilename = uniqid('proof_') . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $uploadPath = $uploadDir . $proofOfPaymentFilename;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to upload proof of payment file.');
            }
        }

        // Sanitize and validate input data
        $sanitized_data = array();
        
        // Handle product_ids (comma-separated string)
        $productIdsString = isset($data['product_ids']) ? $data['product_ids'] : '';
        $sanitized_data['product_ids'] = array_filter(explode(',', $productIdsString), function($id) {
            return !empty(trim($id));
        });
        
        // Sanitize name fields
        $sanitized_data['first_name'] = filter_var($data['first_name'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['middle_initial'] = filter_var($data['middle_initial'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['last_name'] = filter_var($data['last_name'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['suffix'] = filter_var($data['suffix'] ?? '', FILTER_SANITIZE_STRING);

        // Sanitize address fields
        $sanitized_data['region'] = filter_var($data['region'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['province'] = filter_var($data['province'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['city'] = filter_var($data['city'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['barangay'] = filter_var($data['barangay'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['district'] = filter_var($data['district'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['purok'] = filter_var($data['purok'] ?? '', FILTER_SANITIZE_STRING);

        $sanitized_data['contact_number'] = filter_var($data['contact_number'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized_data['email'] = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $sanitized_data['reservation_fee'] = floatval($data['reservation_fee'] ?? 0);
        $sanitized_data['proof_of_payment'] = $proofOfPaymentFilename;

        // Validate product selection count (1-5 products)
        if (count($sanitized_data['product_ids']) < 1 || count($sanitized_data['product_ids']) > 5) {
            throw new Exception("Please select between 1 and 5 products for reservation");
        }

        // Additional validation
        if (empty($sanitized_data['email']) || !filter_var($sanitized_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (!preg_match('/^[0-9]{11}$/', $sanitized_data['contact_number'])) {
            throw new Exception("Invalid contact number format");
        }

        $conn = getDBConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        // Check if all products exist and get their details
        $placeholders = str_repeat('?,', count($sanitized_data['product_ids']) - 1) . '?';
        
        $checkProduct = $conn->prepare("SELECT product_id, product, brand, model, selling_price, image1 FROM products WHERE product_id IN ($placeholders)");
        if (!$checkProduct) {
            throw new Exception("Error preparing product check");
        }
        
        $checkProduct->execute($sanitized_data['product_ids']);
        $result = $checkProduct->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($result) !== count($sanitized_data['product_ids'])) {
            // Find which products are missing
            $found_ids = array_column($result, 'product_id');
            $missing_ids = array_diff($sanitized_data['product_ids'], $found_ids);
            throw new Exception("One or more selected products are invalid. Missing IDs: " . implode(', ', $missing_ids));
        }

        // Check if any product requires payment (above 1000)
        $paymentRequired = false;
        $productsAbove1000 = [];
        foreach ($result as $row) {
            if ($row['selling_price'] > 1000) {
                $paymentRequired = true;
                $productsAbove1000[] = $row;
            }
        }
        $checkProduct->closeCursor();

        // Validate required fields (after determining if payment is required)
        $required_fields = ['product_ids', 'first_name', 'last_name', 'region', 'province', 'city', 'barangay', 'contact_number', 'email'];
        if ($paymentRequired) {
            $required_fields[] = 'proof_of_payment';
        }
        
        foreach ($required_fields as $field) {
            if (empty($sanitized_data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate reservation fee if payment is required
        if ($paymentRequired) {
            if (empty($sanitized_data['reservation_fee'])) {
                throw new Exception("Reservation fee is required for products above ₱1,000");
            }
            
            $reservationFee = $sanitized_data['reservation_fee'];
            $countAbove1000 = count($productsAbove1000);
            $minPayment = $countAbove1000 * 500; // ₱500 minimum per item
            $totalAmount = $countAbove1000 * 1000; // ₱1,000 total per item
            
            if ($reservationFee < $minPayment) {
                throw new Exception("Minimum reservation fee is ₱" . number_format($minPayment, 2) . " (₱500 per product × {$countAbove1000} products)");
            }
            
            if ($reservationFee > $totalAmount) {
                throw new Exception("Reservation fee cannot exceed ₱" . number_format($totalAmount, 2) . " (₱1,000 per product × {$countAbove1000} products)");
            }
        }

        // Check for existing pending reservations
        $checkReservation = $conn->prepare("
            SELECT reservation_id 
            FROM reservations 
            WHERE email = ? 
            AND status = 'pending' 
            AND reservation_date >= CURDATE() - INTERVAL 7 DAY
        ");
        
        if (!$checkReservation) {
            throw new Exception("Error preparing reservation check");
        }
        
        $checkReservation->execute([$sanitized_data['email']]);
        $reservationResult = $checkReservation->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($reservationResult) > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'You already have a pending reservation. Please wait for it to be processed or contact support.',
                'show_modal' => 'existing_reservation'
            ]);
            exit;
        }
        $checkReservation->closeCursor();

        // Use the product details from the previous query
        $productDetails = $result;

        // Calculate reservation fee based on products above ₱1,000
        $reservationFee = 0;
        $productsAbove1000 = 0;
        foreach ($productDetails as $product) {
            if ($product['selling_price'] > 1000) {
                $productsAbove1000++;
            }
        }
        $reservationFee = $productsAbove1000 * 500; // ₱500 per product above ₱1,000

        // Prepare product data for insertion
        $productData = [
            'product_id_1' => null, 'product_id_2' => null, 'product_id_3' => null, 'product_id_4' => null, 'product_id_5' => null,
            'product_name_1' => null, 'product_name_2' => null, 'product_name_3' => null, 'product_name_4' => null, 'product_name_5' => null,
            'product_brand_1' => null, 'product_brand_2' => null, 'product_brand_3' => null, 'product_brand_4' => null, 'product_brand_5' => null,
            'product_model_1' => null, 'product_model_2' => null, 'product_model_3' => null, 'product_model_4' => null, 'product_model_5' => null,
            'product_price_1' => null, 'product_price_2' => null, 'product_price_3' => null, 'product_price_4' => null, 'product_price_5' => null
        ];

        // Fill in product data for selected products
        for ($i = 0; $i < count($productDetails) && $i < 5; $i++) {
            $product = $productDetails[$i];
            $index = $i + 1;
            
            $productData["product_id_$index"] = $product['product_id'];
            $productData["product_name_$index"] = $product['product'];
            $productData["product_brand_$index"] = $product['brand'];
            $productData["product_model_$index"] = $product['model'];
            $productData["product_price_$index"] = $product['selling_price'];
        }

        // Generate next reservation_id
        $getMaxIdStmt = $conn->prepare("SELECT MAX(reservation_id) as max_id FROM reservations");
        $getMaxIdStmt->execute();
        $maxIdResult = $getMaxIdStmt->fetch(PDO::FETCH_ASSOC);
        $newReservationId = ($maxIdResult['max_id'] ?? 0) + 1;
        $getMaxIdStmt->closeCursor();

        // Concatenate name and address for the original columns
        $name_parts = [
            $sanitized_data['first_name'],
            $sanitized_data['middle_initial'],
            $sanitized_data['last_name'],
            $sanitized_data['suffix']
        ];
        $full_name = trim(strtoupper(implode(' ', array_filter($name_parts))));
        $full_address = trim(strtoupper($sanitized_data['purok'] . ', ' . $sanitized_data['barangay'] . ', ' . $sanitized_data['city'] . ', ' . $sanitized_data['province'] . ', ' . $sanitized_data['region']));

        // Insert single reservation with all products
        $stmt = $conn->prepare("
            INSERT INTO reservations (
                reservation_id,
                name,
                first_name,
                middle_initial,
                last_name,
                suffix,
                contact_number,
                address,
                region,
                province,
                city,
                barangay,
                district,
                purok,
                email,
                reservation_date,
                reservation_time,
                status,
                proof_of_payment,
                reservation_fee,
                remaining_reservation_fee,
                product_count,
                product_id_1, product_id_2, product_id_3, product_id_4, product_id_5,
                product_name_1, product_name_2, product_name_3, product_name_4, product_name_5,
                product_brand_1, product_brand_2, product_brand_3, product_brand_4, product_brand_5,
                product_model_1, product_model_2, product_model_3, product_model_4, product_model_5,
                product_price_1, product_price_2, product_price_3, product_price_4, product_price_5
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?
            )
        ");
        
        if (!$stmt) {
            $errorInfo = $conn->errorInfo();
            throw new Exception("Database prepare error: " . implode(" ", $errorInfo));
        }
        
        // Format date and time
        $reservation_date = date('Y-m-d');
        $reservation_time = date('H:i:s');
        
        // Calculate remaining reservation fee
        $totalProductValue = 0;
        foreach ($productDetails as $product) {
            $totalProductValue += $product['selling_price'];
        }
        $remainingReservationFee = $totalProductValue - $reservationFee;
        
        $params = [
            $newReservationId,
            $full_name,
            $sanitized_data['first_name'],
            $sanitized_data['middle_initial'],
            $sanitized_data['last_name'],
            $sanitized_data['suffix'],
            $sanitized_data['contact_number'],
            $full_address,
            $sanitized_data['region'],
            $sanitized_data['province'],
            $sanitized_data['city'],
            $sanitized_data['barangay'],
            $sanitized_data['district'],
            $sanitized_data['purok'],
            $sanitized_data['email'],
            $reservation_date,
            $reservation_time,
            $sanitized_data['proof_of_payment'],
            $reservationFee,
            $remainingReservationFee,
            count($sanitized_data['product_ids']),
            // Product IDs
            $productData['product_id_1'], $productData['product_id_2'], $productData['product_id_3'], $productData['product_id_4'], $productData['product_id_5'],
            // Product names
            $productData['product_name_1'], $productData['product_name_2'], $productData['product_name_3'], $productData['product_name_4'], $productData['product_name_5'],
            // Product brands
            $productData['product_brand_1'], $productData['product_brand_2'], $productData['product_brand_3'], $productData['product_brand_4'], $productData['product_brand_5'],
            // Product models
            $productData['product_model_1'], $productData['product_model_2'], $productData['product_model_3'], $productData['product_model_4'], $productData['product_model_5'],
            // Product prices
            $productData['product_price_1'], $productData['product_price_2'], $productData['product_price_3'], $productData['product_price_4'], $productData['product_price_5']
        ];



        
        if (!$stmt->execute($params)) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Database execute error: " . implode(" ", $errorInfo));
        }



        echo json_encode([
            'success' => true,
            'message' => 'Reservation created successfully',
            'reservation_id' => $newReservationId,
            'product_count' => count($sanitized_data['product_ids']),
            'reservation_fee' => $reservationFee,
            'remaining_fee' => $remainingReservationFee
        ]);

        $stmt->closeCursor();
        $conn = null; // Close PDO connection
        error_log("=== RESERVATION SUCCESSFUL ===");
        exit;
        
    } catch (Exception $e) {
        // Log the error
        error_log("=== RESERVATION ERROR ===");
        error_log("Error message: " . $e->getMessage());
        error_log("Error file: " . $e->getFile() . ":" . $e->getLine());
        error_log("Error trace: " . $e->getTraceAsString());
        error_log("Data: " . json_encode($data ?? []));
        error_log("Time: " . date('Y-m-d H:i:s'));
        error_log("IP: " . $_SERVER['REMOTE_ADDR']);
        error_log("User Agent: " . $_SERVER['HTTP_USER_AGENT']);
        error_log("----------------------------------------");

        // Clear any output buffer
        ob_clean();
        
        // Ensure proper JSON response
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        $json_response = json_encode($response);
        if ($json_response === false) {
            error_log("JSON encode error: " . json_last_error_msg());
            $json_response = json_encode([
                'success' => false,
                'message' => 'Server error occurred'
            ]);
        }
        
        echo $json_response;
        exit;
    }
}

// Ensure no further output for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exit;
}

require_once 'functions.php';
require_once 'db.php';

// Get existing reservations for display
$conn = getConnection();
$query = "SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC";
$stmt = $conn->query($query);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
      background: #d7d7d7 !important;
      color: #000;
      min-height: 100vh;
            margin: 0;
            padding: 0;
    }
    
    /* Main Container */
    .reservations-container {
      max-width: 1600px;
      margin: 0 auto;
      padding: 16px 32px;
    }
    
    /* Page Header */
    .page-header {
      text-align: center;
      margin-bottom: 24px;
      background: #000;
      padding: 32px 32px;
      border-radius: 24px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.08);
      border: 2px solid #000;
        }

    .page-header h1 {
      font-family: 'Times New Roman', Times, serif;
      font-size: 46px;
      color: #fff;
      margin-bottom: 16px;
      font-weight: 700;
            position: relative;
      display: inline-block;
      padding-bottom: 16px;
        } 

    .page-header h1::after {
      content: '';
            position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 4px;
      background: #fff;
      border-radius: 2px;
    }
    
    .page-header p {
      font-size: 18px;
      color: #fff;
      max-width: 600px;
      margin: 0 auto;
      line-height: 1.6;
    }
    
    /* Reservation Form */
        .reservation-form {
      margin-bottom: 48px;
        }

        .reservation-form h2 {
      font-family: 'Times New Roman', Times, serif;
            font-size: 32px;
      color: #000;
      margin-bottom: 32px;
            text-align: center;
      font-weight: 700;
            position: relative;
            padding-bottom: 16px;
        }

        .reservation-form h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
      height: 3px;
      background: #000;
            border-radius: 2px;
        }

    /* Notice Section */
    .reservation-notice {
      background: #fff;
      padding: 20px;
      border-radius: 16px;
      margin-bottom: 24px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
        }

    .reservation-notice h3 {
      color: #000;
      margin-bottom: 20px;
      font-size: 22px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
        }

    .reservation-notice ol {
      margin: 0;
      padding-left: 24px;
      color: #000;
      line-height: 1.8;
    }
    
    .reservation-notice li {
      margin-bottom: 12px;
            font-size: 16px;
    }
    
    /* Step Indicator */
    .step-indicator {
      display: flex;
      justify-content: center;
      margin-bottom: 40px;
      gap: 24px;
      flex-wrap: wrap;
        }

    .step {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px 24px;
      background: #fff;
            border: 2px solid #000;
            border-radius: 16px;
      font-weight: 600;
      color: #000;
            transition: all 0.3s ease;
      cursor: default;
        }

    .step.active {
      background: #000;
      color: #fff;
      border-color: #000;
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

    .step i {
      font-size: 18px;
        }

    /* Form Steps */
    .form-step {
      display: none;
        }

    .form-step.active {
      display: block;
        }

    /* Product Selector */
    .product-selector {
            background: #fff;
      border: 2px solid #000;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

    /* Selection Counter */
    #selectionCounter {
            background: #000;
            color: #fff;
      padding: 20px 32px;
      border-radius: 16px;
      margin-bottom: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

    /* Category Sections */
    .product-category-section {
      margin-bottom: 40px;
        }

    /* Category Buttons Container */
    .category-buttons-container {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 24px;
      justify-content: center;
    }

    .category-heading {
      background: linear-gradient(135deg, #000 0%, #222 100%);
      color: #fff;
      border: 2px solid transparent;
      border-radius: 16px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 12px rgba(0,0,0,0.25);
      position: relative;
      overflow: hidden;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 8px;
      white-space: nowrap;
      min-width: fit-content;
        }

    .category-heading:hover,
    .category-heading.active {
      background: linear-gradient(135deg, #222 0%, #000 100%);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.2);
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

    /* Product Grid */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
    }
    
    /* Product Cards */
    .product-card {
      background: #fff;
      border: 2px solid #000;
      border-radius: 20px;
            padding: 24px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      position: relative;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    
    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 40px rgba(0,0,0,0.15);
      border-color: #000;
    }
    
    .product-card.selected {
      border-color: #000;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      transform: translateY(-4px);
        }

    /* Status Badge */
    .product-card .status-badge {
      position: absolute;
      top: 16px;
      right: 16px;
      padding: 6px 12px;
      border-radius: 12px;
      font-size: 11px;
            font-weight: 600;
      text-transform: uppercase;
      z-index: 2;
      background: #000;
      color: #fff;
        }

    .product-card.available .status-badge {
      background: #000;
      color: #fff;
        }

    .product-card.unavailable .status-badge {
      background: #fff;
            color: #000;
      border: 1px solid #000;
        }

    /* Checkbox */
    .product-card .product-checkbox {
      position: absolute;
      top: 16px;
      left: 16px;
      width: 20px;
      height: 20px;
      cursor: pointer;
      z-index: 2;
        }

    /* Product Image */
    .product-card .product-image {
      text-align: center;
      margin: 20px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 180px;
    }

    .product-card .product-image img {
      width: 160px;
      height: 160px;
      object-fit: contain;
      border-radius: 16px;
      background: #fff;
      padding: 16px;
      border: 1px solid #000;
      transition: transform 0.3s ease;
      display: block;
      margin: 0 auto;
    }

    .product-card:hover .product-image img {
            transform: scale(1.05);
        }

    /* Product Details */
    .product-card .product-details {
      text-align: center;
        }

    .product-card h4 {
      margin: 0 0 12px 0;
      font-size: 20px;
            font-weight: 600;
            color: #000;
    }
    
    .product-card p {
      margin: 0 0 6px 0;
      color: #222;
      font-size: 14px;
    }
    
    .product-card .price {
      background: #000;
      color: #fff;
      padding: 12px 20px;
            border-radius: 12px;
      font-weight: bold;
      font-size: 18px;
            margin-top: 16px;
      display: inline-block;
    }
    
    /* Form Sections */
    .form-section {
      background: #fff;
      border: 2px solid #000;
      border-radius: 20px;
      padding: 40px;
      margin-bottom: 32px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            }

    .form-section h3 {
      color: #000;
      margin-bottom: 28px;
      font-size: 24px;
      font-weight: 700;
                text-align: center;
      border-bottom: 3px solid #000;
      padding-bottom: 16px;
            }

    /* Form Groups */
    .form-group {
      margin-bottom: 24px;
            }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #000;
                font-size: 16px;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid #000;
      border-radius: 12px;
                font-size: 16px;
      transition: all 0.3s ease;
      background: #fff;
      color: #000;
        }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: #000;
      box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
    }
    
    .form-group input[readonly] {
            background: #f8f9fa;
            color: #666;
    }
    
    /* Error states for form fields */
    .form-group input.error,
    .form-group textarea.error,
    .form-group select.error {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
    }
    
    .form-group .error-message {
        color: #dc3545;
        font-size: 14px;
        margin-top: 5px;
        display: none;
    }
    
    .form-group .error-message.show {
        display: block;
    }
    
    /* Form Row */
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 32px;
      margin-bottom: 24px;
        }

    /* Buttons */
    .next-btn,
    .prev-btn,
    .submit-btn {
      background: #000;
      color: #fff;
            border: none;
      border-radius: 16px;
      padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      display: inline-flex;
      align-items: center;
      gap: 8px;
        }

    .next-btn:hover,
    .submit-btn:hover {
            transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(0,0,0,0.18);
      background: #222;
        }

    .prev-btn {
      background: #fff;
      color: #000;
      border: 2px solid #000;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

    .prev-btn:hover {
      background: #000;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(0,0,0,0.18);
        }

    /* Checkbox Container */
    .checkbox-container {
            display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 24px;
      background: #fff;
      border-radius: 16px;
      border: 2px solid #000;
        }

    .checkbox-container input[type="checkbox"] {
      margin-top: 4px;
      transform: scale(1.2);
      accent-color: #000;
      border: 2px solid #000;
      border-radius: 4px;
      cursor: pointer;
      width: 20px;
      height: 20px;
      min-width: 20px;
      min-height: 20px;
        }

    .checkbox-container label {
            font-size: 16px;
      line-height: 1.6;
      color: #000;
      cursor: pointer;
      margin: 0;
        }

    .checkbox-container a {
            color: #000;
      text-decoration: underline;
            font-weight: 600;
    }
    
    /* Field Messages */
    .field-message {
      margin-top: 8px;
                font-size: 14px;
      color: #222;
            }

    /* Payment Messages */
    .payment-message,
    .balance-message {
      margin-top: 8px;
                font-size: 14px;
      padding: 8px 12px;
            border-radius: 8px;
      background: #fff;
      color: #000;
      border: 1px solid #000;
        }
    
    .payment-message.error,
    .balance-message.negative {
      background: #fff;
      color: #dc3545;
      border: 1px solid #dc3545;
        }

    .balance-message.positive {
      background: #fff;
      color: #28a745;
      border: 1px solid #28a745;
    }
    
    .balance-message.zero {
      background: #fff;
      color: #007dd1;
      border: 1px solid #007dd1;
        }

    /* Image Preview */
    #imagePreview {
      margin-top: 16px;
      text-align: center;
        }

    #imagePreview img {
      max-width: 200px;
      max-height: 200px;
      border-radius: 12px;
      border: 2px solid #000;
        }

    #imagePreview button {
      margin-left: 12px;
      padding: 8px 16px;
      background: #000;
      color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
    }
    
    /* Confirmation List */
    #confirmationList ul {
      list-style: none;
      padding: 0;
    }
    
    #confirmationList li {
      display: flex;
      align-items: center;
      gap: 24px;
      margin-bottom: 20px;
      background: #fff;
      border-radius: 16px;
      padding: 20px 24px;
      border: 1.5px solid #000;
        }

    #confirmationList img {
      width: 80px;
      height: 80px;
      object-fit: contain;
      border-radius: 12px;
      background: #fff;
      border: 1px solid #000;
        }

    /* Responsive Design */
    @media (max-width: 768px) {
      .reservations-container {
        padding: 20px 16px;
        }

      .page-header {
        padding: 32px 20px;
        }

      .page-header h1 {
        font-size: 32px;
        }

      .reservation-form {
        padding: 32px 20px;
        }

      .reservation-form h2 {
        font-size: 24px;
        }

      .step-indicator {
        flex-direction: column;
        gap: 16px;
      }
      
      .step {
        justify-content: center;
            }

      .product-grid {
        grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }

      .next-btn,
      .prev-btn,
            .submit-btn {
        width: 100%;
            justify-content: center;
      }
      
      .category-buttons-container {
        gap: 8px;
        margin-bottom: 20px;
      }
      
      .category-heading {
        padding: 6px 12px;
        font-size: 12px;
      }
    }
    
    @media (max-width: 480px) {
      .category-buttons-container {
        gap: 6px;
        margin-bottom: 16px;
      }
      
      .category-heading {
        padding: 5px 10px;
        font-size: 11px;
      }
    }
    
    /* Hide search container in header */
    .search-container {
      display: none !important;
    }
    
    /* Header styling to match theme */
    header {
      background: #000 !important;
      box-shadow: none !important;
      border-bottom: none !important;
        }

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
      color: #fff;
    }
    
    .menu-wrapper ul li a {
      color: #fff !important;
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
    
    .menu-bar .logo {
      height: 60px;
      margin-right: 30px;
      border: 2px solid #fff;
      border-radius: 15px;
      padding: 8px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      background: rgba(255, 255, 255, 0.08);
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }
    
    .menu-bar .logo:hover { 
      transform: scale(1.08) translateY(-3px);
      box-shadow: none;
      border-color: #fff;
      background: rgba(255, 255, 255, 0.12);
        }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 100000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      animation: modalFadeIn 0.3s ease-out;
      pointer-events: auto;
    }
    
    /* Success Modal Specific Styles */
    #successModal {
      z-index: 999999 !important;
    }
    
    #successModal.show {
      display: block !important;
    }
    
    /* Force success modal to stay visible */
    #successModal[style*="display: block"] {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      pointer-events: auto !important;
    }

    @keyframes modalFadeIn {
      from { 
        opacity: 0; 
        transform: scale(0.9);
      }
      to { 
        opacity: 1; 
        transform: scale(1);
      }
    }

    .modal.show {
      display: block !important;
      animation: modalFadeIn 0.3s ease-out;
      opacity: 1;
      visibility: visible;
    }

    .modal[style*="display: block"] {
      display: block !important;
      animation: modalFadeIn 0.3s ease-out;
      opacity: 1;
      visibility: visible;
    }
    
    /* Ensure modals are always on top */
    .modal {
      z-index: 100000 !important;
    }
    
    /* Modal content animation */
    .modal-content {
      animation: modalContentPopIn 0.3s ease-out;
    }
    
    @keyframes modalContentPopIn {
      from {
        opacity: 0;
        transform: scale(0.8) translateY(-50px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    .modal-content {
      background: #fff;
      margin: 5% auto;
      padding: 0;
      border: 2px solid #000;
      border-radius: 20px;
      width: 90%;
      max-width: 500px;
      position: relative;
      animation: modalPopIn 0.3s ease-out;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      pointer-events: auto;
    }

    @keyframes modalPopIn {
      from {
        opacity: 0;
        transform: scale(0.8) translateY(-50px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    .modal-header {
      background: #000;
      color: #fff;
      padding: 20px 30px;
      border-bottom: 2px solid #000;
      position: relative;
    }

    .modal-header h2,
    .modal-header h3 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
      color: #fff;
    }

    .modal-body {
      padding: 30px;
      color: #333;
      line-height: 1.6;
    }

    .modal-footer {
      padding: 20px 30px;
      background: #f8f9fa;
      border-top: 1px solid #e9ecef;
      text-align: center;
    }

    .modal .close {
      position: absolute;
      right: 20px;
      top: 20px;
      color: #fff;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      background: #000;
      border: 2px solid #fff;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      z-index: 100001;
    }

    .modal .close:hover,
    .modal .close:focus {
      color: #fff;
      background: #333;
      transform: scale(1.1) rotate(90deg);
      box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    }

    .modal-btn {
      background: #000;
      color: #fff;
      border: none;
      border-radius: 12px;
      padding: 12px 24px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin: 0 8px;
      min-width: 120px;
    }

    .modal-btn:hover {
      background: #333;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }

    .modal-btn.cancel-btn {
      background: #6c757d;
    }

    .modal-btn.cancel-btn:hover {
      background: #5a6268;
    }

    .modal-btn.confirm-btn {
      background: #dc3545;
    }

    .modal-btn.confirm-btn:hover {
      background: #c82333;
    }

    /* Success Modal Specific Styles */
    .success-icon {
      color: #28a745;
      font-size: 48px;
      margin-bottom: 16px;
    }

    .warning-icon {
      color: #ffc107;
      font-size: 48px;
      margin-bottom: 16px;
    }

    .exclamation-triangle {
      color: #ffc107;
    }

    /* Responsive Modal */
    @media (max-width: 768px) {
      .modal-content {
        width: 95%;
        margin: 10% auto;
        border-radius: 16px;
      }

      .modal-header {
        padding: 16px 20px;
      }

      .modal-header h2,
      .modal-header h3 {
        font-size: 20px;
      }

      .modal-body {
        padding: 20px;
      }

      .modal-footer {
        padding: 16px 20px;
      }

      .modal .close {
        right: 15px;
        top: 15px;
        width: 35px;
        height: 35px;
        font-size: 24px;
      }

      .modal-btn {
        padding: 10px 20px;
        font-size: 14px;
        min-width: 100px;
        margin: 4px;
      }
    }
    
    /* Additional modal improvements */
    .modal-content {
      max-height: 90vh;
      overflow-y: auto;
    }
    
    .modal-body {
      max-height: 60vh;
      overflow-y: auto;
    }
    
    /* Terms modal specific styling */
    #termsModal .modal-body {
      max-height: 70vh;
      overflow-y: auto;
    }
    
    #termsModal .terms-content {
      line-height: 1.8;
    }
    
    #termsModal .terms-content h4 {
      color: #000;
      margin-top: 20px;
      margin-bottom: 10px;
      font-size: 18px;
      font-weight: 600;
    }
    
    #termsModal .terms-content ul {
      margin-bottom: 15px;
      padding-left: 20px;
    }
    
    #termsModal .terms-content li {
      margin-bottom: 8px;
      color: #333;
    }
    
    /* Form validation modal improvements */
    #formValidationModal .modal-body ul {
      max-height: 300px;
      overflow-y: auto;
    }
    
    #formValidationModal .modal-body li {
      padding: 8px 0;
      border-bottom: 1px solid #eee;
    }
    
    #formValidationModal .modal-body li:last-child {
      border-bottom: none;
    }


    </style>
</head>
<body>
<?php include 'kioskheader.php'; ?>
<?php include 'kioskmodals.php'; ?>

    <div class="reservations-container">
    <!-- Page Header -->
            <div class="page-header">
        <h1>Product Reservations</h1>
        <p>Reserve your favorite products with ease. Select up to 5 items and complete your reservation in just a few simple steps.</p>

    </div>

                <div class="reservation-form">
            
        <div class="reservation-notice">
            <h2>Make a Reservation</h2>
            <h3>
                <i class="fas fa-info-circle"></i>
                    How to Reserve Products
            </h3>
            <ol>
                <li>Select 1 to 5 products from the available options below</li>
                <li>Fill in your personal details accurately</li>
                <li>For products above ₱1,000: Pay reservation fee of ₱500-₱1,000 per item</li>
                <li>For products ₱1,000 or below: No reservation fee required</li>
                <li>Upload proof of payment (only if reservation fee is required)</li>
                <li>Agree to the terms and conditions</li>
                    <li>Submit your reservation and wait for confirmation</li>
                </ol>
            </div>

            <!-- Step Indicator -->
        <div class="step-indicator">
                <div class="step active" id="step1-indicator">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Step 1: Select Products</span>
                </div>
                <div class="step" id="step2-indicator">
                    <i class="fas fa-list"></i>
                    <span>Step 2: Confirm Selection</span>
                </div>
                <div class="step" id="step3-indicator">
                    <i class="fas fa-user-edit"></i>
                    <span>Step 3: Personal Details</span>
                </div>
            </div>
            
            <form id="reservationForm" enctype="multipart/form-data">
                <!-- Step 1: Product Selection -->
                <div class="form-step active" id="step1">
                    <div class="form-group full-width" style="margin-bottom: 40px;">
                        <label style="font-size: 20px; font-weight: 700; color: #333; margin-bottom: 20px; display: block;">Select Products for Reservation</label>
                        
                        <!-- Selection Counter -->
                    <div id="selectionCounter">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                                <span style="font-size: 18px; font-weight: 600;">Selected Products: <span id="selectedCount">0</span> / 5</span>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                                <span id="selectionStatus">No products selected</span>
                            </div>
                        </div>
                        
                    <div class="product-selector">
                            <?php
                            // First, get all products with their category information (not just available ones)
                            $productsQuery = "SELECT p.*, c.category_name 
                                             FROM products p 
                                             LEFT JOIN categories c ON p.category_id = c.category_id 
                                             ORDER BY p.product, c.category_name";
                            
                            try {
                            $productsStmt = $conn->query($productsQuery);
                            $allProducts = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
                                error_log("Products query executed successfully");
                            } catch (Exception $e) {
                                error_log("Error executing products query: " . $e->getMessage());
                                $allProducts = [];
                            }
                            


                            // Group products by product name
                            $productsByProduct = [];
                            foreach ($allProducts as $product) {
                                $productName = $product['product'];
                                if (!isset($productsByProduct[$productName])) {
                                    $productsByProduct[$productName] = [];
                                }
                                $productsByProduct[$productName][] = $product;
                            }

                            $selectedProductIds = isset($_GET['product_ids']) ? explode(',', $_GET['product_ids']) : [];
                            $productIndex = 0;
                            $autoOpenFound = false;
                            
                            if (empty($productsByProduct)) {
                                echo '<div style="color: #666; padding: 40px; text-align: center; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 12px; font-size: 16px;">';
                                echo '<i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 16px; display: block;"></i>';
                                echo 'No products found in the database.';
                                echo '</div>';
                            } else {
                                // Create category buttons container
                                echo '<div class="category-buttons-container">';
                                
                                foreach ($productsByProduct as $productName => $products) {
                                    $productId = 'product_' . $productIndex;
                                    $isIphone = (strtoupper($productName) === 'IPHONE');
                                    $autoOpen = '';
                                    if ($isIphone && !$autoOpenFound) {
                                        $autoOpen = ' auto-open';
                                        $autoOpenFound = true;
                                    }
                                    
                                    echo '<button class="category-heading' . $autoOpen . '" data-target="' . $productId . '">';
                                    echo '<span>' . htmlspecialchars($productName) . '</span>';
                                    echo '<span style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 10px; font-size: 11px;">' . count($products) . '</span>';
                                    echo '</button>';
                                    
                                    $productIndex++;
                                }
                                
                                echo '</div>';
                                
                                // Create product sections
                                $productIndex = 0;
                                foreach ($productsByProduct as $productName => $products) {
                                    $productId = 'product_' . $productIndex;
                                    $isIphone = (strtoupper($productName) === 'IPHONE');
                                    $autoOpen = '';
                                    if ($isIphone && !$autoOpenFound) {
                                        $autoOpen = ' auto-open';
                                        $autoOpenFound = true;
                                    }
                                    
                                    echo '<div class="product-category-section" style="margin-bottom: 30px;">';
                                    echo '<div class="category-products' . $autoOpen . '" id="' . $productId . '" style="display:none;">';
                                    echo '<div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">';
                                    
                                    foreach ($products as $product) {
                                        $isSelected = in_array($product['product_id'], $selectedProductIds);
                                        $statusClass = ($product['status'] === 'available') ? 'available' : 'unavailable';
                                        $productIdHtml = 'product-check-' . htmlspecialchars($product['product_id']);
                                        
                                        // Skip products with invalid product_id
                                        if (empty($product['product_id'])) {
                                            continue;
                                        }
                                        
                                    echo '<div class="product-card ' . $statusClass . ($isSelected ? ' selected' : '') . '">';
                                        
                                        // Checkbox
                                    echo '<input type="checkbox" class="product-checkbox" id="' . $productIdHtml . '" value="' . htmlspecialchars($product['product_id']) . '" data-selling-price="' . htmlspecialchars($product['selling_price']) . '" ' . ($isSelected ? 'checked' : '') . '>';
                                    echo '<label for="' . $productIdHtml . '" class="product-card-label">'; // New label wrapper
                                        
                                        // Product image
                                    echo '<div class="product-image">';
                                        if (!empty($product['image1'])) {
                                        echo '<img src="' . htmlspecialchars($product['image1']) . '" alt="' . htmlspecialchars($product['product']) . '">';
                                        } else {
                                        echo '<div style="width: 120px; height: 120px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 14px; margin: 0 auto;">No Image</div>';
                                        }
                                        echo '</div>';
                                        
                                        // Product details
                                    echo '<div class="product-details">';
                                    echo '<h4>' . htmlspecialchars($product['product']) . '</h4>';
                                        
                                        if (!empty($product['brand'])) {
                                        echo '<p><strong>Brand:</strong> ' . htmlspecialchars($product['brand']) . '</p>';
                                        }
                                        
                                        if (!empty($product['model'])) {
                                        echo '<p><strong>Model:</strong> ' . htmlspecialchars($product['model']) . '</p>';
                                        }
                                        
                                        if (!empty($product['category_name'])) {
                                        echo '<p><strong>Category:</strong> ' . htmlspecialchars($product['category_name']) . '</p>';
                                        }
                                        
                                        if (!empty($product['selling_price'])) {
                                        echo '<div class="price">₱' . number_format($product['selling_price'], 2) . '</div>';
                                        }
                                        echo '</label>'; // Close label wrapper
                                        echo '</div>';
                                        
                                        echo '</div>';
                                    }
                                    
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                    $productIndex++;
                                }
                            }
                            ?>
                        </div>
                        <input type="hidden" id="product_ids" name="product_ids" value="<?php echo htmlspecialchars(implode(',', $selectedProductIds)); ?>" required>
                    </div>

                    <div style="text-align: center; margin-top: 40px;">
                        <button type="button" class="next-btn" onclick="nextStep()">
                            <i class="fas fa-arrow-right" style="margin-right: 8px;"></i>
                            Next: Confirm Selection
                        </button>
                    </div>
                </div>

                <!-- Step 2: Confirmation List -->
                <div class="form-step" id="step2">
                <div class="form-section">
                    <h3>Confirm Your Selected Products</h3>
                        <div id="confirmationList" style="margin-bottom: 24px;"></div>
                        <div style="text-align: center;">
                        <button type="button" class="prev-btn" onclick="prevStep()">
                            <i class="fas fa-arrow-left"></i>
                                Back to Products
                            </button>
                        <button type="button" class="next-btn" onclick="confirmSelectionAndContinue()">
                            <i class="fas fa-check"></i>
                                Confirm & Continue
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Personal Information, Payment, etc. -->
                <div class="form-step" id="step3">
                <div class="form-section">
                    <h3>Personal Information</h3>
                        
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name"><i class="fas fa-user" style="color: #007dd1; margin-right: 8px;"></i>First Name*</label>
                            <input type="text" id="first_name" name="first_name" required oninput="this.value = this.value.toUpperCase()" placeholder="JUAN">
                        </div>
                        <div class="form-group">
                            <label for="middle_initial">Middle Initial</label>
                            <input type="text" id="middle_initial" name="middle_initial" maxlength="2" placeholder="C.">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_name">Last Name*</label>
                            <input type="text" id="last_name" name="last_name" required oninput="this.value = this.value.toUpperCase()" placeholder="DELA CRUZ">
                        </div>
                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <select id="suffix" name="suffix">
                                <option value="">None</option>
                                <option value="JR.">JR.</option>
                                <option value="SR.">SR.</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                            <label for="contact_number">
                                    <i class="fas fa-phone" style="color: #007dd1; margin-right: 8px;"></i>Contact Number
                                </label>
                                <input type="tel" id="contact_number" name="contact_number" required 
                                    pattern="[0-9]{11}" minlength="11" maxlength="11" 
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                                placeholder="09123456789">
                            <div class="field-message">Must be a valid 11-digit phone number (e.g., 09123456789)</div>
                            </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="region">Region*</label>
                            <select id="region" name="region" required class="form-input"></select>
                        </div>
                        <div class="form-group">
                            <label for="province">Province*</label>
                            <select id="province" name="province" required disabled class="form-input"></select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City/Municipality*</label>
                            <select id="city" name="city" required disabled class="form-input"></select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="barangay">Barangay*</label>
                            <select id="barangay" name="barangay" required disabled class="form-input"></select>
                        </div>
                        <div class="form-group">
                            <label for="district">District</label>
                            <input type="text" id="district" name="district" oninput="this.value = this.value.toUpperCase()" placeholder="e.g., DISTRICT 1">
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="purok">Purok/Street/Subdivision</label>
                        <input type="text" id="purok" name="purok" oninput="this.value = this.value.toUpperCase()" placeholder="e.g., PUROK 1, ESPIRITU STREET">
                    </div>

                    <div class="form-group">
                        <label for="email">
                                <i class="fas fa-envelope" style="color: #007dd1; margin-right: 8px;"></i>Email Address
                            </label>
                            <input type="email" id="email" name="email" required 
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                            placeholder="Enter your email address">
                        <div class="field-message">Must be a valid email address (e.g., name@domain.com)</div>
                        </div>
                    </div>

                <div class="form-section">
                    <h3>Payment Information</h3>
                        
                    <div class="form-row">
                            <div class="form-group">
                                <label for="down_payment">Reservation Fee (₱500-₱1,000 per item above ₱1,000):</label>
                                <input type="number" id="down_payment" name="down_payment" step="0.01" min="0">
                                <div id="paymentMessage" class="payment-message"></div>
                                </div>
                            <div class="form-group">
                                <label for="balance">Remaining Balance:</label>
                                <input type="number" id="balance" name="balance" step="0.01" readonly>
                                <div id="balanceMessage" class="balance-message"></div>
                            </div>
                            <div class="form-group">
                                <label for="proof_of_payment">Proof of Payment (Required for items above ₱1,000):</label>
                                <input type="file" id="proof_of_payment" name="proof_of_payment" accept="image/*" onchange="previewImage(this)">
                                <small>Upload a screenshot or photo of your payment receipt (JPG, JPEG, PNG only, max 5MB)</small>
                            <div id="imagePreview">
                                <img id="preview" src="#" alt="Preview">
                                <button type="button" onclick="removeImage()">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="form-section">
                    <h3>Terms and Agreement</h3>
                        
                    <div class="form-group">
                        <div class="checkbox-container">
                            <input type="checkbox" id="user_agreement" name="user_agreement" required>
                            <label for="user_agreement">
                                I agree to the <a href="#" onclick="showTermsModal(); return false;">Terms and Conditions</a> for product reservations. 
                                    I understand that for products above ₱1,000, I must pay a minimum reservation fee of ₱500 per item (up to ₱1,000 per item), and the remaining balance must be paid upon collection within 7 days.
                                </label>
                            </div>
                            <div id="agreementError" style="color: #dc3545; font-size: 14px; margin-top: 8px; display: none;"></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px; gap: 32px; flex-wrap: wrap;">
                    <button type="button" class="prev-btn" onclick="prevStep()">
                        <i class="fas fa-arrow-left"></i>
                            Back to Confirmation
                        </button>
                    <button type="submit" class="submit-btn" id="submitBtn" title="Please complete all required fields and agree to the terms to enable this button.">
                        <i class="fas fa-paper-plane"></i>
                            Submit Reservation
                    </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Category toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-open IPHONE or first category
            // --- Address Dropdown Logic ---
            const regionSelect = document.getElementById('region');
            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const barangaySelect = document.getElementById('barangay');
            let addressData = [];

            // Fetch address data
            fetch('ph_addresses.json')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    addressData = data;
                    populateRegions();
                })
                .catch(error => console.error('Error loading address data:', error));

            function populateRegions() {
                regionSelect.innerHTML = '<option value="">Select Region</option>';
                const regions = [...new Set(addressData.map(item => item.region))].sort();
                regions.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region;
                    option.textContent = region;
                    regionSelect.appendChild(option);
                });
            }

            regionSelect.addEventListener('change', function() {
                const selectedRegion = this.value;
                populateProvinces(selectedRegion);
                // Reset city and barangay
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                citySelect.disabled = true;
                barangaySelect.disabled = true;
            });

            function populateProvinces(regionName) {
                provinceSelect.innerHTML = '<option value="">Select Province</option>';
                provinceSelect.disabled = true;

                if (regionName) {
                    const provincesInRegion = addressData.filter(p => p.region === regionName);
                    provincesInRegion.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.name;
                        option.textContent = province.name;
                        provinceSelect.appendChild(option);
                    });
                    provinceSelect.disabled = false;
                }
            }

            provinceSelect.addEventListener('change', function() {
                const selectedProvinceName = provinceSelect.value;
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                citySelect.disabled = true;
                barangaySelect.disabled = true;

                if (selectedProvinceName) {
                    const selectedProvince = addressData.find(p => p.name === selectedProvinceName);
                    if (selectedProvince && selectedProvince.cities) {
                        selectedProvince.cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.name;
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                        citySelect.disabled = false;
                    }
                }
            });

            citySelect.addEventListener('change', function() {
                const selectedProvinceName = provinceSelect.value;
                const selectedCityName = this.value;
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangaySelect.disabled = true;

                if (selectedProvinceName && selectedCityName) {
                    const selectedProvince = addressData.find(p => p.name === selectedProvinceName);
                    if (selectedProvince) {
                        const selectedCity = selectedProvince.cities.find(c => c.name === selectedCityName);
                        if (selectedCity && selectedCity.barangays) {
                            selectedCity.barangays.forEach(barangay => {
                                const option = document.createElement('option');
                                option.value = barangay; 
                                option.textContent = barangay;
                                barangaySelect.appendChild(option);
                            });
                            barangaySelect.disabled = false;
                        }
                    }
                }
            });
            var autoOpenHeading = document.querySelector('.category-heading.auto-open');
            var autoOpenProducts = document.querySelector('.category-products.auto-open');
            if (autoOpenHeading && autoOpenProducts) {
                autoOpenProducts.style.display = 'block';
                autoOpenHeading.classList.add('active');
            } else {
                // fallback: open first
                var firstHeading = document.querySelector('.category-heading');
                var firstProducts = document.querySelector('.category-products');
                if (firstHeading && firstProducts) {
                    firstProducts.style.display = 'block';
                    firstHeading.classList.add('active');
                }
            }
            
            // Add click event listeners to category headings
            document.querySelectorAll('.category-heading').forEach(function(heading) {
                heading.addEventListener('click', function() {
                    var targetId = this.getAttribute('data-target');
                    
                    // Hide all product lists
                    document.querySelectorAll('.category-products').forEach(function(div) {
                        div.style.display = 'none';
                    });
                    
                    // Remove active class from all headings
                    document.querySelectorAll('.category-heading').forEach(function(h) {
                        h.classList.remove('active');
                    });
                    
                    // Show the selected one
                    var targetDiv = document.getElementById(targetId);
                    if (targetDiv) {
                        targetDiv.style.display = 'block';
                        this.classList.add('active');
                    }
                });
            });

            // Add event listeners to product checkboxes
            document.querySelectorAll('.product-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    console.log('Checkbox changed:', {
                        value: this.value,
                        checked: this.checked,
                        price: this.getAttribute('data-selling-price')
                    });
                    
                    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
                    const currentSelectedCount = selectedCheckboxes.length;
                    
                    // If trying to select and already at max, prevent selection and show modal
                    if (this.checked && currentSelectedCount > 5) {
                        this.checked = false;
                        showSelectionLimitModal();
                        return;
                    }
                    
                    updateSelectedProductIds();
                });
            });
        });

        function updateSelectedProductIds() {
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            
    
            console.log('updateSelectedProductIds called');
            console.log('Selected checkboxes:', selectedCheckboxes.length);
            console.log('Selected IDs:', selectedIds);
            
            document.getElementById('product_ids').value = selectedIds.join(',');
            
    
            console.log('Hidden field value:', document.getElementById('product_ids').value);
            
            // Update selection counter
            const selectedCount = selectedIds.length;
            const selectedCountElement = document.getElementById('selectedCount');
            const selectionStatusElement = document.getElementById('selectionStatus');
            const selectionCounter = document.getElementById('selectionCounter');
            
            selectedCountElement.textContent = selectedCount;
            
            // Update status and styling
            if (selectedCount === 0) {
                selectionStatusElement.textContent = 'No products selected';
                selectionCounter.style.background = 'linear-gradient(135deg, #007dd1, #005fa3)';
            } else if (selectedCount === 1) {
                selectionStatusElement.textContent = '1 product selected';
                selectionCounter.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            } else if (selectedCount >= 2 && selectedCount <= 4) {
                selectionStatusElement.textContent = `${selectedCount} products selected`;
                selectionCounter.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            } else if (selectedCount === 5) {
                selectionStatusElement.textContent = 'Maximum reached (5 products)';
                selectionCounter.style.background = 'linear-gradient(135deg, #ffc107, #fd7e14)';
            }
            
            // Update payment calculation when products change
            if (selectedCount > 0) {
                validateAndCalculatePayment();
            } else {
                // Reset payment fields when no products selected
                document.getElementById('down_payment').value = '';
                document.getElementById('balance').value = '';
                document.getElementById('paymentMessage').textContent = '';
                document.getElementById('balanceMessage').textContent = '';
            }
        }

        // Function to toggle product selection when clicking on the product item
        function toggleProductSelection(productCard) {
            const checkbox = productCard.querySelector('.product-checkbox');
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const currentSelectedCount = selectedCheckboxes.length;
            
            // If trying to select and already at max, prevent selection and show modal
            if (!checkbox.checked && currentSelectedCount >= 5) {
                showSelectionLimitModal();
                return;
            }
            
            checkbox.checked = !checkbox.checked;
            updateSelectedProductIds();
            
            // Update visual feedback for card-based layout
            if (checkbox.checked) {
                productCard.style.borderColor = '#007dd1';
                productCard.style.boxShadow = '0 8px 24px rgba(0, 125, 209, 0.3)';
                productCard.style.transform = 'translateY(-2px)';
            } else {
                productCard.style.borderColor = '#e9ecef';
                productCard.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
                productCard.style.transform = 'translateY(0)';
            }
        }

        // Ensure the submit handler is only attached once
        (function() {
            const form = document.getElementById('reservationForm');
            const submitBtn = document.getElementById('submitBtn');
            if (!form || !submitBtn) {
                console.error('Reservation form or submit button not found!');
                return;
            }
            if (form._submitHandlerAttached) return;
            form._submitHandlerAttached = true;
            console.log('Attaching submit handler to reservation form');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Reservation form submit event fired');
                
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                submitBtn.disabled = true;
                
                const selectedIds = document.getElementById('product_ids').value;
                if (!selectedIds) {
                    showNoProductSelectedModal();
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }
                const selectedCount = selectedIds.split(',').filter(id => id.trim() !== '').length;
                if (selectedCount < 1 || selectedCount > 5) {
                    showNoProductSelectedModal();
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Validate required fields with specific messages
                const requiredFields = [
                    { id: 'first_name', label: 'First Name', validation: (value) => value.trim().length >= 2 },
                    { id: 'last_name', label: 'Last Name', validation: (value) => value.trim().length >= 2 },
                    { id: 'region', label: 'Region', validation: (value) => value.trim().length > 0 },
                    { id: 'province', label: 'Province', validation: (value) => value.trim().length > 0 },
                    { id: 'city', label: 'City/Municipality', validation: (value) => value.trim().length > 0 },
                    { id: 'barangay', label: 'Barangay', validation: (value) => value.trim().length > 0 },
                    { id: 'contact_number', label: 'Contact Number', validation: (value) => /^[0-9]{11}$/.test(value) },
                    { id: 'email', label: 'Email Address', validation: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) }
                ];
                
                let missingFields = [];
                let invalidFields = [];
                
                requiredFields.forEach(field => {
                    const element = document.getElementById(field.id);
                    if (!element || !element.value.trim()) {
                        missingFields.push(field.label);
                    } else if (!field.validation(element.value)) {
                        invalidFields.push(field.label);
                    }
                });

                if (missingFields.length > 0) {
                    showFormValidationModal(missingFields, 'missing');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }
                
                if (invalidFields.length > 0) {
                    showFormValidationModal(invalidFields, 'invalid');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Check agreement
                if (!document.getElementById('user_agreement').checked) {
                    showFormValidationModal(['Terms and Conditions Agreement'], 'other');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Check if payment is required and validate
                const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
                let hasAbove1000 = false;
                selectedCheckboxes.forEach(cb => {
                    const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                    if (price > 1000) hasAbove1000 = true;
                });

                if (hasAbove1000) {
                    const paymentInput = document.getElementById('down_payment');
                    const fileInput = document.getElementById('proof_of_payment');
                    
                    if (!paymentInput.value || parseFloat(paymentInput.value) < (selectedCheckboxes.length * 500)) {
                        showFormValidationModal(['Reservation Fee Payment'], 'other');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        return;
                    }
                    
                    if (!fileInput.files || fileInput.files.length === 0) {
                        showFormValidationModal(['Proof of Payment Upload'], 'other');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        return;
                    }
                }

                // Prepare form data
                const formData = new FormData();
                formData.append('product_ids', selectedIds);
                formData.append('first_name', document.getElementById('first_name').value);
                formData.append('middle_initial', document.getElementById('middle_initial').value);
                formData.append('last_name', document.getElementById('last_name').value);
                formData.append('suffix', document.getElementById('suffix').value);
                formData.append('contact_number', document.getElementById('contact_number').value);
                formData.append('region', document.getElementById('region').value);
                formData.append('province', document.getElementById('province').value);
                formData.append('city', document.getElementById('city').value);
                formData.append('barangay', document.getElementById('barangay').value);
                formData.append('purok', document.getElementById('purok').value);
                formData.append('district', document.getElementById('district').value);
                formData.append('email', document.getElementById('email').value);
                formData.append('reservation_fee', document.getElementById('down_payment').value || '0');
                
                // Handle proof of payment
                const fileInput = document.getElementById('proof_of_payment');
                if (fileInput.files && fileInput.files[0]) {
                    formData.append('proof_of_payment', fileInput.files[0]);
                }

        
                console.log('Form data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }

                // Submit via AJAX
                fetch('reservations.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.text().then(text => {
                        console.log('Raw response:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            throw new Error('Invalid JSON response: ' + text);
                        }
                    });
                })
                .then(data => {
                    console.log('Parsed response:', data);
                    if (data.success) {
                        console.log('Success response received, calling showSuccessModal');
                        // Show success modal using the new function
                        showSuccessModal(data.message);
                    } else {
                        console.log('Error response received:', data.message);
                        // Show error
                        if (data.show_modal === 'existing_reservation') {
                            console.log('Showing existing reservation modal');
                            const modal = document.getElementById('existingReservationModal');
                            modal.style.display = 'block';
                            setTimeout(function() {
                                if (modal) {
                                    modal.focus();
                                }
                            }, 350);
                        } else {
                            showFormValidationModal([data.message]);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showFormValidationModal(['Network error: ' + error.message + '. Please check your connection and try again.']);
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        })();

        // Multi-step form navigation
        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(function(div, idx) {
                div.classList.remove('active');
            });
            document.getElementById('step' + step).classList.add('active');
            // Update step indicator
            document.getElementById('step1-indicator').classList.toggle('active', step === 1);
            document.getElementById('step2-indicator').classList.toggle('active', step === 2);
            document.getElementById('step3-indicator').classList.toggle('active', step === 3);
        }
        function nextStep() {
            // Validate at least one product selected before moving to next step
            const selectedIds = document.getElementById('product_ids').value;
            if (!selectedIds) {
                showNoProductSelectedModal();
                return;
            }
            const selectedCount = selectedIds.split(',').filter(id => id.trim() !== '').length;
            if (selectedCount < 1 || selectedCount > 5) {
                showNoProductSelectedModal();
                return;
            }
            // Show confirmation step
            populateConfirmationList();
            showStep(2);
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        function prevStep() {
            // If on confirmation step, go back to product selection
            if (document.getElementById('step2').classList.contains('active')) {
                showStep(1);
            } else {
                // If on personal info step, go back to confirmation
                showStep(2);
            }
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        function confirmSelectionAndContinue() {
            showStep(3);
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        // Populate confirmation list with selected products
        function populateConfirmationList() {
            const selectedIds = document.getElementById('product_ids').value.split(',').filter(id => id.trim() !== '');
            const allProductCards = document.querySelectorAll('.product-card');
            let html = '';
            if (selectedIds.length === 0) {
                html = '<div style="color: #c62828; font-weight: bold;">No products selected.</div>';
            } else {
                html = '<ul style="list-style: none; padding: 0;">';
                allProductCards.forEach(card => {
                    const checkbox = card.querySelector('.product-checkbox');
                    if (checkbox && selectedIds.includes(checkbox.value)) {
                        const img = card.querySelector('img');
                        const name = card.querySelector('h4') ? card.querySelector('h4').textContent : '';
                        const brand = card.querySelector('p strong') ? card.querySelector('p strong').parentNode.textContent : '';
                        const priceDiv = card.querySelector('div[style*="background: linear-gradient"]');
                        const price = priceDiv ? priceDiv.textContent : '';
                        html += `<li style='display: flex; align-items: center; gap: 24px; margin-bottom: 18px; background: #f8f9fa; border-radius: 12px; padding: 16px 24px; border: 1.5px solid #e0e0e0;'>`;
                        if (img) html += `<img src='${img.src}' alt='' style='width: 80px; height: 80px; object-fit: contain; border-radius: 8px; background: #fff; border: 1px solid #dee2e6; margin-right: 16px;'>`;
                        html += `<div><div style='font-weight: 600; font-size: 18px; color: #333;'>${name}</div>`;
                        if (brand) html += `<div style='color: #666; font-size: 14px;'>${brand}</div>`;
                        if (price) html += `<div style='color: #007dd1; font-weight: bold; font-size: 16px; margin-top: 4px;'>${price}</div>`;
                        html += `</div></li>`;
                    }
                });
                html += '</ul>';
            }
            document.getElementById('confirmationList').innerHTML = html;
        }
        // On page load, show step 1
        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
        });

        // Add this function to check if the form is valid and enable/disable the submit button
        function checkFormValidity() {
            const submitBtn = document.getElementById('submitBtn');
            if (!submitBtn) {
                return;
            }
            
            const selectedIds = document.getElementById('product_ids').value;
            const selectedCount = selectedIds.split(',').filter(id => id.trim() !== '').length;
            const requiredFields = [
                'first_name', 'last_name', 'region', 'province', 'city', 'barangay', 'contact_number', 'email'
            ];
            let allFieldsFilled = true;
            
            // Check required fields
            requiredFields.forEach(id => {
                const el = document.getElementById(id);
                if (!el || !el.value.trim() || (el.tagName === 'SELECT' && el.value === '') || (el.validity && !el.validity.valid)) {
                    allFieldsFilled = false;
                }
            });
            
            // Check agreement
            const agreement = document.getElementById('user_agreement');
            if (!agreement || !agreement.checked) allFieldsFilled = false;
            
            // Check product selection
            if (selectedCount < 1 || selectedCount > 5) allFieldsFilled = false;
            
            // Check payment requirements
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            let hasAbove1000 = false;
            let productsAbove1000Count = 0;
            selectedCheckboxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                if (price > 1000) {
                    hasAbove1000 = true;
                    productsAbove1000Count++;
                }
            });
            
            if (hasAbove1000) {
                const paymentInput = document.getElementById('down_payment');
                const fileInput = document.getElementById('proof_of_payment');
                const minPayment = productsAbove1000Count * 500;

                if (!paymentInput || !paymentInput.value || parseFloat(paymentInput.value) < minPayment) {
                    allFieldsFilled = false;
                }
                
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    allFieldsFilled = false;
                }
            }
            
            // Enable or disable submit button and update its title
            submitBtn.disabled = !allFieldsFilled;
            if (allFieldsFilled) {
                submitBtn.title = 'Click to submit your reservation';
            } else {
                submitBtn.title = 'Please complete all required fields and agree to the terms to enable this button.';
            }
        }

        // Attach listeners for real-time validation
        document.addEventListener('DOMContentLoaded', function() {
            // Listen to all relevant fields
            ['first_name', 'middle_initial', 'last_name', 'suffix', 'region', 'province', 'city', 'barangay', 'district', 'purok', 'contact_number','email','user_agreement','down_payment','proof_of_payment'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        checkFormValidity();
                        // Clear error state when user starts typing
                        this.classList.remove('error');
                    });
                    el.addEventListener('change', checkFormValidity);
                }
            });
            
            // Listen to product selection
            document.querySelectorAll('.product-checkbox').forEach(cb => {
                cb.addEventListener('change', checkFormValidity);
            });
            
            // Listen to agreement
            const agreement = document.getElementById('user_agreement');
            if (agreement) {
                agreement.addEventListener('change', function() {
                    checkFormValidity();
                    // Clear error state when user checks the agreement
                    this.classList.remove('error');
                });
            }
            
            // Initial check
            checkFormValidity();
        });

        // Add or update this function to calculate and display the remaining balance
        function validateAndCalculatePayment() {
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const downPaymentInput = document.getElementById('down_payment');
            const balanceInput = document.getElementById('balance');
            const paymentMessage = document.getElementById('paymentMessage');
            const balanceMessage = document.getElementById('balanceMessage');
            const submitBtn = document.getElementById('submitBtn');
            
            if (!downPaymentInput || !balanceInput || !paymentMessage || !balanceMessage || !submitBtn) {
                console.error('Payment calculation elements not found');
                return;
            }
            
            let totalProductValue = 0;
            let countAbove1000 = 0;
            
            selectedCheckboxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                totalProductValue += price;
                if (price > 1000) countAbove1000++;
            });
            
            // Set min/max for down_payment
            const minPayment = countAbove1000 * 500;
            const maxPayment = countAbove1000 * 1000;
            
            downPaymentInput.min = minPayment;
            downPaymentInput.max = maxPayment;
            
            // Do NOT auto-fill the value if empty
            let downPayment = downPaymentInput.value === '' ? '' : parseFloat(downPaymentInput.value);
            
            // Calculate remaining balance
            let remaining = (downPayment !== '' ? totalProductValue - downPayment : '');
            balanceInput.value = (downPayment !== '' && remaining >= 0) ? remaining.toFixed(2) : '';
            
            // Show payment message
            let paymentError = '';
            
            if (countAbove1000 > 0) {
                paymentMessage.textContent = `Enter a reservation fee between ₱${minPayment.toLocaleString()} and ₱${maxPayment.toLocaleString()} for ${countAbove1000} product(s) above ₱1,000.`;
                
                // Validation: must not be empty, must be within min/max
                if (downPayment === '' || isNaN(downPayment)) {
                    paymentError = 'Reservation fee is required.';
                } else if (downPayment < minPayment) {
                    paymentError = `Reservation fee cannot be less than ₱${minPayment.toLocaleString()}.`;
                } else if (downPayment > maxPayment) {
                    paymentError = `Reservation fee cannot exceed ₱${maxPayment.toLocaleString()}.`;
                }
            } else {
                paymentMessage.textContent = 'No reservation fee required for selected products.';
            }
            
            // Show error if any
            if (paymentError) {
                paymentMessage.textContent += ' ' + paymentError;
                submitBtn.disabled = true;
            } else {
                // Don't enable here - let checkFormValidity handle it
            }
            
            // Show balance message
            if (downPayment !== '' && remaining > 0) {
                balanceMessage.textContent = `Remaining balance: ₱${remaining.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                balanceMessage.className = 'balance-message positive';
            } else if (downPayment !== '' && remaining === 0) {
                balanceMessage.textContent = 'No remaining balance.';
                balanceMessage.className = 'balance-message zero';
            } else {
                balanceMessage.textContent = '';
                balanceMessage.className = 'balance-message';
            }
        }

        // Listen for changes to down_payment to update balance in real time
        document.addEventListener('DOMContentLoaded', function() {
            const downPaymentInput = document.getElementById('down_payment');
            if (downPaymentInput) {
                downPaymentInput.addEventListener('input', validateAndCalculatePayment);
            }
        });

        // Add or update the previewImage and removeImage functions for proof of payment
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            
            if (!preview || !imagePreview) {
                console.error('Preview elements not found');
                return;
            }
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, JPEG, or PNG only).');
                    input.value = '';
                    return;
                }
                
                // Validate file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size too large. Please select an image smaller than 5MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    // Show remove button if present
                    const removeBtn = imagePreview.querySelector('button');
                    if (removeBtn) removeBtn.style.display = 'inline-block';
                };
                reader.onerror = function() {
                    alert('Error reading file. Please try again.');
                    input.value = '';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
                // Hide remove button if present
                const removeBtn = imagePreview.querySelector('button');
                if (removeBtn) removeBtn.style.display = 'none';
            }
        }
        
        function removeImage() {
            const fileInput = document.getElementById('proof_of_payment');
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            
            if (fileInput) fileInput.value = '';
            if (preview) {
                preview.src = '#';
                preview.style.display = 'none';
            }
            // Hide remove button if present
            if (imagePreview) {
                const removeBtn = imagePreview.querySelector('button');
                if (removeBtn) removeBtn.style.display = 'none';
            }
        }
        // Hide preview and remove button initially
        window.addEventListener('DOMContentLoaded', function() {
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            if (preview) preview.style.display = 'none';
            const removeBtn = imagePreview ? imagePreview.querySelector('button') : null;
            if (removeBtn) removeBtn.style.display = 'none';
        });


        
        // Modal functions
        function showSuccessModal(message) {
            console.log('showSuccessModal called with message:', message);

            const modal = document.getElementById('successModal');
            if (!modal) {
                console.error('successModal element not found');
                return;
            }

            // Set message
            const successMessage = modal.querySelector('#successMessage');
            if (successMessage) {
                successMessage.textContent = message || 'Your reservation has been submitted successfully.';
            }

            // Wire up close button
            const closeBtn = modal.querySelector('.close');
            if (closeBtn && !closeBtn._wired) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                });
                closeBtn._wired = true;
            }

            // Wire up return button
            const returnBtn = modal.querySelector('#returnHomeBtn');
            if (returnBtn && !returnBtn._wired) {
                returnBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    window.location.href = 'kiosk.php';
                });
                returnBtn._wired = true;
            }

            // Show modal
            modal.style.display = 'block';
            modal.classList.add('show');

            // Focus for accessibility
            setTimeout(function() {
                modal.focus && modal.focus();
            }, 50);

            console.log('Success modal shown');
        }






        // document.getElementById('submitBtn').disabled = false;
        // Remove alert for disabled state, just log for devs
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            if (this.disabled) {
                console.log('Submit button is disabled.');
            } else {
                console.log('Submit button clicked.');
            }
        });
    </script>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reservation Successful!</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <i class="fas fa-check-circle success-icon"></i>
                    <p style="margin: 0;">Your reservation has been submitted successfully.</p>
                </div>
                <p id="successMessage">We will contact you shortly to confirm your reservation.</p>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button class="modal-btn" id="returnHomeBtn" style="background: #000; color: white;">Return to Home</button>
            </div>
        </div>
    </div>

    <!-- Product Removal Confirmation Modal -->
    <div id="productRemovalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Remove Product</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="product-removal-content">
                    <div class="product-image-container">
                        <img id="removalProductImage" src="" alt="Selected Product" class="removal-product-image">
                    </div>
                    <div class="product-details">
                        <h3 id="removalProductName"></h3>
                        <p id="removalProductBrand"></p>
                        <p id="removalProductModel"></p>
                        <p id="removalProductPrice"></p>
                    </div>
                    <div class="removal-message">
                        <i class="fas fa-exclamation-circle warning-icon"></i>
                        <p>Are you sure you want to remove this product from your selection?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel-btn" onclick="hideRemoveConfirmModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="confirmProductRemoval()">Remove Product</button>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Terms and Conditions</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="terms-content">
                    <h3>Terms and Conditions for Product Reservations</h3>
                    <div class="terms-content">
                        <h4>Reservation Fee Structure:</h4>
                        <ul>
                            <li><strong>Products priced ₱1,000 and below:</strong> No reservation fee required</li>
                            <li><strong>Products priced above ₱1,000:</strong> 
                                <ul>
                                    <li>Minimum reservation fee: ₱500 per item</li>
                                    <li>Maximum reservation fee: ₱1,000 per item</li>
                                    <li>Proof of payment is required</li>
                                </ul>
                            </li>
                        </ul>
                        <h4>Payment Terms:</h4>
                        <ul>
                            <li>Reservation fee must be paid within 24 hours of making the reservation</li>
                            <li>Remaining balance must be paid upon product collection</li>
                            <li>Reservation is valid for 7 days from the date of reservation</li>
                            <li>Failure to pay the remaining balance within 7 days will result in cancellation of the reservation</li>
                        </ul>
                        <h4>Reservation Process:</h4>
                        <ul>
                            <li>Submit your reservation with complete and accurate information</li>
                            <li>Pay the required reservation fee (₱500-₱1,000 per item above ₱1,000)</li>
                            <li>Upload proof of payment for verification</li>
                            <li>We will contact you within 24 hours to confirm your reservation</li>
                            <li>Collect your product within 7 days and pay the remaining balance</li>
                        </ul>
                        <h4>Cancellation Policy:</h4>
                        <ul>
                            <li>Reservations can be cancelled within 24 hours without penalty</li>
                            <li>After 24 hours, a 10% cancellation fee will be deducted from the refund</li>
                            <li>No refunds for cancellations after 7 days</li>
                        </ul>
                        <h4>Important Notes:</h4>
                        <ul>
                            <li>Products are reserved on a first-come, first-served basis</li>
                            <li>We reserve the right to cancel reservations if products become unavailable</li>
                            <li>All prices are subject to change without prior notice</li>
                            <li>Valid government-issued ID is required upon product collection</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="closeTermsModal()" style="background: #000; color: white;">Close</button>
            </div>
        </div>
    </div>

    <!-- Selection Limit Modal -->
    <div id="selectionLimitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Selection Limit Reached</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-triangle exclamation-triangle" style="font-size: 64px; color: #ffc107; margin-bottom: 24px;"></i>
                    <h3 style="color: #333; margin-bottom: 16px; font-size: 24px;">Maximum Products Reached</h3>
                    <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
                        You have reached the maximum limit of <strong>5 products</strong> for a single reservation.
                    </p>
                    <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                        To add more products, please remove one of your current selections first.
                    </p>
                    <div class="info-box">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Current Selection: <span id="currentSelectionCount" class="current-count">5</span> products
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="hideSelectionLimitModal()" style="background: #007dd1; color: white;">Got It</button>
            </div>
        </div>
    </div>

    <!-- No Product Selected Modal -->
    <div id="noProductSelectedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Product Selection Required</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-circle warning-icon" style="font-size: 64px; color: #000; margin-bottom: 24px;"></i>
                    <h3 style="color: #000; margin-bottom: 16px; font-size: 24px;">Please select at least one product to reserve.</h3>
                    <p style="color: #000; font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
                        You must select at least one product before proceeding with your reservation.
                    </p>
                </div>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button class="modal-btn" onclick="hideNoProductSelectedModal()" style="background: #000; color: white;">OK</button>
            </div>
        </div>
    </div>

    <!-- Form Validation Error Modal -->
    <div id="formValidationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 10px;"></i>Form Validation Error</h2>
                <span class="close" onclick="hideFormValidationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Please correct the following errors before submitting:</p>
                <ul id="errorList" style="margin-top: 15px; padding-left: 20px; color: #dc3545;"></ul>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="hideFormValidationModal()" style="background: #000; color: white;">Close</button>
            </div>
        </div>
    </div>

    <!-- Existing Reservation Modal -->
    <div id="existingReservationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle" style="color: #007dd1; margin-right: 10px;"></i>Existing Reservation Found</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-clock" style="font-size: 48px; color: #007dd1; margin-bottom: 20px;"></i>
                    <h4 style="color: #333; margin-bottom: 15px;">Reservation Already Exists</h4>
                    <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                        You already have a pending reservation in our system. Please wait for it to be processed or contact our support team for assistance.
                    </p>
                    <div style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 12px; padding: 15px; margin: 20px 0;">
                        <h5 style="color: #333; margin-bottom: 10px;"><i class="fas fa-phone" style="color: #007dd1; margin-right: 8px;"></i>Contact Support</h5>
                        <p style="color: #666; margin: 0;">Phone: <strong>0912-345-6789</strong></p>
                        <p style="color: #666; margin: 5px 0 0 0;">Email: <strong>support@bisligicenter.com</strong></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button class="modal-btn" onclick="hideExistingReservationModal()" style="background: #000; color: white;">Close</button>
            </div>
        </div>
    </div>

    <script>
    // Modal functions
    function showTermsModal() {
        console.log('showTermsModal called');
        const modal = document.getElementById('termsModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function closeTermsModal() {
        console.log('closeTermsModal called');
        const modal = document.getElementById('termsModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }
    
    function showSelectionLimitModal() {
        console.log('showSelectionLimitModal called');
        const modal = document.getElementById('selectionLimitModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function hideSelectionLimitModal() {
        console.log('hideSelectionLimitModal called');
        const modal = document.getElementById('selectionLimitModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        showStep(1);
    }
    
    function showNoProductSelectedModal() {
        console.log('showNoProductSelectedModal called');
        const modal = document.getElementById('noProductSelectedModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function hideNoProductSelectedModal() {
        console.log('hideNoProductSelectedModal called');
        const modal = document.getElementById('noProductSelectedModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        showStep(1);
    }
    
    function hideExistingReservationModal() {
        console.log('hideExistingReservationModal called');
        const modal = document.getElementById('existingReservationModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        showStep(1);
    }
    
    function showFormValidationModal(fields, type = 'missing') {
        console.log('showFormValidationModal called with fields:', fields, 'type:', type);
        const modal = document.getElementById('formValidationModal');
        const errorList = document.getElementById('errorList');
        
        // Clear any previous error highlighting
        clearFieldErrors();
        
        if (errorList) {
            errorList.innerHTML = '';
            
            // Add a header message based on type
            const headerLi = document.createElement('li');
            if (type === 'missing' || type === 'invalid') {
                headerLi.innerHTML = '<strong style="color: #dc3545; font-size: 16px;">Please complete the following required fields:</strong>';
            } else {
                headerLi.innerHTML = '<strong style="color: #dc3545; font-size: 16px;">Please address the following issues:</strong>';
            }
            headerLi.style.marginBottom = '10px';
            errorList.appendChild(headerLi);
            
            const fieldMap = {
                'First Name': 'first_name',
                'Last Name': 'last_name',
                'Region': 'region',
                'Province': 'province',
                'City/Municipality': 'city',
                'Barangay': 'barangay',
                'Contact Number': 'contact_number',
                'Email Address': 'email',
                'Terms and Conditions Agreement': 'user_agreement',
                'Reservation Fee Payment': 'down_payment',
                'Proof of Payment Upload': 'proof_of_payment'
            };

            function highlightField(fieldName) {
                const fieldId = fieldMap[fieldName];
                if (fieldId) {
                    const field = document.getElementById(fieldId);
                    if (field) field.classList.add('error');
                }
            }

            fields.forEach(field => {
                const li = document.createElement('li');
                let message = field;
                
                // Add specific validation messages for invalid fields
                if (type === 'invalid') {
                    switch(field) {
                        case 'Contact Number':
                            message = 'Contact Number (must be exactly 11 digits)';
                            break;
                        case 'Email Address':
                            message = 'Email Address (must be a valid email format)';
                            break;
                    }
                } else if (type === 'other') {
                    switch(field) {
                        case 'Terms and Conditions Agreement':
                            message = 'Terms and Conditions Agreement (you must agree to the terms before submitting)';
                            break;
                        case 'Reservation Fee Payment':
                            message = 'Reservation Fee Payment (minimum ₱500 per product above ₱1,000 is required)';
                            break;
                        case 'Proof of Payment Upload':
                            message = 'Proof of Payment Upload (required for products above ₱1,000)';
                            break;
                        default:
                            message = field;
                    }
                }

                highlightField(field);
                
                li.innerHTML = `<i class="fas fa-exclamation-circle" style="color: #dc3545; margin-right: 8px;"></i>${message}`;
                li.style.marginBottom = '8px';
                errorList.appendChild(li);
            });
            
            // Add a footer message based on type
            const footerLi = document.createElement('li');
            if (type === 'missing' || type === 'invalid') {
                footerLi.innerHTML = '<em style="color: #666; font-size: 14px;">Please complete all required fields before submitting your reservation.</em>';
            } else {
                footerLi.innerHTML = '<em style="color: #666; font-size: 14px;">Please address all issues before submitting your reservation.</em>';
            }
            footerLi.style.marginTop = '15px';
            footerLi.style.borderTop = '1px solid #eee';
            footerLi.style.paddingTop = '10px';
            errorList.appendChild(footerLi);
        }
        
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function clearFieldErrors() {
        document.querySelectorAll('.form-group input, .form-group textarea, .form-group select').forEach(field => {
            field.classList.remove('error');
        });
    }
    
    function hideFormValidationModal() {
        console.log('hideFormValidationModal called');
        const modal = document.getElementById('formValidationModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }
    
    function hideRemoveConfirmModal() {
        console.log('hideRemoveConfirmModal called');
        const modal = document.getElementById('productRemovalModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }
    
    function confirmProductRemoval() {
        // This function would handle the actual product removal logic
        // For now, just close the modal
        hideRemoveConfirmModal();
    }
    
    // Initialize modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing modal functionality...');
        
        // Handle modal close buttons (excluding success modal which has its own handling)
        document.querySelectorAll('.modal:not(#successModal) .close').forEach(function(closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                console.log('Close button clicked for modal:', this.closest('.modal').id);
                e.preventDefault();
                e.stopPropagation();
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    // Return to step 1 for specific modals
                    if (['existingReservationModal', 'noProductSelectedModal', 'selectionLimitModal'].includes(modal.id)) {
                        showStep(1);
                    }
                }
            });
        });
        
        // Handle modal button clicks
        document.querySelectorAll('.modal:not(#successModal) .modal-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                console.log('Modal button clicked:', this.textContent.trim());
                e.preventDefault();
                e.stopPropagation();
                
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    
                    // Handle specific button actions
                    if (this.textContent.trim() === 'Close') {
                        // Close button - no additional action needed
                    } else if (this.textContent.trim() === 'Got It') {
                        showStep(1);
                    } else if (this.textContent.trim() === 'OK') {
                        showStep(1);
                    } else if (this.textContent.trim() === 'Remove Product') {
                        confirmProductRemoval();
                    } else if (this.textContent.trim() === 'Cancel') {
                        // Cancel button - no additional action needed
                    }
                }
            });
        });
        
        // Prevent modal closing when clicking inside modal content (excluding success modal)
        document.querySelectorAll('.modal:not(#successModal)').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    console.log('Modal background clicked - preventing close');
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
        
        // Prevent ESC key from closing modals (excluding success modal)
        window.addEventListener('keydown', function(e) {
            const anyModalOpen = Array.from(document.querySelectorAll('.modal:not(#successModal)')).some(m => m.style.display === 'block');
            if (anyModalOpen && (e.key === 'Escape' || e.keyCode === 27)) {
                console.log('ESC key pressed - preventing modal close');
                e.preventDefault();
                e.stopPropagation();
            }
        });
        
        // Initialize form validation
        checkFormValidity();
        
        console.log('Modal functionality initialized');
    });
    </script>


</body>
</html>
                allFieldsFilled = false;
            }
            
            // Check product selection
            if (selectedCount < 1 || selectedCount > 5) {
                allFieldsFilled = false;
            }
            
            // Check if any selected product is above 1000
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            let hasAbove1000 = false;
            selectedCheckboxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                if (price > 1000) hasAbove1000 = true;
            });
            
            // If payment required, check payment and proof
            if (hasAbove1000) {
                const paymentInput = document.getElementById('down_payment');
                const fileInput = document.getElementById('proof_of_payment');
                
                if (!paymentInput || !paymentInput.value || parseFloat(paymentInput.value) < (selectedCheckboxes.length * 500)) {
                    allFieldsFilled = false;
                }
                
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    allFieldsFilled = false;
                }
            }
            
            // Enable or disable submit button
            submitBtn.disabled = !allFieldsFilled;
        }

        // Attach listeners for real-time validation
        document.addEventListener('DOMContentLoaded', function() {
            // Listen to all relevant fields
            ['first_name', 'middle_initial', 'last_name', 'suffix', 'region', 'province', 'city', 'barangay', 'district', 'purok', 'contact_number','email','user_agreement','down_payment','proof_of_payment'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        checkFormValidity();
                        // Clear error state when user starts typing
                        this.classList.remove('error');
                    });
                    el.addEventListener('change', checkFormValidity);
                }
            });
            
            // Listen to product selection
            document.querySelectorAll('.product-checkbox').forEach(cb => {
                cb.addEventListener('change', checkFormValidity);
            });
            
            // Listen to agreement
            const agreement = document.getElementById('user_agreement');
            if (agreement) {
                agreement.addEventListener('change', function() {
                    checkFormValidity();
                    // Clear error state when user checks the agreement
                    this.classList.remove('error');
                });
            }
            
            // Initial check
            checkFormValidity();
        });

        // Add or update this function to calculate and display the remaining balance
        function validateAndCalculatePayment() {
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const downPaymentInput = document.getElementById('down_payment');
            const balanceInput = document.getElementById('balance');
            const paymentMessage = document.getElementById('paymentMessage');
            const balanceMessage = document.getElementById('balanceMessage');
            const submitBtn = document.getElementById('submitBtn');
            
            if (!downPaymentInput || !balanceInput || !paymentMessage || !balanceMessage || !submitBtn) {
                console.error('Payment calculation elements not found');
                return;
            }
            
            let totalProductValue = 0;
            let countAbove1000 = 0;
            
            selectedCheckboxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                totalProductValue += price;
                if (price > 1000) countAbove1000++;
            });
            
            // Set min/max for down_payment
            const minPayment = countAbove1000 * 500;
            const maxPayment = countAbove1000 * 1000;
            
            downPaymentInput.min = minPayment;
            downPaymentInput.max = maxPayment;
            
            // Do NOT auto-fill the value if empty
            let downPayment = downPaymentInput.value === '' ? '' : parseFloat(downPaymentInput.value);
            
            // Calculate remaining balance
            let remaining = (downPayment !== '' ? totalProductValue - downPayment : '');
            balanceInput.value = (downPayment !== '' && remaining >= 0) ? remaining.toFixed(2) : '';
            
            // Show payment message
            let paymentError = '';
            
            if (countAbove1000 > 0) {
                paymentMessage.textContent = `Enter a reservation fee between ₱${minPayment.toLocaleString()} and ₱${maxPayment.toLocaleString()} for ${countAbove1000} product(s) above ₱1,000.`;
                
                // Validation: must not be empty, must be within min/max
                if (downPayment === '' || isNaN(downPayment)) {
                    paymentError = 'Reservation fee is required.';
                } else if (downPayment < minPayment) {
                    paymentError = `Reservation fee cannot be less than ₱${minPayment.toLocaleString()}.`;
                } else if (downPayment > maxPayment) {
                    paymentError = `Reservation fee cannot exceed ₱${maxPayment.toLocaleString()}.`;
                }
            } else {
                paymentMessage.textContent = 'No reservation fee required for selected products.';
            }
            
            // Show error if any
            if (paymentError) {
                paymentMessage.textContent += ' ' + paymentError;
                submitBtn.disabled = true;
            } else {
                // Don't enable here - let checkFormValidity handle it
            }
            
            // Show balance message
            if (downPayment !== '' && remaining > 0) {
                balanceMessage.textContent = `Remaining balance: ₱${remaining.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
                balanceMessage.className = 'balance-message positive';
            } else if (downPayment !== '' && remaining === 0) {
                balanceMessage.textContent = 'No remaining balance.';
                balanceMessage.className = 'balance-message zero';
            } else {
                balanceMessage.textContent = '';
                balanceMessage.className = 'balance-message';
            }
        }

        // Listen for changes to down_payment to update balance in real time
        document.addEventListener('DOMContentLoaded', function() {
            const downPaymentInput = document.getElementById('down_payment');
            if (downPaymentInput) {
                downPaymentInput.addEventListener('input', validateAndCalculatePayment);
            }
        });

        // Add or update the previewImage and removeImage functions for proof of payment
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            
            if (!preview || !imagePreview) {
                console.error('Preview elements not found');
                return;
            }
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, JPEG, or PNG only).');
                    input.value = '';
                    return;
                }
                
                // Validate file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size too large. Please select an image smaller than 5MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    // Show remove button if present
                    const removeBtn = imagePreview.querySelector('button');
                    if (removeBtn) removeBtn.style.display = 'inline-block';
                };
                reader.onerror = function() {
                    alert('Error reading file. Please try again.');
                    input.value = '';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
                // Hide remove button if present
                const removeBtn = imagePreview.querySelector('button');
                if (removeBtn) removeBtn.style.display = 'none';
            }
        }
        
        function removeImage() {
            const fileInput = document.getElementById('proof_of_payment');
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            
            if (fileInput) fileInput.value = '';
            if (preview) {
                preview.src = '#';
                preview.style.display = 'none';
            }
            // Hide remove button if present
            if (imagePreview) {
                const removeBtn = imagePreview.querySelector('button');
                if (removeBtn) removeBtn.style.display = 'none';
            }
        }
        // Hide preview and remove button initially
        window.addEventListener('DOMContentLoaded', function() {
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');
            if (preview) preview.style.display = 'none';
            const removeBtn = imagePreview ? imagePreview.querySelector('button') : null;
            if (removeBtn) removeBtn.style.display = 'none';
        });


        
        // Modal functions
        function showSuccessModal(message) {
            console.log('showSuccessModal called with message:', message);

            const modal = document.getElementById('successModal');
            if (!modal) {
                console.error('successModal element not found');
                return;
            }

            // Set message
            const successMessage = modal.querySelector('#successMessage');
            if (successMessage) {
                successMessage.textContent = message || 'Your reservation has been submitted successfully.';
            }

            // Wire up close button
            const closeBtn = modal.querySelector('.close');
            if (closeBtn && !closeBtn._wired) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                });
                closeBtn._wired = true;
            }

            // Wire up return button
            const returnBtn = modal.querySelector('#returnHomeBtn');
            if (returnBtn && !returnBtn._wired) {
                returnBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    window.location.href = 'kiosk.php';
                });
                returnBtn._wired = true;
            }

            // Show modal
            modal.style.display = 'block';
            modal.classList.add('show');

            // Focus for accessibility
            setTimeout(function() {
                modal.focus && modal.focus();
            }, 50);

            console.log('Success modal shown');
        }






        // document.getElementById('submitBtn').disabled = false;
        // Remove alert for disabled state, just log for devs
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            if (this.disabled) {
                console.log('Submit button is disabled.');
            } else {
                console.log('Submit button clicked.');
            }
        });
    </script>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reservation Successful!</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <i class="fas fa-check-circle success-icon"></i>
                    <p style="margin: 0;">Your reservation has been submitted successfully.</p>
                </div>
                <p id="successMessage">We will contact you shortly to confirm your reservation.</p>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button class="modal-btn" id="returnHomeBtn" style="background: #000; color: white;">Return to Home</button>
            </div>
        </div>
    </div>

    <!-- Product Removal Confirmation Modal -->
    <div id="productRemovalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Remove Product</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="product-removal-content">
                    <div class="product-image-container">
                        <img id="removalProductImage" src="" alt="Selected Product" class="removal-product-image">
                    </div>
                    <div class="product-details">
                        <h3 id="removalProductName"></h3>
                        <p id="removalProductBrand"></p>
                        <p id="removalProductModel"></p>
                        <p id="removalProductPrice"></p>
                    </div>
                    <div class="removal-message">
                        <i class="fas fa-exclamation-circle warning-icon"></i>
                        <p>Are you sure you want to remove this product from your selection?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel-btn" onclick="hideRemoveConfirmModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="confirmProductRemoval()">Remove Product</button>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Terms and Conditions</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="terms-content">
                    <h3>Terms and Conditions for Product Reservations</h3>
                    <div class="terms-content">
                        <h4>Reservation Fee Structure:</h4>
                        <ul>
                            <li><strong>Products priced ₱1,000 and below:</strong> No reservation fee required</li>
                            <li><strong>Products priced above ₱1,000:</strong> 
                                <ul>
                                    <li>Minimum reservation fee: ₱500 per item</li>
                                    <li>Maximum reservation fee: ₱1,000 per item</li>
                                    <li>Proof of payment is required</li>
                                </ul>
                            </li>
                        </ul>
                        <h4>Payment Terms:</h4>
                        <ul>
                            <li>Reservation fee must be paid within 24 hours of making the reservation</li>
                            <li>Remaining balance must be paid upon product collection</li>
                            <li>Reservation is valid for 7 days from the date of reservation</li>
                            <li>Failure to pay the remaining balance within 7 days will result in cancellation of the reservation</li>
                        </ul>
                        <h4>Reservation Process:</h4>
                        <ul>
                            <li>Submit your reservation with complete and accurate information</li>
                            <li>Pay the required reservation fee (₱500-₱1,000 per item above ₱1,000)</li>
                            <li>Upload proof of payment for verification</li>
                            <li>We will contact you within 24 hours to confirm your reservation</li>
                            <li>Collect your product within 7 days and pay the remaining balance</li>
                        </ul>
                        <h4>Cancellation Policy:</h4>
                        <ul>
                            <li>Reservations can be cancelled within 24 hours without penalty</li>
                            <li>After 24 hours, a 10% cancellation fee will be deducted from the refund</li>
                            <li>No refunds for cancellations after 7 days</li>
                        </ul>
                        <h4>Important Notes:</h4>
                        <ul>
                            <li>Products are reserved on a first-come, first-served basis</li>
                            <li>We reserve the right to cancel reservations if products become unavailable</li>
                            <li>All prices are subject to change without prior notice</li>
                            <li>Valid government-issued ID is required upon product collection</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="closeTermsModal()" style="background: #000; color: white;">Close</button>
            </div>
        </div>
    </div>

    <!-- Selection Limit Modal -->
    <div id="selectionLimitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Selection Limit Reached</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-triangle exclamation-triangle" style="font-size: 64px; color: #ffc107; margin-bottom: 24px;"></i>
                    <h3 style="color: #333; margin-bottom: 16px; font-size: 24px;">Maximum Products Reached</h3>
                    <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
                        You have reached the maximum limit of <strong>5 products</strong> for a single reservation.
                    </p>
                    <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                        To add more products, please remove one of your current selections first.
                    </p>
                    <div class="info-box">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Current Selection: <span id="currentSelectionCount" class="current-count">5</span> products
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="hideSelectionLimitModal()" style="background: #007dd1; color: white;">Got It</button>
            </div>
        </div>
    </div>

    <!-- No Product Selected Modal -->
    <div id="noProductSelectedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Product Selection Required</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-circle warning-icon" style="font-size: 64px; color: #000; margin-bottom: 24px;"></i>
                    <h3 style="color: #000; margin-bottom: 16px; font-size: 24px;">Please select at least one product to reserve.</h3>
                    <p style="color: #000; font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
                        You must select at least one product before proceeding with your reservation.
                    </p>
                </div>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button class="modal-btn" onclick="hideNoProductSelectedModal()" style="background: #000; color: white;">OK</button>
            </div>
        </div>
    </div>

    <!-- Form Validation Error Modal -->
    <div id="formValidationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 10px;"></i>Form Validation Error</h2>
                <span class="close" onclick="hideFormValidationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Please correct the following errors before submitting:</p>
                <ul id="errorList" style="margin-top: 15px; padding-left: 20px; color: #dc3545;"></ul>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="hideFormValidationModal()" style="background: #000; color: white;">Close</button>
            </div>
        </div>
    </div>

    <!-- Existing Reservation Modal -->
    <div id="existingReservationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle" style="color: #007dd1; margin-right: 10px;"></i>Existing Reservation Found</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-clock" style="font-size: 48px; color: #007dd1; margin-bottom: 20px;"></i>
                    <h4 style="color: #333; margin-bottom: 15px;">Reservation Already Exists</h4>
                    <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                        You already have a pending reservation in our system. Please wait for it to be processed or contact our support team for assistance.
                    </p>
                    <div style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 12px; padding: 15px; margin: 20px 0;">
                        <h5 style="color: #333; margin-bottom: 10px;"><i class="fas fa-phone" style="color: #007dd1; margin-right: 8px;"></i>Contact Support</h5>
                        <p style="color: #666; margin: 0;">Phone: <strong>0912-345-6789</strong></p>
                        <p style="color: #666; margin: 5px 0 0 0;">Email: <strong>support@bisligicenter.com</strong></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="text-align: center;">
                <button class="modal-btn" onclick="hideExistingReservationModal()" style="background: #000; color: white;">Close</button>
            </div>
        </div>
    </div>

    <script>
    // Modal functions
    function showTermsModal() {
        console.log('showTermsModal called');
        const modal = document.getElementById('termsModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function closeTermsModal() {
        console.log('closeTermsModal called');
        const modal = document.getElementById('termsModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }
    
    function showSelectionLimitModal() {
        console.log('showSelectionLimitModal called');
        const modal = document.getElementById('selectionLimitModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function hideSelectionLimitModal() {
        console.log('hideSelectionLimitModal called');
        const modal = document.getElementById('selectionLimitModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        showStep(1);
    }
    
    function showNoProductSelectedModal() {
        console.log('showNoProductSelectedModal called');
        const modal = document.getElementById('noProductSelectedModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function hideNoProductSelectedModal() {
        console.log('hideNoProductSelectedModal called');
        const modal = document.getElementById('noProductSelectedModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        showStep(1);
    }
    
    function hideExistingReservationModal() {
        console.log('hideExistingReservationModal called');
        const modal = document.getElementById('existingReservationModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
        showStep(1);
    }
    
    function showFormValidationModal(fields, type = 'missing') {
        console.log('showFormValidationModal called with fields:', fields, 'type:', type);
        const modal = document.getElementById('formValidationModal');
        const errorList = document.getElementById('errorList');
        
        // Clear any previous error highlighting
        clearFieldErrors();
        
        if (errorList) {
            errorList.innerHTML = '';
            
            // Add a header message based on type
            const headerLi = document.createElement('li');
            if (type === 'missing' || type === 'invalid') {
                headerLi.innerHTML = '<strong style="color: #dc3545; font-size: 16px;">Please complete the following required fields:</strong>';
            } else {
                headerLi.innerHTML = '<strong style="color: #dc3545; font-size: 16px;">Please address the following issues:</strong>';
            }
            headerLi.style.marginBottom = '10px';
            errorList.appendChild(headerLi);
            
            const fieldMap = {
                'First Name': 'first_name',
                'Last Name': 'last_name',
                'Region': 'region',
                'Province': 'province',
                'City/Municipality': 'city',
                'Barangay': 'barangay',
                'Contact Number': 'contact_number',
                'Email Address': 'email',
                'Terms and Conditions Agreement': 'user_agreement',
                'Reservation Fee Payment': 'down_payment',
                'Proof of Payment Upload': 'proof_of_payment'
            };

            function highlightField(fieldName) {
                const fieldId = fieldMap[fieldName];
                if (fieldId) {
                    const field = document.getElementById(fieldId);
                    if (field) field.classList.add('error');
                }
            }

            fields.forEach(field => {
                const li = document.createElement('li');
                let message = field;
                
                // Add specific validation messages for invalid fields
                if (type === 'invalid') {
                    switch(field) {
                        case 'Contact Number':
                            message = 'Contact Number (must be exactly 11 digits)';
                            break;
                        case 'Email Address':
                            message = 'Email Address (must be a valid email format)';
                            break;
                    }
                } else if (type === 'other') {
                    switch(field) {
                        case 'Terms and Conditions Agreement':
                            message = 'Terms and Conditions Agreement (you must agree to the terms before submitting)';
                            break;
                        case 'Reservation Fee Payment':
                            message = 'Reservation Fee Payment (minimum ₱500 per product above ₱1,000 is required)';
                            break;
                        case 'Proof of Payment Upload':
                            message = 'Proof of Payment Upload (required for products above ₱1,000)';
                            break;
                        default:
                            message = field;
                    }
                }

                highlightField(field);
                
                li.innerHTML = `<i class="fas fa-exclamation-circle" style="color: #dc3545; margin-right: 8px;"></i>${message}`;
                li.style.marginBottom = '8px';
                errorList.appendChild(li);
            });
            
            // Add a footer message based on type
            const footerLi = document.createElement('li');
            if (type === 'missing' || type === 'invalid') {
                footerLi.innerHTML = '<em style="color: #666; font-size: 14px;">Please complete all required fields before submitting your reservation.</em>';
            } else {
                footerLi.innerHTML = '<em style="color: #666; font-size: 14px;">Please address all issues before submitting your reservation.</em>';
            }
            footerLi.style.marginTop = '15px';
            footerLi.style.borderTop = '1px solid #eee';
            footerLi.style.paddingTop = '10px';
            errorList.appendChild(footerLi);
        }
        
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
    
    function clearFieldErrors() {
        document.querySelectorAll('.form-group input, .form-group textarea, .form-group select').forEach(field => {
            field.classList.remove('error');
        });
    }
    
    function hideFormValidationModal() {
        console.log('hideFormValidationModal called');
        const modal = document.getElementById('formValidationModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }
    
    function hideRemoveConfirmModal() {
        console.log('hideRemoveConfirmModal called');
        const modal = document.getElementById('productRemovalModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }
    
    function confirmProductRemoval() {
        // This function would handle the actual product removal logic
        // For now, just close the modal
        hideRemoveConfirmModal();
    }
    
    // Initialize modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing modal functionality...');
        
        // Handle modal close buttons (excluding success modal which has its own handling)
        document.querySelectorAll('.modal:not(#successModal) .close').forEach(function(closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                console.log('Close button clicked for modal:', this.closest('.modal').id);
                e.preventDefault();
                e.stopPropagation();
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    // Return to step 1 for specific modals
                    if (['existingReservationModal', 'noProductSelectedModal', 'selectionLimitModal'].includes(modal.id)) {
                        showStep(1);
                    }
                }
            });
        });
        
        // Handle modal button clicks
        document.querySelectorAll('.modal:not(#successModal) .modal-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                console.log('Modal button clicked:', this.textContent.trim());
                e.preventDefault();
                e.stopPropagation();
                
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    
                    // Handle specific button actions
                    if (this.textContent.trim() === 'Close') {
                        // Close button - no additional action needed
                    } else if (this.textContent.trim() === 'Got It') {
                        showStep(1);
                    } else if (this.textContent.trim() === 'OK') {
                        showStep(1);
                    } else if (this.textContent.trim() === 'Remove Product') {
                        confirmProductRemoval();
                    } else if (this.textContent.trim() === 'Cancel') {
                        // Cancel button - no additional action needed
                    }
                }
            });
        });
        
        // Prevent modal closing when clicking inside modal content (excluding success modal)
        document.querySelectorAll('.modal:not(#successModal)').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    console.log('Modal background clicked - preventing close');
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
        
        // Prevent ESC key from closing modals (excluding success modal)
        window.addEventListener('keydown', function(e) {
            const anyModalOpen = Array.from(document.querySelectorAll('.modal:not(#successModal)')).some(m => m.style.display === 'block');
            if (anyModalOpen && (e.key === 'Escape' || e.keyCode === 27)) {
                console.log('ESC key pressed - preventing modal close');
                e.preventDefault();
                e.stopPropagation();
            }
        });
        
        // Initialize form validation
        checkFormValidity();
        
        console.log('Modal functionality initialized');
    });
    </script>


</body>
</html>