<?php

namespace App\Middleware;

use App\Helpers\Session;
use App\Helpers\Logger;

/**
 * Role-Based Access Control Middleware
 * Manages user roles and permissions
 */
class RBAC
{
    // Role hierarchy (higher number = more permissions)
    const ROLES = [
        'subscriber' => 1,
        'author' => 2,
        'editor' => 3,
        'admin' => 4
    ];

    /**
     * Get current user's role
     *
     * @return string|null
     */
    public static function getUserRole()
    {
        return Session::get('user_role', 'subscriber');
    }

    /**
     * Check if user has a specific role
     *
     * @param string $role Role to check
     * @return bool
     */
    public static function hasRole($role)
    {
        $userRole = self::getUserRole();
        return $userRole === $role;
    }

    /**
     * Check if user has role level or higher
     *
     * @param string $minimumRole Minimum required role
     * @return bool
     */
    public static function hasRoleOrHigher($minimumRole)
    {
        $userRole = self::getUserRole();
        $userLevel = self::ROLES[$userRole] ?? 0;
        $requiredLevel = self::ROLES[$minimumRole] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return self::hasRole('admin');
    }

    /**
     * Check if user is editor or higher
     *
     * @return bool
     */
    public static function isEditor()
    {
        return self::hasRoleOrHigher('editor');
    }

    /**
     * Check if user is author or higher
     *
     * @return bool
     */
    public static function isAuthor()
    {
        return self::hasRoleOrHigher('author');
    }

    /**
     * Require specific role or redirect
     *
     * @param string $role Required role
     * @param string|null $redirectTo URL to redirect if unauthorized
     * @return void
     */
    public static function requireRole($role, $redirectTo = null)
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to access this page');
            header('Location: ' . ($redirectTo ?? '/public/login.php'));
            exit;
        }

        if (!self::hasRole($role)) {
            Logger::warning('Unauthorized access attempt', [
                'user_id' => Auth::userId(),
                'required_role' => $role,
                'user_role' => self::getUserRole()
            ]);

            Session::flash('error', 'You do not have permission to access this page');
            header('Location: ' . ($redirectTo ?? '/public/index.php'));
            exit;
        }
    }

    /**
     * Require minimum role level or redirect
     *
     * @param string $minimumRole Minimum required role
     * @param string|null $redirectTo URL to redirect if unauthorized
     * @return void
     */
    public static function requireRoleOrHigher($minimumRole, $redirectTo = null)
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to access this page');
            header('Location: ' . ($redirectTo ?? '/public/login.php'));
            exit;
        }

        if (!self::hasRoleOrHigher($minimumRole)) {
            Logger::warning('Unauthorized access attempt', [
                'user_id' => Auth::userId(),
                'required_role' => $minimumRole,
                'user_role' => self::getUserRole()
            ]);

            Session::flash('error', 'You do not have permission to access this page');
            header('Location: ' . ($redirectTo ?? '/public/index.php'));
            exit;
        }
    }

    /**
     * Check if user can edit post
     *
     * @param array $post Post data
     * @return bool
     */
    public static function canEditPost($post)
    {
        if (!Auth::check()) {
            return false;
        }

        // Admins and editors can edit any post
        if (self::hasRoleOrHigher('editor')) {
            return true;
        }

        // Authors can only edit their own posts
        if (self::isAuthor() && $post['author_id'] == Auth::userId()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can delete post
     *
     * @param array $post Post data
     * @return bool
     */
    public static function canDeletePost($post)
    {
        if (!Auth::check()) {
            return false;
        }

        // Admins can delete any post
        if (self::isAdmin()) {
            return true;
        }

        // Editors can delete published posts
        if (self::isEditor()) {
            return true;
        }

        // Authors can only delete their own posts
        if (self::isAuthor() && $post['author_id'] == Auth::userId()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can publish posts
     *
     * @return bool
     */
    public static function canPublish()
    {
        return self::hasRoleOrHigher('author');
    }

    /**
     * Check if user can manage users
     *
     * @return bool
     */
    public static function canManageUsers()
    {
        return self::isAdmin();
    }

    /**
     * Check if user can manage comments
     *
     * @return bool
     */
    public static function canManageComments()
    {
        return self::hasRoleOrHigher('editor');
    }

    /**
     * Get all available roles
     *
     * @return array
     */
    public static function getAllRoles()
    {
        return array_keys(self::ROLES);
    }

    /**
     * Get role display name
     *
     * @param string $role Role key
     * @return string
     */
    public static function getRoleDisplayName($role)
    {
        return ucfirst($role);
    }
}
