<?php

/**
 * User Profile Management Page
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Models\UserRepository;
use App\Middleware\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validator;
use App\Helpers\Session;
use App\Helpers\ImageUpload;

// Require authentication
Auth::require();

$userRepo = new UserRepository();
$user = $userRepo->findById(Auth::userId());
$error = null;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        CSRF::verify();

        $updateData = [];

        // Update bio
        if (isset($_POST['bio'])) {
            $updateData['bio'] = Validator::sanitize($_POST['bio']);
        }

        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploader = new ImageUpload();
            $result = $uploader->upload($_FILES['avatar']);

            if ($result['success']) {
                // Delete old avatar if exists
                if ($user['avatar']) {
                    $uploader->delete(basename($user['avatar']));
                }

                $updateData['avatar'] = $result['url'];
            } else {
                throw new Exception($result['error']);
            }
        }

        // Update user
        if (!empty($updateData)) {
            $userRepo->update(Auth::userId(), $updateData);
            Session::flash('success', 'Profile updated successfully!');

            // Refresh user data
            $user = $userRepo->findById(Auth::userId());
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/../src/Views/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h1 class="mb-4">
            <i class="bi bi-person-circle"></i> My Profile
        </h1>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-permanent">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Info Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if ($user['avatar']): ?>
                            <img src="<?= htmlspecialchars($user['avatar']) ?>"
                                 alt="<?= htmlspecialchars($user['username']) ?>"
                                 class="rounded-circle mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-person-fill text-white" style="font-size: 80px;"></i>
                            </div>
                        <?php endif; ?>

                        <h4><?= htmlspecialchars($user['username']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                        <p class="badge bg-primary"><?= ucfirst($user['role'] ?? 'subscriber') ?></p>

                        <hr>

                        <p class="small text-muted mb-1">
                            <i class="bi bi-calendar"></i>
                            Joined <?= date('M Y', strtotime($user['created_at'])) ?>
                        </p>
                        <?php if ($user['last_login']): ?>
                            <p class="small text-muted mb-0">
                                <i class="bi bi-clock"></i>
                                Last login: <?= date('M d, Y', strtotime($user['last_login'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="profile.php" enctype="multipart/form-data">
                            <?= CSRF::field() ?>

                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Username
                                </label>
                                <input type="text" class="form-control" id="username"
                                       value="<?= htmlspecialchars($user['username']) ?>"
                                       disabled>
                                <div class="form-text">Username cannot be changed</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email"
                                       value="<?= htmlspecialchars($user['email']) ?>"
                                       disabled>
                                <div class="form-text">Email cannot be changed</div>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">
                                    <i class="bi bi-file-text"></i> Bio
                                </label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"
                                          placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="avatar" class="form-label">
                                    <i class="bi bi-image"></i> Profile Picture
                                </label>
                                <input type="file" class="form-control" id="avatar" name="avatar"
                                       accept="image/*">
                                <div class="form-text">JPG, PNG, GIF or WebP. Max 5MB.</div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Security</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Want to change your password?</p>
                        <a href="password-reset.php" class="btn btn-outline-secondary">
                            <i class="bi bi-key"></i> Reset Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/Views/footer.php'; ?>
