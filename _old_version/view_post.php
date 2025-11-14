<?php
// view_post.php
require_once 'config.php';

\$id = \$_GET['id'] ?? null;
if (!\$id) {
    header("Location: index.php");
    exit;
}

// Fetch post by ID
\$stmt = \$pdo->prepare("SELECT * FROM posts WHERE id = ?");
\$stmt->execute([\$id]);
\$post = \$stmt->fetch(PDO::FETCH_ASSOC);

if (!\$post) {
    die("Post not found.");
}

require_once 'inc/header.php';
?>

<h1><?php echo htmlspecialchars(\$post['title']); ?></h1>
<p><small>Posted on <?php echo \$post['created_at']; ?></small></p>
<div>
    <?php echo nl2br(htmlspecialchars(\$post['content'])); ?>
</div>
<p>
    <a href="edit_post.php?id=<?php echo \$post['id']; ?>" class="btn btn-secondary">Edit</a>
    <a href="delete_post.php?id=<?php echo \$post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
</p>
<a href="index.php" class="btn btn-primary">Back to Posts</a>

<?php require_once 'inc/footer.php'; ?>
