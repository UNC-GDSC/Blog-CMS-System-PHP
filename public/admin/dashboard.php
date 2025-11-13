<?php

/**
 * Admin Dashboard
 * Statistics and management overview
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Middleware\RBAC;
use App\Models\PostRepository;
use App\Models\UserRepository;
use App\Models\CommentRepository;

// Require admin access
RBAC::requireRoleOrHigher('editor');

// Gather statistics
$postRepo = new PostRepository();
$userRepo = new UserRepository();
$commentRepo = new CommentRepository();

$stats = [
    'total_posts' => $postRepo->count(),
    'total_users' => $userRepo->count(),
    'total_comments' => 0,  // Will be implemented with comments
    'pending_comments' => 0
];

// Recent posts
$recentPosts = $postRepo->findAllOrdered(5);

// Recent users (admin only)
$recentUsers = [];
if (RBAC::isAdmin()) {
    $recentUsers = $userRepo->findAll(5);
}

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../../src/Views/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="mb-0">
            <i class="bi bi-speedometer2"></i> Admin Dashboard
        </h1>
        <p class="text-muted">Overview and statistics</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Posts</h6>
                        <h2 class="card-title mb-0"><?= $stats['total_posts'] ?></h2>
                    </div>
                    <i class="bi bi-file-text" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-white">
                <a href="../index.php" class="text-white text-decoration-none small">
                    View all posts <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Users</h6>
                        <h2 class="card-title mb-0"><?= $stats['total_users'] ?></h2>
                    </div>
                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-white">
                <?php if (RBAC::isAdmin()): ?>
                    <a href="#users" class="text-white text-decoration-none small">
                        View all users <i class="bi bi-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <span class="small">Registered members</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Comments</h6>
                        <h2 class="card-title mb-0"><?= $stats['total_comments'] ?></h2>
                    </div>
                    <i class="bi bi-chat-dots" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-white">
                <span class="small">Total comments</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Pending</h6>
                        <h2 class="card-title mb-0"><?= $stats['pending_comments'] ?></h2>
                    </div>
                    <i class="bi bi-clock-history" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-dark">
                <span class="small">Awaiting moderation</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Posts -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Posts</h5>
                <a href="../create_post.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> New Post
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentPosts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No posts yet
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentPosts as $post): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($post['title']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= ($post['status'] ?? 'published') === 'published' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($post['status'] ?? 'published') ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                                        <td>
                                            <a href="../view_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="../edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Info -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="../create_post.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create New Post
                    </a>
                    <?php if (RBAC::isAdmin()): ?>
                        <a href="users.php" class="btn btn-outline-primary">
                            <i class="bi bi-people"></i> Manage Users
                        </a>
                        <a href="settings.php" class="btn btn-outline-secondary">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    <?php endif; ?>
                    <a href="../profile.php" class="btn btn-outline-secondary">
                        <i class="bi bi-person-circle"></i> My Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Info</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="bi bi-info-circle text-primary"></i>
                        <strong>Version:</strong> 2.0.0
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-server text-success"></i>
                        <strong>PHP:</strong> <?= PHP_VERSION ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-person-badge text-info"></i>
                        <strong>Your Role:</strong> <?= ucfirst(RBAC::getUserRole()) ?>
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-clock text-warning"></i>
                        <strong>Server Time:</strong> <?= date('H:i:s') ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../src/Views/footer.php'; ?>
