#!/usr/bin/env php
<?php

/**
 * Error Monitoring Script
 * 
 * Checks application logs for errors in the last hour and sends email alerts
 * if critical errors are found.
 * 
 * Usage: php scripts/check-errors.php
 * Cron: 0 * * * * cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Container;

// Configuration
$logFile = __DIR__ . '/../logs/app.log';
$monitoringLog = __DIR__ . '/../logs/monitoring.log';
$errorThreshold = 5; // Send alert if more than this many errors
$checkPeriodHours = 1;

// Get admin email from environment
$adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com';
$siteUrl = $_ENV['SITE_URL'] ?? $_ENV['APP_URL'] ?? 'http://localhost';

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
 * Send email alert
 */
function sendAlert(string $subject, string $body, string $adminEmail): bool
{
    try {
        $container = Container::getInstance();
        $mailer = $container->get('mailer');
        
        return $mailer->send([
            'to' => $adminEmail,
            'subject' => $subject,
            'body' => $body,
            'isHtml' => false
        ]);
    } catch (Exception $e) {
        logMessage("Failed to send email: " . $e->getMessage(), 'red');
        return false;
    }
}

/**
 * Parse log file and extract errors
 */
function parseLogFile(string $logFile, int $hours): array
{
    if (!file_exists($logFile)) {
        logMessage("Log file not found: {$logFile}", 'yellow');
        return [];
    }
    
    $errors = [];
    $cutoffTime = time() - ($hours * 3600);
    
    $handle = fopen($logFile, 'r');
    if (!$handle) {
        logMessage("Failed to open log file: {$logFile}", 'red');
        return [];
    }
    
    while (($line = fgets($handle)) !== false) {
        // Match log format: [2024-01-01 12:00:00] LEVEL: Message
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (ERROR|CRITICAL|ALERT|EMERGENCY): (.+)$/', $line, $matches)) {
            $timestamp = strtotime($matches[1]);
            $level = $matches[2];
            $message = $matches[3];
            
            if ($timestamp >= $cutoffTime) {
                $errors[] = [
                    'timestamp' => $matches[1],
                    'level' => $level,
                    'message' => $message
                ];
            }
        }
    }
    
    fclose($handle);
    return $errors;
}

/**
 * Group errors by message (to avoid duplicate alerts)
 */
function groupErrors(array $errors): array
{
    $grouped = [];
    
    foreach ($errors as $error) {
        $key = md5($error['message']);
        
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'message' => $error['message'],
                'level' => $error['level'],
                'count' => 0,
                'first_occurrence' => $error['timestamp'],
                'last_occurrence' => $error['timestamp']
            ];
        }
        
        $grouped[$key]['count']++;
        $grouped[$key]['last_occurrence'] = $error['timestamp'];
    }
    
    return array_values($grouped);
}

/**
 * Generate alert email body
 */
function generateAlertBody(array $groupedErrors, int $totalErrors, string $siteUrl): string
{
    $body = "ERROR ALERT\n";
    $body .= "====================\n\n";
    $body .= "Site: {$siteUrl}\n";
    $body .= "Server: " . gethostname() . "\n";
    $body .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $body .= "Total Errors: {$totalErrors}\n";
    $body .= "Unique Error Types: " . count($groupedErrors) . "\n\n";
    
    $body .= "ERROR DETAILS\n";
    $body .= "====================\n\n";
    
    foreach ($groupedErrors as $index => $error) {
        $body .= sprintf(
            "%d. [%s] (Count: %d)\n",
            $index + 1,
            $error['level'],
            $error['count']
        );
        $body .= "   Message: {$error['message']}\n";
        $body .= "   First: {$error['first_occurrence']}\n";
        $body .= "   Last: {$error['last_occurrence']}\n\n";
    }
    
    $body .= "\n";
    $body .= "Please check the application logs for more details:\n";
    $body .= "logs/app.log\n";
    
    return $body;
}

// Main execution
try {
    logMessage("Starting error monitoring check...", 'blue');
    
    // Parse log file
    $errors = parseLogFile($logFile, $checkPeriodHours);
    $errorCount = count($errors);
    
    if ($errorCount === 0) {
        logMessage("✓ No errors found in the last {$checkPeriodHours} hour(s)", 'green');
        exit(0);
    }
    
    logMessage("Found {$errorCount} error(s) in the last {$checkPeriodHours} hour(s)", 'yellow');
    
    // Check if threshold exceeded
    if ($errorCount > $errorThreshold) {
        logMessage("⚠ Error threshold exceeded ({$errorCount} > {$errorThreshold})", 'red');
        
        // Group errors
        $groupedErrors = groupErrors($errors);
        
        // Generate alert
        $subject = "[ALERT] {$errorCount} Errors Detected - {$siteUrl}";
        $body = generateAlertBody($groupedErrors, $errorCount, $siteUrl);
        
        // Send alert
        logMessage("Sending email alert to {$adminEmail}...", 'yellow');
        if (sendAlert($subject, $body, $adminEmail)) {
            logMessage("✓ Alert email sent successfully", 'green');
        } else {
            logMessage("✗ Failed to send alert email", 'red');
        }
    } else {
        logMessage("Error count within acceptable threshold ({$errorCount} <= {$errorThreshold})", 'green');
    }
    
    // Log summary to monitoring log
    $summary = sprintf(
        "Errors found: %d | Threshold: %d | Status: %s",
        $errorCount,
        $errorThreshold,
        $errorCount > $errorThreshold ? 'ALERT SENT' : 'OK'
    );
    
    file_put_contents(
        $monitoringLog,
        "[" . date('Y-m-d H:i:s') . "] {$summary}\n",
        FILE_APPEND
    );
    
    logMessage("✓ Monitoring check completed", 'blue');
    exit(0);
    
} catch (Exception $e) {
    logMessage("Error during monitoring: " . $e->getMessage(), 'red');
    exit(1);
}
