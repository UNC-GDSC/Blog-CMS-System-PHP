<?php

/**
 * REST API Endpoint for Posts
 * Provides JSON API for blog posts
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Controllers\PostController;
use App\Middleware\Auth;
use App\Helpers\Logger;

// Set JSON header
header('Content-Type: application/json');

// Enable CORS (configure for production)
if (App\Helpers\Env::get('APP_ENV') === 'development') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $controller = new PostController();
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    // Parse request
    $pathParts = explode('/', trim(parse_url($uri, PHP_URL_PATH), '/'));
    $postId = $pathParts[count($pathParts) - 1] ?? null;

    // Route requests
    switch ($method) {
        case 'GET':
            if (is_numeric($postId)) {
                // GET /api/posts/{id} - Get single post
                $post = $controller->show($postId);

                if (!$post) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Post not found'
                    ]);
                    exit;
                }

                echo json_encode([
                    'success' => true,
                    'data' => $post
                ]);
            } else {
                // GET /api/posts - List all posts
                $page = $_GET['page'] ?? 1;
                $search = $_GET['search'] ?? null;

                $result = $controller->index($page, $search);

                echo json_encode([
                    'success' => true,
                    'data' => $result['posts'],
                    'meta' => [
                        'current_page' => $result['currentPage'],
                        'total_pages' => $result['totalPages'],
                        'total_posts' => $result['totalPosts']
                    ]
                ]);
            }
            break;

        case 'POST':
            // POST /api/posts - Create new post
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            try {
                $postId = $controller->create($data);

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'data' => ['id' => $postId],
                    'message' => 'Post created successfully'
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            break;

        case 'PUT':
            // PUT /api/posts/{id} - Update post
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]);
                exit;
            }

            if (!is_numeric($postId)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid post ID'
                ]);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            try {
                $controller->update($postId, $data);

                echo json_encode([
                    'success' => true,
                    'message' => 'Post updated successfully'
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            break;

        case 'DELETE':
            // DELETE /api/posts/{id} - Delete post
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]);
                exit;
            }

            if (!is_numeric($postId)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid post ID'
                ]);
                exit;
            }

            try {
                // For DELETE, we need CSRF in header or query param
                $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? null;
                $_POST['csrf_token'] = $csrfToken;

                $controller->delete($postId);

                echo json_encode([
                    'success' => true,
                    'message' => 'Post deleted successfully'
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
            break;
    }
} catch (Exception $e) {
    Logger::error('API Error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
