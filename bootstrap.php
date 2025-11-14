<?php

/**
 * Bootstrap file - Initialize application
 * Loads environment variables, sets up autoloading, and configures PHP settings
 */

// Define base path
define('BASE_PATH', __DIR__);
define('SRC_PATH', BASE_PATH . '/src');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load environment variables
require_once SRC_PATH . '/Helpers/Env.php';
App\Helpers\Env::load(BASE_PATH . '/.env');

// Set timezone
date_default_timezone_set(App\Helpers\Env::get('APP_TIMEZONE', 'UTC'));

// Set error reporting based on environment
if (App\Helpers\Env::getBool('APP_DEBUG', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Register autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    // App\Controllers\PostController => src/Controllers/PostController.php
    $prefix = 'App\\';
    $base_dir = SRC_PATH . '/';

    // Check if class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separator with directory separator
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});

// Start session
App\Helpers\Session::start();

// Set custom error handler to log errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $errorType = match ($errno) {
        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => 'ERROR',
        E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => 'WARNING',
        E_NOTICE, E_USER_NOTICE => 'NOTICE',
        default => 'UNKNOWN'
    };

    App\Helpers\Logger::error("PHP {$errorType}: {$errstr} in {$errfile}:{$errline}");

    // Let PHP handle the error normally
    return false;
});

// Set custom exception handler
set_exception_handler(function ($exception) {
    App\Helpers\Logger::critical('Uncaught exception: ' . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (App\Helpers\Env::getBool('APP_DEBUG', false)) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>An error occurred</h1>';
        echo '<p>Please try again later.</p>';
    }
});
