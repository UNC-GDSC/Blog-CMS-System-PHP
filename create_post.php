<?php
// create_post.php
require_once 'config.php';

if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form inputs
    \$title = \$_POST['title'] ?? '';
    \$content = \$_POST['content'] ?? '';

    if (!empty(\$title) && !empty(\$content)) {
        // Prepare and execute insert query
        \$stmt = \$pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        \$stmt->execute([\$title, \$content]);

        // Redirect to home page after creation
        header("Location: index.php");
        exit;
    } else {
        \$error = "Both title and content are required.";
    }
}

require_once 'inc/header.php';
?>

<h1>Create New Post</h1>
<?php if(isset(\$error)): ?>
    <div class="alert alert-danger"><?php echo \$error; ?></div>
<?php endif; ?>
<form method="post" action="create_post.php">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" class="form-control" name="title" id="title" required>
    </div>
    <div class="form-group">
        <label for="content">Content</label>
        <textarea class="form-control" name="content" id="content" rows="8" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Create Post</button>
</form>

<?php require_once 'inc/footer.php'; ?>
