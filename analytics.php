<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Analytics Dashboard</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: { primary: "#4f46e5", secondary: "#6366f1" },
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
      :where([class^="ri-"])::before { content: "\f3c2"; }
      body {
          font-family: 'Inter', sans-serif;
      }
      input[type="number"]::-webkit-inner-spin-button,
      input[type="number"]::-webkit-outer-spin-button {
          -webkit-appearance: none;
          margin: 0;
      }
      input[type="number"] {
          -moz-appearance: textfield;
      }
      .custom-checkbox {
          position: relative;
          cursor: pointer;
      }
      .custom-checkbox input {
          position: absolute;
          opacity: 0;
          cursor: pointer;
      }
      .checkmark {
          position: absolute;
          top: 0;
          left: 0;
          height: 20px;
          width: 20px;
          background-color: #fff;
          border: 2px solid #e2e8f0;
          border-radius: 4px;
          transition: all 0.2s ease;
      }
      .custom-checkbox:hover input ~ .checkmark {
          border-color: #cbd5e1;
      }
      .custom-checkbox input:checked ~ .checkmark {
          background-color: #4f46e5;
          border-color: #4f46e5;
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
          left: 6px;
          top: 2px;
          width: 5px;
          height: 10px;
          border: solid white;
          border-width: 0 2px 2px 0;
          transform: rotate(45deg);
      }
      .custom-switch {
          position: relative;
          display: inline-block;
          width: 44px;
          height: 24px;
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
          background-color: #e2e8f0;
          transition: .4s;
          border-radius: 34px;
      }
      .switch-slider:before {
          position: absolute;
          content: "";
          height: 18px;
          width: 18px;
          left: 3px;
          bottom: 3px;
          background-color: white;
          transition: .4s;
          border-radius: 50%;
      }
      input:checked + .switch-slider {
          background-color: #4f46e5;
      }
      input:checked + .switch-slider:before {
          transform: translateX(20px);
      }
      .custom-range {
          -webkit-appearance: none;
          width: 100%;
          height: 6px;
          border-radius: 3px;
          background: #e2e8f0;
          outline: none;
      }
      .custom-range::-webkit-slider-thumb {
          -webkit-appearance: none;
          appearance: none;
          width: 18px;
          height: 18px;
          border-radius: 50%;
          background: #4f46e5;
          cursor: pointer;
          border: 2px solid #fff;
          box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
      }
      .custom-range::-moz-range-thumb {
          width: 18px;
          height: 18px;
          border-radius: 50%;
          background: #4f46e5;
          cursor: pointer;
          border: 2px solid #fff;
          box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
      }
      .custom-radio {
          position: relative;
          cursor: pointer;
      }
      .custom-radio input {
          position: absolute;
          opacity: 0;
          cursor: pointer;
      }
      .radio-mark {
          position: absolute;
          top: 0;
          left: 0;
          height: 20px;
          width: 20px;
          background-color: #fff;
          border: 2px solid #e2e8f0;
          border-radius: 50%;
          transition: all 0.2s ease;
      }
      .custom-radio:hover input ~ .radio-mark {
          border-color: #cbd5e1;
      }
      .custom-radio input:checked ~ .radio-mark {
          border-color: #4f46e5;
      }
      .radio-mark:after {
          content: "";
          position: absolute;
          display: none;
      }
      .custom-radio input:checked ~ .radio-mark:after {
          display: block;
      }
      .custom-radio .radio-mark:after {
          top: 4px;
          left: 4px;
          width: 8px;
          height: 8px;
          border-radius: 50%;
          background: #4f46e5;
      }
    </style>
  </head>
  <body class="bg-gray-50 min-h-screen flex">
    <!-- Sidebar -->
    <aside
      class="w-60 bg-gray-900 text-white fixed h-full overflow-y-auto transition-all duration-300 ease-in-out z-10"
    >
      <div class="p-5 flex items-center border-b border-gray-800">
        <div class="font-['Pacifico'] text-2xl text-white">logo</div>
      </div>
      <nav class="mt-5 px-2">
        <div class="space-y-1">
          <a
            href="#"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-md bg-gray-800 text-white"
          >
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-dashboard-line"></i>
            </div>
            Dashboard
          </a>
          <a
            href="#"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white"
          >
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-line-chart-line"></i>
            </div>
            Analytics
          </a>
          <a
            href="#"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white"
          >
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-shopping-bag-line"></i>
            </div>
            Products
          </a>
          <a
            href="#"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white"
          >
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-user-line"></i>
            </div>
            Customers
          </a>
          <a
            href="#"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white"
          >
            <div class="w-5 h-5 flex items-center justify-center mr-3">
              <i class="ri-settings-line"></i>
            </div>
            Settings
          </a>
        </div>
        <div class="mt-8 pt-5 border-t border-gray-800">
          <h3
            class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider"
          >
            Reports
          </h3>
          <div class="mt-2 space-y-1">
            <a
              href="#"
              class="flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white"
            >
              <div class="w-5 h-5 flex items-center justify-center mr-3">
                <i class="ri-file-chart-line"></i>
              </div>
              Sales Report
            </a>
            <a
              href="#"
              class="flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white"
            >
              <div class="w-5 h-5 flex items-center justify-center mr-3">
                <i class="ri-user-heart-line"></i>
              </div>
              Customer Insights
            </a>
            <a
              href="#"
              class="flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white"
            >
              <div class="w-5 h-5 flex items-center justify-center mr-3">
                <i class="ri-store-line"></i>
              </div>
              Inventory Status
            </a>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 ml-60">
      <!-- Header -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between h-16 px-6">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-800">
              Analytics Dashboard
            </h1>
