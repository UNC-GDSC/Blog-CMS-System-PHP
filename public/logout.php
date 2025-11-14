<?php

/**
 * User logout
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\AuthController;
use App\Middleware\Auth;

// Require authentication
Auth::require();

// Logout user
$controller = new AuthController();
$controller->logout();

// Redirect to home
header('Location: index.php');
exit;
