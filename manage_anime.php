<?php
/**
 * Manage Anime
 * 
 * This page allows administrators to manage (view, edit, delete) all anime entries
 */

// Suppress notices for this page to avoid undefined array key warnings
error_reporting(E_ALL & ~E_NOTICE);

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

// Initialize variables
$animeList = [];
$error = null;
$success = null;

// Check for success/error messages from other pages
if (isset($_GET['message'])) {
    if (isset($_GET['error']) && $_GET['error'] === '1') {
        $error = $_GET['message'];
    } else {
        $success = $_GET['message'];
    }
}

// Get all anime entries with their genres
try {
    $stmt = $pdo->query("
        SELECT 
            a.*, 
            IFNULL(a.episode_count, 'N/A') AS episodes,
            GROUP_CONCAT(g.name SEPARATOR ', ') as genres
        FROM anime a
        LEFT JOIN anime_genres ag ON a.id = ag.anime_id
        LEFT JOIN genres g ON ag.genre_id = g.id
        GROUP BY a.id
        ORDER BY a.title
    ");
    $animeList = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching anime: " . $e->getMessage();
}

// Include page header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Manage Anime</h2>
                <div>
                    <a href="admin_add_anime.php" class="btn btn-sm btn-light">
                        <i class="bi bi-plus-lg"></i> Add New Anime
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Year</th>
                                <th>Episodes</th>
                                <th>Genres</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($animeList)): ?>
                                <?php foreach ($animeList as $anime): ?>
                                    <tr>
                                        <td><?= $anime['id'] ?></td>
                                        <td>
                                            <a href="anime_detail.php?id=<?= $anime['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($anime['title']) ?>
                                            </a>
                                        </td>
                                        <td><?= $anime['release_year'] ?? 'N/A' ?></td>
                                        <td><?= $anime['episodes'] ?? 'N/A' ?></td>
                                        <td>
                                            <small><?= htmlspecialchars($anime['genres'] ?? 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="anime_detail.php?id=<?= $anime['id'] ?>"
                                                    class="btn btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="edit_anime.php?id=<?= $anime['id'] ?>" class="btn btn-outline-primary"
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="delete_anime.php?id=<?= $anime['id'] ?>" class="btn btn-outline-danger"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No anime found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="admin.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include page footer
include 'includes/footer.php';
?>