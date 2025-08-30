<?php
/**
 * Delete Anime
 * 
 * This script handles the deletion of anime entries
 * Only administrators can access this functionality
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

// Require admin access
require_admin();

// Check if anime ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_anime.php?message=Invalid anime ID&error=1");
    exit;
}

$animeId = (int) $_GET['id'];
$error = null;
$success = null;

// Check if the form was submitted with confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    try {
        // Begin transaction
        $pdo->beginTransaction();

        // First, delete related records in other tables

        // Delete from anime_genres
        $stmt = $pdo->prepare("DELETE FROM anime_genres WHERE anime_id = :anime_id");
        $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
        $stmt->execute();

        // Delete from user_anime_watchlist
        $stmt = $pdo->prepare("DELETE FROM user_anime_watchlist WHERE anime_id = :anime_id");
        $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
        $stmt->execute();

        // Finally, delete the anime record
        $stmt = $pdo->prepare("DELETE FROM anime WHERE id = :id");
        $stmt->bindParam(':id', $animeId, PDO::PARAM_INT);
        $stmt->execute();
        // Commit transaction
        $pdo->commit();

        // Redirect back to manage anime page with success message
        header("Location: manage_anime.php?message=Anime deleted successfully&success=1");
        exit;
    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = "Error deleting anime: " . $e->getMessage();
    }
} else {    // Get anime details for confirmation
    try {
        // Use the get_anime_details function from functions.php
        $anime = get_anime_details($pdo, $animeId);

        if (!$anime) {
            header("Location: manage_anime.php?message=Anime not found&error=1");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error fetching anime details: " . $e->getMessage();
    }
}

// Include page header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h2 class="h5 mb-0">Delete Anime</h2>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <a href="manage_anime.php" class="btn btn-secondary">Back to Manage Anime</a>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h3 class="h5">Are you sure you want to delete this anime?</h3>
                        <p><strong>Title:</strong> <?= htmlspecialchars($anime['title'] ?? 'Unknown') ?></p>
                        <p class="mb-0 text-danger">This action cannot be undone. All related data including watchlist
                            entries will be permanently removed.</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <?php if (!empty($anime['poster_path'])): ?>
                                <img src="<?= htmlspecialchars($anime['poster_path']) ?>" class="img-fluid rounded mb-3"
                                    alt="<?= htmlspecialchars($anime['title'] ?? 'Anime') ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <dl class="row">
                                <dt class="col-sm-4">Release Year:</dt>
                                <dd class="col-sm-8"><?= $anime['release_year'] ?? 'N/A' ?></dd>

                                <dt class="col-sm-4">Episodes:</dt>
                                <dd class="col-sm-8"><?= $anime['episode_count'] ?? 'N/A' ?></dd>

                                <?php if (!empty($anime['genres'])): ?>
                                    <dt class="col-sm-4">Genres:</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($anime['genres']) ?></dd>
                                <?php endif; ?>

                                <?php if (!empty($anime['description'])): ?>
                                    <dt class="col-sm-12">Description:</dt>
                                    <dd class="col-sm-12">
                                        <?= nl2br(htmlspecialchars(substr($anime['description'], 0, 200) . (strlen($anime['description']) > 200 ? '...' : ''))) ?>
                                    </dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <form method="POST" action="delete_anime.php?id=<?= $animeId ?>">
                        <input type="hidden" name="confirm_delete" value="yes">

                        <div class="d-flex justify-content-between"><a href="manage_anime.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Confirm Delete
                            </button>
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