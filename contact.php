<?php
/**
 * Contact Page
 * 
 * This page provides a contact form for users to send messages to the site administrators
 */

// Include database connection
require_once 'config/database.php';
// Include functions
require_once 'includes/functions.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$name = '';
$email = '';
$subject = '';
$message = '';
$success = false;
$error = null;

// Pre-fill details if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch();

        if ($userData) {
            $name = $userData['username'];
            $email = $userData['email'];
        }
    } catch (PDOException $e) {
        // Silently ignore any errors with pre-filling
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize inputs
    $name = trim(filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $subject = trim(filter_var($_POST['subject'] ?? '', FILTER_SANITIZE_STRING));
    $message = trim(filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING));

    // Enhanced validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (strlen($name) < 2 || strlen($name) > 100) {
        $error = "Name must be between 2 and 100 characters";
    } elseif (strlen($subject) < 5 || strlen($subject) > 200) {
        $error = "Subject must be between 5 and 200 characters";
    } elseif (strlen($message) < 10) {
        $error = "Message must be at least 10 characters long";
    } else {
        try {
            // First check if we need to create a contact_messages table
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
            if ($tableCheck->rowCount() == 0) {
                // Create the table if it doesn't exist
                $pdo->exec("
                    CREATE TABLE contact_messages (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        subject VARCHAR(200) NOT NULL,
                        message TEXT NOT NULL,
                        user_id INT NULL,
                        status ENUM('new', 'read', 'replied') DEFAULT 'new',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                    )
                ");
            }

            // Save the message to the database
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message, user_id)
                VALUES (:name, :email, :subject, :message, :user_id)
            ");
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            // Fix: create a variable first as bindParam needs a variable reference
            $user_id = $_SESSION['user_id'] ?? null;
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            $stmt->execute();

            // Clear the form fields on success
            $name = $email = $subject = $message = '';
            $success = true;

        } catch (PDOException $e) {
            $error = "Error saving message: " . $e->getMessage();
        }
    }
}

// Include page header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">Contact Us</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle-fill"></i> Thank you for your message!</h5>
                        <p>We have received your inquiry and will respond as soon as possible.</p>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-5 mb-4 mb-md-0">
                        <h3 class="h5 mb-3">Get in Touch</h3>
                        <p>Have questions about AniCore? Want to suggest a new anime or report an issue? We'd love to
                            hear from you!</p>

                        <div class="mb-3">
                            <h4 class="h6"><i class="bi bi-envelope me-2"></i> Email</h4>
                            <p><a href="mailto:contact@anicore.example.com">contact@anicore.example.com</a></p>
                        </div>

                        <div class="mb-3">
                            <h4 class="h6"><i class="bi bi-geo-alt me-2"></i> Address</h4>
                            <p>AniCore Headquarters<br>
                                123 Anime Street<br>
                                Tokyo, Japan 100-0001</p>
                        </div>

                        <div>
                            <h4 class="h6"><i class="bi bi-clock me-2"></i> Business Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 5:00 PM<br>
                                Saturday - Sunday: Closed</p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <h3 class="h5 mb-3">Send Us a Message</h3>
                        <form method="POST" action="contact.php" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?= htmlspecialchars($name) ?>" required data-minlength="2"
                                    data-maxlength="100">
                                <div class="invalid-feedback">Please enter your name (2-100 characters)</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($email) ?>" required data-type="email">
                                <div class="invalid-feedback">Please enter a valid email address</div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject"
                                    value="<?= htmlspecialchars($subject) ?>" required data-minlength="5"
                                    data-maxlength="200">
                                <div class="invalid-feedback">Subject must be between 5 and 200 characters</div>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required
                                    data-minlength="10"><?= htmlspecialchars($message) ?></textarea>
                                <div class="invalid-feedback">Message must be at least 10 characters long</div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include page footer
include 'includes/footer.php';
?>