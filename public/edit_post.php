<?php

/**
 * Edit existing blog post
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\PostController;
use App\Middleware\Auth;
use App\Helpers\CSRF;

// Require authentication
Auth::require();

// Get post ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = (int)$_GET['id'];
$controller = new PostController();
$error = null;

// Load post
try {
    $post = $controller->show($postId);

    if (!$post) {
        App\Helpers\Session::flash('error', 'Post not found');
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    App\Helpers\Session::flash('error', 'Error loading post: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->update($postId, $_POST);
        header('Location: view_post.php?id=' . $postId);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
        $post['title'] = $_POST['title'] ?? $post['title'];
        $post['content'] = $_POST['content'] ?? $post['content'];
    }
}

$pageTitle = 'Edit Post - ' . htmlspecialchars($post['title']);
include __DIR__ . '/../src/Views/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4">
            <i class="bi bi-pencil"></i> Edit Post
        </h1>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-permanent">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="edit_post.php?id=<?= $postId ?>">
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">
                            <i class="bi bi-cursor-text"></i> Post Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= htmlspecialchars($post['title']) ?>"
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
                                  required><?= htmlspecialchars($post['content']) ?></textarea>
                        <div class="form-text">Minimum 10 characters</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="view_post.php?id=<?= $postId ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Post
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-muted mt-2 small">
            <i class="bi bi-info-circle"></i> Last updated: <?= date('F d, Y \a\t g:i A', strtotime($post['updated_at'] ?? $post['created_at'])) ?>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../src/Views/footer.php'; ?>
