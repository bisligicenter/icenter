<?php
/**
 * Error Management and Log Analysis
 * This file helps manage and analyze error logs
 */

// Function to analyze error log
function analyzeErrorLog($logFile = 'php_error.log') {
    if (!file_exists($logFile)) {
        return ['error' => 'Log file not found'];
    }
    
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    
    $analysis = [
        'total_lines' => count($lines),
        'file_size' => filesize($logFile),
        'error_types' => [],
        'recent_errors' => [],
        'recommendations' => []
    ];
    
    // Get last 50 lines for recent errors
    $recentLines = array_slice($lines, -50);
    
    foreach ($recentLines as $line) {
        if (empty(trim($line))) continue;
        
        // Extract error type
        if (preg_match('/PHP (Warning|Error|Notice|Fatal error|Parse error)/i', $line, $matches)) {
            $errorType = strtolower($matches[1]);
            $analysis['error_types'][$errorType] = ($analysis['error_types'][$errorType] ?? 0) + 1;
        }
        
        $analysis['recent_errors'][] = $line;
    }
    
    // Generate recommendations
    if ($analysis['file_size'] > 5 * 1024 * 1024) { // 5MB
        $analysis['recommendations'][] = 'Error log is very large. Consider rotating logs.';
    }
    
    if (isset($analysis['error_types']['fatal error'])) {
        $analysis['recommendations'][] = 'Fatal errors detected. Check for syntax errors or missing files.';
    }
    
    if (isset($analysis['error_types']['parse error'])) {
        $analysis['recommendations'][] = 'Parse errors detected. Check PHP syntax in your files.';
    }
    
    return $analysis;
}

// Function to clear error log
function clearErrorLog($logFile = 'php_error.log') {
    if (file_exists($logFile)) {
        return file_put_contents($logFile, '') !== false;
    }
    return false;
}

// Function to rotate error log
function rotateErrorLog($logFile = 'php_error.log') {
    if (!file_exists($logFile)) {
        return false;
    }
    
    $backupFile = $logFile . '.' . date('Y-m-d-H-i-s');
    return rename($logFile, $backupFile);
}

// Usage example (uncomment to use):
/*
$analysis = analyzeErrorLog();
echo "<pre>";
print_r($analysis);
echo "</pre>";
*/
?> 