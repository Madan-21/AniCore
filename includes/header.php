<?php
// Start session at the very top if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection if it's not already included
if (!isset($pdo)) {
    $dbIncludePath = dirname(__FILE__) . '/../config/database.php';
    if (file_exists($dbIncludePath)) {
        require_once $dbIncludePath;
    }
}

// Include functions if not already included
if (!function_exists('check_admin')) {
    $functionsPath = dirname(__FILE__) . '/functions.php';
    if (file_exists($functionsPath)) {
        require_once $functionsPath;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniCore - Personal Anime Watchlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Added link to custom CSS -->
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-collection-play me-2"></i>AniCore
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">Search</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my_watchlist.php">My Watchlist</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        // Check if user is admin - use the check_admin function if available
                        $isAdmin = function_exists('check_admin') ? check_admin() : false;

                        // Fallback to manual check if function doesn't exist or returns false
                        if (!$isAdmin && isset($pdo)) {
                            try {
                                $adminCheck = $pdo->prepare("SELECT role FROM users WHERE id = :id");
                                $adminCheck->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                                $adminCheck->execute();
                                $userData = $adminCheck->fetch();
                                $isAdmin = ($userData && $userData['role'] === 'admin');
                            } catch (PDOException $e) {
                                // Silently ignore any errors
                                error_log("Admin check error in header: " . $e->getMessage());
                            }
                        }

                        // Last resort: check session directly
                        if (!$isAdmin) {
                            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
                        }

                        if ($isAdmin):
                            ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">Admin</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="contact_messages.php">Messages</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="my_watchlist.php">My Watchlist</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container pt-2">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-<?= isset($_GET['error']) && $_GET['error'] ? 'danger' : 'success' ?> alert-dismissible fade show"
                role="alert">
                <?= htmlspecialchars($_GET['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>