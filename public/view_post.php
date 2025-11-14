<?php

/**
 * View single post
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\PostController;
use App\Middleware\Auth;
use App\Helpers\CSRF;

// Get post ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = (int)$_GET['id'];

// Load post
$controller = new PostController();
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

$pageTitle = htmlspecialchars($post['title']);
include __DIR__ . '/../src/Views/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- Post Card -->
        <div class="card">
            <div class="card-body">
                <h1 class="card-title"><?= htmlspecialchars($post['title']) ?></h1>

                <p class="text-muted">
                    <i class="bi bi-calendar"></i>
                    Posted on <?= date('F d, Y \a\t g:i A', strtotime($post['created_at'])) ?>
                </p>

                <hr>

                <div class="post-content">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
            </div>

            <?php if (Auth::check()): ?>
                <div class="card-footer bg-transparent">
                    <div class="btn-group" role="group">
                        <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit Post
                        </a>
                        <form method="POST" action="delete_post.php" class="d-inline"
                              onsubmit="return confirmDelete(<?= $post['id'] ?>, '<?= addslashes($post['title']) ?>')">
                            <input type="hidden" name="id" value="<?= $post['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::getToken() ?>">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i> Delete Post
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to All Posts
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/Views/footer.php'; ?>