<span class="ml-12 text-sm text-gray-500"><?php echo date('l, F j, Y g:i A'); ?></span>
          </div>
          <div class="flex items-center space-x-4">
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <div
                  class="w-5 h-5 flex items-center justify-center text-gray-400"
                >
                  <i class="ri-search-line"></i>
                </div>
              </div>
              <input
                type="text"
                class="block w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-primary focus:border-primary text-sm"
                placeholder="Search..."
              />
            </div>
            <button
              class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary !rounded-button"
            >
              <div class="w-6 h-6 flex items-center justify-center">
                <i class="ri-notification-3-line"></i>
              </div>
              <span
                class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"
              ></span>
            </button>
            <div class="flex items-center">
              <img
                class="h-8 w-8 rounded-full object-cover"
                src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20a%20young%20business%20person%20with%20neutral%20expression%2C%20high%20quality%2C%20studio%20lighting%2C%20professional&width=200&height=200&seq=1&orientation=squarish"
                alt="User avatar"
              />
              <span class="ml-2 text-sm font-medium text-gray-700"
                >James Wilson</span
              >
            </div>
          </div>
        </div>
      </header>

      <!-- Filter Controls -->
      <div class="bg-white border-b border-gray-200 py-4 px-6">
        <div class="flex flex-wrap items-center justify-between">
          <div class="flex items-center space-x-4">
            <div class="inline-flex rounded-md shadow-sm">
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-l-md !rounded-button whitespace-nowrap"
              >
                Daily
              </button>
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 whitespace-nowrap"
              >
                Weekly
              </button>
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 whitespace-nowrap"
              >
                Monthly
              </button>
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 !rounded-button whitespace-nowrap"
              >
                Custom
              </button>
            </div>
            <div class="relative inline-block text-left">
              <button
                type="button"
                class="inline-flex justify-between w-44 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
              >
                All Categories
                <div class="w-5 h-5 flex items-center justify-center ml-2">
                  <i class="ri-arrow-down-s-line"></i>
                </div>
              </button>
            </div>
            <div class="relative inline-block text-left">
              <button
                type="button"
                class="inline-flex justify-between w-44 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
              >
                All Channels
                <div class="w-5 h-5 flex items-center justify-center ml-2">
                  <i class="ri-arrow-down-s-line"></i>
                </div>
              </button>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <a
              href="#"
              class="text-sm text-primary hover:text-primary-600 font-medium"
            >
              <div class="flex items-center">
                <div class="w-4 h-4 flex items-center justify-center mr-1">
                  <i class="ri-arrow-left-line"></i>
                </div>
                Back to Overview
              </div>
            </a>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
            >
              <div class="w-4 h-4 flex items-center justify-center mr-2">
                <i class="ri-download-line"></i>
              </div>
              Export
            </button>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
            >
              <div class="w-4 h-4 flex items-center justify-center mr-2">
                <i class="ri-share-line"></i>
              </div>
              Share
            </button>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Content -->
      <div class="p-6 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Revenue Analysis Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">
                  Revenue Analysis
                </h3>
                <p class="text-sm text-gray-500">
                  Total revenue across all channels
                </p>
              </div>
              <div class="flex items-center text-sm font-medium text-green-600">
                <div class="w-4 h-4 flex items-center justify-center mr-1">
                  <i class="ri-arrow-up-line"></i>
                </div>
                12.5%
              </div>
            </div>
            <div class="flex items-baseline">
              <span class="text-3xl font-bold text-gray-900">$128,459</span>
              <span class="ml-2 text-sm text-gray-500"
                >vs $114,185 last period</span
              >
            </div>
            <div class="mt-4">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Progress to goal</span>
                <span class="font-medium text-gray-900">78%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-primary h-2 rounded-full"
                  style="width: 78%"
                ></div>
              </div>
            </div>
            <div class="mt-4 h-20" id="revenue-chart"></div>
          </div>

          <!-- Customer Lifetime Value Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">
                  Customer Lifetime Value
                </h3>
                <p class="text-sm text-gray-500">
                  Average revenue per customer
                </p>
              </div>
              <div class="flex items-center text-sm font-medium text-green-600">
                <div class="w-4 h-4 flex items-center justify-center mr-1">
                  <i class="ri-arrow-up-line"></i>
                </div>
                8.3%
              </div>
            </div>
            <div class="flex items-baseline">
              <span class="text-3xl font-bold text-gray-900">$1,245</span>
              <span class="ml-2 text-sm text-gray-500"
                >vs $1,150 last period</span
              >
            </div>
            <div class="mt-4">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Progress to goal</span>
                <span class="font-medium text-gray-900">62%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-primary h-2 rounded-full"
                  style="width: 62%"
                ></div>
              </div>
            </div>
            <div class="mt-4 h-20" id="ltv-chart"></div>
          </div>
          <!-- Conversion Rate Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">
                  Conversion Rate
                </h3>
                <p class="text-sm text-gray-500">
                  Visitors who completed purchase
                </p>
              </div>
              <div class="flex items-center text-sm font-medium text-red-600">
                <div class="w-4 h-4 flex items-center justify-center mr-1">
                  <i class="ri-arrow-down-line"></i>
                </div>
                2.1%
              </div>
            </div>
            <div class="flex items-baseline">
              <span class="text-3xl font-bold text-gray-900">4.8%</span>
              <span class="ml-2 text-sm text-gray-500"
                >vs 4.9% last period</span
              >
            </div>
            <div class="mt-4">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Progress to goal</span>
                <span class="font-medium text-gray-900">85%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-primary h-2 rounded-full"
                  style="width: 85%"
                ></div>
              </div>
            </div>
            <div class="mt-4 h-20" id="conversion-chart"></div>
          </div>

          <!-- Average Order Value Card -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">
                  Average Order Value
                </h3>
                <p class="text-sm text-gray-500">
                  Average amount spent per order
                </p>
              </div>
              <div class="flex items-center text-sm font-medium text-green-600">
                <div class="w-4 h-4 flex items-center justify-center mr-1">
                  <i class="ri-arrow-up-line"></i>
                </div>
                5.7%
              </div>
            </div>
            <div class="flex items-baseline">
              <span class="text-3xl font-bold text-gray-900">$87.32</span>
              <span class="ml-2 text-sm text-gray-500"
                >vs $82.60 last period</span
              >
            </div>
            <div class="mt-4">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Progress to goal</span>
                <span class="font-medium text-gray-900">91%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-primary h-2 rounded-full"
                  style="width: 91%"
                ></div>
              </div>
            </div>
            <div class="mt-4 h-20" id="aov-chart"></div>
          </div>
        </div>

        <!-- Advanced Analytics Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
          <!-- Predictive Analytics -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-semibold text-gray-800">
                Predictive Analytics
              </h3>
              <div class="flex items-center space-x-2">
                <button
                  class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-refresh-line"></i>
                  </div>
                </button>
                <button
                  class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-more-2-fill"></i>
                  </div>
                </button>
              </div>
            </div>
            <div class="h-72" id="predictive-chart"></div>
            <div class="mt-4 bg-blue-50 p-4 rounded-md">
              <div class="flex items-start">
                <div
                  class="w-5 h-5 flex items-center justify-center text-blue-600 mt-0.5"
                >
                  <i class="ri-lightbulb-line"></i>
                </div>
                <div class="ml-3">
                  <h4 class="text-sm font-medium text-blue-800">AI Insight</h4>
                  <p class="mt-1 text-sm text-blue-700">
                    Based on current trends, we predict a 15% increase in
                    revenue over the next 30 days. Consider increasing inventory
                    for top-selling products in the "Electronics" category.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
              >
                <span class="w-2 h-2 bg-primary rounded-full mr-1.5"></span>
                Actual Sales
              </span>
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
              >
                <span class="w-2 h-2 bg-blue-400 rounded-full mr-1.5"></span>
                Predicted Sales
              </span>
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
              >
                <span class="w-2 h-2 bg-blue-200 rounded-full mr-1.5"></span>
                Confidence Interval
              </span>
            </div>
          </div>

          <!-- Customer Behavior Analysis -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-semibold text-gray-800">
                Customer Behavior Analysis
              </h3>
              <div class="flex items-center space-x-2">
                <button
                  class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-refresh-line"></i>
                  </div>
                </button>
                <button
                  class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-more-2-fill"></i>
                  </div>
                </button>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">
                  Customer Segments
                </h4>
                <div class="h-56" id="segments-chart"></div>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">
                  Purchase Patterns
                </h4>
                <div class="h-56" id="patterns-chart"></div>
              </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4">
              <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="text-sm font-medium text-gray-700 mb-1">
                  Retention Rate
                </h4>
                <div class="flex items-baseline">
                  <span class="text-2xl font-bold text-gray-900">76.4%</span>
                  <span class="ml-2 text-xs text-green-600 flex items-center">
                    <div class="w-3 h-3 flex items-center justify-center">
                      <i class="ri-arrow-up-line"></i>
                    </div>
                    3.2%
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Compared to previous period
                </p>
              </div>
              <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="text-sm font-medium text-gray-700 mb-1">
                  Engagement Score
                </h4>
                <div class="flex items-baseline">
                  <span class="text-2xl font-bold text-gray-900">8.7/10</span>
                  <span class="ml-2 text-xs text-green-600 flex items-center">
                    <div class="w-3 h-3 flex items-center justify-center">
                      <i class="ri-arrow-up-line"></i>
                    </div>
                    0.5
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Based on 5,248 customer interactions
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Interactive Visualizations -->
        <div class="mt-6 bg-white rounded-lg shadow-sm">
          <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
              <button
                class="text-primary border-primary px-1 py-4 border-b-2 font-medium text-sm whitespace-nowrap mx-6"
              >
                Time Series
              </button>
              <button
                class="text-gray-500 hover:text-gray-700 hover:border-gray-300 px-1 py-4 border-b-2 border-transparent font-medium text-sm whitespace-nowrap mx-6"
              >
                Comparisons
              </button>
              <button
                class="text-gray-500 hover:text-gray-700 hover:border-gray-300 px-1 py-4 border-b-2 border-transparent font-medium text-sm whitespace-nowrap mx-6"
              >
                Distributions
              </button>
              <button
                class="text-gray-500 hover:text-gray-700 hover:border-gray-300 px-1 py-4 border-b-2 border-transparent font-medium text-sm whitespace-nowrap mx-6"
              >
                Correlations
              </button>
            </nav>
          </div>
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">
                  Sales Performance Over Time
                </h3>
                <p class="text-sm text-gray-500">
                  Breakdown by product category
                </p>
              </div>
              <div class="flex items-center space-x-3">
                <div class="relative inline-block text-left">
                  <button
                    type="button"
                    class="inline-flex justify-between items-center w-40 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                  >
                    Last 6 Months
                    <div class="w-5 h-5 flex items-center justify-center ml-2">
                      <i class="ri-arrow-down-s-line"></i>
                    </div>
                  </button>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-4 h-4 flex items-center justify-center">
                    <i class="ri-download-line"></i>
                  </div>
                </button>
              </div>
            </div>
            <div class="h-80" id="time-series-chart"></div>
            <div class="mt-4 flex flex-wrap gap-2">
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
              >
                <span
                  class="w-2 h-2 bg-[rgba(87,181,231,1)] rounded-full mr-1.5"
                ></span>
                Electronics
              </span>
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
              >
                <span
                  class="w-2 h-2 bg-[rgba(141,211,199,1)] rounded-full mr-1.5"
                ></span>
                Clothing
              </span>
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
              >
                <span
                  class="w-2 h-2 bg-[rgba(251,191,114,1)] rounded-full mr-1.5"
                ></span>
                Home & Garden
              </span>
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
              >
                <span
                  class="w-2 h-2 bg-[rgba(252,141,98,1)] rounded-full mr-1.5"
                ></span>
                Sports & Outdoors
              </span>
            </div>
          </div>
        </div>

        <!-- Custom Report Builder -->
        <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800">
              Custom Report Builder
            </h3>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-700 focus:outline-none !rounded-button whitespace-nowrap"
            >
              Save Report
            </button>
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Metrics Panel -->
            <div
              class="lg:col-span-3 border border-gray-200 rounded-md p-4 h-96 overflow-y-auto"
            >
              <h4 class="font-medium text-gray-700 mb-3">Metrics</h4>
              <div class="space-y-3">
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" checked />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700">Revenue</span>
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" checked />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700">Orders</span>
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700"
                    >Average Order Value</span
                  >
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700"
                    >Conversion Rate</span
                  >
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700"
                    >Customer Acquisition Cost</span
                  >
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700">Return Rate</span>
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700"
                    >Customer Lifetime Value</span
                  >
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700"
                    >Traffic Sources</span
                  >
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700">Page Views</span>
                </label>
                <label class="custom-checkbox flex items-center">
                  <input type="checkbox" />
                  <span class="checkmark"></span>
                  <span class="ml-7 text-sm text-gray-700">Bounce Rate</span>
                </label>
              </div>
            </div>

            <!-- Visualization Canvas -->
            <div
              class="lg:col-span-6 border border-gray-200 border-dashed rounded-md p-4 h-96 flex flex-col"
            >
              <div class="text-center text-gray-500 text-sm mb-4">
                Drag metrics from the left panel to create your visualization
              </div>
              <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                  <div
                    class="mx-auto w-16 h-16 flex items-center justify-center text-gray-400 mb-3"
                  >
                    <i class="ri-bar-chart-2-line ri-3x"></i>
                  </div>
                  <h3 class="text-lg font-medium text-gray-700">
                    Your visualization will appear here
                  </h3>
                  <p class="mt-1 text-sm text-gray-500">
                    Select metrics and visualization type to get started
                  </p>
                </div>
              </div>
            </div>

            <!-- Properties Panel -->
            <div
              class="lg:col-span-3 border border-gray-200 rounded-md p-4 h-96 overflow-y-auto"
            >
              <h4 class="font-medium text-gray-700 mb-3">Properties</h4>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Chart Type</label
                  >
                  <div class="relative inline-block w-full">
                    <button
                      type="button"
                      class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                    >
                      Bar Chart
                      <div
                        class="w-5 h-5 flex items-center justify-center ml-2"
                      >
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Time Range</label
                  >
                  <div class="relative inline-block w-full">
                    <button
                      type="button"
                      class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                    >
                      Last 30 Days
                      <div
                        class="w-5 h-5 flex items-center justify-center ml-2"
                      >
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Group By</label
                  >
                  <div class="relative inline-block w-full">
                    <button
                      type="button"
                      class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                    >
                      Day
                      <div
                        class="w-5 h-5 flex items-center justify-center ml-2"
                      >
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Compare To</label
                  >
                  <div class="relative inline-block w-full">
                    <button
                      type="button"
                      class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                    >
                      Previous Period
                      <div
                        class="w-5 h-5 flex items-center justify-center ml-2"
                      >
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Show Labels</label
                  >
                  <label class="custom-switch">
                    <input type="checkbox" checked />
                    <span class="switch-slider"></span>
                  </label>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Show Legend</label
                  >
                  <label class="custom-switch">
                    <input type="checkbox" checked />
                    <span class="switch-slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
            >
              Reset
            </button>
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-700 focus:outline-none !rounded-button whitespace-nowrap"
            >
              Generate Report
            </button>
          </div>
        </div>

        <!-- Action Center -->
        <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-6">
            Action Center
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gray-50 p-5 rounded-lg">
              <div
                class="w-10 h-10 flex items-center justify-center bg-primary bg-opacity-10 text-primary rounded-lg mb-4"
              >
                <i class="ri-download-line ri-lg"></i>
              </div>
              <h4 class="text-base font-medium text-gray-900 mb-2">
                Download Reports
              </h4>
              <p class="text-sm text-gray-500 mb-4">
                Export your analytics data in multiple formats
              </p>
              <div class="space-y-2">
                <button
                  type="button"
                  class="inline-flex items-center w-full px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-4 h-4 flex items-center justify-center mr-2">
                    <i class="ri-file-excel-line"></i>
                  </div>
                  Excel
                </button>
                <button
                  type="button"
                  class="inline-flex items-center w-full px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-4 h-4 flex items-center justify-center mr-2">
                    <i class="ri-file-pdf-line"></i>
                  </div>
                  PDF
                </button>
                <button
                  type="button"
                  class="inline-flex items-center w-full px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  <div class="w-4 h-4 flex items-center justify-center mr-2">
                    <i class="ri-file-chart-line"></i>
                  </div>
                  CSV
                </button>
              </div>
            </div>
            <div class="bg-gray-50 p-5 rounded-lg">
              <div
                class="w-10 h-10 flex items-center justify-center bg-primary bg-opacity-10 text-primary rounded-lg mb-4"
              >
                <i class="ri-calendar-line ri-lg"></i>
              </div>
              <h4 class="text-base font-medium text-gray-900 mb-2">
                Schedule Exports
              </h4>
              <p class="text-sm text-gray-500 mb-4">
                Set up automated report delivery
              </p>
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Frequency</label
                  >
                  <div class="relative inline-block w-full">
                    <button
                      type="button"
                      class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                    >
                      Weekly
                      <div
                        class="w-4 h-4 flex items-center justify-center ml-2"
                      >
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Recipients</label
                  >
                  <input
                    type="text"
                    class="block w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                    placeholder="Enter email addresses"
                  />
                </div>
                <button
                  type="button"
                  class="inline-flex items-center w-full justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-700 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  Schedule
                </button>
              </div>
            </div>
            <div class="bg-gray-50 p-5 rounded-lg">
              <div
                class="w-10 h-10 flex items-center justify-center bg-primary bg-opacity-10 text-primary rounded-lg mb-4"
              >
                <i class="ri-share-line ri-lg"></i>
              </div>
              <h4 class="text-base font-medium text-gray-900 mb-2">
                Share Dashboard
              </h4>
              <p class="text-sm text-gray-500 mb-4">
                Collaborate with your team members
              </p>
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Team Members</label
                  >
                  <div class="relative inline-block w-full">
                    <button
                      type="button"
                      class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                    >
                      Select members
                      <div
                        class="w-4 h-4 flex items-center justify-center ml-2"
                      >
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Permission</label
                  >
                  <div class="flex space-x-3">
                    <label class="custom-radio flex items-center">
                      <input type="radio" name="permission" checked />
                      <span class="radio-mark"></span>
                      <span class="ml-7 text-sm text-gray-700">View</span>
                    </label>
                    <label class="custom-radio flex items-center">
                      <input type="radio" name="permission" />
                      <span class="radio-mark"></span>
                      <span class="ml-7 text-sm text-gray-700">Edit</span>
                    </label>
                  </div>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center w-full justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-700 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  Share
                </button>
              </div>
            </div>
            <div class="bg-gray-50 p-5 rounded-lg">
              <div
                class="w-10 h-10 flex items-center justify-center bg-primary bg-opacity-10 text-primary rounded-lg mb-4"
              >
                <i class="ri-notification-line ri-lg"></i>
              </div>
              <h4 class="text-base font-medium text-gray-900 mb-2">
                Configure Alerts
              </h4>
              <p class="text-sm text-gray-500 mb-4">
                Get notified about important changes
              </p>
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Metric</label
                  >
                  <div class="relative inline-block w-full">
                    <button
                      type="button"
                      class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                    >
                      Revenue
                      <div
                        class="w-4 h-4 flex items-center justify-center ml-2"
                      >
                        <i class="ri-arrow-down-s-line"></i>
                      </div>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1"
                    >Condition</label
                  >
                  <div class="flex space-x-2">
                    <div class="relative inline-block w-1/3">
                      <button
                        type="button"
                        class="inline-flex justify-between w-full rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none !rounded-button whitespace-nowrap"
                      >
                        <div class="w-4 h-4 flex items-center justify-center">
                          <i class="ri-arrow-down-line"></i>
                        </div>
                      </button>
                    </div>
                    <input
                      type="number"
                      class="block w-2/3 px-3 py-1.5 border border-gray-300 rounded-md text-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary"
                      placeholder="Value"
                    />
                  </div>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center w-full justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-700 focus:outline-none !rounded-button whitespace-nowrap"
                >
                  Set Alert
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Revenue Chart
        const revenueChart = echarts.init(document.getElementById("revenue-chart"));
        revenueChart.setOption({
          animation: false,
          grid: { top: 0, right: 0, bottom: 0, left: 0 },
          xAxis: {
            type: "category",
            data: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            show: false,
          },
          yAxis: { show: false },
          series: [
            {
              data: [82000, 93000, 90000, 94000, 100000, 128459],
              type: "line",
              smooth: true,
              symbol: "none",
              lineStyle: { width: 2, color: "rgba(87, 181, 231, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(87, 181, 231, 0.1)" },
                    { offset: 1, color: "rgba(87, 181, 231, 0.01)" },
                  ],
                },
              },
            },
          ],
        });

        // LTV Chart
        const ltvChart = echarts.init(document.getElementById("ltv-chart"));
        ltvChart.setOption({
          animation: false,
          grid: { top: 0, right: 0, bottom: 0, left: 0 },
          xAxis: {
            type: "category",
            data: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            show: false,
          },
          yAxis: { show: false },
          series: [
            {
              data: [980, 1020, 1050, 1100, 1150, 1245],
              type: "line",
              smooth: true,
              symbol: "none",
              lineStyle: { width: 2, color: "rgba(141, 211, 199, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(141, 211, 199, 0.1)" },
                    { offset: 1, color: "rgba(141, 211, 199, 0.01)" },
                  ],
                },
              },
            },
          ],
        });

        // Conversion Chart
        const conversionChart = echarts.init(
          document.getElementById("conversion-chart"),
        );
        conversionChart.setOption({
          animation: false,
          grid: { top: 0, right: 0, bottom: 0, left: 0 },
          xAxis: {
            type: "category",
            data: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            show: false,
          },
          yAxis: { show: false },
          series: [
            {
              data: [5.2, 5.0, 4.9, 4.7, 4.6, 4.8],
              type: "line",
              smooth: true,
              symbol: "none",
              lineStyle: { width: 2, color: "rgba(251, 191, 114, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(251, 191, 114, 0.1)" },
                    { offset: 1, color: "rgba(251, 191, 114, 0.01)" },
                  ],
                },
              },
            },
          ],
        });

        // AOV Chart
        const aovChart = echarts.init(document.getElementById("aov-chart"));
        aovChart.setOption({
          animation: false,
          grid: { top: 0, right: 0, bottom: 0, left: 0 },
          xAxis: {
            type: "category",
            data: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            show: false,
          },
          yAxis: { show: false },
          series: [
            {
              data: [75.4, 78.2, 80.1, 81.5, 84.7, 87.32],
              type: "line",
              smooth: true,
              symbol: "none",
              lineStyle: { width: 2, color: "rgba(252, 141, 98, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(252, 141, 98, 0.1)" },
                    { offset: 1, color: "rgba(252, 141, 98, 0.01)" },
                  ],
                },
              },
            },
          ],
        });

        // Predictive Chart
        const predictiveChart = echarts.init(
          document.getElementById("predictive-chart"),
        );
        predictiveChart.setOption({
          animation: false,
          tooltip: {
            trigger: "axis",
            backgroundColor: "rgba(255, 255, 255, 0.8)",
            borderColor: "#e2e8f0",
            textStyle: { color: "#1f2937" },
            axisPointer: { type: "shadow" },
          },
          legend: {
            data: ["Actual Sales", "Predicted Sales"],
            bottom: 0,
            textStyle: { color: "#1f2937" },
          },
          grid: { top: 10, right: 10, bottom: 40, left: 40 },
          xAxis: {
            type: "category",
            data: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"],
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
          },
          yAxis: {
            type: "value",
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
            splitLine: { lineStyle: { color: "#f1f5f9" } },
          },
          series: [
            {
              name: "Actual Sales",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [
                120000,
                132000,
                101000,
                134000,
                90000,
                130000,
                140000,
                null,
                null,
              ],
              lineStyle: { width: 3, color: "rgba(87, 181, 231, 1)" },
            },
            {
              name: "Predicted Sales",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [null, null, null, null, null, 130000, 140000, 160000, 184000],
              lineStyle: {
                width: 3,
                color: "rgba(141, 211, 199, 1)",
                type: "dashed",
              },
            },
            {
              name: "Confidence Interval",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [null, null, null, null, null, 145000, 158000, 180000, 210000],
              lineStyle: { width: 0 },
              areaStyle: {
                color: "rgba(141, 211, 199, 0.1)",
                origin: "start",
              },
              stack: "confidence",
            },
            {
              name: "Confidence Interval",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [null, null, null, null, null, 115000, 122000, 140000, 158000],
              lineStyle: { width: 0 },
              areaStyle: {
                color: "rgba(141, 211, 199, 0.1)",
                origin: "end",
              },
              stack: "confidence",
            },
          ],
        });

        // Segments Chart
        const segmentsChart = echarts.init(document.getElementById("segments-chart"));
        segmentsChart.setOption({
          animation: false,
          tooltip: {
            trigger: "item",
            backgroundColor: "rgba(255, 255, 255, 0.8)",
            borderColor: "#e2e8f0",
            textStyle: { color: "#1f2937" },
          },
          legend: {
            orient: "vertical",
            right: 10,
            top: "center",
            textStyle: { color: "#1f2937" },
          },
          series: [
            {
              type: "pie",
              radius: ["40%", "70%"],
              center: ["40%", "50%"],
              avoidLabelOverlap: false,
              itemStyle: {
                borderRadius: 8,
                borderColor: "#fff",
                borderWidth: 2,
              },
              label: { show: false },
              emphasis: {
                label: { show: false },
              },
              data: [
                {
                  value: 1048,
                  name: "Loyal Customers",
                  itemStyle: { color: "rgba(87, 181, 231, 1)" },
                },
                {
                  value: 735,
                  name: "New Customers",
                  itemStyle: { color: "rgba(141, 211, 199, 1)" },
                },
                {
                  value: 580,
                  name: "Occasional Buyers",
                  itemStyle: { color: "rgba(251, 191, 114, 1)" },
                },
                {
                  value: 484,
                  name: "One-time Shoppers",
                  itemStyle: { color: "rgba(252, 141, 98, 1)" },
                },
              ],
            },
          ],
        });

        // Patterns Chart
        const patternsChart = echarts.init(document.getElementById("patterns-chart"));
        patternsChart.setOption({
          animation: false,
          tooltip: {
            trigger: "axis",
            backgroundColor: "rgba(255, 255, 255, 0.8)",
            borderColor: "#e2e8f0",
            textStyle: { color: "#1f2937" },
          },
          grid: { top: 10, right: 10, bottom: 40, left: 40 },
          xAxis: {
            type: "category",
            data: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
          },
          yAxis: {
            type: "value",
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
            splitLine: { lineStyle: { color: "#f1f5f9" } },
          },
          series: [
            {
              data: [120, 200, 150, 80, 70, 110, 130],
              type: "bar",
              itemStyle: {
                color: "rgba(87, 181, 231, 1)",
                borderRadius: [4, 4, 0, 0],
              },
            },
          ],
        });
        // Time Series Chart
        const timeSeriesChart = echarts.init(
          document.getElementById("time-series-chart"),
        );
        timeSeriesChart.setOption({
          animation: false,
          tooltip: {
            trigger: "axis",
            backgroundColor: "rgba(255, 255, 255, 0.8)",
            borderColor: "#e2e8f0",
            textStyle: { color: "#1f2937" },
          },
          legend: {
            data: ["Electronics", "Clothing", "Home & Garden", "Sports & Outdoors"],
            bottom: 0,
            textStyle: { color: "#1f2937" },
          },
          grid: { top: 10, right: 10, bottom: 60, left: 40 },
          xAxis: {
            type: "category",
            data: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
          },
          yAxis: {
            type: "value",
            axisLine: { lineStyle: { color: "#e2e8f0" } },
            axisLabel: { color: "#1f2937" },
            splitLine: { lineStyle: { color: "#f1f5f9" } },
          },
          series: [
            {
              name: "Electronics",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [50000, 55000, 48000, 52000, 58000, 65000],
              lineStyle: { width: 3, color: "rgba(87, 181, 231, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(87, 181, 231, 0.1)" },
                    { offset: 1, color: "rgba(87, 181, 231, 0.01)" },
                  ],
                },
              },
            },
            {
              name: "Clothing",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [30000, 32000, 35000, 38000, 40000, 42000],
              lineStyle: { width: 3, color: "rgba(141, 211, 199, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(141, 211, 199, 0.1)" },
                    { offset: 1, color: "rgba(141, 211, 199, 0.01)" },
                  ],
                },
              },
            },
            {
              name: "Home & Garden",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [20000, 18000, 22000, 25000, 28000, 30000],
              lineStyle: { width: 3, color: "rgba(251, 191, 114, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(251, 191, 114, 0.1)" },
                    { offset: 1, color: "rgba(251, 191, 114, 0.01)" },
                  ],
                },
              },
            },
            {
              name: "Sports & Outdoors",
              type: "line",
              smooth: true,
              symbol: "none",
              data: [15000, 14000, 16000, 18000, 20000, 22000],
              lineStyle: { width: 3, color: "rgba(252, 141, 98, 1)" },
              areaStyle: {
                color: {
                  type: "linear",
                  x: 0,
                  y: 0,
                  x2: 0,
                  y2: 1,
                  colorStops: [
                    { offset: 0, color: "rgba(252, 141, 98, 0.1)" },
                    { offset: 1, color: "rgba(252, 141, 98, 0.01)" },
                  ],
                },
              },
            },
          ],
        });

        // Handle window resize
        window.addEventListener("resize", function () {
          revenueChart.resize();
          ltvChart.resize();
          conversionChart.resize();
          aovChart.resize();
          predictiveChart.resize();
          segmentsChart.resize();
          patternsChart.resize();
          timeSeriesChart.resize();
        });
      });
    </script>
  </body>
</html>
