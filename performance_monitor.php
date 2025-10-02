<?php
/**
 * Performance Monitor for Admin Dashboard
 * Tracks page load times and database performance
 */

class PerformanceMonitor {
    private $startTime;
    private $queries = [];
    private $logFile = 'logs/performance.log';
    
    public function __construct() {
        $this->startTime = microtime(true);
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Start monitoring a database query
     */
    public function startQuery($sql) {
        return [
            'sql' => $sql,
            'start_time' => microtime(true)
        ];
    }
    
    /**
     * End monitoring a database query
     */
    public function endQuery($queryData) {
        $endTime = microtime(true);
        $duration = ($endTime - $queryData['start_time']) * 1000; // Convert to milliseconds
        
        $this->queries[] = [
            'sql' => $queryData['sql'],
            'duration' => $duration
        ];
        
        // Log slow queries (over 100ms)
        if ($duration > 100) {
            $this->logSlowQuery($queryData['sql'], $duration);
        }
    }
    
    /**
     * Get performance summary
     */
    public function getSummary() {
        $totalTime = (microtime(true) - $this->startTime) * 1000;
        $totalQueries = count($this->queries);
        $avgQueryTime = $totalQueries > 0 ? array_sum(array_column($this->queries, 'duration')) / $totalQueries : 0;
        $slowQueries = array_filter($this->queries, function($q) { return $q['duration'] > 100; });
        
        return [
            'total_time' => $totalTime,
            'total_queries' => $totalQueries,
            'avg_query_time' => $avgQueryTime,
            'slow_queries' => count($slowQueries),
            'memory_usage' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * Log slow query
     */
    private function logSlowQuery($sql, $duration) {
        $logEntry = date('Y-m-d H:i:s') . " - Slow Query ({$duration}ms): " . substr($sql, 0, 200) . "\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log performance summary
     */
    public function logSummary() {
        $summary = $this->getSummary();
        $logEntry = date('Y-m-d H:i:s') . " - Page Load: {$summary['total_time']}ms, Queries: {$summary['total_queries']}, Avg: {$summary['avg_query_time']}ms, Memory: " . number_format($summary['memory_usage']) . " bytes\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// Usage example (add to admin.php):
// $monitor = new PerformanceMonitor();
// 
// // Before database query:
// $queryData = $monitor->startQuery($sql);
// $result = $stmt->execute();
// $monitor->endQuery($queryData);
// 
// // At end of page:
// $monitor->logSummary();
?> 