#!/usr/bin/env php
<?php

/**
 * Log Rotation Script
 * 
 * Rotates application logs to prevent them from growing too large.
 * - Compresses logs older than 7 days
 * - Deletes logs older than 30 days
 * 
 * Usage: php scripts/rotate-logs.php
 * Cron: 0 3 * * * cd /home/c/ch167436/3dPrint && php scripts/rotate-logs.php >> logs/cron.log 2>&1
 */

declare(strict_types=1);

// Configuration
$logDirectory = __DIR__ . '/../logs';
$compressAfterDays = 7;
$deleteAfterDays = 30;
$compressExtensions = ['log', 'txt'];

// ANSI colors for terminal output
$colors = [
    'red' => "\033[0;31m",
    'green' => "\033[0;32m",
    'yellow' => "\033[1;33m",
    'blue' => "\033[0;34m",
    'reset' => "\033[0m"
];

/**
 * Log message with timestamp
 */
function logMessage(string $message, string $color = 'reset'): void
{
    global $colors;
    $timestamp = date('Y-m-d H:i:s');
    echo $colors[$color] . "[{$timestamp}] {$message}" . $colors['reset'] . PHP_EOL;
}

/**
 * Get human-readable file size
 */
function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Compress a log file
 */
function compressFile(string $filePath): bool
{
    $gzPath = $filePath . '.gz';
    
    // Don't compress if already compressed or if gz file exists
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'gz' || file_exists($gzPath)) {
        return false;
    }
    
    try {
        $originalSize = filesize($filePath);
        
        // Read original file
        $data = file_get_contents($filePath);
        if ($data === false) {
            logMessage("Failed to read file: {$filePath}", 'red');
            return false;
        }
        
        // Compress and write
        $gz = gzopen($gzPath, 'wb9');
        if ($gz === false) {
            logMessage("Failed to create compressed file: {$gzPath}", 'red');
            return false;
        }
        
        gzwrite($gz, $data);
        gzclose($gz);
        
        // Verify compressed file
        if (file_exists($gzPath) && filesize($gzPath) > 0) {
            $compressedSize = filesize($gzPath);
            $ratio = round((1 - $compressedSize / $originalSize) * 100, 1);
            
            // Delete original
            unlink($filePath);
            
            logMessage(
                "Compressed: " . basename($filePath) . 
                " (" . formatBytes($originalSize) . " → " . formatBytes($compressedSize) . 
                ", saved {$ratio}%)",
                'green'
            );
            
            return true;
        } else {
            logMessage("Compressed file is invalid: {$gzPath}", 'red');
            if (file_exists($gzPath)) {
                unlink($gzPath);
            }
            return false;
        }
    } catch (Exception $e) {
        logMessage("Error compressing {$filePath}: " . $e->getMessage(), 'red');
        return false;
    }
}

/**
 * Delete a file
 */
function deleteFile(string $filePath): bool
{
    try {
        if (unlink($filePath)) {
            logMessage("Deleted: " . basename($filePath), 'yellow');
            return true;
        }
        return false;
    } catch (Exception $e) {
        logMessage("Error deleting {$filePath}: " . $e->getMessage(), 'red');
        return false;
    }
}

// Main execution
try {
    logMessage("Starting log rotation...", 'blue');
    
    if (!is_dir($logDirectory)) {
        logMessage("Log directory does not exist: {$logDirectory}", 'red');
        exit(1);
    }
    
    $now = time();
    $compressCutoff = $now - ($compressAfterDays * 86400);
    $deleteCutoff = $now - ($deleteAfterDays * 86400);
    
    $filesScanned = 0;
    $filesCompressed = 0;
    $filesDeleted = 0;
    $spaceFreed = 0;
    
    // Scan log directory
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($logDirectory),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $file) {
        if (!$file->isFile()) {
            continue;
        }
        
        $filePath = $file->getPathname();
        $fileName = $file->getFilename();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mtime = $file->getMTime();
        $age = $now - $mtime;
        $ageDays = floor($age / 86400);
        
        // Skip certain files
        if (in_array($fileName, ['.gitkeep', '.htaccess', 'README.md'])) {
            continue;
        }
        
        $filesScanned++;
        
        // Delete old compressed logs
        if ($extension === 'gz' && $mtime < $deleteCutoff) {
            $fileSize = $file->getSize();
            if (deleteFile($filePath)) {
                $filesDeleted++;
                $spaceFreed += $fileSize;
            }
        }
        // Compress old logs
        elseif (in_array($extension, $compressExtensions) && $mtime < $compressCutoff) {
            $originalSize = $file->getSize();
            if (compressFile($filePath)) {
                $filesCompressed++;
                $compressedSize = file_exists($filePath . '.gz') ? filesize($filePath . '.gz') : 0;
                $spaceFreed += ($originalSize - $compressedSize);
            }
        }
    }
    
    // Summary
    logMessage("", 'reset');
    logMessage("=== Log Rotation Summary ===", 'blue');
    logMessage("Files scanned: {$filesScanned}", 'reset');
    logMessage("Files compressed: {$filesCompressed}", 'green');
    logMessage("Files deleted: {$filesDeleted}", 'yellow');
    logMessage("Space freed: " . formatBytes($spaceFreed), 'green');
    
    // Calculate total log directory size
    $totalSize = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($logDirectory)) as $file) {
        if ($file->isFile()) {
            $totalSize += $file->getSize();
        }
    }
    logMessage("Total log directory size: " . formatBytes($totalSize), 'reset');
    
    logMessage("✓ Log rotation completed", 'blue');
    exit(0);
    
} catch (Exception $e) {
    logMessage("Error during log rotation: " . $e->getMessage(), 'red');
    exit(1);
}
