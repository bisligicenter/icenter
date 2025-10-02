<?php
session_start(); // Start the session

// Check if the user is logged in
if (
    (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) &&
    (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true)
) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'db.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  <title>Reserved Products - Admin Dashboard</title>
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
  <link rel="icon" type="image/png" href="images/iCenter.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Reset & base */
    * {
      box-sizing: border-box;
    }
    /* Body styling */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
      color: #333;
      -webkit-font-smoothing: antialiased;
      min-height: 100vh;
    }
    
    /* Enhanced Header - matching admin.php styling */
    header {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      will-change: transform, margin-left;
    }
    
    .status-badge {
      @apply px-3 py-1.5 rounded-full text-sm font-semibold transition-all duration-200;
    }
    .status-completed {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #34d399;
    }
    .status-not_completed {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #f87171;
    }
    .row-highlight {
      animation: highlightRow 1s;
    }
    @keyframes highlightRow {
      0% { background-color: #fef9c3; }
      100% { background-color: transparent; }
    }
    .modal-bg {
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(4px);
    }
    .table-container {
      max-height: calc(100vh - 250px);
      overflow-y: auto;
    }
    .table-container table {
      border-collapse: collapse;
    }
    .table-container th,
    .table-container td {
      padding: 6px 8px;
      border: 1px solid #e5e7eb;
    }
    .table-container th {
      background-color: #f3f4f6;
      font-weight: 600;
      text-align: left;
    }
    .table-container::-webkit-scrollbar {
      width: 8px;
    }
    .table-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb:hover {
      background: #555;
    }
    @media print {
      body * {
        visibility: hidden;
      }
      .table-container, .table-container * {
        visibility: visible;
      }
      .table-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .table-container table {
        width: 100%;
      }
      .table-container th {
        background-color: #f3f4f6 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .table-container td {
        border: 1px solid #e5e7eb !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      .status-badge {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
      button, select, input {
        display: none !important;
      }
    }
    .tooltip {
      position: relative;
      display: inline-block;
    }
    .tooltip .tooltiptext {
      visibility: hidden;
      width: 200px;
      background-color: #333;
      color: #fff;
      text-align: center;
      border-radius: 6px;
      padding: 8px;
      position: absolute;
      z-index: 1;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      opacity: 0;
      transition: opacity 0.3s;
    }
    .tooltip:hover .tooltiptext {
      visibility: visible;
      opacity: 1;
    }
    /* Custom Scrollbar Styles */
    .custom-scrollbar::-webkit-scrollbar {
      width: 8px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #555;
    }
    
    /* For Firefox */
    .custom-scrollbar {
      scrollbar-width: thin;
      scrollbar-color: #888 #f1f1f1;
    }

    /* Image Modal Styles */
    #imgModal .modal-bg {
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(4px);
    }
    
    #imgModal img {
      transform-origin: center;
      cursor: move;
    }
    
    #imgModal .zoom-controls button {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
    }
    
    #imgModal .zoom-controls button:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    /* Enhanced Modal Styles */
    .modal-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border: 1px solid #e2e8f0;
    }
    
    /* Gradient Backgrounds */
    .gradient-blue {
      background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    }
    
    .gradient-green {
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    }
    
    .gradient-orange {
      background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    }
    
    .gradient-purple {
      background: linear-gradient(135deg, #e9d5ff 0%, #c4b5fd 100%);
    }
    
    .gradient-gray {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    }
    
    /* Enhanced Button Styles */
    .action-btn {
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .action-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .action-btn:hover::before {
      left: 100%;
    }
    
    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    /* Product Card Enhancements */
    .product-card {
      transition: all 0.3s ease;
      position: relative;
    }
    
    .product-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #3b82f6, #8b5cf6);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    
    .product-card:hover::before {
      transform: scaleX(1);
    }
    
    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    /* Status Badge Enhancements */
    .status-badge {
      position: relative;
      overflow: hidden;
    }
    
    .status-badge::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }
    
    .status-badge:hover::after {
      left: 100%;
    }
    
    /* Responsive Design - Mobile First Approach */
    
    /* Extra Small Devices (phones, 576px and down) */
    @media (max-width: 575.98px) {
      body {
        padding: 0.5rem !important;
      }
      
      /* Header Responsive */
      header {
        padding: 0.5rem !important;
      }
      
      .header-logo {
        height: 50px;
        margin-right: 0.5rem !important;
      }
      
      .header-date {
        font-size: 14px;
      }
      
      .header-time {
        font-size: 12px;
      }
      
      /* Stats Cards */
      .grid.grid-cols-1.md\\:grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      .grid.grid-cols-1.md\\:grid-cols-3 > div {
        padding: 1rem;
      }
      
      .grid.grid-cols-1.md\\:grid-cols-3 h3 {
        font-size: 1.5rem;
      }
      
      /* Table Responsive */
      .table-container {
        max-height: calc(100vh - 400px);
        overflow-x: auto;
      }
      
      .table-container table {
        min-width: 800px;
        font-size: 12px;
      }
      
      .table-container th,
      .table-container td {
        padding: 4px 6px;
        white-space: nowrap;
      }
      
      /* Search and Filter */
      .flex.justify-between.items-center {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
      
      .flex.items-center.space-x-4 {
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
      }
      
      #searchInput,
      #statusFilter {
        width: 100%;
        font-size: 14px;
      }
      
      /* Buttons */
      .view-archived-btn {
        width: 100%;
        justify-content: center;
        font-size: 14px;
        padding: 0.75rem;
      }
      
      /* Modal Responsive */
      .modal-card {
        margin: 0.5rem;
        max-width: calc(100vw - 1rem);
        padding: 1rem;
      }
      
      #viewModal .grid.grid-cols-1.lg\\:grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      #viewModal .max-w-6xl {
        max-width: calc(100vw - 1rem);
        margin: 0.5rem;
      }
      
      #viewModal .p-8 {
        padding: 1rem;
      }
      
      /* Action Buttons in Table */
      .flex.space-x-2.justify-center {
        flex-direction: column;
        gap: 0.25rem;
      }
      
      .flex.space-x-2.justify-center button {
        width: 100%;
        font-size: 11px;
        padding: 0.25rem 0.5rem;
      }
      
      /* Status Dropdown */
      select[name^="status_"] {
        font-size: 11px;
        padding: 0.25rem;
        margin-top: 0.25rem;
      }
      
      /* Toast */
      #toast {
        top: 1rem;
        right: 1rem;
        left: 1rem;
        max-width: none;
        font-size: 14px;
      }
    }
    
    /* Small Devices (landscape phones, 576px and up) */
    @media (min-width: 576px) and (max-width: 767.98px) {
      body {
        padding: 1rem !important;
      }
      
      .header-logo {
        height: 60px;
      }
      
      .grid.grid-cols-1.md\\:grid-cols-3 {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }
      
      .table-container {
        max-height: calc(100vh - 350px);
      }
      
      .table-container table {
        font-size: 13px;
      }
      
      .flex.justify-between.items-center {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }
      
      .flex.items-center.space-x-4 {
        flex-direction: row;
        gap: 0.5rem;
        width: 100%;
      }
      
      #searchInput {
        flex: 1;
      }
      
      #statusFilter {
        min-width: 120px;
      }
      
      .modal-card {
        margin: 1rem;
        max-width: calc(100vw - 2rem);
      }
      
      #viewModal .grid.grid-cols-1.lg\\:grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
    }
    
    /* Medium Devices (tablets, 768px and up) */
    @media (min-width: 768px) and (max-width: 991.98px) {
      .header-logo {
        height: 70px;
      }
      
      .grid.grid-cols-1.md\\:grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
      }
      
      .table-container {
        max-height: calc(100vh - 300px);
      }
      
      .table-container table {
        font-size: 14px;
      }
      
      .modal-card {
        margin: 1.5rem;
        max-width: calc(100vw - 3rem);
      }
      
      #viewModal .grid.grid-cols-1.lg\\:grid-cols-3 {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
      }
    }
    
    /* Large Devices (desktops, 992px and up) */
    @media (min-width: 992px) and (max-width: 1199.98px) {
      .header-logo {
        height: 75px;
      }
      
      .table-container {
        max-height: calc(100vh - 280px);
      }
      
      .modal-card {
        margin: 2rem;
        max-width: calc(100vw - 4rem);
      }
      
      #viewModal .grid.grid-cols-1.lg\\:grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
      }
    }
    
    /* Extra Large Devices (large desktops, 1200px and up) */
    @media (min-width: 1200px) {
      .header-logo {
        height: 80px;
      }
      
      .table-container {
        max-height: calc(100vh - 250px);
      }
      
      .modal-card {
        margin: 2rem;
        max-width: calc(100vw - 4rem);
      }
      
      #viewModal .grid.grid-cols-1.lg\\:grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
      }
    }
    
    /* Landscape Orientation Adjustments */
    @media (orientation: landscape) and (max-height: 600px) {
      .table-container {
        max-height: calc(100vh - 200px);
      }
      
      .modal-card {
        max-height: calc(100vh - 2rem);
      }
      
      #viewModal .max-h-\\[90vh\\] {
        max-height: calc(100vh - 2rem);
      }
    }
    
    /* Touch Device Optimizations */
    @media (hover: none) and (pointer: coarse) {
      .action-btn:hover {
        transform: none;
        box-shadow: none;
      }
      
      .product-card:hover {
        transform: none;
        box-shadow: none;
      }
      
      .status-badge:hover::after {
        left: -100%;
      }
      
      /* Increase touch targets */
      button, select, input {
        min-height: 44px;
      }
      
      .table-container th,
      .table-container td {
        padding: 8px 12px;
      }
      
      .flex.space-x-2.justify-center button {
        min-height: 44px;
        padding: 0.5rem 1rem;
      }
    }
    
    /* High DPI Displays */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
      .header-logo {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
      }
    }
    
    /* Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
      * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
      }
    }
    
    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
      /* Keep existing light theme as default */
    }
    
    /* Animation Enhancements */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .animate-fade-in-up {
      animation: fadeInUp 0.6s ease-out;
    }
    
    /* Loading States */
    .loading {
      position: relative;
      overflow: hidden;
    }
    
    .loading::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
  </style>
