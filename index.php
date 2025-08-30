<?php
/**
 * Home Page - Browse Master Anime List
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

// Include header
include 'includes/header.php';

// Get all anime from the database
try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $perPage = 12; // Number of anime per page
    $offset = ($page - 1) * $perPage;

    // Get total count for pagination
    $countStmt = $pdo->query("SELECT COUNT(*) FROM anime");
    $totalAnime = $countStmt->fetchColumn();
    $totalPages = ceil($totalAnime / $perPage);    // Get anime with pagination
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            a.poster_path, 
            a.banner_path,
            a.episode_count,
            GROUP_CONCAT(g.name SEPARATOR ', ') as genres
        FROM anime a
        LEFT JOIN anime_genres ag ON a.id = ag.anime_id
        LEFT JOIN genres g ON ag.genre_id = g.id
        GROUP BY a.id
        ORDER BY a.title
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $animeList = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching anime list: " . $e->getMessage();
    $animeList = [];
}

// Check if user is logged in to display watchlist buttons
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Get user's watchlist entries if logged in
$userWatchlist = [];
if ($isLoggedIn) {
    try {
        $watchlistStmt = $pdo->prepare("
            SELECT anime_id, status FROM user_anime_watchlist
            WHERE user_id = :user_id
        ");
        $watchlistStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $watchlistStmt->execute();

        while ($row = $watchlistStmt->fetch()) {
            $userWatchlist[$row['anime_id']] = $row['status'];
        }
    } catch (PDOException $e) {
        // Silently fail, we'll just not show the watchlist status
    }
}
?>

<h1 class="mb-4">Browse Anime</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
<?php endif; ?>

<?php if (empty($animeList)): ?>
    <div class="alert alert-info">
        <h4 class="alert-heading">No anime found!</h4>
        <p>There are no anime in the database yet.</p>
        <hr>
        <p>Check the <a href="db_check.php">database connection status</a> if you're having issues.</p>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($animeList as $anime): ?>
            <div class="col">
                <div class="card h-100 anime-card"> <?php if (isset($anime['poster_path']) && $anime['poster_path']): ?>
                        <img src="<?= htmlspecialchars($anime['poster_path']) ?>" class="card-img-top"
                            alt="<?= htmlspecialchars($anime['title']) ?> poster">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                            style="height: 300px;">
                            <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($anime['title']) ?></h5>
                        <?php if (!empty($anime['release_year'] ?? '')): ?>
                            <p class="card-text"><small class="text-muted"><?= $anime['release_year'] ?? 'N/A' ?></small></p>
                        <?php endif; ?>

                        <?php if (!empty($anime['genres'] ?? '')): ?>
                            <p class="card-text"><small
                                    class="text-muted"><?= htmlspecialchars($anime['genres'] ?? 'N/A') ?></small></p>
                        <?php endif; ?>        <?php if (isset($userWatchlist[$anime['id']])): ?>
                            <span class="badge status-<?= str_replace(' ', '-', $userWatchlist[$anime['id']]) ?>">
                                <?= $userWatchlist[$anime['id']] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer bg-white border-top-0">
                        <div class="d-grid gap-2">
                            <a href="anime_detail.php?id=<?= $anime['id'] ?>" class="btn btn-sm btn-outline-primary">View
                                Details</a>

                            <?php if ($isLoggedIn): ?>             <?php if (isset($userWatchlist[$anime['id']])): ?>
                                    <a href="anime_detail.php?id=<?= $anime['id'] ?>#watchlist" class="btn btn-sm btn-outline-success">
                                        Manage in Watchlist
                                    </a> <?php else: ?>
                                    <form action="actions/add_to_watchlist.php" method="POST" class="d-inline">
                                        <input type="hidden" name="anime_id" value="<?= $anime['id'] ?>">
                                        <input type="hidden" name="status" value="Plan to Watch">
                                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Add to Watchlist</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-sm btn-outline-secondary">Login to Add</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Anime pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
<!-- Close the "if empty($animeList)" check -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Bootstrap dropdowns directly
        const dropdownTriggerList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownTriggerList.forEach(function (dropdownTriggerEl) {
            new bootstrap.Dropdown(dropdownTriggerEl);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>