<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\Env;

/**
 * Authentication middleware
 * Protects routes that require user authentication
 */
class Auth
{
    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function check()
    {
        Session::start();
        return Session::has('user_id') && Session::has('username');
    }

    /**
     * Get current authenticated user ID
     *
     * @return int|null
     */
    public static function userId()
    {
        return Session::get('user_id');
    }

    /**
     * Get current authenticated username
     *
     * @return string|null
     */
    public static function username()
    {
        return Session::get('username');
    }

    /**
     * Get current authenticated user data
     *
     * @return array|null
     */
    public static function user()
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => self::userId(),
            'username' => self::username(),
            'email' => Session::get('user_email')
        ];
    }

    /**
     * Require authentication (redirect if not authenticated)
     *
     * @param string $redirectTo URL to redirect to if not authenticated
     * @return void
     */
    public static function require($redirectTo = null)
    {
        if (!self::check()) {
            if ($redirectTo === null) {
                $redirectTo = Env::get('APP_URL', '') . '/public/login.php';
            }

            Session::flash('error', 'Please log in to access this page');
            header("Location: {$redirectTo}");
            exit;
        }
    }

    /**
     * Log in user
     *
     * @param array $user User data
     * @return void
     */
    public static function login(array $user)
    {
        Session::start();
        Session::regenerate(); // Prevent session fixation

        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('user_email', $user['email'] ?? null);
    }

    /**
     * Log out user
     *
     * @return void
     */
    public static function logout()
    {
        Session::destroy();
    }

    /**
     * Redirect to home if already authenticated
     *
     * @param string $redirectTo URL to redirect to if authenticated
     * @return void
     */
    public static function guest($redirectTo = null)
    {
        if (self::check()) {
            if ($redirectTo === null) {
                $redirectTo = Env::get('APP_URL', '') . '/public/index.php';
            }

            header("Location: {$redirectTo}");
            exit;
        }
    }
}
