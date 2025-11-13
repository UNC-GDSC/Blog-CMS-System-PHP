<?php

/**
 * Password Reset Request Page
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Models\UserRepository;
use App\Helpers\CSRF;
use App\Helpers\Email;
use App\Helpers\Validator;
use App\Helpers\Session;
use App\Helpers\Env;
use App\Helpers\Logger;
use App\Middleware\Auth;
use App\Middleware\RateLimiter;

// Redirect if already authenticated
Auth::guest();

$error = null;
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // Rate limiting - 5 attempts per hour
    $rateLimitKey = 'password_reset:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    if (RateLimiter::tooManyAttempts($rateLimitKey, 5, 3600)) {
        $error = 'Too many password reset attempts. Please try again later.';
    } else {
        try {
            CSRF::verify();

            $validator = new Validator(['email' => $email]);
            $validator->rule('email', 'required|email', 'Email');

            if ($validator->fails()) {
                throw new Exception('Please enter a valid email address');
            }

            RateLimiter::hit($rateLimitKey);

            $userRepo = new UserRepository();
            $user = $userRepo->findByEmail($email);

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                // Store token in database
                $db = App\Config\Database::getInstance()->getConnection();
                $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
                $stmt->execute([
                    'email' => $email,
                    'token' => $token,
                    'expires_at' => $expiresAt
                ]);

                // Send reset email
                $resetLink = Env::get('APP_URL') . '/public/password-reset-confirm.php?token=' . $token;
                $emailHelper = new Email();
                $emailHelper->sendPasswordReset($email, $user['username'], $resetLink);

                Logger::info('Password reset requested', ['email' => $email]);
            }

            // Always show success message (security best practice)
            $success = true;
            Session::flash('success', 'If an account exists with that email, you will receive a password reset link shortly.');
        } catch (Exception $e) {
            $error = $e->getMessage();
            Logger::error('Password reset error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Reset Password';
include __DIR__ . '/../src/Views/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
        <div class="card shadow">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">
                    <i class="bi bi-key"></i> Reset Password
                </h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Check your email for a password reset link.
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-4">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <form method="POST" action="password-reset.php">
                        <?= CSRF::field() ?>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i> Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="Enter your email"
                                   required autofocus>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-send"></i> Send Reset Link
                        </button>

                        <p class="text-center text-muted mb-0">
                            Remember your password?
                            <a href="login.php">Login here</a>
                        </p>
                    </form>
                <?php endif; ?>
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
