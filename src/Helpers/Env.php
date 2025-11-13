<?php

namespace App\Helpers;

/**
 * Environment variable loader and accessor
 * Loads .env file and provides type-safe access to environment variables
 */
class Env
{
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load environment variables from .env file
     *
     * @param string $path Path to .env file
     * @return void
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = dirname(__DIR__, 2) . '/.env';
        }

        if (!file_exists($path)) {
            throw new \RuntimeException(".env file not found at: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes
                $value = trim($value, '"\'');

                // Store in static array and set as environment variable
                self::$variables[$key] = $value;
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable value
     *
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Get boolean environment variable
     *
     * @param string $key Variable name
     * @param bool $default Default value
     * @return bool
     */
    public static function getBool($key, $default = false)
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get integer environment variable
     *
     * @param string $key Variable name
     * @param int $default Default value
     * @return int
     */
    public static function getInt($key, $default = 0)
    {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }

        return (int) $value;
    }
}