</head>
<body class="font-sans min-h-screen">
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
  <div id="toast" class="fixed top-4 sm:top-6 right-2 sm:right-6 left-2 sm:left-auto z-50 hidden bg-green-500 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg shadow-lg transition-all duration-300 flex items-center text-sm sm:text-base">
    <i class="fas fa-check-circle mr-2"></i>
    <span id="toast-message"></span>
  </div>

  <!-- Image Modal -->
  <div id="imgModal" class="fixed inset-0 z-50 hidden items-center justify-center modal-bg">
    <div class="bg-white rounded-xl shadow-2xl p-3 sm:p-4 lg:p-6 relative max-w-4xl w-full mx-2 sm:mx-4">
      <button onclick="closeImgModal()" class="absolute top-2 sm:top-4 right-2 sm:right-4 text-gray-500 hover:text-black text-xl sm:text-2xl transition-colors">
        <i class="fas fa-times"></i>
      </button>
      <div class="flex items-center justify-between mb-3 sm:mb-4">
        <h3 class="text-lg sm:text-xl font-semibold">Proof of Payment</h3>
      </div>
      <div class="relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
        <div id="imageContainer" class="relative w-full h-[50vh] sm:h-[60vh] lg:h-[70vh] flex items-center justify-center">
          <img id="modalImg" src="" alt="Proof of Payment" class="max-w-full max-h-full object-contain" />
        </div>
      </div>
      <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-500 text-center">
        <p>Click outside the image or press ESC to close</p>
      </div>
    </div>
  </div>

  <!-- Status Change Confirmation Modal -->
  <div id="statusModal" class="fixed inset-0 z-40 hidden items-center justify-center modal-bg">
    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 relative max-w-md w-full mx-2 sm:mx-4">
      <button onclick="closeStatusModal()" class="absolute top-2 sm:top-4 right-2 sm:right-4 text-gray-500 hover:text-black text-xl sm:text-2xl transition-colors">
        <i class="fas fa-times"></i>
      </button>
      <div class="text-center mb-4 sm:mb-6">
        <i class="fas fa-exclamation-circle text-2xl sm:text-3xl text-yellow-500 mb-3 sm:mb-4"></i>
        <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">Confirm Status Change</h3>
        <p class="text-gray-600 text-sm sm:text-base" id="statusModalText">Are you sure you want to change the status of this reservation?</p>
      </div>
      <div class="flex flex-col sm:flex-row justify-center space-y-2 sm:space-y-0 sm:space-x-4">
        <button onclick="closeStatusModal()" class="px-3 sm:px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base">
          Cancel
        </button>
        <button onclick="confirmStatusChange()" class="px-3 sm:px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm sm:text-base">
          Confirm
        </button>
      </div>
    </div>
  </div>

  <!-- View Modal -->
  <div id="viewModal" class="fixed inset-0 z-40 hidden items-center justify-center modal-bg">
    <div class="bg-white rounded-xl shadow-2xl p-4 sm:p-6 lg:p-8 relative max-w-6xl w-full mx-2 sm:mx-4 max-h-[90vh] flex flex-col modal-card">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3">
        <div class="flex items-center">
          <i class="fas fa-clipboard-list text-xl sm:text-2xl text-blue-500 mr-2 sm:mr-3"></i>
          <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800">Reservation Details</h3>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
          <div class="flex items-center space-x-2">
            <span class="text-gray-700 font-bold text-sm sm:text-base lg:text-lg">Reservation No.</span>
            <span id="viewReservationIdBadge" class="bg-blue-100 text-blue-800 px-2 sm:px-4 py-1 sm:py-2 rounded-full text-sm sm:text-base lg:text-lg font-mono font-bold"></span>
          </div>
          <button onclick="closeViewModal()" class="text-gray-500 hover:text-black text-xl sm:text-2xl transition-colors">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="overflow-y-auto pr-2 custom-scrollbar flex-1">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 items-stretch">
          <!-- Left Column - Customer & Reservation Info -->
          <div class="space-y-6">
            <!-- Customer & Reservation Information Card -->
            <div class="gradient-gray rounded-xl p-3 sm:p-4 lg:p-6 border border-gray-200 hover:border-gray-300 transition-colors modal-card animate-fade-in-up h-full">
              <div class="flex items-center mb-3 sm:mb-4">
                <i class="fas fa-user-circle text-lg sm:text-xl text-gray-500 mr-2 sm:mr-3"></i>
              </div>
              <div class="space-y-3 sm:space-y-4">
                <!-- Customer Information Section -->
                <div class="space-y-3 sm:space-y-4">
                  <h5 class="text-sm sm:text-base font-semibold text-gray-700 border-b border-gray-200 pb-2">Customer Information</h5>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Full Name:</span>
                    <span id="viewFullName" class="text-gray-800 flex-1 font-semibold text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">First Name:</span>
                    <span id="viewFirstName" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Last Name:</span>
                    <span id="viewLastName" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">M.I.:</span>
                    <span id="viewMiddleInitial" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Suffix:</span>
                    <span id="viewSuffix" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Contact:</span>
                    <div class="flex-1">
                      <span id="viewContact" class="text-gray-800 font-mono text-xs sm:text-sm"></span>
                    </div>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Email:</span>
                    <div class="flex-1">
                      <span id="viewEmail" class="text-gray-800 text-xs sm:text-sm"></span>
                    </div>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Region:</span>
                    <span id="viewRegion" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Province:</span>
                    <span id="viewProvince" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">City:</span>
                    <span id="viewCity" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">District:</span>
                    <span id="viewDistrict" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Barangay:</span>
                    <span id="viewBarangay" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Purok/Street:</span>
                    <span id="viewPurok" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                </div>
                
                <!-- Reservation Details Section -->
                <div class="space-y-3 sm:space-y-4 pt-3 sm:pt-4">
                  <h5 class="text-sm sm:text-base font-semibold text-gray-700 border-b border-gray-200 pb-2">Reservation Details</h5>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Date:</span>
                    <span id="viewDate" class="text-gray-800 flex-1 font-semibold text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Time:</span>
                    <span id="viewTime" class="text-gray-800 flex-1 font-semibold text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Status:</span>
                    <span id="viewStatus" class="flex-1 text-xs sm:text-sm"></span>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-start">
                    <span class="w-full sm:w-20 lg:w-24 text-gray-600 font-medium text-xs sm:text-sm">Duration:</span>
                    <span id="viewDuration" class="text-gray-800 flex-1 text-xs sm:text-sm"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Middle Column - Products -->
          <div class="space-y-6">
            <!-- Product Information Card -->
            <div class="gradient-gray rounded-xl p-3 sm:p-4 lg:p-6 border border-gray-200 modal-card animate-fade-in-up h-full">
              <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="flex items-center">
                  <i class="fas fa-box text-lg sm:text-xl text-gray-500 mr-2 sm:mr-3"></i>
                  <h4 class="text-base sm:text-lg font-semibold text-gray-800">Product Information</h4>
                </div>
                <span id="viewProductCount" class="bg-gray-100 text-gray-800 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold"></span>
              </div>
              <div class="space-y-3 sm:space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-start">
                  <span class="w-full sm:w-28 lg:w-32 text-gray-600 font-medium text-xs sm:text-sm">Total Value:</span>
                  <span id="viewTotalValue" class="text-gray-800 flex-1 font-mono font-bold text-base sm:text-lg"></span>
                </div>
                <div class="mt-3 sm:mt-4">
                  <p class="text-gray-600 font-medium mb-2 sm:mb-3 text-xs sm:text-sm">Products:</p>
                  <div id="viewProductsList" class="space-y-2 sm:space-y-3">
                    <!-- Products will be populated here -->
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Payment & Actions -->
          <div class="space-y-6">
            <!-- Payment Information Card -->
            <div class="gradient-gray rounded-xl p-3 sm:p-4 lg:p-6 border border-gray-200 hover:border-gray-300 transition-colors modal-card animate-fade-in-up h-full">
              <div class="flex items-center mb-3 sm:mb-4">
                <i class="fas fa-money-bill-wave text-lg sm:text-xl text-gray-500 mr-2 sm:mr-3"></i>
                <h4 class="text-base sm:text-lg font-semibold text-gray-800">Payment Information</h4>
              </div>
              <div class="space-y-3 sm:space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-start">
                  <span class="w-full sm:w-28 lg:w-32 text-gray-600 font-medium text-xs sm:text-sm">Reservation Fee:</span>
                  <span id="viewDownPayment" class="text-gray-800 flex-1 font-mono font-bold text-xs sm:text-sm"></span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-start">
                  <span class="w-full sm:w-28 lg:w-32 text-gray-600 font-medium text-xs sm:text-sm">Remaining Balance:</span>
                  <span id="viewBalance" class="text-gray-800 flex-1 font-mono font-bold text-xs sm:text-sm"></span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-start">
                  <span class="w-full sm:w-28 lg:w-32 text-gray-600 font-medium text-xs sm:text-sm">Payment Status:</span>
                  <span id="viewPaymentStatus" class="flex-1 text-xs sm:text-sm"></span>
                </div>
                <div class="mt-3 sm:mt-4">
                  <p class="text-gray-600 font-medium mb-2 text-xs sm:text-sm">Proof of Payment:</p>
                  <div class="relative group max-w-full sm:max-w-[300px]">
                    <img id="viewProof" src="" alt="Proof of Payment" 
                         class="w-full h-auto rounded-lg border border-gray-200 shadow-sm cursor-pointer transition-all duration-300 hover:scale-[1.02] hover:shadow-lg">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 rounded-lg flex items-center justify-center">
                      <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                        <i class="fas fa-eye text-white text-lg sm:text-2xl mb-1"></i>
                        <p class="text-white text-xs font-medium">Click to view</p>
                      </div>
                    </div>
                    <!-- View indicator badge -->
                    <div class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                      <i class="fas fa-eye mr-1"></i>View
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Archive Confirmation Modal -->
  <div id="archiveModal" class="fixed inset-0 z-40 hidden items-center justify-center modal-bg">
    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 relative max-w-md w-full mx-2 sm:mx-4">
      <button onclick="closeArchiveModal()" class="absolute top-2 sm:top-4 right-2 sm:right-4 text-gray-500 hover:text-black text-xl sm:text-2xl transition-colors">
        <i class="fas fa-times"></i>
      </button>
      <div class="text-center mb-4 sm:mb-6">
        <i class="fas fa-archive text-2xl sm:text-3xl text-gray-500 mb-3 sm:mb-4"></i>
        <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">Archive Reservation</h3>
        <p class="text-gray-600 text-sm sm:text-base">Are you sure you want to archive this reservation? This action cannot be undone.</p>
      </div>
      <div class="flex flex-col sm:flex-row justify-center space-y-2 sm:space-y-0 sm:space-x-4">
        <button onclick="closeArchiveModal()" class="px-3 sm:px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors text-sm sm:text-base">
          Cancel
        </button>
        <button onclick="confirmArchive()" class="px-3 sm:px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm sm:text-base">
          Archive
        </button>
      </div>
    </div>
  </div>

  <!-- Header Section -->
  <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
    <div class="flex justify-between items-center">
      <div class="flex space-x-2 sm:space-x-4">
        <a href="admin.php" class="inline-flex items-center text-xs sm:text-sm bg-black text-white rounded-lg px-2 sm:px-4 py-2">
          <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
          <span class="hidden sm:inline">Back to Dashboard</span>
          <span class="sm:hidden">Back</span>
        </a>
      </div>
    </div>
    <!-- Dynamic Reservation Stats Card -->
    <div class="mt-3 sm:mt-4 mb-4 sm:mb-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
        <?php
        // Calculate reservation statistics
        try {
          // Total reservations (non-archived)
          $totalStmt = $conn->query("SELECT COUNT(*) as total FROM reservations WHERE (archived IS NULL OR archived = 0)");
          $totalCount = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
          
          // Completed reservations (non-archived)
          $completedStmt = $conn->query("SELECT COUNT(*) as completed FROM reservations WHERE (archived IS NULL OR archived = 0) AND (STATUS = 'completed' OR STATUS = 'complete' OR STATUS = 'done')");
          $completedCount = $completedStmt->fetch(PDO::FETCH_ASSOC)['completed'];
          
          // Pending reservations (non-archived)
          $pendingStmt = $conn->query("SELECT COUNT(*) as pending FROM reservations WHERE (archived IS NULL OR archived = 0) AND (STATUS IS NULL OR STATUS = '' OR STATUS = 'pending' OR STATUS != 'completed' AND STATUS != 'complete' AND STATUS != 'done')");
          $pendingCount = $pendingStmt->fetch(PDO::FETCH_ASSOC)['pending'];
          
          // Total archived reservations
          $totalArchivedStmt = $conn->query("SELECT COUNT(*) as total_archived FROM reservations WHERE archived = 1");
          $totalArchivedCount = $totalArchivedStmt->fetch(PDO::FETCH_ASSOC)['total_archived'];
          
          // Completed archived reservations
          $completedArchivedStmt = $conn->query("SELECT COUNT(*) as completed_archived FROM reservations WHERE archived = 1 AND (STATUS = 'completed' OR STATUS = 'complete' OR STATUS = 'done')");
          $completedArchivedCount = $completedArchivedStmt->fetch(PDO::FETCH_ASSOC)['completed_archived'];
        } catch (PDOException $e) {
          // Fallback values if database query fails
          $totalCount = 0;
          $completedCount = 0;
          $pendingCount = 0;
          $totalArchivedCount = 0;
          $completedArchivedCount = 0;
          error_log("Error calculating reservation stats: " . $e->getMessage());
        }
        ?>
        <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 lg:p-6 border-2 border-black border-l-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-xs sm:text-sm">Total Reservations</p>
              <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800"><?php echo $totalCount; ?></h3>
            </div>
            <i class="fas fa-shopping-cart text-xl sm:text-2xl lg:text-3xl text-blue-500"></i>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 lg:p-6 border-2 border-black border-l-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-xs sm:text-sm">Completed</p>
              <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800"><?php echo $completedCount; ?></h3>
            </div>
            <i class="fas fa-check-circle text-xl sm:text-2xl lg:text-3xl text-green-500"></i>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 lg:p-6 border-2 border-black border-l-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-xs sm:text-sm">Pending</p>
              <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800"><?php echo $pendingCount; ?></h3>
            </div>
            <i class="fas fa-clock text-xl sm:text-2xl lg:text-3xl text-red-500"></i>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 lg:p-6 border-2 border-black border-l-4 rounded-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500 text-xs sm:text-sm">Total Archived</p>
              <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800"><?php echo $totalArchivedCount; ?></h3>
            </div>
            <i class="fas fa-archive text-xl sm:text-2xl lg:text-3xl text-gray-500"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Table Section -->
  <div class="bg-white rounded-xl shadow-lg p-3 sm:p-4 lg:p-6 border border-gray-200">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3 sm:gap-4">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
        <div class="relative flex-1 sm:flex-none">
          <input type="text" id="searchInput" placeholder="Search reservations..." 
                 class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <select id="statusFilter" class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 sm:px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base">
          <option value="all">All Status</option>
          <option value="completed">Completed</option>
          <option value="pending">Pending</option>
        </select>
      </div>
      <div class="flex items-center space-x-2 w-full sm:w-auto">
        <a href="reserved_archive.php" class="view-archived-btn inline-flex items-center justify-center border-2 border-black bg-black text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-white hover:text-black hover:border-black transition-colors text-sm sm:text-base w-full sm:w-auto">
          <i class="fas fa-archive mr-1 sm:mr-2"></i>
          <span class="hidden sm:inline">View Archived</span>
          <span class="sm:hidden">Archived</span>
        </a>
      </div>
    </div>

    <div class="table-container">
      <table class="min-w-full table-auto border-collapse">
        <thead class="bg-gray-50 sticky top-0">
          <tr class="text-gray-900 font-semibold text-sm sm:text-base lg:text-lg">
            <th class="border-b border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-center" style="text-align: center !important;">
              <span class="hidden sm:inline">Reservation ID</span>
              <span class="sm:hidden">ID</span>
            </th>
            <th class="border-b border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-center" style="text-align: center !important;">Name</th>
            <th class="border-b border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-center" style="text-align: center !important;">
              <span class="hidden sm:inline">Contact</span>
              <span class="sm:hidden">Phone</span>
            </th>
            <th class="border-b border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-center" style="text-align: center !important;">
              <span class="hidden lg:inline">Address</span>
              <span class="lg:hidden">Addr</span>
            </th>
            <th class="border-b border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-center" style="text-align: center !important;">Status</th>
            <th class="border-b border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-center" style="text-align: center !important;">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php
          require_once 'db.php';
          try {
            $stmt = $conn->query("SELECT * FROM reservations WHERE (archived IS NULL OR archived = 0) ORDER BY reservation_date DESC, reservation_time DESC");
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($reservations) === 0) {
              echo '<tr><td colspan="11" class="text-center p-8 text-gray-500">
                      <i class="fas fa-inbox text-4xl mb-2"></i>
                      <p>No reservations found.</p>
                    </td></tr>';
            } else {
              foreach ($reservations as $res) {
                $rowId = 'row_' . htmlspecialchars($res['reservation_id']);
                echo '<tr id="' . $rowId . '" class="hover:bg-gray-50 transition-colors">';
                echo '<td class="border border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm">' . htmlspecialchars($res['reservation_id']) . '</td>';
                
                $name_parts = [
                    htmlspecialchars($res['first_name'] ?? ''),
                    htmlspecialchars($res['middle_initial'] ?? ''),
                    htmlspecialchars($res['last_name'] ?? ''),
                    htmlspecialchars($res['suffix'] ?? '')
                ];
                $full_name = implode(' ', array_filter($name_parts));
                echo '<td class="border border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm">' . ($full_name ?: htmlspecialchars($res['name'])) . '</td>';
                echo '<td class="border border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm">
                        <div class="tooltip">
                          <i class="fas fa-phone-alt text-blue-500 mr-1 sm:mr-2"></i>' . htmlspecialchars($res['contact_number']) . '
                          <span class="tooltiptext">Click to copy</span>
                        </div>
                      </td>';
                $address_parts = [
                    htmlspecialchars($res['purok'] ?? ''),
                    htmlspecialchars($res['barangay'] ?? ''),
                    htmlspecialchars($res['city'] ?? ''),
                    htmlspecialchars($res['province'] ?? ''),
                    htmlspecialchars($res['region'] ?? '')
                ];
                $full_address = implode(', ', array_filter($address_parts));
                echo '<td class="border border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-center text-xs sm:text-sm">' . ($full_address ?: htmlspecialchars($res['address'])) . '</td>';
                $status = htmlspecialchars($res['STATUS'] ?? 'pending');
                
                // Debug logging
                error_log("reserved.php: Reservation ID " . $res['reservation_id'] . " has status: " . $status);
                
                $badgeClass = $status === 'completed' ? 'status-badge status-completed' : 'status-badge status-not_completed';
                
                // Fix dropdown selection logic - handle various possible status values
                $completedSelected = '';
                $notCompletedSelected = '';
                
                // Normalize status for comparison
                $normalizedStatus = strtolower(trim($status));
                if ($normalizedStatus === 'completed' || $normalizedStatus === 'complete' || $normalizedStatus === 'done') {
                    $completedSelected = 'selected';
                } else {
                    // Default to pending for any non-completed status
                    $notCompletedSelected = 'selected';
                }
                
                echo '<td class="border border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-center">
                        <span class="' . $badgeClass . ' text-xs sm:text-sm" id="badge_' . $res['reservation_id'] . '">
                          <i class="fas ' . ($status === 'completed' ? 'fa-check-circle' : 'fa-clock') . ' mr-1 sm:mr-2"></i>' . 
                          ucfirst(str_replace('_', ' ', $status)) . 
                        '</span>
                        <select name="status_' . htmlspecialchars($res['reservation_id']) . '" 
                                class="border border-gray-300 rounded-md px-1 sm:px-2 py-1 mt-1 sm:mt-2 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm" 
                                onchange="updateStatus(' . htmlspecialchars($res['reservation_id']) . ', this.value)">
                          <option value="pending" ' . $notCompletedSelected . '>Pending</option>
                          <option value="completed" ' . $completedSelected . '>Completed</option>
                        </select>
                      </td>';
                echo '<td class="border border-gray-300 px-2 sm:px-4 lg:px-6 py-2 sm:py-3 text-center">
                        <div class="flex flex-col sm:flex-row space-y-1 sm:space-y-0 sm:space-x-1 lg:space-x-2 justify-center">
                          <button onclick="viewReservation(' . htmlspecialchars($res['reservation_id']) . ')" 
                             class="text-white bg-black hover:bg-gray-800 px-2 sm:px-3 py-1 rounded-md shadow transition-colors duration-200 text-xs sm:text-sm">
                            <i class="fas fa-eye mr-1"></i><span class="hidden sm:inline">View</span>
                          </button>
                          <button onclick="archiveReservation(' . htmlspecialchars($res['reservation_id']) . ')" 
                                  class="text-white bg-gray-500 hover:bg-gray-600 px-2 sm:px-3 py-1 rounded-md shadow transition-colors duration-200 text-xs sm:text-sm">
                            <i class="fas fa-archive mr-1"></i><span class="hidden sm:inline">Archive</span>
                          </button>
                        </div>
                      </td>';
                echo '</tr>';
              }
            }
          } catch (PDOException $e) {
            echo '<tr><td colspan="11" class="text-center p-4 text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p>Error loading reservations: ' . htmlspecialchars($e->getMessage()) . '</p>
                  </td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Show toast with icon
    function showToast(msg) {
      const toast = document.getElementById('toast');
      const toastMessage = document.getElementById('toast-message');
      toastMessage.textContent = msg;
      toast.classList.remove('hidden');
      setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // Row highlight
    function highlightRow(rowId) {
      const row = document.getElementById(rowId);
      if (row) {
        row.classList.add('row-highlight');
        setTimeout(() => row.classList.remove('row-highlight'), 1000);
      }
    }

    // Add these variables at the top of your script section
    let pendingStatusChange = {
      reservationId: null,
      newStatus: null,
      originalStatus: null  // Store original status for rollback
    };

    let pendingArchive = {
      reservationId: null
    };

    // Update the updateStatus function
    function updateStatus(reservationId, status) {
      // Store the original status for potential rollback
      const dropdown = document.querySelector('select[name="status_' + reservationId + '"]');
      const originalStatus = dropdown ? dropdown.value : 'pending';
      
      pendingStatusChange = {
        reservationId: reservationId,
        newStatus: status,
        originalStatus: originalStatus
      };
      
      const statusText = status === 'completed' ? 'mark as completed' : 'mark as pending';
      document.getElementById('statusModalText').textContent = `Are you sure you want to ${statusText} this reservation?`;
      
      // Show the modal
      const modal = document.getElementById('statusModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    // Function to reset dropdown to original state
    function resetDropdownToOriginal() {
      if (pendingStatusChange.reservationId && pendingStatusChange.originalStatus) {
        const dropdown = document.querySelector('select[name="status_' + pendingStatusChange.reservationId + '"]');
        if (dropdown) {
          dropdown.value = pendingStatusChange.originalStatus;
          console.log('Reset dropdown to original status:', pendingStatusChange.originalStatus);
        }
      }
    }

    // Add these new functions
    function closeStatusModal() {
      const modal = document.getElementById('statusModal');
      modal.classList.remove('flex');
      modal.classList.add('hidden');
      
      // Reset dropdown to original state if modal is closed without confirming
      if (pendingStatusChange.reservationId && pendingStatusChange.originalStatus) {
        resetDropdownToOriginal();
      }
      
      pendingStatusChange = {
        reservationId: null,
        newStatus: null,
        originalStatus: null
      };
    }

    function confirmStatusChange() {
      if (!pendingStatusChange.reservationId || !pendingStatusChange.newStatus) {
        console.log('No pending status change to confirm');
        return;
      }

      console.log('Confirming status change:', pendingStatusChange);

      // Add loading state
      const confirmButton = document.querySelector('#statusModal button[onclick="confirmStatusChange()"]');
      const originalText = confirmButton.textContent;
      confirmButton.textContent = 'Updating...';
      confirmButton.disabled = true;

      // Add timestamp to prevent caching
      const timestamp = new Date().getTime();
      const url = `update_status.php?t=${timestamp}`;
      
      console.log('Making request to:', url);
      console.log('Request data:', {
        reservation_id: pendingStatusChange.reservationId,
        status: pendingStatusChange.newStatus
      });

      fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "Cache-Control": "no-cache"
        },
        body: "reservation_id=" + encodeURIComponent(pendingStatusChange.reservationId) + 
              "&status=" + encodeURIComponent(pendingStatusChange.newStatus)
      })
      .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        console.log('Response URL:', response.url);
        return response.text();
      })
      .then(data => {
        console.log('Response data:', data);
        console.log('Response data length:', data.length);
        console.log('Response data type:', typeof data);
        console.log('Response data trimmed:', data.trim());
        console.log('Response data comparison:', data.trim() === 'success');
        
        if (data.trim() === 'success') {
          console.log('Status update successful, updating UI...');
          
          const badge = document.getElementById('badge_' + pendingStatusChange.reservationId);
          if (badge) {
            badge.innerHTML = '<i class="fas ' + 
              (pendingStatusChange.newStatus === 'completed' ? 'fa-check-circle' : 'fa-clock') + 
              ' mr-2"></i>' +
              pendingStatusChange.newStatus.charAt(0).toUpperCase() + 
              pendingStatusChange.newStatus.slice(1);
            badge.className = 'status-badge ' + 
              (pendingStatusChange.newStatus === 'completed' ? 'status-completed' : 'status-not_completed');
            console.log('Updated badge for reservation', pendingStatusChange.reservationId);
          } else {
            console.error('Badge element not found for reservation', pendingStatusChange.reservationId);
          }
          
          // Update the dropdown selection to match the new status
          const dropdown = document.querySelector('select[name="status_' + pendingStatusChange.reservationId + '"]');
          if (dropdown) {
            // Ensure the dropdown value is properly set
            dropdown.value = pendingStatusChange.newStatus;
            
            // Force a change event to ensure the UI updates
            const event = new Event('change', { bubbles: true });
            dropdown.dispatchEvent(event);
            
            console.log('Updated dropdown for reservation', pendingStatusChange.reservationId, 'to', pendingStatusChange.newStatus);
          } else {
            console.error('Dropdown not found for reservation', pendingStatusChange.reservationId);
          }
          
          highlightRow('row_' + pendingStatusChange.reservationId);
          showToast('Status updated successfully!');
          updateStats();
          closeStatusModal();
          
          // Log success for debugging
          console.log('Status update completed successfully. Changes should persist in database.');
          console.log('If changes are not persisting, check the database directly or refresh the page manually.');
        } else {
          console.error('Status update failed, response:', data);
          showToast('Error updating status: ' + data);
          resetDropdownToOriginal();
          closeStatusModal();
        }
      })
      .catch(error => {
        console.error('Error updating status:', error);
        console.error('Error details:', {
          message: error.message,
          stack: error.stack,
          reservationId: pendingStatusChange.reservationId,
          newStatus: pendingStatusChange.newStatus
        });
        showToast('Error updating status: ' + error.message);
        resetDropdownToOriginal();
        closeStatusModal();
      })
      .finally(() => {
        // Reset button state
        if (confirmButton) {
          confirmButton.textContent = originalText;
          confirmButton.disabled = false;
        }
      });
    }

    // Modal event listeners
    document.getElementById('viewModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeViewModal();
      }
    });

    document.getElementById('statusModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeStatusModal();
      }
    });

    document.getElementById('archiveModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeArchiveModal();
      }
    });

    document.getElementById('imgModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeImgModal();
      }
    });

    // ESC key to close modals
    document.addEventListener('keydown', function(e) {
      if (e.key === "Escape") {
        const viewModal = document.getElementById('viewModal');
        const statusModal = document.getElementById('statusModal');
        const archiveModal = document.getElementById('archiveModal');
        const imgModal = document.getElementById('imgModal');
        
        if (viewModal && viewModal.classList.contains('flex')) {
          closeViewModal();
        } else if (statusModal && statusModal.classList.contains('flex')) {
          closeStatusModal();
        } else if (archiveModal && archiveModal.classList.contains('flex')) {
          closeArchiveModal();
        } else if (imgModal && imgModal.classList.contains('flex')) {
          closeImgModal();
        }
      }
    });

    // Simple function to show image modal
    function centerAndShowImage(src) {
      console.log('centerAndShowImage called with src:', src);
      
      const modal = document.getElementById('imgModal');
      const img = document.getElementById('modalImg');
      
      if (!modal || !img) {
        console.error('Required modal elements not found');
        return;
      }
      
      // Set image source
      img.src = src;
      
      // Show modal
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      
      // Prevent body scrolling
      document.body.style.overflow = 'hidden';
      
      // Handle image error
      img.onerror = function() {
        console.error('Failed to load image:', src);
        showToast('Error loading image. Please try again.');
        closeImgModal();
      };
    }

    // Function to handle proof of payment image click
    function handleImageClick() {
      console.log('=== Proof of payment image clicked ===');
      console.log('Image element:', this);
      console.log('Image src:', this.src);
      console.log('Image id:', this.id);
      console.log('Image display style:', this.style.display);
      console.log('Image visibility:', this.offsetParent !== null);
      
      if (this.src && this.src.trim() !== '') {
        console.log('Calling centerAndShowImage with src:', this.src);
        centerAndShowImage(this.src);
      } else {
        console.error('Image src is empty or invalid');
        showToast('Error: Image source is invalid');
      }
    }

    // Close image modal function
    function closeImgModal() {
      const modal = document.getElementById('imgModal');
      if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
      }
      
      const img = document.getElementById('modalImg');
      if (img) {
        img.src = '';
      }
      
      // Re-enable body scrolling
      document.body.style.overflow = '';
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
      const searchText = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
      });
      
      updateStats();
    });

    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function(e) {
      const status = e.target.value;
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        if (status === 'all') {
          row.style.display = '';
        } else {
          const rowStatus = row.querySelector('.status-badge').textContent.toLowerCase();
          row.style.display = rowStatus.includes(status) ? '' : 'none';
        }
      });
      
      updateStats();
    });

    // Update statistics via AJAX
    function updateStats() {
      fetch('get_reservation_stats.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update the stats display
            const statsContainer = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4');
            if (statsContainer) {
              const statCards = statsContainer.querySelectorAll('h3');
              if (statCards.length >= 4) {
                statCards[0].textContent = data.total;
                statCards[1].textContent = data.completed;
                statCards[2].textContent = data.pending;
                statCards[3].textContent = data.total_archived;
                

              }
            }
          }
        })
        .catch(error => {
          console.error('Error updating stats:', error);
        });
    }

    // Initialize stats
    document.addEventListener('DOMContentLoaded', function() {
      updateStats();
    });

    // Copy contact number
    document.querySelectorAll('.tooltip').forEach(tooltip => {
      tooltip.addEventListener('click', function() {
        const text = this.querySelector('i').nextSibling.textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
          showToast('Contact number copied to clipboard!');
        });
      });
    });

    // Send email
    function sendEmail(email) {
      window.location.href = `mailto:${email}`;
    }



    // Print table
    function printTable() {
      const printWindow = window.open('', '_blank');
      const tableContent = document.querySelector('.table-container').innerHTML;
      
      printWindow.document.write(`
        <html>
          <head>
            <title>Reserved Products Report</title>
            <style>
              @page {
                size: landscape;
                margin: 10mm;
              }
              body { 
                font-family: Arial, sans-serif;
                margin: 20px;
                padding: 0;
              }
              table { 
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
              }
              th, td { 
                border: 1px solid #000;
                padding: 4px 6px;
                text-align: left;
                font-size: 11px;
                line-height: 1.2;
              }
              th { 
                background-color: #f3f4f6;
                font-weight: bold;
                text-transform: uppercase;
              }
              tr:nth-child(even) {
                background-color: #f9fafb;
              }
              .status-badge { 
                padding: 2px 6px;
                border-radius: 4px;
                display: inline-block;
                font-weight: bold;
                font-size: 10px;
              }
              .status-completed { 
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #065f46;
              }
              .status-not_completed { 
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #991b1b;
              }
              @media print {
                body { margin: 0; }
                .no-print { display: none; }
              }
            </style>
          </head>
          <body>
            <h2 style="text-align: center; margin-bottom: 15px; font-size: 16px;">Reserved Products Report</h2>
            ${tableContent}
          </body>
        </html>
      `);
      
      printWindow.document.close();
      printWindow.focus();
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 250);
    }

    // Update the viewReservation function in the script section
    function viewReservation(reservationId) {
      console.log('viewReservation called with ID:', reservationId); // Debug log
      
      // Get the modal element
      const modal = document.getElementById('viewModal');
      console.log('Modal element:', modal); // Debug log
      console.log('Modal classes:', modal ? modal.className : 'Modal not found'); // Debug log
      
      // Check if modal is currently visible (has flex class and not hidden)
      if (modal && modal.classList.contains('flex') && !modal.classList.contains('hidden')) {
        console.log('Modal already open, returning early'); // Debug log
        return;
      }
      
      // Ensure modal is properly reset before opening
      resetModalState();
      
      // Small delay to ensure reset is complete
      setTimeout(() => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        console.log('Modal opened, new state:', modal.className); // Debug log
        
        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
      }, 50);
      
      // Fetch reservation data via AJAX
      const url = 'get_reservation_details.php?id=' + encodeURIComponent(reservationId) + '&t=' + Date.now();
      console.log('Fetching from URL:', url); // Debug log
      
      fetch(url)
        .then(response => {
          console.log('Response status:', response.status); // Debug log
          if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
          }
          return response.json();
        })
        .then(reservation => {
          console.log('Response data:', reservation); // Debug log
          if (!reservation.success) {
            showToast('Error loading reservation details: ' + reservation.message);
            return;
          }
          
          const data = reservation.data;
          console.log('Reservation data:', data); // Debug log
          
          // Store current reservation for quick actions
          window.currentReservation = data;
          
          // Set reservation ID badge
          document.getElementById('viewReservationIdBadge').textContent = data.reservation_id || 'N/A';
          
          // Populate the modal with reservation data
          document.getElementById('viewFullName').textContent = data.name || 'N/A';
          document.getElementById('viewFirstName').textContent = data.first_name || 'N/A';
          document.getElementById('viewLastName').textContent = data.last_name || 'N/A';
          document.getElementById('viewMiddleInitial').textContent = data.middle_initial || 'N/A';
          document.getElementById('viewSuffix').textContent = data.suffix || 'N/A';
          document.getElementById('viewContact').textContent = data.contact_number || 'N/A';
          document.getElementById('viewEmail').textContent = data.email || 'N/A';
          
          // Populate address components
          document.getElementById('viewRegion').textContent = data.region || 'N/A';
          document.getElementById('viewProvince').textContent = data.province || 'N/A';
          document.getElementById('viewCity').textContent = data.city || 'N/A';
          document.getElementById('viewDistrict').textContent = data.district || 'N/A';
          document.getElementById('viewBarangay').textContent = data.barangay || 'N/A';
          document.getElementById('viewPurok').textContent = data.purok || 'N/A';
          
          // Format date
          const reservationDate = data.reservation_date ? new Date(data.reservation_date) : null;
          document.getElementById('viewDate').textContent = reservationDate ? reservationDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          }) : 'N/A';
          
          // Format time
          const reservationTime = data.reservation_time;
          document.getElementById('viewTime').textContent = reservationTime ? 
            new Date('1970-01-01T' + reservationTime).toLocaleTimeString('en-US', {
              hour: '2-digit',
              minute: '2-digit'
            }) : 'N/A';
          
          // Calculate duration since reservation
          if (reservationDate) {
            const now = new Date();
            const diffTime = Math.abs(now - reservationDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
            
            let durationText = '';
            if (diffDays > 1) {
              durationText = `${diffDays} days ago`;
            } else if (diffDays === 1) {
              durationText = '1 day ago';
            } else if (diffHours > 1) {
              durationText = `${diffHours} hours ago`;
            } else {
              durationText = 'Less than an hour ago';
            }
            document.getElementById('viewDuration').textContent = durationText;
          } else {
            document.getElementById('viewDuration').textContent = 'N/A';
          }
          
          // Set product count
          document.getElementById('viewProductCount').textContent = data.product_count || 'N/A';
          
          // Calculate total value from all products and populate products list
          let totalValue = 0;
          const productsList = document.getElementById('viewProductsList');
          productsList.innerHTML = '';
          
          // Check for both old and new product structure
          let products = [];
          
          // New multi-product structure (product_name_1, product_name_2, etc.)
          for (let i = 1; i <= 5; i++) {
            if (data[`product_name_${i}`]) {
              const productPrice = parseFloat(data[`product_price_${i}`]) || 0;
              totalValue += productPrice;
              
              products.push({
                name: data[`product_name_${i}`],
                brand: data[`product_brand_${i}`] || '',
                model: data[`product_model_${i}`] || '',
                price: productPrice
              });
            }
          }
          
          // Old single product structure (if no multi-product data found)
          if (products.length === 0 && data.product_name) {
            const productPrice = parseFloat(data.product_price) || 0;
            totalValue = productPrice;
            
            products.push({
              name: data.product_name || 'N/A',
              brand: data.product_brand || '',
              model: data.product_model || '',
              price: productPrice
            });
          }
          
          // Display products in the list with enhanced styling
          products.forEach((product, index) => {
            const productDiv = document.createElement('div');
            productDiv.className = 'bg-white p-4 rounded-lg border border-gray-200 shadow-sm product-card h-24 flex items-center';
            productDiv.innerHTML = `
              <div class="flex items-center justify-between w-full">
                <div class="flex-1">
                  <div class="flex items-center mb-2">
                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full mr-2">#${index + 1}</span>
                    <p class="font-semibold text-gray-800 truncate">${product.name}</p>
                  </div>
                  ${product.brand || product.model ? `<p class="text-sm text-gray-600 truncate">${product.brand} ${product.model}</p>` : ''}
                </div>
                <div class="text-right ml-3 flex-shrink-0">
                  <p class="font-mono font-bold text-gray-800 text-lg">₱${product.price.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                  })}</p>
                </div>
              </div>
            `;
            productsList.appendChild(productDiv);
          });
          
          // Set total value
          document.getElementById('viewTotalValue').textContent = '₱' + totalValue.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          
          // Set reservation fee and balance
          const reservationFee = parseFloat(data.reservation_fee || 0);
          const remainingFee = parseFloat(data.remaining_reservation_fee || 0);
          
          document.getElementById('viewDownPayment').textContent = '₱' + reservationFee.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          document.getElementById('viewBalance').textContent = '₱' + remainingFee.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          
          // Set payment status
          const paymentStatusElement = document.getElementById('viewPaymentStatus');
          if (remainingFee <= 0) {
            paymentStatusElement.innerHTML = '<span class="status-badge status-completed inline-flex items-center"><i class="fas fa-check-circle mr-2"></i>Fully Paid</span>';
          } else if (reservationFee > 0) {
            paymentStatusElement.innerHTML = '<span class="status-badge status-not_completed inline-flex items-center"><i class="fas fa-clock mr-2"></i>Partial Payment</span>';
          } else {
            paymentStatusElement.innerHTML = '<span class="status-badge status-not_completed inline-flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i>No Payment</span>';
          }
          
          // Set status with appropriate styling
          const statusElement = document.getElementById('viewStatus');
          // Use STATUS (uppercase) from database, fallback to status (lowercase) for compatibility
          const reservationStatus = data.STATUS || data.status || 'pending';
          const statusText = reservationStatus === 'completed' ? 'Completed' : 'Pending';
          const statusClass = reservationStatus === 'completed' ? 'status-completed' : 'status-not_completed';
          statusElement.innerHTML = `
            <span class="status-badge ${statusClass} inline-flex items-center">
              <i class="fas ${reservationStatus === 'completed' ? 'fa-check-circle' : 'fa-clock'} mr-2"></i>
              ${statusText}
            </span>
          `;
          
          // Set proof of payment image
          const proofImg = document.getElementById('viewProof');
          console.log('Proof of payment data:', data.proof_of_payment); // Debug log
          console.log('Full reservation object:', data); // Debug log
          console.log('Proof image element:', proofImg); // Debug log
          
          if (!proofImg) {
            console.error('Proof image element not found in DOM');
            // Try to find the element again after a short delay
            setTimeout(() => {
              const retryProofImg = document.getElementById('viewProof');
              if (retryProofImg) {
                console.log('Proof image element found on retry');
                handleProofImage(retryProofImg, data);
              } else {
                console.error('Proof image element still not found after retry');
              }
            }, 100);
            return;
          }
          
          handleProofImage(proofImg, data);
          
          // Show the modal
          const modal = document.getElementById('viewModal');
          console.log('Opening modal, current state:', modal ? modal.className : 'Modal not found'); // Debug log
          
          // Ensure modal is properly reset before opening
          resetModalState();
          
          // Small delay to ensure reset is complete
          setTimeout(() => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            console.log('Modal opened, new state:', modal.className); // Debug log
            
            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
          }, 50);
        })
        .catch(error => {
          console.error('Error fetching reservation details:', error);
          console.error('Error details:', {
            message: error.message,
            stack: error.stack,
            reservationId: reservationId
          });
          showToast('Error loading reservation details: ' + error.message);
        });
    }

    // Function to reset modal state
    function resetModalState() {
      const modal = document.getElementById('viewModal');
      console.log('Closing view modal'); // Debug log
      console.log('Modal before closing:', modal ? modal.className : 'Modal not found'); // Debug log
      
      if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        console.log('Modal after closing:', modal.className); // Debug log
      }
      
      // Clear any stored reservation data
      window.currentReservation = null;
      
      // Re-enable body scrolling
      document.body.style.overflow = '';
      
      // Force a small delay to ensure state is properly reset
      setTimeout(() => {
        console.log('Modal state after delay:', modal ? modal.className : 'Modal not found'); // Debug log
      }, 100);
    }

    // Function to close view modal
    function closeViewModal() {
      const modal = document.getElementById('viewModal');
      console.log('Closing view modal'); // Debug log
      console.log('Modal before closing:', modal ? modal.className : 'Modal not found'); // Debug log
      
      if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        console.log('Modal after closing:', modal.className); // Debug log
      }
      
      // Clear any stored reservation data
      window.currentReservation = null;
      
      // Re-enable body scrolling
      document.body.style.overflow = '';
      
      // Force a small delay to ensure state is properly reset
      setTimeout(() => {
        console.log('Modal state after delay:', modal ? modal.className : 'Modal not found'); // Debug log
      }, 100);
    }

    // Function to test modal state
    function testModalState() {
      const modal = document.getElementById('viewModal');
      console.log('=== Modal State Test ===');
      console.log('Modal element:', modal);
      console.log('Modal classes:', modal ? modal.className : 'Modal not found');
      console.log('Has flex class:', modal ? modal.classList.contains('flex') : false);
      console.log('Has hidden class:', modal ? modal.classList.contains('hidden') : false);
      console.log('Is visible:', modal ? (modal.classList.contains('flex') && !modal.classList.contains('hidden')) : false);
      console.log('=======================');
    }

    // Function to handle proof of payment image
    function handleProofImage(proofImg, data) {
      console.log('=== handleProofImage Debug ===');
      console.log('Data object:', data);
      console.log('Proof of payment field:', data.proof_of_payment);
      console.log('Proof image element:', proofImg);
      
      if (data.proof_of_payment && data.proof_of_payment.trim() !== '') {
        // Check if the data is base64 (old format) or filename (new format)
        const isBase64 = data.proof_of_payment.includes('data:image') || data.proof_of_payment.length > 100;
        
        if (isBase64) {
          // Handle old base64 format
          console.log('Detected base64 format, converting to data URL');
          proofImg.src = data.proof_of_payment;
          proofImg.style.display = 'block';
          
          // Add click handler for base64 images
          proofImg.removeEventListener('click', handleImageClick);
          proofImg.addEventListener('click', handleImageClick);
        } else {
          // Handle new file-based format
          console.log('Detected file-based format');
          
          // Try different path formats for file-based images
          const pathFormats = [
            'uploads/proof_of_payment/' + data.proof_of_payment,
            './uploads/proof_of_payment/' + data.proof_of_payment,
            '../uploads/proof_of_payment/' + data.proof_of_payment,
            '/admin/uploads/proof_of_payment/' + data.proof_of_payment
          ];
          
          console.log('Trying path formats:', pathFormats);
          
          // Use the first path format
          const imagePath = pathFormats[0];
          console.log('Using image path:', imagePath);
          
          // Set the image source
          proofImg.src = imagePath;
          proofImg.style.display = 'block';
          
          // Add click handler immediately as fallback
          proofImg.removeEventListener('click', handleImageClick);
          proofImg.addEventListener('click', handleImageClick);
          
          // Add error handling for the image
          proofImg.onerror = function() {
            console.error('Failed to load image:', imagePath);
            
            // Try alternative paths if the first one fails
            const currentIndex = pathFormats.indexOf(imagePath);
            if (currentIndex < pathFormats.length - 1) {
              const nextPath = pathFormats[currentIndex + 1];
              console.log('Trying alternative path:', nextPath);
              this.src = nextPath;
              return; // Don't show error yet, let it try the next path
            }
            
            // All paths failed, show error message
            this.style.display = 'none';
            const parentDiv = this.parentElement;
            if (parentDiv) {
              parentDiv.innerHTML = `
                <div class="bg-gray-100 p-4 rounded-lg border border-gray-200 text-center">
                  <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                  <p class="text-gray-700 text-sm font-medium">Proof of payment not found</p>
                  <p class="text-gray-500 text-xs mt-1">Filename: ${data.proof_of_payment}</p>
                  <p class="text-gray-400 text-xs">Please contact support if this is an error</p>
                </div>
              `;
            }
          };
          
          // Add success handling
          proofImg.onload = function() {
            console.log('Image loaded successfully');
            console.log('Image details:', {
              src: this.src,
              naturalWidth: this.naturalWidth,
              naturalHeight: this.naturalHeight,
              complete: this.complete
            });
            this.style.display = 'block';
            
            // Remove any existing click handlers and add new one
            this.removeEventListener('click', handleImageClick);
            this.addEventListener('click', handleImageClick);
          };
        }
        
      } else {
        console.log('No proof of payment data available');
        proofImg.style.display = 'none';
        // Show a placeholder
        const parentDiv = proofImg.parentElement;
        if (parentDiv) {
          parentDiv.innerHTML = `
            <div class="bg-gray-100 p-4 rounded-lg border border-gray-200 text-center">
              <i class="fas fa-image text-gray-400 text-2xl mb-2"></i>
              <p class="text-gray-500 text-sm">No proof of payment uploaded</p>
              <p class="text-gray-400 text-xs">This reservation does not require payment</p>
            </div>
          `;
        }
      }
      console.log('=== End handleProofImage Debug ===');
    }

    // Update the archive functionality
    function archiveReservation(reservationId) {
      pendingArchive.reservationId = reservationId;
      
      // Show the modal
      const modal = document.getElementById('archiveModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeArchiveModal() {
      const modal = document.getElementById('archiveModal');
      modal.classList.remove('flex');
      modal.classList.add('hidden');
      pendingArchive.reservationId = null;
    }

    function confirmArchive() {
      if (!pendingArchive.reservationId) {
        return;
      }

      fetch("archive_reservation.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "reservation_id=" + encodeURIComponent(pendingArchive.reservationId)
      })
      .then(response => response.text())
      .then(data => {
        const row = document.getElementById('row_' + pendingArchive.reservationId);
        if (row) {
          row.remove();
          showToast('Reservation archived successfully!');
          updateStats();
        }
        closeArchiveModal();
      })
      .catch(error => {
        showToast('Error archiving reservation!');
        console.error("Error archiving reservation:", error);
        closeArchiveModal();
      });
    }

    // Real-time clock functionality
    function updateTime() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
      document.getElementById('currentTime').textContent = timeString;
    }

    // Update time every second
    setInterval(updateTime, 1000);
    updateTime(); // Initial call

    function updateDate() {
      const now = new Date();
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', options);
    }
    setInterval(updateDate, 60000); // Update every minute
    updateDate(); // Initial call
  </script>
  </div>
</body>
</html>
