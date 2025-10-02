<?php 
session_start();
require_once 'db.php'; 

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get customers for the dropdown from reservations table
try {
    $stmt = $conn->query("SELECT DISTINCT name, email, messenger_psid, contact_number FROM reservations WHERE name IS NOT NULL AND name != '' ORDER BY name ASC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $customers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Customers - Bislig iCenter</title>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .email-form-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .form-input {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
            transform: translateY(-2px);
        }
        
        .customer-list {
            max-height: 300px;
            overflow-y: auto;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #f9fafb;
        }
        
        .customer-item {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .customer-item:last-child {
            border-bottom: none;
        }
        
        .customer-item:hover {
            background: #f3f4f6;
        }
        
        .success-message {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .error-message {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }
        
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f4f6;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="min-h-screen">
        <!-- Main content -->
        <div id="mainContent" class="w-full">
            <!-- Enhanced Header -->
            <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
                <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6 space-x-2 lg:space-x-4">
                    <div class="flex items-center space-x-3 lg:space-x-6">
                        <img src="images/iCenter.png" alt="Logo" class="h-12 lg:h-20 w-auto border-2 border-white rounded-lg shadow-lg" />
                        <div class="text-xs lg:text-sm text-white flex flex-col space-y-1">
                            <span class="font-semibold text-sm lg:text-lg" id="currentDate"></span>
                            <div class="text-white/80 text-xs lg:text-sm">
                                <i class="ri-time-line mr-1 lg:mr-2"></i>
                                <span id="currentTime"></span>
                            </div>
                        </div>
                    </div>
                    
        <div class="flex items-center space-x-4">
            <h1 class="text-white text-xl lg:text-2xl font-bold">Email Customers</h1>
        </div>
                </div>
            </header>

            <!-- Email Form Content -->
            <div class="p-6 lg:p-8">
                <div class="mb-4">
                    <a href="admin.php" class="inline-flex items-center px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors duration-300" title="Back to Dashboard">
                        <i class="ri-arrow-left-line mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
                <div class="max-w-4xl mx-auto">
                    <!-- Success/Error Messages -->
                    <div id="successMessage" class="success-message">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span id="successText"></span>
                    </div>
                    
                    <div id="errorMessage" class="error-message">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span id="errorText"></span>
                    </div>
                    
                    <!-- Email Form -->
                    <div class="email-form-container p-8">
                        <div class="text-center mb-8">
                            <h2 id="formTitle" class="text-2xl font-bold text-gray-900 mb-2">Communicate with Customers</h2>
                            <p id="formSubtitle" class="text-gray-600">Send promotional messages to your customers</p>
                        </div>
                        
                        <form id="emailForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Channel *</label>
                                <div id="channelSelector" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Email Channel -->
                                    <label class="channel-card p-4 border-2 rounded-lg flex items-center cursor-pointer transition-all duration-300" data-channel="email">
                                        <input type="radio" name="channel" value="email" class="hidden" checked>
                                        <i class="ri-mail-line text-3xl text-blue-600 mr-4"></i>
                                        <div>
                                            <h4 class="font-bold text-gray-800">Email</h4>
                                            <p class="text-xs text-gray-500">Classic email communication</p>
                                        </div>
                                    </label>
                                    <!-- Messenger Channel -->
                                    <label class="channel-card p-4 border-2 rounded-lg flex items-center cursor-pointer transition-all duration-300" data-channel="messenger">
                                        <input type="radio" name="channel" value="messenger" class="hidden">
                                        <i class="ri-messenger-line text-3xl text-purple-600 mr-4"></i>
                                        <div>
                                            <h4 class="font-bold text-gray-800">Messenger</h4>
                                            <p class="text-xs text-gray-500">Direct Facebook messages</p>
                                        </div>
                                    </label>
                                    <!-- SMS Channel -->
                                    <label class="channel-card p-4 border-2 rounded-lg flex items-center cursor-pointer transition-all duration-300" data-channel="sms">
                                        <input type="radio" name="channel" value="sms" class="hidden">
                                        <i class="ri-message-2-line text-3xl text-green-600 mr-4"></i>
                                        <div>
                                            <h4 class="font-bold text-gray-800">SMS</h4>
                                            <p class="text-xs text-gray-500">Text messages to phones</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="subjectDiv">
                                <label for="emailSubject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                <select id="emailSubject" name="subject" required 
                                        class="form-input w-full px-4 py-3 rounded-lg focus:outline-none">
                                    <option value="">Select a subject...</option>
                                    <option value="New Product Arrivals">New Product Arrivals</option>
                                    <option value="Special Promotions">Special Promotions</option>
                                    <option value="Holiday Sale">Holiday Sale</option>
                                    <option value="Customer Appreciation">Customer Appreciation</option>
                                    <option value="Technical Support">Technical Support</option>
                                    <option value="Store Updates">Store Updates</option>
                                    <option value="Custom Message">Custom Message</option>
                                </select>
                            </div>
                            
                            <div id="customSubjectDiv" class="hidden">
                                <label for="customSubject" class="block text-sm font-medium text-gray-700 mb-2">Custom Subject *</label>
                                <input type="text" id="customSubject" name="customSubject" 
                                       class="form-input w-full px-4 py-3 rounded-lg focus:outline-none" 
                                       placeholder="Enter custom subject">
                            </div>
                            
                            <div>
                                <label for="emailMessage" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                                <textarea id="emailMessage" name="message" rows="8" required 
                                          class="form-input w-full px-4 py-3 rounded-lg focus:outline-none resize-none" 
                                          placeholder="Select a subject to auto-populate the message..."></textarea>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="sendToAll" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span id="sendToAllLabel" class="text-sm text-gray-700">Send to all customers</span>
                                </label>
                            </div>
                            
                            <div id="customerSelection">
                                <div class="flex justify-between items-center mb-2">
                                    <label id="customerSelectionLabel" class="block text-sm font-medium text-gray-700">Select Customers</label>
                                    <button type="button" id="toggleSelectAll" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">Select All Available</button>
                                </div>
                                <div class="customer-list p-4 mt-2">
                                    <div class="mb-2 text-xs text-gray-500">
                                        <span id="customerCount"></span>
                                    </div>
                                    <div id="customerListContainer" class="space-y-2">
                                        <!-- Customer items will be dynamically inserted here -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-4 pt-6">
                                <a href="admin.php" class="btn-secondary px-6 py-3 text-white rounded-lg font-medium">
                                    Cancel
                                </a>
                                <button type="submit" id="sendBtn" class="btn-primary px-6 py-3 text-white rounded-lg font-medium shadow-md">
                                    <span class="flex items-center">
                                        <i id="sendIcon" class="ri-mail-send-line mr-2"></i>
                                        <span id="sendText">Send Email</span>
                                        <div id="loadingSpinner" class="loading-spinner ml-2"></div>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Confirmation Modal -->
    <div id="sendConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                    <i id="confirmModalIcon" class="fas fa-envelope text-blue-600 text-xl"></i>
                </div>
                <h3 id="confirmModalTitle" class="text-xl font-bold mb-4 text-gray-900">Confirm Email Send</h3>
                <p class="mb-6 text-gray-600" id="confirmMessage">Are you sure you want to send this message?</p>
                <div class="flex justify-center space-x-4">
                    <button id="cancelSend" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-all duration-300 font-medium">
                        Cancel
                    </button>
                    <button id="confirmSend" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg" data-channel="email">
                        Send Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 id="successModalTitle" class="text-xl font-bold mb-4 text-green-700">Message Sent Successfully!</h3>
                <p class="mb-6 text-gray-600" id="successModalMessage">Your message has been sent successfully.</p>
                <button id="closeSuccessModal" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg">
                    OK
                </button>
            </div>
        </div>
    </div>
    <script>const allCustomers = <?= json_encode($customers) ?>;</script>

    <script>
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

        setInterval(updateTime, 1000);
        updateTime();

        // Email form functionality
        document.addEventListener('DOMContentLoaded', function() {
            const emailForm = document.getElementById('emailForm');
            const emailSubject = document.getElementById('emailSubject');
            const customSubjectDiv = document.getElementById('customSubjectDiv');
            const customSubject = document.getElementById('customSubject');
            const emailMessage = document.getElementById('emailMessage');
            const subjectDiv = document.getElementById('subjectDiv');
            const sendToAllCheckbox = document.getElementById('sendToAll');
            const customerSelection = document.getElementById('customerSelection');
            const sendBtn = document.getElementById('sendBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const sendText = document.getElementById('sendText');
            const sendIcon = document.getElementById('sendIcon');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            const successText = document.getElementById('successText');
            const errorText = document.getElementById('errorText');
            const channelCards = document.querySelectorAll('.channel-card');
            const customerListContainer = document.getElementById('customerListContainer');
            const customerCountSpan = document.getElementById('customerCount');
            const sendToAllLabel = document.getElementById('sendToAllLabel');
            const toggleSelectAllBtn = document.getElementById('toggleSelectAll');
            const customerSelectionLabel = document.getElementById('customerSelectionLabel');
            
            // Modal elements
            const sendConfirmModal = document.getElementById('sendConfirmModal');
            const successModal = document.getElementById('successModal');
            const confirmMessage = document.getElementById('confirmMessage');
            const confirmModalIcon = document.getElementById('confirmModalIcon');
            const confirmModalTitle = document.getElementById('confirmModalTitle');
            const successModalMessage = document.getElementById('successModalMessage');
            const successModalTitle = document.getElementById('successModalTitle');
            const cancelSendBtn = document.getElementById('cancelSend');
            const confirmSendBtn = document.getElementById('confirmSend');
            const closeSuccessModalBtn = document.getElementById('closeSuccessModal');
            
            // Store email data for confirmation
            let pendingMessageData = null;

            // Predefined messages for each subject
            const predefinedMessages = {
                'New Product Arrivals': `Dear Valued Customer,

We're excited to announce that we have new products available at Bislig iCenter! 

Our latest arrivals include:
• Latest smartphones and tablets
• High-quality accessories
• Gaming devices and equipment
• Professional audio equipment

Visit our store today to check out these amazing new products. Our knowledgeable staff is ready to help you find the perfect device for your needs.

Best regards,
Bislig iCenter Team`,

                'Special Promotions': `Dear Valued Customer,

Don't miss out on our exclusive special promotions at Bislig iCenter!

🎉 LIMITED TIME OFFERS:
• Up to 30% off on selected smartphones
• Buy 1 Get 1 on accessories
• Free screen protector with any phone purchase
• Special student discounts available

These offers are available for a limited time only. Visit our store or contact us to learn more about these amazing deals.

Best regards,
Bislig iCenter Team`,

                'Holiday Sale': `Dear Valued Customer,

Happy Holidays from Bislig iCenter! 🎄

We're spreading holiday cheer with our special holiday sale:

🎁 HOLIDAY SPECIALS:
• Massive discounts on all electronics
• Gift bundles with free accessories
• Extended warranty offers
• Special financing options

Make this holiday season special with the latest technology. Visit our store today!

Best regards,
Bislig iCenter Team`,

                'Customer Appreciation': `Dear Valued Customer,

Thank you for choosing Bislig iCenter! We truly appreciate your continued support and trust in our services.

As a token of our appreciation, we're offering:
• Exclusive loyalty discounts
• Priority customer service
• Early access to new products
• Special member-only events

We look forward to serving you again and providing the best technology solutions for your needs.

Best regards,
Bislig iCenter Team`,

                'Technical Support': `Dear Valued Customer,

We're here to help with any technical support you may need!

Our technical support services include:
• Device troubleshooting
• Software installation and updates
• Data recovery services
• Hardware repairs and maintenance
• Technical consultation

Contact our technical support team for assistance with any device-related issues.

Best regards,
Bislig iCenter Technical Support Team`,

                'Store Updates': `Dear Valued Customer,

We have some exciting updates about Bislig iCenter!

📢 STORE UPDATES:
• Extended business hours
• New product categories
• Enhanced customer service
• Improved store layout
• New payment options available

We're constantly working to improve your shopping experience. Visit us to see all the new improvements!

Best regards,
Bislig iCenter Team`
            };

            // Handle subject dropdown change
            if (emailSubject) {
                emailSubject.addEventListener('change', function() {
                    const selectedSubject = this.value;
                    
                    if (selectedSubject === 'Custom Message') {
                        customSubjectDiv.classList.remove('hidden');
                        emailMessage.value = '';
                        emailMessage.placeholder = 'Enter your custom message here...';
                    } else if (selectedSubject) {
                        customSubjectDiv.classList.add('hidden');
                        emailMessage.value = predefinedMessages[selectedSubject] || '';
                        emailMessage.placeholder = 'Message auto-populated based on selected subject';
                    } else {
                        customSubjectDiv.classList.add('hidden');
                        emailMessage.value = '';
                        emailMessage.placeholder = 'Select a subject to auto-populate the message...';
                    }
                });
            }

            // Handle send to all checkbox
            if (sendToAllCheckbox) {
                sendToAllCheckbox.addEventListener('change', function() {
                    customerSelection.classList.toggle('hidden', this.checked);
                });
            }

            // Handle "Select All" button
            if (toggleSelectAllBtn) {
                toggleSelectAllBtn.addEventListener('click', () => {
                    const checkboxes = customerListContainer.querySelectorAll('input[type="checkbox"]:not(:disabled)');
                    // Are all *available* checkboxes currently checked?
                    const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);

                    checkboxes.forEach(cb => {
                        cb.checked = !allChecked;
                    });

                    toggleSelectAllBtn.textContent = allChecked ? 'Select All Available' : 'Deselect All';
                });
            }

            // Handle form submission
            if (emailForm) {
                emailForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const channel = document.querySelector('input[name="channel"]:checked').value;
                    const selectedSubject = emailSubject.value;
                    const customSubjectValue = customSubject.value.trim();
                    const message = emailMessage.value.trim();
                    const sendToAll = sendToAllCheckbox.checked;
                    
                    // Determine the final subject
                    let finalSubject = (channel === 'email') ? selectedSubject : '';
                    if (selectedSubject === 'Custom Message') {
                        if (!customSubjectValue) {
                            showError('Please enter a custom subject.');
                            return;
                        }
                        finalSubject = customSubjectValue;
                    }
                    
                    if ((channel === 'email' && !finalSubject) || !message) {
                        showError('Please fill in all required fields.');
                        return;
                    }

                    // Get selected customers
                    let selectedCustomers = [];
                    if (!sendToAll) {
                        const checkboxes = customerSelection.querySelectorAll('input[type="checkbox"]:checked');
                        selectedCustomers = Array.from(checkboxes).map(cb => cb.value);
                        
                        if (selectedCustomers.length === 0) {
                            showError('Please select at least one customer.');
                            return;
                        }
                    }

                    // Store email data for confirmation
                    pendingMessageData = {
                        channel: channel,
                        subject: finalSubject,
                        message: message,
                        sendToAll: sendToAll,
                        selectedCustomers: selectedCustomers
                    };

                    showConfirmModal(channel, finalSubject, sendToAll, selectedCustomers.length);
                });
            }

            // Show confirmation modal
            function showConfirmModal(channel, subject, sendToAll, customerCount) {
                const channelName = channel.charAt(0).toUpperCase() + channel.slice(1);
                let message = `Are you sure you want to send this ${channelName} message?\n\n`;
                if (channel === 'email') {
                    message += `Subject: ${subject}\n`;
                }
                message += `Recipients: ${sendToAll ? 'All available customers' : customerCount + ' selected customers'}`;
                
                confirmMessage.textContent = message;
                confirmModalTitle.textContent = `Confirm ${channelName} Send`;
                let iconClass = 'fa-envelope'; // Default for email
                if (channel === 'messenger') {
                    iconClass = 'fa-paper-plane';
                } else if (channel === 'sms') {
                    iconClass = 'fa-comment-dots';
                }
                confirmModalIcon.className = `fas text-blue-600 text-xl ${iconClass}`;
                confirmSendBtn.textContent = `Send ${channelName}`;
                confirmSendBtn.dataset.channel = channel;
                sendConfirmModal.classList.remove('hidden');
            }

            // Send email function
            function sendMessage(data) {
                let endpoint;
                if (data.channel === 'email') {
                    endpoint = 'send_email.php';
                } else if (data.channel === 'messenger') {
                    endpoint = 'send_messenger.php';
                } else { // SMS
                    endpoint = 'send_sms.php';
                }

                // Show loading state
                setLoadingState(true);
                
                // Hide confirmation modal
                sendConfirmModal.classList.add('hidden');
                
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    setLoadingState(false);
                    if (data.success) {
                        showSuccessModal(pendingMessageData.channel, data.message);
                        emailForm.reset();
                        sendToAllCheckbox.checked = true;
                        sendToAllCheckbox.dispatchEvent(new Event('change'));
                        updateCustomerList(document.querySelector('input[name="channel"]:checked').value);
                    } else {
                        showError(data.message || 'Failed to send email.');
                    }
                })
                .catch(error => {
                    setLoadingState(false);
                    console.error('Error sending email:', error);
                    showError('An error occurred while sending the message.');
                });
            }

            // Set loading state
            function setLoadingState(loading) {
                if (loading) {
                    sendBtn.disabled = true;
                    loadingSpinner.style.display = 'block';
                    sendText.textContent = 'Sending...';
                } else {
                    sendBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                    const channel = document.querySelector('input[name="channel"]:checked').value;
                    updateSendButton(channel);
                }
            }

            // Show success modal
            function showSuccessModal(channel, message) {
                const channelName = channel.charAt(0).toUpperCase() + channel.slice(1);
                successModalTitle.textContent = `${channelName} Sent Successfully!`;
                successModalMessage.textContent = message;
                successModal.classList.remove('hidden');
            }


            // Show success message (for inline messages)
            function showSuccess(message) {
                successText.textContent = message;
                successMessage.style.display = 'block';
                errorMessage.style.display = 'none';
                
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 5000);
            }

            // Show error message
            function showError(message) {
                errorText.textContent = message;
                errorMessage.style.display = 'block';
                successMessage.style.display = 'none';
                
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 5000);
            }

            // Modal event handlers
            if (cancelSendBtn) {
                cancelSendBtn.addEventListener('click', function() {
                    sendConfirmModal.classList.add('hidden');
                    pendingMessageData = null;
                });
            }

            if (confirmSendBtn) {
                confirmSendBtn.addEventListener('click', function() {
                    if (pendingMessageData) {
                        sendMessage(pendingMessageData);
                    }
                });
            }

            if (closeSuccessModalBtn) {
                closeSuccessModalBtn.addEventListener('click', function() {
                    successModal.classList.add('hidden');
                });
            }

            // Close modals when clicking outside
            sendConfirmModal.addEventListener('click', function(e) {
                if (e.target === sendConfirmModal) {
                    sendConfirmModal.classList.add('hidden');
                    pendingMessageData = null;
                }
            });

            successModal.addEventListener('click', function(e) {
                if (e.target === successModal) {
                    successModal.classList.add('hidden');
                }
            });

            // Enhanced Channel Selector Logic
            channelCards.forEach(card => {
                card.addEventListener('click', () => {
                    // Unselect all cards
                    channelCards.forEach(c => {
                        c.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-md');
                        c.classList.add('border-gray-200', 'bg-white', 'hover:bg-gray-50');
                    });
                    // Select the clicked card
                    card.classList.add('border-blue-500', 'bg-blue-50', 'shadow-md');
                    card.classList.remove('border-gray-200', 'bg-white', 'hover:bg-gray-50');
                    
                    // Check the hidden radio button
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        updateUIForChannel(radio.value);
                    }
                });
            });

            function setInitialChannelState() {
                const selectedCard = document.querySelector('.channel-card input[type="radio"]:checked').closest('.channel-card');
                channelCards.forEach(c => {
                    c.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-md');
                    c.classList.add('border-gray-200', 'bg-white', 'hover:bg-gray-50');
                });
                if (selectedCard) {
                    selectedCard.classList.add('border-blue-500', 'bg-blue-50', 'shadow-md');
                    selectedCard.classList.remove('border-gray-200', 'bg-white', 'hover:bg-gray-50');
                }
            }

            function updateUIForChannel(channel) {
                const isEmail = channel === 'email';
                const isSms = channel === 'sms';
                subjectDiv.style.display = isEmail ? 'block' : 'none';
                customSubjectDiv.classList.add('hidden');
                emailSubject.value = '';
                emailSubject.required = isEmail;
                
                if (isEmail) {
                    emailMessage.placeholder = 'Select a subject to auto-populate the message...';
                } else if (isSms) {
                    emailMessage.placeholder = 'Enter your SMS message here (max 160 characters)...';
                } else {
                    emailMessage.placeholder = 'Enter your Messenger message here...';
                }
                emailMessage.value = '';
                updateSendButton(channel);
                updateCustomerList(channel);
            }

            function updateSendButton(channel) {
                const channelName = channel.charAt(0).toUpperCase() + channel.slice(1);
                sendText.textContent = `Send ${channelName}`;
                let iconClass = 'ri-mail-send-line'; // default to email
                if (channel === 'messenger') {
                    iconClass = 'ri-send-plane-2-line';
                } else if (channel === 'sms') {
                    iconClass = 'ri-message-3-line';
                }
                sendIcon.className = `mr-2 ${iconClass}`;
            }

            function updateCustomerList(channel) {
                customerListContainer.innerHTML = '';
                const isEmail = channel === 'email';
                const isMessenger = channel === 'messenger';
                const isSms = channel === 'sms';

                // Always show all customers from reservations
                const allReservationCustomers = allCustomers;

                const eligibleCustomerCount = allReservationCustomers.filter(c => {
                    if (isEmail) return c.email && c.email !== '';
                    if (isMessenger) return c.messenger_psid && c.messenger_psid !== '';
                    if (isSms) return c.contact_number && c.contact_number !== '';
                    return false;
                }).length;

                customerCountSpan.textContent = `Showing ${allReservationCustomers.length} total customers. ${eligibleCustomerCount} available for ${channel}.`;
                sendToAllLabel.textContent = `Send to all ${eligibleCustomerCount} available ${channel} customers`;
                customerSelectionLabel.textContent = `Select Customers (All reservations)`;

                if (allReservationCustomers.length === 0) {
                    customerListContainer.innerHTML = `<div class="text-center py-4 text-gray-500">
                        <i class="fas fa-users text-2xl mb-2"></i>
                        <p>No customers found in reservations.</p>
                    </div>`;
                    return;
                }

                allReservationCustomers.forEach(customer => {
                    let value = '', detail = '', isEligible = false, disabledAttr = 'disabled', itemClass = 'opacity-50 cursor-not-allowed';

                    if (isEmail) {
                        isEligible = customer.email && customer.email !== '';
                        value = isEligible ? customer.email : '';
                        detail = customer.email || 'No email address';
                    } else if (isMessenger) {
                        isEligible = customer.messenger_psid && customer.messenger_psid !== '';
                        value = isEligible ? customer.messenger_psid : '';
                        detail = customer.messenger_psid ? `PSID: ${customer.messenger_psid.substring(0, 10)}...` : 'No Messenger ID';
                    } else { // SMS
                        isEligible = customer.contact_number && customer.contact_number !== '';
                        value = isEligible ? customer.contact_number : '';
                        detail = customer.contact_number || 'No phone number';
                    }

                    if (isEligible) {
                        disabledAttr = '';
                        itemClass = 'hover:bg-blue-50';
                    }

                    const itemHTML = `
                        <div class="customer-item p-3 rounded-lg transition-colors duration-200 ${itemClass}">
                            <label class="flex items-center space-x-3 ${isEligible ? 'cursor-pointer' : ''}">
                                <input type="checkbox" value="${htmlspecialchars(value)}" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" ${disabledAttr}>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">${htmlspecialchars(customer.name)}</div>
                                    <div class="text-sm text-gray-500">${htmlspecialchars(detail)}</div>
                                </div>
                            </label>
                        </div>`;
                    customerListContainer.insertAdjacentHTML('beforeend', itemHTML);
                });

                // Reset the 'Select All' button text
                if (toggleSelectAllBtn) {
                    toggleSelectAllBtn.textContent = 'Select All Available';
                }
            }
            
            function htmlspecialchars(str) {
                return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function channelName(channel) {
                if (!channel) return '';
                return channel.charAt(0).toUpperCase() + channel.slice(1);
            }

            // Initial UI setup
            setInitialChannelState();
            updateUIForChannel('email');
        });
    </script>
</body>
</html> 