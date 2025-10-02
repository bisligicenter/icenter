<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle SMS sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    $sms_type = $_POST['sms_type'];
    $target_customers = $_POST['target_customers'];
    
    if (empty($message)) {
        $error = "Message is required.";
    } else {
        try {
            // Get customer phone numbers based on target
            $where_clause = "";
            switch ($target_customers) {
                case 'all':
                    $where_clause = "WHERE contact_number IS NOT NULL AND contact_number != ''";
                    break;
                case 'recent':
                    $where_clause = "WHERE contact_number IS NOT NULL AND contact_number != '' AND reservation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case 'high_value':
                    $where_clause = "WHERE contact_number IS NOT NULL AND contact_number != '' AND reservation_fee >= 1000";
                    break;
                case 'new':
                    $where_clause = "WHERE contact_number IS NOT NULL AND contact_number != '' AND reservation_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
            }
            
            $stmt = $conn->prepare("SELECT DISTINCT contact_number FROM reservations $where_clause");
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $sent_count = 0;
            $failed_count = 0;
            
            foreach ($customers as $customer) {
                $phone = $customer['contact_number'];
                
                // Format phone number for SMS
                if (strpos($phone, '09') === 0) {
                    $formatted_phone = $phone;
                } else {
                    $formatted_phone = '09' . $phone;
                }
                
                // Send SMS using a simple HTTP API (you can integrate with SMS providers like Twilio, etc.)
                $sms_result = sendSMS($formatted_phone, $message);
                
                if ($sms_result) {
                    $sent_count++;
                } else {
                    $failed_count++;
                }
            }
            
            // Log the SMS campaign
            $stmt = $conn->prepare("INSERT INTO sms_logs (message, type, target_customers, sent_count, failed_count, sent_by, sent_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$message, $sms_type, $target_customers, $sent_count, $failed_count, $_SESSION['username'] ?? 'admin']);
            
            $success = "SMS campaign sent successfully! Sent: $sent_count, Failed: $failed_count";
            
        } catch (Exception $e) {
            $error = "Error sending SMS: " . $e->getMessage();
        }
    }
}

// Function to send SMS (placeholder - integrate with your SMS provider)
function sendSMS($phone, $message) {
    // This is a placeholder function
    // You should integrate with SMS providers like:
    // - Twilio
    // - Vonage (Nexmo)
    // - MessageBird
    // - Local SMS gateways
    
    // For now, we'll simulate success
    // Replace this with actual SMS API integration
    
    $api_url = "https://your-sms-provider.com/api/send";
    $api_key = "your-api-key";
    
    $data = [
        'to' => $phone,
        'message' => $message,
        'from' => 'Bislig iCenter'
    ];
    
    // Simulate API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // For demo purposes, return true
    // In production, check the actual response
    return $http_code == 200;
}

// Get recent SMS logs
$stmt = $conn->query("SELECT * FROM sms_logs ORDER BY sent_at DESC LIMIT 10");
$recent_sms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Notifications - Admin</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .btn { transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="min-h-screen">
    <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
        <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6">
            <div class="flex items-center space-x-3 lg:space-x-6">
                <img src="images/iCenter.png" alt="Logo" class="h-12 lg:h-20 w-auto border-2 border-white rounded-lg shadow-lg" />
                <span class="text-white text-lg font-semibold">SMS Notifications</span>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-6 lg:p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">SMS Notifications</h1>
            <a href="admin.php" class="btn bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-sm border border-gray-300 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="ri-check-circle-line mr-2"></i><?= $success ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="ri-error-warning-line mr-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- SMS Form -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="flex items-center mb-6">
                    <i class="ri-message-3-line mr-3 text-2xl text-green-600"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Send SMS Campaign</h2>
                </div>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMS Type</label>
                        <select name="sms_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="sale">Sale Alert</option>
                            <option value="new_product">New Product Alert</option>
                            <option value="promotion">Special Offer</option>
                            <option value="reminder">Reminder</option>
                            <option value="general">General Announcement</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Customers</label>
                        <select name="target_customers" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Customers</option>
                            <option value="recent">Recent Customers (Last 30 days)</option>
                            <option value="high_value">High-Value Customers</option>
                            <option value="new">New Customers (Last 7 days)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message (Max 160 characters)</label>
                        <textarea name="message" rows="4" maxlength="160" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter your SMS message..."></textarea>
                        <div class="text-xs text-gray-500 mt-1">
                            <span id="char-count">0</span>/160 characters
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 mb-2">Message Templates</h3>
                        <div class="space-y-2 text-sm">
                            <button type="button" class="template-btn text-blue-600 hover:text-blue-800" data-template="🎉 BIG SALE! Get up to 50% off on selected iPhones. Visit us today! Limited time only.">Sale Template</button><br>
                            <button type="button" class="template-btn text-blue-600 hover:text-blue-800" data-template="📱 NEW ARRIVAL! iPhone 16 Pro now available at Bislig iCenter. Be the first to get yours!">New Product Template</button><br>
                            <button type="button" class="template-btn text-blue-600 hover:text-blue-800" data-template="💝 SPECIAL OFFER! Free screen protector with any iPhone purchase. Valid until this weekend.">Promotion Template</button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="ri-send-plane-line mr-2"></i>Send SMS Campaign
                    </button>
                </form>
            </div>

            <!-- Recent SMS -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="flex items-center mb-6">
                    <i class="ri-history-line mr-3 text-2xl text-blue-600"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Recent SMS Campaigns</h2>
                </div>

                <div class="space-y-4">
                    <?php foreach ($recent_sms as $sms): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-gray-800"><?= ucfirst($sms['type']) ?></h3>
                                <span class="text-xs text-gray-500"><?= date('M j, Y', strtotime($sms['sent_at'])) ?></span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($sms['message']) ?></p>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Target: <?= ucfirst(str_replace('_', ' ', $sms['target_customers'])) ?></span>
                                <span>Sent: <?= $sms['sent_count'] ?>, Failed: <?= $sms['failed_count'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter
        const messageTextarea = document.querySelector('textarea[name="message"]');
        const charCount = document.getElementById('char-count');
        
        messageTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            if (this.value.length > 150) {
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
            }
        });
        
        // Template buttons
        document.querySelectorAll('.template-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                messageTextarea.value = this.dataset.template;
                charCount.textContent = messageTextarea.value.length;
            });
        });
    </script>
</body>
</html> 