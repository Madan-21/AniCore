<?php
/**
 * Admin Dashboard
 * 
 * This page provides administrative functionality for the AniCore application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'config/database.php';
// Include functions
require_once 'includes/functions.php';

// Require admin access to access this page
require_admin();

// Check if the user is an admin
$isAdmin = false;
try {
    // Ensure user_id is set and is an integer
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $userId = (int) $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        $isAdmin = ($user && $user['role'] === 'admin');
    } else {
        // If no user_id in session, definitely not an admin
        $isAdmin = false;
    }
} catch (PDOException $e) {
    // In case of error, default to non-admin
    error_log("Error checking admin status: " . $e->getMessage());
}

// If not admin, redirect to homepage with an error message
if (!$isAdmin) {
    header("Location: index.php?message=You do not have permission to access this page&error=1");
    exit;
}

// Initialize variables
$stats = [];
$users = []; // Initialize users array to prevent undefined variable
$error = null;
$success = null;

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_user_role':
                if (isset($_POST['user_id'], $_POST['role'])) {
                    $userId = (int) $_POST['user_id'];
                    $role = $_POST['role'];

                    // Make sure we're not changing our own role
                    if ($userId === (int) $_SESSION['user_id']) {
                        $error = "You cannot modify your own admin status";
                    } else {
                        try {
                            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
                            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            $success = "User role updated successfully";
                        } catch (PDOException $e) {
                            $error = "Error updating user role: " . $e->getMessage();
                        }
                    }
                }
                break;

            case 'delete_user':
                if (isset($_POST['user_id'])) {
                    $userId = (int) $_POST['user_id'];

                    // Make sure we're not deleting ourselves
                    if ($userId === (int) $_SESSION['user_id']) {
                        $error = "You cannot delete your own account";
                    } else {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            $success = "User deleted successfully";
                        } catch (PDOException $e) {
                            $error = "Error deleting user: " . $e->getMessage();
                        }
                    }
                }
                break;
        }
    }
}

// Get application statistics
try {
    // Ensure users have a role column
    $roleColumnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($roleColumnCheck->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");

        // Make the first user an admin
        $pdo->exec("UPDATE users SET role = 'admin' WHERE id = 1");
    }

    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();

    // Total anime
    $stmt = $pdo->query("SELECT COUNT(*) FROM anime");
    $stats['total_anime'] = $stmt->fetchColumn();

    // Total watchlist entries
    $stmt = $pdo->query("SELECT COUNT(*) FROM user_anime_watchlist");
    $stats['total_watchlist_entries'] = $stmt->fetchColumn();

    // Total genres
    $stmt = $pdo->query("SELECT COUNT(*) FROM genres");
    $stats['total_genres'] = $stmt->fetchColumn();

    // Users registered in the last 7 days
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['new_users'] = $stmt->fetchColumn();

    // Anime added in the last 30 days
    $stmt = $pdo->query("SELECT COUNT(*) FROM anime WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['new_anime'] = $stmt->fetchColumn();

    // Get watchlist statistics by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM user_anime_watchlist GROUP BY status");
    $watchlistByStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $stats['watching'] = $watchlistByStatus['Watching'] ?? 0;
    $stats['completed'] = $watchlistByStatus['Completed'] ?? 0;
    $stats['planned'] = $watchlistByStatus['Plan to Watch'] ?? 0;

    // Check for unread contact messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    $stats['unread_messages'] = $stmt->fetchColumn();

    // Get all users for management
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching statistics: " . $e->getMessage();
}

// Include page header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Admin Dashboard</h2>
                <span class="badge bg-light text-primary">Admin Access</span>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Dashboard Header with Quick Stats -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="h5 mb-0">Dashboard Overview</h3>
                    <div>
                        <a href="admin_add_anime.php" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bi bi-plus-lg"></i> Add New Anime
                        </a>
                        <a href="manage_anime.php" class="btn btn-sm btn-outline-success me-2">
                            <i class="bi bi-collection-play"></i> Manage Anime
                        </a>
                        <a href="contact_messages.php" class="btn btn-sm btn-outline-info position-relative">
                            <i class="bi bi-envelope"></i> Messages
                            <?php if (($stats['unread_messages'] ?? 0) > 0): ?>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $stats['unread_messages'] ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <!-- Stats Cards Row -->
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card text-center h-100 bg-primary bg-opacity-10">
                            <div class="card-body">
                                <i class="bi bi-people-fill mb-2" style="font-size: 2rem; color: #0d6efd;"></i>
                                <h5 class="display-4 fw-bold text-primary"><?= $stats['total_users'] ?? 0 ?></h5>
                                <p class="card-text">Total Users</p>
                            </div>
                            <div class="card-footer bg-primary bg-opacity-25">
                                <span class="text-primary">+<?= $stats['new_users'] ?? 0 ?> in the last 7 days</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card text-center h-100 bg-success bg-opacity-10">
                            <div class="card-body">
                                <i class="bi bi-collection-play mb-2" style="font-size: 2rem; color: #198754;"></i>
                                <h5 class="display-4 fw-bold text-success"><?= $stats['total_anime'] ?? 0 ?></h5>
                                <p class="card-text">Anime Titles</p>
                            </div>
                            <div class="card-footer bg-success bg-opacity-25">
                                <span class="text-success">+<?= $stats['new_anime'] ?? 0 ?> in the last 30 days</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card text-center h-100 bg-info bg-opacity-10">
                            <div class="card-body">
                                <i class="bi bi-list-check mb-2" style="font-size: 2rem; color: #0dcaf0;"></i>
                                <h5 class="display-4 fw-bold text-info"><?= $stats['total_watchlist_entries'] ?? 0 ?>
                                </h5>
                                <p class="card-text">Watchlist Entries</p>
                            </div>
                            <div class="card-footer bg-info bg-opacity-25">
                                <span class="text-info"><?= $stats['watching'] ?? 0 ?> currently watching</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card text-center h-100 bg-warning bg-opacity-10">
                            <div class="card-body">
                                <i class="bi bi-tag-fill mb-2" style="font-size: 2rem; color: #ffc107;"></i>
                                <h5 class="display-4 fw-bold text-warning"><?= $stats['total_genres'] ?? 0 ?></h5>
                                <p class="card-text">Genres</p>
                            </div>
                            <div class="card-footer bg-warning bg-opacity-25">
                                <span class="text-warning">Categories</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col">
                        <div class="card">
                            <div
                                class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0">Quick Links</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="admin_add_anime.php" class="btn btn-outline-primary d-block">
                                            <i class="bi bi-plus-circle me-1"></i> Add New Anime
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="manage_anime.php" class="btn btn-outline-success d-block">
                                            <i class="bi bi-collection-play me-1"></i> Manage Anime
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="contact_messages.php" class="btn btn-outline-secondary d-block">
                                            <i class="bi bi-envelope me-1"></i> Contact Messages
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <a href="index.php" class="btn btn-outline-info d-block">
                                            <i class="bi bi-house-door me-1"></i> Home Page
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h3 class="h6 mb-0">User Management</h3>
                        <span class="badge bg-light text-dark"><?= isset($users) ? count($users) : 0 ?> Users</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?= $user['id'] ?></td>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'info' ?>">
                                                        <?= ucfirst(htmlspecialchars($user['role'] ?? 'user')) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                Actions
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <form method="POST" action="admin.php">
                                                                        <input type="hidden" name="action" value="update_user_role">
                                                                        <input type="hidden" name="user_id"
                                                                            value="<?= $user['id'] ?>">
                                                                        <input type="hidden" name="role"
                                                                            value="<?= $user['role'] === 'admin' ? 'user' : 'admin' ?>">
                                                                        <button type="submit" class="dropdown-item">
                                                                            <?= $user['role'] === 'admin' ? 'Revoke Admin' : 'Make Admin' ?>
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li>
                                                                    <form method="POST" action="admin.php"
                                                                        onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                                        <input type="hidden" name="action" value="delete_user">
                                                                        <input type="hidden" name="user_id"
                                                                            value="<?= $user['id'] ?>">
                                                                        <button type="submit" class="dropdown-item text-danger">
                                                                            Delete User
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Current User</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr> <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No users found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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