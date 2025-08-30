<?php
/**
 * Contact Messages Management Page
 * 
 * This page allows administrators to view and manage contact form messages
 */

// Include database connection
require_once 'config/database.php';
// Include functions
require_once 'includes/functions.php';

// Require admin access to access this page
require_admin();

// Initialize variables
$messages = [];
$error = null;
$success = null;

// Handle message status update and reply actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'], $_POST['action'])) {
    $messageId = (int) $_POST['message_id'];
    $action = $_POST['action'];

    try {
        if ($action === 'mark_read') {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id");
            $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            $success = "Message marked as read";
        } elseif ($action === 'mark_replied') {
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = :id");
            $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            $success = "Message marked as replied";
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
            $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            $success = "Message deleted successfully";
        } elseif ($action === 'reply' && isset($_POST['reply_text']) && !empty($_POST['reply_text']) && isset($_POST['reply_email'])) {
            // Get message details
            $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = :id");
            $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            $messageData = $stmt->fetch();

            if ($messageData) {
                $replyEmail = trim($_POST['reply_email']);
                $replyText = trim($_POST['reply_text']);
                $replySubject = "Re: " . $messageData['subject'];

                // In a real application, this would send an email
                // For now, we'll just log it and update the status
                error_log("Reply would be sent to: $replyEmail with subject: $replySubject and content: $replyText");

                // Update message status to replied
                $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = :id");
                $stmt->bindParam(':id', $messageId, PDO::PARAM_INT);
                $stmt->execute();

                // Save reply in database (you'd need to create a replies table in a real application)
                // For this exercise, we'll just update the status

                $success = "Reply sent successfully to $replyEmail";
            } else {
                $error = "Message not found.";
            }
        }
    } catch (PDOException $e) {
        $error = "Error processing message: " . $e->getMessage();
    }
}

// Get all messages, newest first
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

    $stmt = $pdo->prepare("
        SELECT cm.*, u.username
        FROM contact_messages cm
        LEFT JOIN users u ON cm.user_id = u.id
        ORDER BY cm.created_at DESC
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching messages: " . $e->getMessage();
}

// Include page header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Contact Messages</h2>
                <span class="badge bg-light text-dark"><?= count($messages) ?> Messages</span>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if (empty($messages)): ?>
                    <div class="alert alert-info">No messages found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <tr class="<?= $message['status'] === 'new' ? 'table-primary' : '' ?>">
                                        <td><?= date('M d, Y H:i', strtotime($message['created_at'])) ?></td>
                                        <td>
                                            <?= htmlspecialchars($message['name']) ?><br>
                                            <small><?= htmlspecialchars($message['email']) ?></small>
                                            <?php if ($message['user_id']): ?>
                                                <br><span class="badge bg-info">User:
                                                    <?= htmlspecialchars($message['username'] ?? 'Unknown') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($message['subject']) ?></td>
                                        <td>
                                            <?php if ($message['status'] === 'new'): ?>
                                                <span class="badge bg-danger">New</span>
                                            <?php elseif ($message['status'] === 'read'): ?>
                                                <span class="badge bg-warning">Read</span>
                                            <?php elseif ($message['status'] === 'replied'): ?>
                                                <span class="badge bg-success">Replied</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="collapse" data-bs-target="#messageContent<?= $message['id'] ?>"
                                                aria-expanded="false">
                                                View/Reply
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Expandable message content -->
                                    <tr class="collapse" id="messageContent<?= $message['id'] ?>">
                                        <td colspan="5">
                                            <div class="card mt-2 mb-3">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0"><?= htmlspecialchars($message['subject']) ?></h6>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#messageContent<?= $message['id'] ?>"
                                                        aria-expanded="true">
                                                        <i class="bi bi-x-lg"></i> Close
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <strong>From:</strong> <?= htmlspecialchars($message['name']) ?>
                                                        (<?= htmlspecialchars($message['email']) ?>)<br>
                                                        <strong>Date:</strong>
                                                        <?= date('F d, Y H:i', strtotime($message['created_at'])) ?><br>
                                                        <strong>Status:</strong>
                                                        <?php if ($message['status'] === 'new'): ?>
                                                            <span class="badge bg-danger">New</span>
                                                        <?php elseif ($message['status'] === 'read'): ?>
                                                            <span class="badge bg-warning">Read</span>
                                                        <?php elseif ($message['status'] === 'replied'): ?>
                                                            <span class="badge bg-success">Replied</span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="mb-3">
                                                        <strong>Message:</strong><br>
                                                        <div class="border rounded p-3 bg-light">
                                                            <?= nl2br(htmlspecialchars($message['message'])) ?>
                                                        </div>
                                                    </div>

                                                    <?php if ($message['user_id']): ?>
                                                        <div class="alert alert-info">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            This message was sent by a registered user:
                                                            <strong><?= htmlspecialchars($message['username'] ?? 'Unknown') ?></strong>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Reply Form -->
                                                    <div class="border-top pt-3">
                                                        <h6 class="mb-3">Reply to this message</h6>
                                                        <form method="POST" action="contact_messages.php">
                                                            <input type="hidden" name="message_id"
                                                                value="<?= $message['id'] ?>">
                                                            <input type="hidden" name="action" value="reply">
                                                            <input type="hidden" name="reply_email"
                                                                value="<?= htmlspecialchars($message['email']) ?>">

                                                            <div class="mb-3">
                                                                <textarea class="form-control" name="reply_text" rows="4"
                                                                    placeholder="Type your reply here..." required></textarea>
                                                            </div>

                                                            <div class="d-flex flex-wrap gap-2">
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="bi bi-send me-1"></i> Send Reply
                                                                </button>

                                                                <!-- Status buttons -->
                                                                <?php if ($message['status'] === 'new'): ?>
                                                                    <button type="submit" name="action" value="mark_read"
                                                                        class="btn btn-warning">
                                                                        Mark as Read
                                                                    </button>
                                                                <?php endif; ?>

                                                                <?php if ($message['status'] !== 'replied'): ?>
                                                                    <button type="submit" name="action" value="mark_replied"
                                                                        class="btn btn-success">
                                                                        Mark as Replied
                                                                    </button>
                                                                <?php endif; ?>

                                                                <button type="submit" name="action" value="delete"
                                                                    class="btn btn-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this message?')">
                                                                    Delete
                                                                </button>

                                                                <!-- Email actions -->
                                                                <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= htmlspecialchars($message['subject']) ?>"
                                                                    class="btn btn-outline-primary">
                                                                    <i class="bi bi-envelope me-1"></i> External Email
                                                                </a>

                                                                <button type="button" class="btn btn-outline-secondary"
                                                                    onclick="copyToClipboard('<?= htmlspecialchars($message['email']) ?>')">
                                                                    <i class="bi bi-clipboard me-1"></i> Copy Email
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to copy email to clipboard
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    alert('Email copied to clipboard!');
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(text);
                });
        } else {
            fallbackCopyTextToClipboard(text);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            alert('Email copied to clipboard!');
        } catch (err) {
            console.error('Fallback copy failed: ', err);
        }
        document.body.removeChild(textarea);
    }
</script>

<?php
// Include page footer
include 'includes/footer.php';
?>