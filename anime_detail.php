<?php
/**
 * Anime Detail Page
 * 
 * Shows details of a specific anime and allows users to manage their watchlist entry
 */

// Suppress notices for this page to avoid undefined array key warnings
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

// Include database connection and functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get anime ID from URL
$animeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Debug session information
error_log("Anime detail page loaded for anime ID: $animeId");
error_log("Session status: " . (isset($_SESSION['user_id']) ? "User ID: {$_SESSION['user_id']}" : "No user logged in"));

if (!$animeId) {
    header('Location: index.php?message=Invalid anime ID&error=1');
    exit;
}

// Get anime details
$anime = get_anime_details($pdo, $animeId);

if (!$anime) {
    header('Location: index.php?message=Anime not found&error=1');
    exit;
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$watchlistEntry = null;

// Get user's watchlist entry for this anime if logged in
if ($isLoggedIn) {
    $watchlistEntry = get_watchlist_entry($pdo, $_SESSION['user_id'], $animeId);
}

// Check if user is an admin
$isAdmin = false;
if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        $isAdmin = ($user && $user['role'] === 'admin');
    } catch (PDOException $e) {
        // In case of error, default to non-admin
        error_log("Error checking admin status: " . $e->getMessage());
    }
}

// Include header
include 'includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($anime['title']) ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Anime Details -->
    <div class="col-md-8">        <?php if (isset($anime['banner_path']) && $anime['banner_path']): ?>
        <img src="<?= htmlspecialchars($anime['banner_path']) ?>" class="img-fluid mb-3 anime-banner"
            alt="<?= htmlspecialchars($anime['title'] ?? 'Anime') ?> banner">
        <?php endif; ?>        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0"><?= htmlspecialchars($anime['title'] ?? 'Anime Details') ?></h1>
            
            <?php if ($isAdmin): ?>
            <div class="admin-controls">
                <div class="btn-group" role="group">
                    <a href="edit_anime.php?id=<?= $animeId ?>" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="delete_anime.php?id=<?= $animeId ?>" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">            <?php if (!empty($anime['genres'] ?? '')): ?>
            <div class="mb-2">
                <strong>Genres:</strong> <?= htmlspecialchars($anime['genres'] ?? 'N/A') ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($anime['release_year'] ?? '')): ?>
            <div class="mb-2">
                <strong>Released:</strong> <?= $anime['release_year'] ?? 'N/A' ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($anime['episode_count'] ?? '')): ?>
            <div>
                <strong>Episodes:</strong> <?= $anime['episode_count'] ?? 'N/A' ?>
            </div>
            <?php endif; ?>
        </div>        <?php if (!empty($anime['description'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Description</h2>
            </div>
            <div class="card-body">
                <p class="card-text"><?= nl2br(htmlspecialchars(str_replace('[Written by MAL Rewrite]', '', trim($anime['description'])))) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar with Poster and Watchlist Form -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">                <?php if (isset($anime['poster_path']) && $anime['poster_path']): ?>
                <img src="<?= htmlspecialchars($anime['poster_path']) ?>" class="img-fluid mb-3"
                    alt="<?= htmlspecialchars($anime['title'] ?? 'Anime') ?> poster">
                <?php else: ?>
                <div class="bg-secondary d-flex align-items-center justify-content-center mb-3" style="height: 300px;">
                    <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                </div>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                <div id="watchlist">
                    <?php if ($watchlistEntry): ?>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h3 class="h6 mb-0">Your Watchlist Status</h3>
                        </div>
                        <div class="card-body">
                            <form action="actions/update_watchlist_item.php" method="POST">
                                <input type="hidden" name="anime_id" value="<?= $animeId ?>">

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Watching"
                                            <?= $watchlistEntry['status'] == 'Watching' ? 'selected' : '' ?>>Watching
                                        </option>
                                        <option value="Completed"
                                            <?= $watchlistEntry['status'] == 'Completed' ? 'selected' : '' ?>>Completed
                                        </option>
                                        <option value="On-Hold"
                                            <?= $watchlistEntry['status'] == 'On-Hold' ? 'selected' : '' ?>>On-Hold
                                        </option>
                                        <option value="Dropped"
                                            <?= $watchlistEntry['status'] == 'Dropped' ? 'selected' : '' ?>>Dropped
                                        </option>
                                        <option value="Plan to Watch"
                                            <?= $watchlistEntry['status'] == 'Plan to Watch' ? 'selected' : '' ?>>Plan
                                            to Watch</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="user_rating" class="form-label">Your Rating (1-10)</label>
                                    <input type="number" class="form-control" id="user_rating" name="user_rating"
                                        min="1" max="10" value="<?= $watchlistEntry['user_rating'] ?? '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="episodes_watched" class="form-label">Episodes Watched</label>
                                    <input type="number" class="form-control" id="episodes_watched"
                                        name="episodes_watched" min="0" max="<?= $anime['episode_count'] ?? '' ?>"
                                        value="<?= $watchlistEntry['episodes_watched'] ?? '0' ?>"
                                        data-counter="episode-counter">
                                    <div class="form-text">
                                        <span id="episode-counter"><?= ($watchlistEntry['episodes_watched'] ?? '0') ?> /
                                            <?= $anime['episode_count'] ?? '?' ?></span> episodes
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mb-2 w-100">Update Watchlist</button>
                            </form>

                            <form action="actions/remove_from_watchlist.php" method="POST">
                                <input type="hidden" name="anime_id" value="<?= $animeId ?>">
                                <button type="submit" class="btn btn-outline-danger w-100 confirm-delete">Remove from
                                    Watchlist</button>
                            </form>
                        </div>
                    </div> <?php else: ?>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h3 class="h6 mb-0">Add to Your Watchlist</h3>
                        </div>                        <div class="card-body">
                            <form action="actions/add_to_watchlist.php" method="POST">
                                <input type="hidden" name="anime_id" value="<?= $animeId ?>">

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Watching">Watching</option>
                                        <option value="Completed">Completed</option>
                                        <option value="On-Hold">On-Hold</option>
                                        <option value="Dropped">Dropped</option>
                                        <option value="Plan to Watch" selected>Plan to Watch</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="user_rating" class="form-label">Your Rating (1-10)</label>
                                    <input type="number" class="form-control" id="user_rating" name="user_rating"
                                        min="1" max="10">
                                </div>

                                <div class="mb-3">
                                    <label for="episodes_watched" class="form-label">Episodes Watched</label>
                                    <input type="number" class="form-control" id="episodes_watched"
                                        name="episodes_watched" min="0" max="<?= $anime['episode_count'] ?? '' ?>"
                                        value="0" data-counter="episode-counter-add">
                                    <div class="form-text">
                                        <span id="episode-counter-add">0 / <?= $anime['episode_count'] ?? '?' ?></span>
                                        episodes
                                    </div>
                                </div>                                <button type="submit" class="btn btn-primary w-100">Add to Watchlist</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <a href="login.php" class="btn btn-secondary btn-lg w-100">Login to Track</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>