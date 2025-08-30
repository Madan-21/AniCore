<?php
/**
 * User Registration Page
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Include database connection
require_once 'config/database.php';

$message = '';
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $username = trim(filter_input(INPUT_POST, 'username') ?? '');
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';    // Validate form data
    if (strlen($username) < 3) {
        $message = 'Username must be at least 3 characters long';
        $error = true;
    } elseif (empty($email)) {
        $message = 'Please enter a valid email address';
        $error = true;
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match';
        $error = true;
    } else {
        // Validate password requirements all at once
        $passwordErrors = [];

        // Always check all password requirements
        if (strlen($password) < 6) {
            $passwordErrors[] = 'at least 6 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $passwordErrors[] = 'at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $passwordErrors[] = 'at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $passwordErrors[] = 'at least one number';
        }
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $passwordErrors[] = 'at least one special character (!@#$%^&*)';
        }

        if (!empty($passwordErrors)) {
            // Show all requirements that are missing
            $message = 'Password must include: ' . implode(', ', $passwordErrors);
            $error = true;
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
                $stmt->bindParam(':username', $username);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $message = 'Username already exists';
                    $error = true;
                } else {
                    // Check if email already exists
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $message = 'Email address already exists';
                        $error = true;
                    } else {                    // Hash the password with stronger algorithm and options
                        $passwordHashOptions = [
                            'cost' => 12 // Higher cost = more secure but slower
                        ];
                        $passwordHash = password_hash($password, PASSWORD_BCRYPT, $passwordHashOptions);

                        // Insert the new user
                        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':password_hash', $passwordHash);
                        $stmt->execute();

                        $message = 'Registration successful! You can now log in.';

                        // Send email notification upon successful registration (in a real application)
                        // $to = $email;
                        // $subject = "Welcome to AniCore!";
                        // $body = "Hi $username,\n\nThank you for registering at AniCore. We hope you enjoy exploring anime!\n\nBest regards,\nAniCore Team";
                        // $headers = "From: no-reply@anicore.com";
                        // mail($to, $subject, $body, $headers);

                        // Redirect to login page
                        header('Location: login.php?message=' . urlencode($message));
                        exit;
                    }
                }
            } catch (PDOException $e) {
                $message = 'Registration error: ' . $e->getMessage();
                $error = true;
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title h5 mb-0">Register</h2>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $error ? 'danger' : 'success' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                <form method="POST" class="needs-validation" novalidate autocomplete="off">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required
                            data-minlength="3" autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required data-type="email"
                            autocomplete="off">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required
                            autocomplete="new-password">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required data-matches="#password" data-matches-name="password" autocomplete="new-password">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>