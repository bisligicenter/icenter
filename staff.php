<?php
session_start();
require_once 'db.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <title>Bislig iCenter - Staff Dashboard</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
      }
      header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
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
      
      /* Enhanced Sidebar */
      #sidebar {
        background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
        box-shadow: 12px 0 32px 0 rgba(0,0,0,0.18), 0 1.5px 0 0 rgba(255,255,255,0.08) inset;
        border-right: 2px solid rgba(255,255,255,0.12);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(12px) saturate(1.2);
        -webkit-backdrop-filter: blur(12px) saturate(1.2);
        background-color: rgba(26,26,26,0.85);
        transform: translateX(0);
        will-change: transform, width;
      }
      #sidebar::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        box-shadow: 0 8px 32px 0 rgba(0,0,0,0.18);
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
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Add sliding animation for sidebar */
      #sidebar.sliding {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Ensure main content slides smoothly */
      #mainContent {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Smooth header transitions */
      header {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
        transition: all 0.3s ease;
      }
      
      #sidebar a:hover .nav-icon {
        box-shadow: 0 4px 16px rgba(0,0,0,0.20), 0 0 20px rgba(255,255,255,0.1);
        transform: scale(1.05);
      }

      #sidebar.collapsed .nav-item {
        justify-content: center;
        padding: 1.2rem 0.5rem;
        min-height: 3.2rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
      }
      
      /* Different colors for each icon in collapsed mode */
      #sidebar.collapsed .nav-item:nth-child(1) .nav-icon {
        background: rgba(34, 197, 94, 0.2);
      }
      #sidebar.collapsed .nav-item:nth-child(2) .nav-icon {
        background: rgba(245, 158, 11, 0.2);
      }
      #sidebar.collapsed .nav-item:nth-child(3) .nav-icon {
        background: rgba(168, 85, 247, 0.2);
      }

      #sidebar.collapsed .sidebar-text {
        opacity: 0;
        transform: translateX(-24px);
        pointer-events: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
      }

      #sidebar a {
        border-radius: 16px;
        margin: 8px 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1.5px 0 0 rgba(255,255,255,0.04) inset;
        transition: all 0.3s ease;
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
      }
      
      /* Mobile responsive styles */
      @media (max-width: 575.98px) {
        #sidebar {
          width: 280px;
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
        
        #sidebar.mobile-open {
          transform: translateX(0);
        }
        
        /* Mobile overlay */
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
        
        .sidebar-overlay.active {
          opacity: 1;
          visibility: visible;
        }
        
        #mainContent {
          margin-left: 0 !important;
          width: 100%;
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
        
        /* Toggle buttons for mobile */
        #sidebarToggle {
          display: none !important;
        }
        
        /* Prevent body scroll when sidebar is open */
        body.sidebar-open {
          overflow: hidden;
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
        
        /* Main content padding */
        .p-8 {
          padding: 1rem !important;
        }
        
        /* Product grid responsive adjustments */
        .grid.grid-cols-1.lg\:grid-cols-2 {
          grid-template-columns: 1fr !important;
          gap: 1rem !important;
        }
        
        /* Product card mobile optimizations */
        .product-card {
          min-height: auto !important;
          flex-direction: column !important;
          margin-bottom: 1rem !important;
        }
        
        .product-card > div:first-child {
          width: 100% !important;
          height: 200px !important;
        }
        
        .product-card > div:last-child {
          width: 100% !important;
          padding: 1rem !important;
        }
        
        /* Category buttons mobile layout */
        #categoryButtons {
          gap: 0.5rem !important;
          justify-content: flex-start !important;
          overflow-x: auto !important;
          padding: 0.5rem 0 !important;
          -webkit-overflow-scrolling: touch !important;
        }
        
        #categoryButtons a {
          white-space: nowrap !important;
          padding: 0.5rem 1rem !important;
          font-size: 0.875rem !important;
          min-width: fit-content !important;
        }
        
        /* Product card text adjustments */
        .product-card h3 {
          font-size: 1.25rem !important;
          line-height: 1.3 !important;
        }
        
        .product-card p {
          font-size: 0.875rem !important;
          line-height: 1.4 !important;
        }
        
        /* Status badge mobile */
        .status-badge {
          font-size: 0.875rem !important;
          padding: 0.5rem 1rem !important;
        }
        
        /* Sold button and input mobile layout */
        .product-card .flex.flex-col.items-center.space-y-3 {
          gap: 0.75rem !important;
        }
        
        .product-card .flex.items-center.space-x-3 {
          flex-direction: column !important;
          gap: 0.5rem !important;
          width: 100% !important;
        }
        
        .sold-quantity-input {
          width: 100% !important;
          max-width: 120px !important;
        }
        
        .sold-button {
          width: 100% !important;
          max-width: 120px !important;
          padding: 0.75rem 1rem !important;
        }
        
        /* Pagination mobile */
        #paginationControls {
          flex-wrap: wrap !important;
          gap: 0.5rem !important;
          justify-content: center !important;
        }
        
        #paginationControls a,
        #paginationControls span {
          padding: 0.5rem 0.75rem !important;
          font-size: 0.875rem !important;
        }
        
        /* iPhone specific optimizations */
        @supports (-webkit-touch-callout: none) {
          /* iOS Safari specific styles */
          .product-card {
            -webkit-transform: translateZ(0);
            transform: translateZ(0);
          }
          
          #categoryButtons {
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
          }
          
          #categoryButtons::-webkit-scrollbar {
            display: none;
          }
          
          /* Prevent zoom on input focus */
          .sold-quantity-input {
            font-size: 16px !important;
          }
        }
        
        /* Touch-friendly button sizes */
        .sold-button,
        .product-card a {
          min-height: 44px !important;
          min-width: 44px !important;
        }
        
        /* Safe area support for iPhone X and newer */
        @supports (padding: max(0px)) {
          header {
            padding-left: max(0.5rem, env(safe-area-inset-left)) !important;
            padding-right: max(0.5rem, env(safe-area-inset-right)) !important;
          }
          
          .p-8 {
            padding-left: max(1rem, env(safe-area-inset-left)) !important;
            padding-right: max(1rem, env(safe-area-inset-right)) !important;
          }
        }
      }
      
      /* Small devices (landscape phones, 576px and up) */
      @media (min-width: 576px) and (max-width: 767.98px) {
        #sidebar {
          width: 300px;
          min-width: 300px;
          max-width: 300px;
          transform: translateX(-100%);
          z-index: 1000;
          position: fixed;
          top: 0;
          left: 0;
          height: 100vh;
          transition: transform 0.3s ease-in-out;
        }
        
        #sidebar.mobile-open {
          transform: translateX(0);
        }
        
        /* Mobile overlay */
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
        
        .sidebar-overlay.active {
          opacity: 1;
          visibility: visible;
        }
        
        #mainContent {
          margin-left: 0 !important;
          width: 100%;
        }
        
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
        
        #sidebarToggle {
          display: none !important;
        }
        
        /* Prevent body scroll when sidebar is open */
        body.sidebar-open {
          overflow: hidden;
        }
        
        /* Header adjustments */
        header {
          padding: 0.75rem 1rem !important;
        }
        header .flex {
          flex-direction: row !important;
          align-items: center !important;
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
        
        .p-8 {
          padding: 1.5rem !important;
        }
        
        /* Product grid for small tablets */
        .grid.grid-cols-1.lg\:grid-cols-2 {
          grid-template-columns: repeat(2, 1fr) !important;
          gap: 1rem !important;
        }
        
        /* Product card adjustments for small tablets */
        .product-card {
          min-height: auto !important;
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
        
        /* Category buttons for small tablets */
        #categoryButtons {
          gap: 0.75rem !important;
          justify-content: center !important;
          flex-wrap: wrap !important;
        }
        
        #categoryButtons a {
          padding: 0.75rem 1.25rem !important;
          font-size: 0.9rem !important;
        }
        
        /* Sold button layout for small tablets */
        .product-card .flex.items-center.space-x-3 {
          flex-direction: row !important;
          gap: 0.75rem !important;
          justify-content: center !important;
        }
        
        .sold-quantity-input {
          width: 80px !important;
        }
        
        .sold-button {
          width: auto !important;
          padding: 0.75rem 1.5rem !important;
        }
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
        
        /* Show toggle button on tablet */
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
        
        .p-8 {
          padding: 2rem !important;
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
      }
    </style>
  </head>
  <body class="min-h-screen">
    <div class="flex">
      <!-- Sidebar -->
      <div id="sidebar" class="w-64 h-screen fixed shadow-md flex flex-col z-10 transition-all duration-300" style="background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);">
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
        <div class="px-6 py-4 border-b border-white/10">
          <div class="flex flex-col items-center cursor-pointer">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-black font-medium shadow-lg transition-all duration-300 mb-2">
              <i class="ri-user-line text-lg m-0 p-0 leading-none"></i>
            </div>
            <span class="text-white text-xs font-semibold transition-colors duration-300">STAFF</span>
          </div>
        </div>
        <div class="flex-1 overflow-y-auto">
          <nav class="px-4 py-2">
            <div class="space-y-2">
              <!-- Inventory Stock -->
              <a href="inventory_stocks1.php" class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium text-white rounded-xl hover:bg-white/10 whitespace-nowrap transition-all duration-300 group shadow-lg hover:shadow-xl">
                <div class="nav-icon w-6 h-6 flex items-center justify-center mr-3 bg-white/20 rounded-lg group-hover:bg-white/30 transition-all duration-300 overflow-hidden">
                  <i class="ri-stack-line text-lg"></i>
                </div>
                <span class="sidebar-text">Inventory Stock</span>
              </a>
              <!-- Reservation -->
              <a href="reserved1.php" class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium text-white rounded-xl hover:bg-white/10 whitespace-nowrap transition-all duration-300 group shadow-lg hover:shadow-xl">
                <div class="nav-icon w-6 h-6 flex items-center justify-center mr-3 bg-white/20 rounded-lg group-hover:bg-white/30 transition-all duration-300 overflow-hidden">
                  <i class="ri-calendar-check-line text-lg"></i>
                </div>
                <span class="sidebar-text">Reservation</span>
              </a>
            </div>
          </nav>
        </div>
        <div class="p-4 border-t border-white/10">
          <a href="logout.php" class="nav-item flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 rounded-xl hover:from-red-600 hover:to-red-700 whitespace-nowrap transition-all duration-300 group shadow-lg hover:shadow-xl">
            <div class="nav-icon w-6 h-6 flex items-center justify-center mr-3 bg-white/20 rounded-lg group-hover:bg-white/30 transition-all duration-300 overflow-hidden">
              <i class="ri-logout-box-line text-lg"></i>
            </div>
            <span class="sidebar-text">Log Out</span>
          </a>
        </div>
      </div>
      
      <!-- Sidebar Overlay for Mobile -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      
      <!-- Main content -->
      <div id="mainContent" class="flex-1 ml-64 transition-all duration-300">
        <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
          <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6 space-x-2 lg:space-x-4">
            <!-- Mobile Menu Toggle -->
            <button id="mobileMenuToggle" class="lg:hidden text-white bg-transparent border border-white/20 p-2 rounded-lg hover:bg-white/10 transition-all duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            
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
            
            <!-- Spacer for mobile layout balance -->
            <div class="lg:hidden w-10"></div>
          </div>
        </header>
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
            try {
                $stmt = $conn->query("SELECT DISTINCT product FROM products ORDER BY product ASC");
                $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                $categories = [];
            }
            $currentCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
            $currentModelBrand = isset($_GET['model_brand']) ? strtolower(trim($_GET['model_brand'])) : 'all models';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            // Add "All Products" button
            renderCategoryButton('All Products', '', $currentModelBrand, $search);
            foreach ($categories as $cat) {
                renderCategoryButton($cat, $currentCategory, $currentModelBrand, $search);
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
                  
                  echo '<div class="flex justify-center mb-4">';
                  echo '<div class="flex justify-between items-center py-2 px-3 bg-red-50 rounded-lg border border-red-200 w-full max-w-xs">';
                  echo '<span class="font-medium text-red-700">Selling Price:</span>';
                  echo '<span class="text-red-700 font-semibold">₱' . number_format($product['selling_price'], 2) . '</span>';
                  echo '</div>';
                  echo '</div>';
                  echo '<p class="status-badge ' . $stockInfo['status'] . ' w-full block text-center px-6 py-3 rounded-xl font-semibold mb-4">' . $stockInfo['text'] . ' (' . $product['stock_quantity'] . ')</p>';
                  
                  // Add Edit button below the status badge
                  $editUrl = 'edit_products1.php?product_id=' . urlencode($product['product_id']);
                  echo '<a href="' . $editUrl . '" class="mt-4 w-full inline-block px-6 py-3 bg-gradient-to-r from-gray-800 to-black text-white font-semibold rounded-xl hover:from-gray-900 hover:to-gray-800 transition-all duration-300 text-center transform hover:scale-105">';
                  echo '<i class="ri-edit-2-line mr-2"></i>Edit Product</a>';
                  
                  // Insert Sold button and quantity input
                  echo '<div class="flex flex-col items-center space-y-3 mt-2 w-full justify-center">';
                  echo '<div class="flex items-center space-x-3">';
                  echo '<input type="number" min="1" value="1" max="' . (int)$product['stock_quantity'] . '" class="sold-quantity-input border border-gray-300 rounded-lg px-3 py-2 w-24 text-center focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" data-product-id="' . htmlspecialchars($product['product_id']) . '" />';
                  echo '<button class="sold-button bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-all duration-300 font-medium shadow-md hover:shadow-lg" data-product-id="' . htmlspecialchars($product['product_id']) . '"><i class="fas fa-shopping-cart mr-2"></i>Sold</button>';
                  echo '</div>';
                  echo '<div class="text-xs text-gray-500 mt-1">Click to process sale</div>';
                  echo '</div>';
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

                $whereClauses = [];
                $params = [];

                // Exclude archived products
                $whereClauses[] = "(archived IS NULL OR archived = 0)";

                if ($search !== '') {
                    $whereClauses[] = "(model LIKE :search OR brand LIKE :search OR storage LIKE :search)";
                    $params[':search'] = '%' . $search . '%';
                }

                                  if ($category !== '') {
                      $whereClauses[] = "TRIM(LOWER(product)) = :category";
                      $params[':category'] = strtolower($category);
                  }

                $whereSQL = '';
                if (count($whereClauses) > 0) {
                    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
                }

                // Count total products
                $countStmt = $conn->prepare("SELECT COUNT(*) FROM products $whereSQL");
                $countStmt->execute($params);
                $totalProducts = $countStmt->fetchColumn();

                // Get products with pagination
                $stmt = $conn->prepare("SELECT * FROM products $whereSQL ORDER BY product_id DESC LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($ajax) {
                    // Return only the product grid for AJAX requests
                    renderProductGridAndPagination($products, $totalProducts, $limit, $page, $search, $category);
                    exit;
                } else {
                    // Render the full page
                    renderProductGridAndPagination($products, $totalProducts, $limit, $page, $search, $category);
                }

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

    <!-- Sale Confirmation Modal -->
    <div id="saleConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Confirm Sale</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to process this sale?</p>
        <div class="flex justify-end space-x-3">
          <button id="cancelSaleConfirm" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Cancel</button>
          <button id="confirmSaleConfirm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Confirm Sale</button>
        </div>
      </div>
    </div>

    <!-- Sale Success Modal -->
    <div id="saleSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center">
          <i class="fas fa-check-circle text-green-500 text-4xl mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Sale Successful!</h3>
          <p class="text-gray-600 mb-6">The product has been sold successfully.</p>
          <button id="closeSaleSuccessModal" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">OK</button>
        </div>
      </div>
    </div>

    <!-- Sale Error Modal -->
    <div id="saleErrorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="text-center">
          <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
          <h3 class="text-lg font-semibold mb-2">Sale Error</h3>
          <p id="saleErrorMessage" class="text-gray-600 mb-6">An error occurred while processing the sale.</p>
          <button id="closeSaleErrorModal" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">OK</button>
        </div>
      </div>
    </div>

    <script>
      // Date and time display
      function updateDateTime() {
        const now = new Date();
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = now.toLocaleDateString(undefined, dateOptions);
        document.getElementById('currentTime').textContent = now.toLocaleTimeString();
      }
      setInterval(updateDateTime, 1000);
      updateDateTime();

      // Sidebar toggle functionality
      const sidebarToggle = document.getElementById("sidebarToggle");
      const mobileMenuToggle = document.getElementById("mobileMenuToggle");
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
          }, 300);
        }
      }

      sidebarToggle.addEventListener("click", toggleSidebar);
      
      // Mobile menu toggle functionality
      if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener("click", function() {
          sidebar.classList.toggle("mobile-open");
          if (sidebarOverlay) {
            sidebarOverlay.classList.toggle("active");
          }
          body.classList.toggle("sidebar-open");
        });
      }
      
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
          }, 150);
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

      // Sale Button Logic (copied from admin.php)
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

      document.addEventListener('DOMContentLoaded', function() {
        initializeSaleEventListeners();
        // Sale error modal close button
        const closeSaleErrorModalBtn = document.getElementById('closeSaleErrorModal');
        if (closeSaleErrorModalBtn) {
          closeSaleErrorModalBtn.addEventListener('click', function () {
            document.getElementById('saleErrorModal').classList.add('hidden');
          });
        }

        // Attach category button handlers
        function attachCategoryButtonHandlers() {
          const categoryButtons = document.querySelectorAll('#categoryButtons a');
          categoryButtons.forEach(button => {
            button.addEventListener('click', handleCategoryClick);
          });
        }

        // Attach pagination handlers
        function attachPaginationHandlers() {
          const paginationLinks = document.querySelectorAll('#paginationControls a');
          paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
              e.preventDefault();
              const url = this.href;
              const productGridContainer = document.getElementById('productGrid');
              
              if (!productGridContainer) return;
              productGridContainer.style.transition = 'opacity 0.1s ease';
              productGridContainer.style.opacity = '0';

              fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                  const parser = new DOMParser();
                  const doc = parser.parseFromString(html, 'text/html');

                  const newProductGrid = doc.getElementById('productGrid');
                  const newPagination = doc.querySelector('#paginationControls');

                  if (newProductGrid && productGridContainer.parentNode) {
                    setTimeout(() => {
                      productGridContainer.innerHTML = newProductGrid.innerHTML;
                      productGridContainer.style.transition = 'opacity 0.1s ease';
                      productGridContainer.style.opacity = '1';
                    }, 100);
                  }

                  const oldPagination = document.querySelector('#paginationControls');
                  if (oldPagination && newPagination) {
                    oldPagination.innerHTML = newPagination.innerHTML;
                  }

                  attachPaginationHandlers();
                  history.pushState(null, '', url);
                })
                .catch(err => {
                  console.error('Error loading page:', err);
                  productGridContainer.style.opacity = '1';
                });
            });
          });
        }

        // Initialize handlers
        attachCategoryButtonHandlers();
        attachPaginationHandlers();
        
        // Add quantity input validation and enhancement
        document.addEventListener('input', function(e) {
          if (e.target.classList.contains('sold-quantity-input')) {
            const input = e.target;
            const value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max'));
            
            if (value > max) {
              input.value = max;
            } else if (value < 1) {
              input.value = 1;
            }
          }
        });
        
        // Add keyboard shortcuts for quantity input
        document.addEventListener('keydown', function(e) {
          if (e.target.classList.contains('sold-quantity-input')) {
            if (e.key === 'Enter') {
              e.preventDefault();
              const productId = e.target.getAttribute('data-product-id');
              const soldButton = document.querySelector(`button[data-product-id="${productId}"]`);
              if (soldButton) {
                soldButton.click();
              }
            }
          }
        });
      });
    </script>
  </body>
</html> 