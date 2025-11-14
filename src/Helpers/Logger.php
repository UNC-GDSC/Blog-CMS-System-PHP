<?php

namespace App\Helpers;

/**
 * Simple file-based logger with log levels
 */
class Logger
{
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';

    private static $instance = null;
    private $logPath;
    private $logLevel;

    private function __construct()
    {
        $this->logPath = Env::get('LOG_PATH', 'logs/app.log');
        $this->logLevel = strtoupper(Env::get('LOG_LEVEL', 'INFO'));

        // Ensure log directory exists
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Get logger instance (Singleton)
     *
     * @return Logger
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log a message
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    private function log($level, $message, array $context = [])
    {
        $levels = [self::DEBUG => 0, self::INFO => 1, self::WARNING => 2, self::ERROR => 3, self::CRITICAL => 4];

        if (!isset($levels[$level]) || !isset($levels[$this->logLevel])) {
            return;
        }

        if ($levels[$level] < $levels[$this->logLevel]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($this->logPath, $logMessage, FILE_APPEND);
    }

    /**
     * Log debug message
     */
    public static function debug($message, array $context = [])
    {
        self::getInstance()->log(self::DEBUG, $message, $context);
    }

    /**
     * Log info message
     */
    public static function info($message, array $context = [])
    {
        self::getInstance()->log(self::INFO, $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning($message, array $context = [])
    {
        self::getInstance()->log(self::WARNING, $message, $context);
    }

    /**
     * Log error message
     */
    public static function error($message, array $context = [])
    {
        self::getInstance()->log(self::ERROR, $message, $context);
    }

    /**
     * Log critical message
     */
    public static function critical($message, array $context = [])
    {
        self::getInstance()->log(self::CRITICAL, $message, $context);
    }
}
