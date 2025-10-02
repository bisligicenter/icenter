<?php
require_once 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $date = $_POST['date'];
        $description = $_POST['description'];
        $amount = floatval($_POST['amount']);
        $category = $_POST['category'];
        $created_at = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO expenses (date, description, amount, category, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$date, $description, $amount, $category, $created_at]);

        header('Location: manage_expenses.php?success=1');
        exit;
    } catch (PDOException $e) {
        $error = "Error adding expense: " . $e->getMessage();
    }
}

// Get all expenses
try {
    $expenses = $conn->query("SELECT expenses_id, date, description, amount, category, created_at FROM expenses ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching expenses: " . $e->getMessage();
    $expenses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Expenses - Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            position: relative;
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Confirm New Expense</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-gray-600 mb-2">Please review the expense details before adding:</p>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="space-y-2">
                            <div>
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium ml-2" id="modalDate"></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Amount:</span>
                                <span class="font-medium ml-2" id="modalAmount"></span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <span class="text-gray-600">Category:</span>
                                <span class="font-medium ml-2" id="modalCategory"></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Description:</span>
                                <span class="font-medium ml-2" id="modalDescription"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button onclick="submitForm()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Confirm & Add Expense
                </button>
            </div>
        </div>
    </div>

    <div class="min-h-screen">
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <a href="admin.php" class="inline-flex items-center text-sm text-gray-500 hover:text-white hover:bg-black border-2 border-blue-500 rounded-lg px-4 py-2 transition-colors duration-200">
                    <i class="ri-arrow-left-line mr-1"></i>
                    Back to Dashboard
                </a>
                <div class="flex items-center">
                    <img src="images/iCenter.png" alt="Company Logo" class="h-20 w-auto">
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Expense Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Add New Expense</h2>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-4">
                                Expense added successfully!
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-4">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-4">
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" id="date" name="date" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <input type="text" id="description" name="description" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter expense description">
                            </div>

                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">₱</span>
                                    <input type="number" id="amount" name="amount" required step="0.01" min="0"
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0.00">
                                </div>
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select id="category" name="category" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select a category</option>
                                    <option value="utilities">Utilities</option>
                                    <option value="rent">Rent</option>
                                    <option value="salaries">Salaries</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="supplies">Supplies</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <button type="button" onclick="validateAndShowModal()"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Add Expense
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Expenses List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">Expense History</h2>
                            <button onclick="printExpenses()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border-2 border-blue-500 rounded-lg hover:bg-black hover:text-white transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="ri-printer-line mr-2"></i>
                                Print Expenses
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($expenses)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No expenses recorded yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($expenses as $expense): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($expense['expenses_id']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($expense['date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($expense['description']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo ucfirst(htmlspecialchars($expense['category'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ₱<?php echo number_format($expense['amount'], 2); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function validateAndShowModal() {
            const date = document.getElementById('date').value;
            const amount = document.getElementById('amount').value;
            const description = document.getElementById('description').value;
            const category = document.getElementById('category').value;
            
            if (!date || !amount || !description || !category) {
                alert('Please fill in all required fields before submitting.');
                return;
            }
            
            // Update modal content
            document.getElementById('modalDate').textContent = new Date(date).toLocaleDateString();
            document.getElementById('modalAmount').textContent = `₱${parseFloat(amount).toFixed(2)}`;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalCategory').textContent = category.charAt(0).toUpperCase() + category.slice(1);
            
            // Show modal
            document.getElementById('confirmationModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }

        function submitForm() {
            document.querySelector('form').submit();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('confirmationModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        function printExpenses() {
            const printContent = document.querySelector('.overflow-x-auto').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div class="p-8">
                    <h1 class="text-2xl font-bold mb-4">Expense Report</h1>
                    <p class="mb-4">Generated on: ${new Date().toLocaleDateString()}</p>
                    ${printContent}
                </div>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            
            // Reattach event listeners after printing
            document.querySelector('button[onclick="printExpenses()"]').addEventListener('click', printExpenses);
        }
    </script>
</body>
</html> 