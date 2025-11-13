<?php

/**
 * Homepage - Display all blog posts with pagination and search
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\PostController;

$controller = new PostController();

// Get pagination and search parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

// Load posts
try {
    $data = $controller->index($page, $search);
    $posts = $data['posts'];
    $currentPage = $data['currentPage'];
    $totalPages = $data['totalPages'];
    $totalPosts = $data['totalPosts'];
} catch (Exception $e) {
    $error = "Error loading posts: " . $e->getMessage();
    $posts = [];
    $currentPage = 1;
    $totalPages = 1;
    $totalPosts = 0;
}

$pageTitle = 'Home - Blog Posts';
include __DIR__ . '/../src/Views/header.php';
?>

<!-- Search Bar -->
<div class="row mb-4">
    <div class="col-md-8 offset-md-2">
        <form method="GET" action="index.php" class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search posts..."
                   value="<?= htmlspecialchars($search ?? '') ?>">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-search"></i> Search
            </button>
            <?php if ($search): ?>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="display-4">
            <i class="bi bi-journal-text"></i>
            <?= $search ? 'Search Results' : 'All Blog Posts' ?>
        </h1>
        <p class="text-muted">
            <?php if ($search): ?>
                Found <?= $totalPosts ?> result(s) for "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                <?= $totalPosts ?> post(s) available
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle"></i>
        <?= $search ? 'No posts found matching your search.' : 'No posts available yet. Create your first post!' ?>
    </div>
    <?php if (App\Middleware\Auth::check()): ?>
        <div class="text-center">
            <a href="create_post.php" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle"></i> Create Your First Post
            </a>
        </div>
    <?php endif; ?>
<?php else: ?>
    <!-- Posts Grid -->
    <div class="row">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                        <p class="card-text text-muted">
                            <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>
                            <?= strlen($post['content']) > 150 ? '...' : '' ?>
                        </p>
                        <p class="text-muted small">
                            <i class="bi bi-calendar"></i>
                            <?= date('M d, Y', strtotime($post['created_at'])) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <?php if (App\Middleware\Auth::check()): ?>
                                <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form method="POST" action="delete_post.php" class="d-inline"
                                      onsubmit="return confirmDelete(<?= $post['id'] ?>, '<?= addslashes($post['title']) ?>')">
                                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= App\Helpers\CSRF::getToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                </li>

                <!-- Page Numbers -->
                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../src/Views/footer.php'; ?>
