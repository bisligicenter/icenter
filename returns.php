<?php
session_start();
require_once 'db.php';

// Get database connection
try {
    $pdo = getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Product - iCenter</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
      body { font-family: 'Inter', sans-serif; }
      .toast { transition: all 0.3s; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen">
  <!-- Enhanced Header -->
  <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
    <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6 space-x-2 lg:space-x-4">
      <div class="flex items-center space-x-3 lg:space-x-6">
        <img src="images/iCenter.png" alt="Logo" class="h-12 lg:h-20 w-auto border-2 border-white rounded-lg shadow-lg" />
        <div class="text-xs lg:text-sm text-white flex flex-col space-y-1">
          <span id="currentDate" class="font-semibold text-sm lg:text-lg"></span>
          <div class="text-white/80 text-xs lg:text-sm">
            <i class="ri-time-line mr-1 lg:mr-2"></i>
            <span id="currentTime"></span>
          </div>
        </div>
      </div>
    </div>
  </header>
  <div class="p-3 lg:p-6">
    <!-- Toast -->
    <div id="toast" class="fixed top-4 sm:top-6 right-2 sm:right-6 left-2 sm:left-auto z-50 hidden bg-green-500 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg shadow-lg transition-all duration-300 flex items-center text-sm sm:text-base toast">
      <i class="fas fa-check-circle mr-2"></i>
      <span id="toast-message"></span>
    </div>
    <!-- Card Section -->
    <div class="w-full mx-auto mt-8 px-2 sm:px-4">
      <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-200 overflow-x-auto">
        <div class="flex items-center mb-6">
          <a href="inventory_stocks.php" class="inline-flex items-center text-xs sm:text-sm bg-black text-white rounded-lg px-3 py-2 mr-4">
            <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
          </a>
          <!-- Removed the 'Return Product' heading and icon above the form -->
        </div>
        <form id="returnForm" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div class="md:col-span-2">
              <label for="product_select" class="block font-semibold text-gray-700 mb-2">Select Product*</label>
              <select class="form-select w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="product_select" name="product_id" required>
                <option value="">Choose a product...</option>
                <?php
                try {
                  $query = "SELECT product_id, product, brand, model FROM products WHERE (archived IS NULL OR archived = 0) ORDER BY product";
                  $stmt = $pdo->prepare($query);
                  $stmt->execute();
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $display_text = $row['product'] . ' - ' . $row['brand'] . ' ' . $row['model'];
                    echo "<option value='{$row['product_id']}'>{$display_text}</option>";
                  }
                } catch (PDOException $e) {
                  echo "<option value=''>Error loading products</option>";
                }
                ?>
              </select>
            </div>
            <div>
              <label for="customer_name" class="block font-semibold text-gray-700 mb-2">Customer Name*</label>
              <input type="text" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase" id="customer_name" name="customer_name" placeholder="Enter customer name" required />
            </div>
            <div>
              <label for="contact_number" class="block font-semibold text-gray-700 mb-2">Contact Number*</label>
              <input type="text" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="contact_number" name="contact_number" placeholder="ENTER CONTACT NUMBER" required maxlength="11" />
            </div>
            <div>
              <label for="returned_to" class="block font-semibold text-gray-700 mb-2">Returned To*</label>
              <input type="text" class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase" id="returned_to" name="returned_to" placeholder="Name" required />
            </div>
          </div>
          <div>
            <label for="return_reason" class="block font-semibold text-gray-700 mb-2">Reason</label>
            <textarea class="form-control w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="return_reason" name="reason" rows="3" placeholder="Enter reason for this return (optional)"></textarea>
          </div>
          <div class="flex flex-col sm:flex-row gap-2">
            <button type="submit" class="bg-black hover:bg-gray-900 text-white font-semibold px-6 py-3 rounded-lg flex items-center gap-2 transition-all w-full sm:w-auto"><i class="fas fa-undo"></i>Return Product</button>
            <button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-3 rounded-lg flex items-center gap-2 transition-all w-full sm:w-auto" onclick="window.location.href='inventory_stocks.php'">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<script>
// Date and time for header
function updateTime() {
  const now = new Date();
  const timeString = now.toLocaleTimeString('en-US', {
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
  });
  document.getElementById('currentTime').textContent = timeString;
}
setInterval(updateTime, 1000); updateTime();
function updateDate() {
  const now = new Date();
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', options);
}
setInterval(updateDate, 60000); updateDate();

$(document).ready(function() {
    // Limit contact number to 11 digits
    $('#contact_number').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
    });
    // Force uppercase for customer name and returned to
    $('#customer_name, #returned_to').on('input', function() {
        this.value = this.value.toUpperCase();
    });
    // Handle form submission
    $('#returnForm').submit(function(e) {
        e.preventDefault();
        const formData = {
            product_id: $('#product_select').val(),
            reason: $('#return_reason').val(),
            customer_name: $('#customer_name').val(),
            contact_number: $('#contact_number').val(),
            returned_to: $('#returned_to').val()
        };
        if (!formData.product_id) {
            showToast('Please select a product', 'danger');
            return;
        }
        // Send AJAX request
        $.ajax({
            url: 'process_return.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#returnForm')[0].reset();
                } else {
                    showToast(response.error, 'danger');
                }
            },
            error: function() {
                showToast('Network error occurred', 'danger');
            }
        });
    });
    // Toast notification
    function showToast(message, type) {
        const toast = $('#toast');
        const toastMsg = $('#toast-message');
        toastMsg.text(message);
        if (type === 'success') {
            toast.removeClass('bg-red-500').addClass('bg-green-500');
            toast.find('i').removeClass('fa-exclamation-triangle').addClass('fa-check-circle');
        } else {
            toast.removeClass('bg-green-500').addClass('bg-red-500');
            toast.find('i').removeClass('fa-check-circle').addClass('fa-exclamation-triangle');
        }
        toast.removeClass('hidden');
        setTimeout(function() {
            toast.addClass('hidden');
        }, 4000);
    }
});
</script>
</body>
</html> 