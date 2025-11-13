<?php

namespace App\Middleware;

use App\Helpers\Logger;
use App\Helpers\Session;

/**
 * Rate limiting middleware
 * Prevents abuse by limiting request frequency
 */
class RateLimiter
{
    private static $storageDir = 'storage/rate_limit';
    private static $enabled = true;

    /**
     * Initialize rate limiter
     */
    private static function init()
    {
        if (!is_dir(self::$storageDir)) {
            mkdir(self::$storageDir, 0755, true);
        }
    }

    /**
     * Check if request should be rate limited
     *
     * @param string $key Unique identifier (IP, user ID, etc.)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decaySeconds Time window in seconds
     * @return bool True if rate limit exceeded
     */
    public static function tooManyAttempts($key, $maxAttempts = 60, $decaySeconds = 60)
    {
        if (!self::$enabled) {
            return false;
        }

        self::init();

        $filepath = self::getFilePath($key);
        $now = time();

        // Get current attempts
        $attempts = self::getAttempts($filepath, $now, $decaySeconds);

        // Check if limit exceeded
        if ($attempts >= $maxAttempts) {
            Logger::warning('Rate limit exceeded', ['key' => $key, 'attempts' => $attempts]);
            return true;
        }

        return false;
    }

    /**
     * Record an attempt
     *
     * @param string $key Unique identifier
     * @return void
     */
    public static function hit($key)
    {
        if (!self::$enabled) {
            return;
        }

        self::init();

        $filepath = self::getFilePath($key);
        $now = time();

        // Get current attempts
        $data = self::loadData($filepath);
        $data['attempts'][] = $now;

        // Save updated data
        file_put_contents($filepath, json_encode($data), LOCK_EX);
    }

    /**
     * Clear attempts for key
     *
     * @param string $key Unique identifier
     * @return void
     */
    public static function clear($key)
    {
        $filepath = self::getFilePath($key);

        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Get remaining attempts
     *
     * @param string $key Unique identifier
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decaySeconds Time window in seconds
     * @return int
     */
    public static function remaining($key, $maxAttempts = 60, $decaySeconds = 60)
    {
        $filepath = self::getFilePath($key);
        $now = time();

        $attempts = self::getAttempts($filepath, $now, $decaySeconds);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get seconds until rate limit resets
     *
     * @param string $key Unique identifier
     * @param int $decaySeconds Time window in seconds
     * @return int
     */
    public static function availableIn($key, $decaySeconds = 60)
    {
        $filepath = self::getFilePath($key);
        $data = self::loadData($filepath);

        if (empty($data['attempts'])) {
            return 0;
        }

        $oldestAttempt = min($data['attempts']);
        $resetsAt = $oldestAttempt + $decaySeconds;
        $now = time();

        return max(0, $resetsAt - $now);
    }

    /**
     * Throttle a callback (login, API, etc.)
     *
     * @param string $key Unique identifier
     * @param int $maxAttempts Maximum attempts
     * @param int $decaySeconds Time window
     * @param callable $callback Callback to execute if not throttled
     * @param callable $throttledCallback Callback if throttled
     * @return mixed
     */
    public static function attempt($key, $maxAttempts, $decaySeconds, callable $callback, callable $throttledCallback = null)
    {
        if (self::tooManyAttempts($key, $maxAttempts, $decaySeconds)) {
            $seconds = self::availableIn($key, $decaySeconds);

            if ($throttledCallback) {
                return $throttledCallback($seconds);
            }

            Session::flash('error', "Too many attempts. Please try again in {$seconds} seconds.");
            return false;
        }

        self::hit($key);
        return $callback();
    }

    /**
     * Get file path for key
     *
     * @param string $key
     * @return string
     */
    private static function getFilePath($key)
    {
        $hash = md5($key);
        return self::$storageDir . '/' . $hash . '.json';
    }

    /**
     * Load data from file
     *
     * @param string $filepath
     * @return array
     */
    private static function loadData($filepath)
    {
        if (!file_exists($filepath)) {
            return ['attempts' => []];
        }

        $json = file_get_contents($filepath);
        $data = json_decode($json, true);

        return $data ?: ['attempts' => []];
    }

    /**
     * Get valid attempts count
     *
     * @param string $filepath
     * @param int $now Current timestamp
     * @param int $decaySeconds Time window
     * @return int
     */
    private static function getAttempts($filepath, $now, $decaySeconds)
    {
        $data = self::loadData($filepath);

        // Filter valid attempts (within time window)
        $validAttempts = array_filter($data['attempts'], function ($timestamp) use ($now, $decaySeconds) {
            return $timestamp > ($now - $decaySeconds);
        });

        return count($validAttempts);
    }

    /**
     * Clean old rate limit files
     *
     * @return int Number of files deleted
     */
    public static function cleanOld()
    {
        self::init();
        $count = 0;
        $now = time();
        $maxAge = 86400; // 24 hours

        $files = glob(self::$storageDir . '/*.json');
        foreach ($files as $file) {
            if ($now - filemtime($file) > $maxAge) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }
}
