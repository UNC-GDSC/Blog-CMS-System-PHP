<?php
// edit_post.php
require_once 'config.php';

\$id = \$_GET['id'] ?? null;
if (!\$id) {
    header("Location: index.php");
    exit;
}

// Fetch the post to edit
\$stmt = \$pdo->prepare("SELECT * FROM posts WHERE id = ?");
\$stmt->execute([\$id]);
\$post = \$stmt->fetch(PDO::FETCH_ASSOC);

if (!\$post) {
    die("Post not found.");
}

if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs
    \$title = \$_POST['title'] ?? '';
    \$content = \$_POST['content'] ?? '';

    if (!empty(\$title) && !empty(\$content)) {
        // Update post in database
        \$stmt = \$pdo->prepare("UPDATE posts SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
        \$stmt->execute([\$title, \$content, \$id]);

        header("Location: view_post.php?id=" . \$id);
        exit;
    } else {
        \$error = "Both title and content are required.";
    }
}

require_once 'inc/header.php';
?>

<h1>Edit Post</h1>
<?php if(isset(\$error)): ?>
    <div class="alert alert-danger"><?php echo \$error; ?></div>
<?php endif; ?>
<form method="post" action="edit_post.php?id=<?php echo \$id; ?>">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" class="form-control" name="title" id="title" value="<?php echo htmlspecialchars(\$post['title']); ?>" required>
    </div>
    <div class="form-group">
        <label for="content">Content</label>
        <textarea class="form-control" name="content" id="content" rows="8" required><?php echo htmlspecialchars(\$post['content']); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update Post</button>
    <a href="view_post.php?id=<?php echo \$id; ?>" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once 'inc/footer.php'; ?>
