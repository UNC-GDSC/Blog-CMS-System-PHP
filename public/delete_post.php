<?php

/**
 * Delete blog post
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\PostController;
use App\Middleware\Auth;

// Require authentication
Auth::require();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get post ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    App\Helpers\Session::flash('error', 'Invalid post ID');
    header('Location: index.php');
    exit;
}

$postId = (int)$_POST['id'];

// Delete post
$controller = new PostController();

try {
    $controller->delete($postId);
} catch (Exception $e) {
    // Error already flashed in controller
}

header('Location: index.php');
exit;
