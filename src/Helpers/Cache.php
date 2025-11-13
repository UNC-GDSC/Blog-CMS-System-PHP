<?php

namespace App\Helpers;

/**
 * Simple file-based cache implementation
 * For production, consider Redis or Memcached
 */
class Cache
{
    private static $cacheDir = 'storage/cache';
    private static $enabled = true;
    private static $defaultTTL = 3600; // 1 hour

    /**
     * Initialize cache directory
     */
    private static function init()
    {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        self::$enabled = Env::getBool('CACHE_ENABLED', true);
    }

    /**
     * Get cache file path for key
     *
     * @param string $key
     * @return string
     */
    private static function getCacheFilePath($key)
    {
        self::init();
        $hash = md5($key);
        return self::$cacheDir . '/' . $hash . '.cache';
    }

    /**
     * Store value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public static function set($key, $value, $ttl = null)
    {
        if (!self::$enabled) {
            return false;
        }

        self::init();
        $ttl = $ttl ?? self::$defaultTTL;

        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];

        $filepath = self::getCacheFilePath($key);

        try {
            file_put_contents($filepath, serialize($data), LOCK_EX);
            return true;
        } catch (\Exception $e) {
            Logger::error('Cache write error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get value from cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (!self::$enabled) {
            return $default;
        }

        $filepath = self::getCacheFilePath($key);

        if (!file_exists($filepath)) {
            return $default;
        }

        try {
            $data = unserialize(file_get_contents($filepath));

            // Check if expired
            if ($data['expires_at'] < time()) {
                unlink($filepath);
                return $default;
            }

            return $data['value'];
        } catch (\Exception $e) {
            Logger::error('Cache read error: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Check if cache key exists and is valid
     *
     * @param string $key Cache key
     * @return bool
     */
    public static function has($key)
    {
        if (!self::$enabled) {
            return false;
        }

        $filepath = self::getCacheFilePath($key);

        if (!file_exists($filepath)) {
            return false;
        }

        try {
            $data = unserialize(file_get_contents($filepath));
            return $data['expires_at'] >= time();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete cache entry
     *
     * @param string $key Cache key
     * @return bool
     */
    public static function delete($key)
    {
        $filepath = self::getCacheFilePath($key);

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    /**
     * Clear all cache
     *
     * @return int Number of files deleted
     */
    public static function clear()
    {
        self::init();
        $count = 0;

        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }

        Logger::info("Cache cleared: {$count} files deleted");
        return $count;
    }

    /**
     * Remember (get or set) cached value
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int $ttl Time to live
     * @return mixed
     */
    public static function remember($key, callable $callback, $ttl = null)
    {
        if (self::has($key)) {
            return self::get($key);
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Clean expired cache entries
     *
     * @return int Number of files deleted
     */
    public static function cleanExpired()
    {
        self::init();
        $count = 0;

        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            try {
                $data = unserialize(file_get_contents($file));
                if ($data['expires_at'] < time()) {
                    unlink($file);
                    $count++;
                }
            } catch (\Exception $e) {
                // Invalid cache file, delete it
                unlink($file);
                $count++;
            }
        }

        if ($count > 0) {
            Logger::info("Expired cache cleaned: {$count} files deleted");
        }

        return $count;
    }
}
