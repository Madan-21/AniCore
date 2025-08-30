<?php
/**
 * User Login Page
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Include database connection
require_once 'config/database.php';

$message = '';
$error = false;

// Ensure all required database columns exist
try {
    // Check if email column exists in users table
    $columnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($columnCheck->rowCount() === 0) {
        // Redirect to migration script to update database
        header('Location: migrate_database.php');
        exit;
    }
} catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
    $error = true;
    error_log("Database check error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username_or_email = trim(filter_input(INPUT_POST, 'username') ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // Get user from database by username or email - using different parameter names
        $sql = 'SELECT id, username, email, password_hash, role FROM users WHERE username = :username OR email = :email';
        $stmt = $pdo->prepare($sql);

        // Bind each parameter separately to avoid confusion
        $stmt->bindValue(':username', $username_or_email, PDO::PARAM_STR);
        $stmt->bindValue(':email', $username_or_email, PDO::PARAM_STR);

        // Execute the statement
        $stmt->execute();

        // Fetch the user record
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user'; // Default role to 'user' if not set

            // Log successful login
            error_log("User login successful: User ID {$user['id']}, Username: {$user['username']}");            // Debug check for session data
            if (!isset($_SESSION['user_id'])) {
                error_log("WARNING: Session ID was not set properly after login!");
                // Try to re-set session variables
                session_regenerate_id();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';
            }

            // Redirect to home page
            header('Location: index.php?message=Login successful!');
            exit;
        } else {
            $message = 'Invalid username/email or password';
            $error = true;
        }
    } catch (PDOException $e) {
        $message = 'Login error: ' . $e->getMessage();
        $error = true;

        // Log detailed information about the error for troubleshooting
        error_log("Login error details: " . $e->getMessage());
        error_log("Username/Email attempted: " . $username_or_email);

        // For development only - comment out in production
        // error_log("SQL query error: " . $sql);
    }
}

// Include header
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title h5 mb-0">Login</h2>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $error ? 'danger' : 'success' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" required value=""
                            placeholder="Enter username or email">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 d-flex justify-content-end">
                        <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>