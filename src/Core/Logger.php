<?php

declare(strict_types=1);

namespace App\Core;

class Logger
{
    private string $logFile;
    private string $logLevel;

    private const LEVELS = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4,
    ];

    public function __construct(string $logFile, string $logLevel = 'info')
    {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
        $this->ensureLogDirectory();
    }

    private function ensureLogDirectory(): void
    {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function shouldLog(string $level): bool
    {
        $currentLevelValue = self::LEVELS[$this->logLevel] ?? 1;
        $messageLevelValue = self::LEVELS[$level] ?? 1;
        return $messageLevelValue >= $currentLevelValue;
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = sprintf(
            "[%s] %s: %s%s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $contextString
        );

        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }
}
