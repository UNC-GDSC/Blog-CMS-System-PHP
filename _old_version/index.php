<?php
// index.php
require_once 'config.php';
require_once 'inc/header.php';

// Fetch all posts from the database
$stmt = \$pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
\$posts = \$stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Blog Posts</h1>
<?php if(count(\$posts) > 0): ?>
    <?php foreach(\$posts as \$post): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="card-title"><?php echo htmlspecialchars(\$post['title']); ?></h3>
                <p class="card-text">
                    <?php echo substr(strip_tags(\$post['content']), 0, 150) . '...'; ?>
                </p>
                <a href="view_post.php?id=<?php echo \$post['id']; ?>" class="btn btn-primary">Read More</a>
                <a href="edit_post.php?id=<?php echo \$post['id']; ?>" class="btn btn-secondary">Edit</a>
                <a href="delete_post.php?id=<?php echo \$post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                <p class="card-text"><small class="text-muted">Posted on <?php echo \$post['created_at']; ?></small></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No posts found. <a href="create_post.php">Create one now</a>.</p>
<?php endif; ?>

<?php require_once 'inc/footer.php'; ?>
