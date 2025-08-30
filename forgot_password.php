<?php
/**
 * Forgot Password Page
 * 
 * This page allows users to request a password reset
 */

// Include database connection
require_once 'config/database.php';
// Include functions
require_once 'includes/functions.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Initialize variables
$email = '';
$error = null;
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        try {
            // Check if email exists in database
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                // We don't want to reveal if an email exists or not for security reasons
                $success = true;
            } else {
                $userId = $stmt->fetch()['id'];

                // Generate a unique reset token
                $resetToken = bin2hex(random_bytes(32));
                $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Check if a reset_tokens table exists, create it if not
                $tableCheck = $pdo->query("SHOW TABLES LIKE 'reset_tokens'");
                if ($tableCheck->rowCount() == 0) {
                    // Create table
                    $pdo->exec("
                        CREATE TABLE reset_tokens (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            token VARCHAR(64) NOT NULL,
                            expiry DATETIME NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                        )
                    ");
                }

                // Delete any existing tokens for this user
                $stmt = $pdo->prepare("DELETE FROM reset_tokens WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();

                // Store the new token
                $stmt = $pdo->prepare("
                    INSERT INTO reset_tokens (user_id, token, expiry)
                    VALUES (:user_id, :token, :expiry)
                ");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':token', $resetToken, PDO::PARAM_STR);
                $stmt->bindParam(':expiry', $tokenExpiry, PDO::PARAM_STR);
                $stmt->execute();                // In a real application, an email would be sent with the reset link
                // For this demo, we'll just create the token but not display it

                // Set success message
                $success = true;
            }
        } catch (PDOException $e) {
            $error = "Error processing request: " . $e->getMessage();
        }
    }
}

// Include page header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">Forgot Password</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?> <?php if ($success): ?>
                    <div class="alert alert-success" id="successAlert">
                        <h5><i class="bi bi-check-circle-fill me-2"></i> Password Reset Email Sent</h5>
                        <p>If the email address you entered is associated with an account, you will receive an email with
                            instructions to reset your password shortly.</p>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Back to Login
                        </a>
                    </div>
                <?php else: ?>
                    <p class="mb-3">Enter your email address below and we'll send you instructions to reset your password.
                    </p>

                    <form method="POST" action="forgot_password.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?= htmlspecialchars($email) ?>" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-envelope me-1"></i> Send Reset Instructions
                            </button>
                            <a href="login.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include page footer
include 'includes/footer.php';
?>