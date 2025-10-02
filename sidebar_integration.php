<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iCenter Admin - Enhanced Sidebar</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Enhanced Sidebar CSS -->
    <link rel="stylesheet" href="sidebar_enhancements_v2.css">
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen">
    
    <!-- Include Enhanced Sidebar -->
    <?php include 'sidebar_menu.html'; ?>
    
    <!-- Main Content Area -->
    <div class="ml-64 transition-all duration-500 ease-in-out">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
                    <p class="text-slate-600">Welcome back, Admin!</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors duration-200">
                        <i class="ri-notification-3-line text-xl"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button class="flex items-center space-x-2 p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors duration-200">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="ri-user-line text-white text-sm"></i>
                            </div>
                            <span class="text-sm font-medium">Admin</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Products</p>
                            <p class="text-3xl font-bold text-slate-900">1,234</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="ri-box-3-line text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-green-600 flex items-center">
                            <i class="ri-arrow-up-line mr-1"></i>
                            12%
                        </span>
                        <span class="text-slate-600 ml-2">from last month</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Sales</p>
                            <p class="text-3xl font-bold text-slate-900">$45,678</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="ri-money-dollar-circle-line text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-green-600 flex items-center">
                            <i class="ri-arrow-up-line mr-1"></i>
                            8%
                        </span>
                        <span class="text-slate-600 ml-2">from last month</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Reservations</p>
                            <p class="text-3xl font-bold text-slate-900">89</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="ri-calendar-line text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-red-600 flex items-center">
                            <i class="ri-arrow-down-line mr-1"></i>
                            3%
                        </span>
                        <span class="text-slate-600 ml-2">from last month</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Low Stock</p>
                            <p class="text-3xl font-bold text-slate-900">23</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="ri-alert-line text-orange-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-orange-600 flex items-center">
                            <i class="ri-arrow-up-line mr-1"></i>
                            5%
                        </span>
                        <span class="text-slate-600 ml-2">from last month</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-slate-900">Recent Activity</h2>
                    <button class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center space-x-4 p-4 bg-slate-50 rounded-lg">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="ri-add-line text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-900">New product added</p>
                            <p class="text-xs text-slate-600">iPhone 15 Pro Max - 256GB</p>
                        </div>
                        <span class="text-xs text-slate-500">2 minutes ago</span>
                    </div>
                    
                    <div class="flex items-center space-x-4 p-4 bg-slate-50 rounded-lg">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="ri-shopping-cart-line text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-900">Product sold</p>
                            <p class="text-xs text-slate-600">MacBook Air M2 - 512GB</p>
                        </div>
                        <span class="text-xs text-slate-500">15 minutes ago</span>
                    </div>
                    
                    <div class="flex items-center space-x-4 p-4 bg-slate-50 rounded-lg">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="ri-calendar-line text-purple-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-900">Reservation created</p>
                            <p class="text-xs text-slate-600">iPad Air - 128GB</p>
                        </div>
                        <span class="text-xs text-slate-500">1 hour ago</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Enhanced Sidebar JavaScript -->
    <script src="sidebar_enhancements_v2.js"></script>
    
    <!-- Additional Scripts -->
    <script>
        // Example of using the sidebar API
        document.addEventListener('DOMContentLoaded', function() {
            // Access the sidebar instance
            const sidebar = window.enhancedSidebar;
            
            // Example: Set active item based on current page
            sidebar.setActiveItem('dashboard');
            
            // Example: Show notification
            setTimeout(() => {
                sidebar.showNotification('Welcome to the enhanced admin panel!', 'success');
            }, 1000);
            
            // Example: Keyboard shortcuts info
            
        });
    </script>
</body>
</html> 