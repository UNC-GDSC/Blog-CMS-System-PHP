<?php

/**
 * Create new blog post
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\PostController;
use App\Middleware\Auth;
use App\Helpers\CSRF;

// Require authentication
Auth::require();

$error = null;
$title = '';
$content = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    $controller = new PostController();

    try {
        $postId = $controller->create($_POST);
        header('Location: view_post.php?id=' . $postId);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Create New Post';
include __DIR__ . '/../src/Views/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4">
            <i class="bi bi-plus-circle"></i> Create New Post
        </h1>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-permanent">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="create_post.php">
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">
                            <i class="bi bi-cursor-text"></i> Post Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= htmlspecialchars($title) ?>"
                               placeholder="Enter post title"
                               required>
                        <div class="form-text">Minimum 3 characters, maximum 200 characters</div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">
                            <i class="bi bi-file-text"></i> Post Content <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="content" name="content" rows="10"
                                  placeholder="Write your post content here..."
                                  required><?= htmlspecialchars($content) ?></textarea>
                        <div class="form-text">Minimum 10 characters</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/Views/footer.php'; ?>
