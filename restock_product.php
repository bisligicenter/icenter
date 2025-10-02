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
    <title>Restock Product - iCenter</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="header-section">
        <!-- Logo Container -->
        <div class="logo-container">
            <img src="images/iCenter.png" alt="iCenter Logo" class="logo-img">
        </div>
        
        <!-- Back Button -->
        <div class="back-button-container">
            <a href="inventory_stocks.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Inventory</span>
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h2 class="card-title">
                        <i class="fas fa-plus-circle"></i>
                        Restock Product
                    </h2>
                    <p class="card-subtitle">Add more stock to existing products</p>
                </div>
                
                <div class="card-body">
                    <form id="restockForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_select" class="form-label">Select Product*</label>
                                    <select class="form-select" id="product_select" name="product_id" required>
                                        <option value="">Choose a product...</option>
                                        <?php
                                        try {
                                            $query = "SELECT product_id, product, brand, model, stock_quantity FROM products WHERE (archived IS NULL OR archived = 0) ORDER BY product";
                                            $stmt = $pdo->prepare($query);
                                            $stmt->execute();
                                            
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $display_text = $row['product'] . ' - ' . $row['brand'] . ' ' . $row['model'] . ' (Current Stock: ' . $row['stock_quantity'] . ')';
                                                echo "<option value='{$row['product_id']}' data-current-stock='{$row['stock_quantity']}'>{$display_text}</option>";
                                            }
                                        } catch (PDOException $e) {
                                            echo "<option value=''>Error loading products</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity_to_add" class="form-label">Quantity to Add*</label>
                                    <input type="number" class="form-control" id="quantity_to_add" name="quantity" min="1" required placeholder="Enter quantity">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_stock" class="form-label">Current Stock</label>
                                    <input type="text" class="form-control" id="current_stock" readonly value="0">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="new_stock" class="form-label">New Stock (After Restock)</label>
                                    <input type="text" class="form-control" id="new_stock" readonly value="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="restock_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="restock_notes" name="notes" rows="3" placeholder="Enter notes about this restock (optional)"></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i>
                                Restock Product
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='inventory_stocks.php'">
                                Cancel
                            </button>
                        </div>
                    </form>
                    
                    <div id="message" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern CSS Variables */
:root {
    --primary-color: #2563eb;
    --primary-dark: #1d4ed8;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #06b6d4;
    --light-color: #f8fafc;
    --dark-color: #1e293b;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --border-radius: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Base Styles */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #fff;
    min-height: 100vh;
    color: var(--dark-color);
    line-height: 1.6;
}

/* Container */
.container-fluid {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header Section */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px 0;
}

.logo-container {
    display: flex;
    align-items: center;
}

.logo-img {
    height: 80px;
    width: auto;
}

.back-button-container {
    display: flex;
    align-items: center;
}

/* Card Styles */
.card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, var(--success-color), #059669);
    color: white;
    padding: 30px;
    border: none;
}

.card-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-subtitle {
    margin: 8px 0 0 0;
    opacity: 0.9;
    font-size: 1rem;
}

.card-body {
    padding: 30px;
}

/* Form Styles */
.form-label {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 8px;
}

.form-control, .form-select {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 1rem;
    transition: var(--transition);
}

.form-control:focus, .form-select:focus {
    border-color: var(--success-color);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    outline: none;
}

.form-control[readonly] {
    background-color: #f8fafc;
    color: var(--secondary-color);
}

/* Button Styles */
.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-success {
    background: var(--success-color);
    border-color: var(--success-color);
}

.btn-success:hover {
    background: #059669;
    border-color: #059669;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Message Styles */
.alert {
    border-radius: 8px;
    border: none;
    padding: 16px 20px;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
}
</style>

<script>
$(document).ready(function() {
    // Update stock calculations when product is selected
    $('#product_select').change(function() {
        const selectedOption = $(this).find('option:selected');
        const currentStock = parseInt(selectedOption.data('current-stock')) || 0;
        
        $('#current_stock').val(currentStock);
        updateNewStock();
    });
    
    // Update new stock calculation when quantity changes
    $('#quantity_to_add').on('input', function() {
        updateNewStock();
    });
    
    function updateNewStock() {
        const currentStock = parseInt($('#current_stock').val()) || 0;
        const quantityToAdd = parseInt($('#quantity_to_add').val()) || 0;
        const newStock = currentStock + quantityToAdd;
        
        $('#new_stock').val(newStock);
    }
    
    // Handle form submission
    $('#restockForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            product_id: $('#product_select').val(),
            quantity: parseInt($('#quantity_to_add').val()),
            notes: $('#restock_notes').val(),
            current_stock: parseInt($('#current_stock').val()),
            new_stock: parseInt($('#new_stock').val())
        };
        
        if (!formData.product_id) {
            showMessage('Please select a product', 'danger');
            return;
        }
        
        if (!formData.quantity || formData.quantity <= 0) {
            showMessage('Please enter a valid quantity', 'danger');
            return;
        }
        
        // Send AJAX request
        $.ajax({
            url: 'process_restock.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $('#restockForm')[0].reset();
                    $('#current_stock').val('0');
                    $('#new_stock').val('0');
                    
                    // Update the product option to reflect new stock
                    const option = $('#product_select option:selected');
                    const newStock = formData.new_stock;
                    const displayText = option.text().replace(/\(Current Stock: \d+\)/, `(Current Stock: ${newStock})`);
                    option.text(displayText);
                    option.data('current-stock', newStock);
                } else {
                    showMessage(response.error, 'danger');
                }
            },
            error: function() {
                showMessage('Network error occurred', 'danger');
            }
        });
    });
    
    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        
        $('#message').html(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    }
});
</script>

</body>
</html> 