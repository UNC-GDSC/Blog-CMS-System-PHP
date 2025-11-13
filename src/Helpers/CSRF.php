<?php

namespace App\Helpers;

/**
 * CSRF (Cross-Site Request Forgery) protection helper
 */
class CSRF
{
    /**
     * Generate CSRF token
     *
     * @return string
     */
    public static function generateToken()
    {
        Session::start();

        $token = bin2hex(random_bytes(32));
        $expiry = time() + Env::getInt('CSRF_TOKEN_EXPIRY', 3600);

        Session::set('csrf_token', $token);
        Session::set('csrf_token_expiry', $expiry);

        return $token;
    }

    /**
     * Get current CSRF token (generate if not exists)
     *
     * @return string
     */
    public static function getToken()
    {
        Session::start();

        $token = Session::get('csrf_token');
        $expiry = Session::get('csrf_token_expiry', 0);

        // Generate new token if doesn't exist or expired
        if (!$token || time() > $expiry) {
            return self::generateToken();
        }

        return $token;
    }

    /**
     * Validate CSRF token
     *
     * @param string $token Token to validate
     * @return bool
     */
    public static function validateToken($token)
    {
        Session::start();

        $storedToken = Session::get('csrf_token');
        $expiry = Session::get('csrf_token_expiry', 0);

        if (!$storedToken || time() > $expiry) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Generate CSRF token HTML input field
     *
     * @return string
     */
    public static function field()
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Verify CSRF token from request
     * Throws exception if invalid
     *
     * @throws \RuntimeException
     * @return void
     */
    public static function verify()
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        if (!self::validateToken($token)) {
            Logger::warning('CSRF token validation failed', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
            throw new \RuntimeException('CSRF token validation failed');
        }
    }
}
