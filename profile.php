<?php
/**
 * User Profile Page
 * 
 * This page allows users to view and update their profile information
 */

// Include database connection
require_once 'config/database.php';
// Include functions
require_once 'includes/functions.php';

// Require login to access this page
require_login();

// Initialize variables
$success = false;
$error = null;
$user = null;

// Get current user data
try {
    $stmt = $pdo->prepare("SELECT id, username, email, profile_picture, created_at FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    // Set default profile picture if none exists
    if (empty($user['profile_picture'])) {
        $user['profile_picture'] = 'default.jpg';
    }
} catch (PDOException $e) {
    $error = "Error loading profile: " . $e->getMessage();
}

// Process form submission to update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $uploadedProfilePic = $_FILES['profile_picture'] ?? null;

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        try {
            // Check if email is already in use by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "This email is already in use by another account";
            } else {
                // Start transaction
                $pdo->beginTransaction();

                // Update email
                if ($email !== $user['email']) {
                    $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }

                // Update password if provided
                if (!empty($currentPassword) && !empty($newPassword)) {                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
                    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $userData = $stmt->fetch();

                    if (!password_verify($currentPassword, $userData['password_hash'])) {
                        throw new Exception("Current password is incorrect");
                    }

                    // Validate new password
                    if (strlen($newPassword) < 8) {
                        throw new Exception("New password must be at least 8 characters long");
                    }

                    if ($newPassword !== $confirmPassword) {
                        throw new Exception("New passwords do not match");
                    }
                    // Update password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = :password WHERE id = :id");
                    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }                // Handle profile picture upload if provided
                if (!empty($uploadedProfilePic) && $uploadedProfilePic['error'] === UPLOAD_ERR_OK) {
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($uploadedProfilePic['type'], $allowedTypes)) {
                        throw new Exception("Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.");
                    }

                    // Validate file size (max 2MB)
                    if ($uploadedProfilePic['size'] > 2 * 1024 * 1024) {
                        throw new Exception("File is too large. Maximum size is 2MB.");
                    }

                    // Create unique filename
                    $fileExtension = pathinfo($uploadedProfilePic['name'], PATHINFO_EXTENSION);
                    $newFilename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExtension;
                    $uploadPath = 'images/profiles/' . $newFilename;

                    // Move uploaded file
                    if (move_uploaded_file($uploadedProfilePic['tmp_name'], $uploadPath)) {
                        // Update database with new profile picture
                        $stmt = $pdo->prepare("UPDATE users SET profile_picture = :pic WHERE id = :id");
                        $stmt->bindParam(':pic', $newFilename, PDO::PARAM_STR);
                        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt->execute();
                    } else {
                        throw new Exception("Failed to upload profile picture. Please try again.");
                    }
                }

                // Commit transaction
                $pdo->commit();

                // Refresh user data
                $stmt = $pdo->prepare("SELECT id, username, email, profile_picture, created_at FROM users WHERE id = :id");
                $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $user = $stmt->fetch();

                $success = true;
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

// Include page header
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">My Profile</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">Profile updated successfully!</div>
                <?php endif; ?>
                <div class="row mb-4">
                    <div class="col-md-4 text-center">
                        <div class="avatar-container mb-3">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="images/profiles/<?= htmlspecialchars($user['profile_picture']) ?>"
                                    alt="Profile Picture" class="img-fluid rounded-circle"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle" style="font-size: 6rem;"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="h5"><?= htmlspecialchars($user['username']) ?></h3>
                        <p class="text-muted small">Member since: <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </p>
                    </div>
                    <div class="col-md-8">
                        <form method="POST" action="profile.php" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username"
                                    value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <div class="form-text">Username cannot be changed</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <hr>
                            <h4 class="h6 mb-3">Change Password</h4>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password"
                                    name="current_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Password must be at least 8 characters long</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password">
                            </div>

                            <hr>
                            <h4 class="h6 mb-3">Profile Picture</h4>

                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Upload a new profile picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture"
                                    accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="form-text">Max file size: 2MB. Accepted formats: JPG, PNG, GIF, WEBP.</div>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <hr>

                <h3 class="h5 mb-3">My Activity Summary</h3>
                <div class="row">
                    <?php
                    // Get watchlist counts by status
                    try {
                        $stmt = $pdo->prepare("
                            SELECT status, COUNT(*) as count 
                            FROM user_anime_watchlist 
                            WHERE user_id = :user_id 
                            GROUP BY status
                        ");
                        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt->execute();
                        $watchlistStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    } catch (PDOException $e) {
                        $watchlistStats = [];
                    }

                    // Define all possible statuses
                    $allStatuses = [
                        'Watching' => 'primary',
                        'Completed' => 'success',
                        'On Hold' => 'warning',
                        'Dropped' => 'danger',
                        'Plan to Watch' => 'info'
                    ];

                    foreach ($allStatuses as $status => $color):
                        $count = $watchlistStats[$status] ?? 0;
                        ?>
                        <div class="col-md-4 col-6 mb-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <h5 class="display-4 fw-bold text-<?= $color ?>"><?= $count ?></h5>
                                    <p class="card-text"><?= $status ?></p>
                                </div>
                                <div class="card-footer bg-<?= $color ?> bg-opacity-25">
                                    <a href="my_watchlist.php?status=<?= urlencode($status) ?>"
                                        class="text-<?= $color ?> text-decoration-none">
                                        View List <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include page footer
include 'includes/footer.php';
?>