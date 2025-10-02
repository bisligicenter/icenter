<?php
require_once 'db.php';
require_once 'error_management.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'clear_log':
                if (clearErrorLog()) {
                    $message = "Error log cleared successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to clear error log.";
                    $messageType = "error";
                }
                break;
                
            case 'rotate_log':
                if (rotateErrorLog()) {
                    $message = "Error log rotated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to rotate error log.";
                    $messageType = "error";
                }
                break;
        }
    }
}

// Analyze current error log
$analysis = analyzeErrorLog();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Log Manager - Bislig iCenter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .log-line {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .error-line { background-color: #fef2f2; border-left: 4px solid #ef4444; }
        .warning-line { background-color: #fffbeb; border-left: 4px solid #f59e0b; }
        .notice-line { background-color: #f0f9ff; border-left: 4px solid #3b82f6; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Error Log Manager</h1>
                <a href="admin.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Back to Admin</a>
            </div>

            <?php if (isset($message)): ?>
                <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Log Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800">File Size</h3>
                    <p class="text-2xl font-bold text-blue-600">
                        <?php echo number_format($analysis['file_size'] / 1024 / 1024, 2); ?> MB
                    </p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800">Total Lines</h3>
                    <p class="text-2xl font-bold text-green-600">
                        <?php echo number_format($analysis['total_lines']); ?>
                    </p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-yellow-800">Error Types</h3>
                    <p class="text-2xl font-bold text-yellow-600">
                        <?php echo count($analysis['error_types']); ?>
                    </p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-red-800">Recent Errors</h3>
                    <p class="text-2xl font-bold text-red-600">
                        <?php echo count($analysis['recent_errors']); ?>
                    </p>
                </div>
            </div>

            <!-- Error Type Breakdown -->
            <?php if (!empty($analysis['error_types'])): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-3">Error Type Breakdown</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($analysis['error_types'] as $type => $count): ?>
                            <div class="bg-gray-50 p-3 rounded">
                                <span class="font-medium text-gray-700"><?php echo ucfirst($type); ?></span>
                                <span class="float-right font-bold text-gray-900"><?php echo $count; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recommendations -->
            <?php if (!empty($analysis['recommendations'])): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-3 text-orange-800">Recommendations</h2>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($analysis['recommendations'] as $recommendation): ?>
                                <li class="text-orange-800"><?php echo htmlspecialchars($recommendation); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="mb-6 flex space-x-4">
                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to clear the error log? This action cannot be undone.');">
                    <input type="hidden" name="action" value="clear_log">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Clear Error Log
                    </button>
                </form>
                
                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to rotate the error log? This will create a backup.');">
                    <input type="hidden" name="action" value="rotate_log">
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                        Rotate Error Log
                    </button>
                </form>
            </div>

            <!-- Recent Errors -->
            <?php if (!empty($analysis['recent_errors'])): ?>
                <div>
                    <h2 class="text-lg font-semibold mb-3">Recent Errors (Last 50 lines)</h2>
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-auto max-h-96">
                        <?php foreach ($analysis['recent_errors'] as $line): ?>
                            <?php
                            $lineClass = '';
                            if (stripos($line, 'fatal error') !== false || stripos($line, 'parse error') !== false) {
                                $lineClass = 'error-line';
                            } elseif (stripos($line, 'warning') !== false) {
                                $lineClass = 'warning-line';
                            } elseif (stripos($line, 'notice') !== false) {
                                $lineClass = 'notice-line';
                            }
                            ?>
                            <div class="log-line <?php echo $lineClass; ?> p-1">
                                <?php echo htmlspecialchars($line); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>No recent errors found or error log is empty.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 