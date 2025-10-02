<?php
/**
 * Cache Manager for Admin Dashboard
 * Handles cache cleanup and optimization
 */

class CacheManager {
    private $cacheDir = 'cache/';
    private $cacheFiles = [
        'categories_cache.json' => 300, // 5 minutes
        'products_cache.json' => 180,   // 3 minutes
        'stats_cache.json' => 600       // 10 minutes
    ];
    
    public function __construct() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Clean expired cache files
     */
    public function cleanExpiredCache() {
        $cleaned = 0;
        
        foreach ($this->cacheFiles as $filename => $maxAge) {
            $filepath = $this->cacheDir . $filename;
            
            if (file_exists($filepath)) {
                $fileAge = time() - filemtime($filepath);
                
                if ($fileAge > $maxAge) {
                    unlink($filepath);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Clear all cache files
     */
    public function clearAllCache() {
        $cleared = 0;
        
        foreach (array_keys($this->cacheFiles) as $filename) {
            $filepath = $this->cacheDir . $filename;
            
            if (file_exists($filepath)) {
                unlink($filepath);
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Get cache statistics
     */
    public function getCacheStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'expired_files' => 0
        ];
        
        foreach ($this->cacheFiles as $filename => $maxAge) {
            $filepath = $this->cacheDir . $filename;
            
            if (file_exists($filepath)) {
                $stats['total_files']++;
                $stats['total_size'] += filesize($filepath);
                
                $fileAge = time() - filemtime($filepath);
                if ($fileAge > $maxAge) {
                    $stats['expired_files']++;
                }
            }
        }
        
        return $stats;
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $cacheManager = new CacheManager();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'clean':
                $cleaned = $cacheManager->cleanExpiredCache();
                echo "Cleaned $cleaned expired cache files.\n";
                break;
                
            case 'clear':
                $cleared = $cacheManager->clearAllCache();
                echo "Cleared $cleared cache files.\n";
                break;
                
            case 'stats':
                $stats = $cacheManager->getCacheStats();
                echo "Cache Statistics:\n";
                echo "- Total files: {$stats['total_files']}\n";
                echo "- Total size: " . number_format($stats['total_size']) . " bytes\n";
                echo "- Expired files: {$stats['expired_files']}\n";
                break;
                
            default:
                echo "Usage: php cache_manager.php [clean|clear|stats]\n";
        }
    } else {
        echo "Usage: php cache_manager.php [clean|clear|stats]\n";
    }
}
?> 