<?php
// delete_post.php
require_once 'config.php';

\$id = \$_GET['id'] ?? null;
if (!\$id) {
    header("Location: index.php");
    exit;
}

// Delete post from database
\$stmt = \$pdo->prepare("DELETE FROM posts WHERE id = ?");
\$stmt->execute([\$id]);

header("Location: index.php");
exit;
?>
