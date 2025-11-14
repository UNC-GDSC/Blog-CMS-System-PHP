<?php

/**
 * User registration page
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\AuthController;
use App\Middleware\Auth;
use App\Helpers\CSRF;

// Redirect if already authenticated
Auth::guest();

$error = null;
$username = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    $controller = new AuthController();

    try {
        $controller->register($_POST);
        header('Location: login.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Register';
include __DIR__ . '/../src/Views/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
        <div class="card shadow">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">
                    <i class="bi bi-person-plus"></i> Register
                </h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person"></i> Username <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= htmlspecialchars($username) ?>"
                               placeholder="Choose a username"
                               required autofocus>
                        <div class="form-text">3-50 characters, letters and numbers only</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($email) ?>"
                               placeholder="Enter your email"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Create a password"
                               required>
                        <div class="form-text">Minimum 6 characters</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-person-plus"></i> Register
                    </button>

                    <p class="text-center text-muted mb-0">
                        Already have an account?
                        <a href="login.php">Login here</a>
                    </p>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="index.php" class="text-muted">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/Views/footer.php'; ?>
