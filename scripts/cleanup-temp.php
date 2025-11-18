#!/usr/bin/env php
<?php

/**
 * Temporary Files Cleanup Script
 * 
 * Cleans up temporary files and old uploads:
 * - Removes temporary files older than 24 hours
 * - Cleans up incomplete uploads
 * - Removes orphaned thumbnails
 * 
 * Usage: php scripts/cleanup-temp.php
 * Cron: 0 6 * * * cd /home/c/ch167436/3dPrint && php scripts/cleanup-temp.php >> logs/cron.log 2>&1
 */

declare(strict_types=1);

// Configuration
$uploadsDir = __DIR__ . '/../uploads';
$tempMaxAge = 86400; // 24 hours

// ANSI colors
$colors = [
    'red' => "\033[0;31m",
    'green' => "\033[0;32m",
    'yellow' => "\033[1;33m",
    'blue' => "\033[0;34m",
    'reset' => "\033[0m"
];

function logMessage(string $message, string $color = 'reset'): void
{
    global $colors;
    echo $colors[$color] . "[" . date('Y-m-d H:i:s') . "] {$message}" . $colors['reset'] . PHP_EOL;
}

function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

try {
    logMessage("Starting cleanup...", 'blue');
    
    $now = time();
    $filesDeleted = 0;
    $spaceFreed = 0;
    
    // Cleanup temp directory if it exists
    $tempDir = sys_get_temp_dir();
    $appTempPattern = $tempDir . '/3dprint_*';
    
    foreach (glob($appTempPattern) as $tempFile) {
        if (is_file($tempFile) && (filemtime($tempFile) < ($now - $tempMaxAge))) {
            $size = filesize($tempFile);
            if (unlink($tempFile)) {
                $filesDeleted++;
                $spaceFreed += $size;
                logMessage("Deleted temp file: " . basename($tempFile), 'yellow');
            }
        }
    }
    
    // Cleanup uploads directory - remove .tmp files
    if (is_dir($uploadsDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            
            $filePath = $file->getPathname();
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            
            // Remove .tmp, .partial, .incomplete files older than 24 hours
            if (in_array($extension, ['tmp', 'partial', 'incomplete'])) {
                if ($file->getMTime() < ($now - $tempMaxAge)) {
                    $size = $file->getSize();
                    if (unlink($filePath)) {
                        $filesDeleted++;
                        $spaceFreed += $size;
                        logMessage("Deleted incomplete upload: " . basename($filePath), 'yellow');
                    }
                }
            }
        }
    }
    
    logMessage("", 'reset');
    logMessage("=== Cleanup Summary ===", 'blue');
    logMessage("Files deleted: {$filesDeleted}", 'yellow');
    logMessage("Space freed: " . formatBytes($spaceFreed), 'green');
    logMessage("✓ Cleanup completed", 'blue');
    
    exit(0);
    
} catch (Exception $e) {
    logMessage("Error during cleanup: " . $e->getMessage(), 'red');
    exit(1);
}
