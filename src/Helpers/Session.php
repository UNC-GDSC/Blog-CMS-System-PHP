<?php

namespace App\Helpers;

/**
 * Session management helper
 * Handles session initialization and secure session operations
 */
class Session
{
    private static $started = false;

    /**
     * Start session with secure configuration
     *
     * @return void
     */
    public static function start()
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $sessionName = Env::get('SESSION_NAME', 'blog_cms_session');
        $sessionLifetime = Env::getInt('SESSION_LIFETIME', 7200);

        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');

        // Enable secure cookies in production
        if (Env::get('APP_ENV') === 'production') {
            ini_set('session.cookie_secure', 1);
        }

        session_name($sessionName);
        session_set_cookie_params($sessionLifetime);
        session_start();

        self::$started = true;

        // Regenerate session ID periodically to prevent fixation
        if (!self::has('last_regeneration')) {
            self::regenerate();
        } elseif (time() - self::get('last_regeneration') > 300) { // Every 5 minutes
            self::regenerate();
        }
    }

    /**
     * Regenerate session ID
     *
     * @return void
     */
    public static function regenerate()
    {
        session_regenerate_id(true);
        self::set('last_regeneration', time());
    }

    /**
     * Set session variable
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session variable
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session variable exists
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session variable
     *
     * @param string $key
     * @return void
     */
    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Set flash message (available only for next request)
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function flash($key, $value)
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash message
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getFlash($key, $default = null)
    {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if flash message exists
     *
     * @param string $key
     * @return bool
     */
    public static function hasFlash($key)
    {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Destroy session
     *
     * @return void
     */
    public static function destroy()
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }
}
