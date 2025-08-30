<?php
/**
 * My Watchlist Page
 * 
 * Shows the current user's watchlist items
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection and functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session information
error_log("Watchlist page loaded. Session data: " . print_r($_SESSION, true));

// Require login for this page
require_login();

$userId = $_SESSION['user_id'];

// Get filter from GET parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;

// Get the user's watchlist
$watchlist = get_user_watchlist($pdo, $userId, $statusFilter);

// Include header
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>My Watchlist</h1>

    <div class="d-flex">
        <a href="add_anime.php" class="btn btn-success me-2">
            <i class="bi bi-plus-lg"></i> Add Anime
        </a>

        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
                <?= $statusFilter ? "Filter: $statusFilter" : "Filter by Status" ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                <li><a class="dropdown-item <?= $statusFilter === null ? 'active' : '' ?>"
                        href="my_watchlist.php">All</a></li>
                <li><a class="dropdown-item <?= $statusFilter === 'Watching' ? 'active' : '' ?>"
                        href="my_watchlist.php?status=Watching">Watching</a></li>
                <li><a class="dropdown-item <?= $statusFilter === 'Completed' ? 'active' : '' ?>"
                        href="my_watchlist.php?status=Completed">Completed</a></li>
                <li><a class="dropdown-item <?= $statusFilter === 'On-Hold' ? 'active' : '' ?>"
                        href="my_watchlist.php?status=On-Hold">On-Hold</a></li>
                <li><a class="dropdown-item <?= $statusFilter === 'Dropped' ? 'active' : '' ?>"
                        href="my_watchlist.php?status=Dropped">Dropped</a></li>
                <li><a class="dropdown-item <?= $statusFilter === 'Plan to Watch' ? 'active' : '' ?>"
                        href="my_watchlist.php?status=Plan to Watch">Plan to Watch</a></li>
            </ul>
        </div>
    </div>
</div>

<?php if (empty($watchlist)): ?>
    <div class="alert alert-info">
        <?php if ($statusFilter): ?>
            No anime with status "<?= $statusFilter ?>" in your watchlist.
            <a href="my_watchlist.php" class="alert-link">View all anime</a>
        <?php else: ?>
            Your watchlist is empty. Go to the <a href="index.php" class="alert-link">home page</a> to add anime.
        <?php endif; ?>
    </div>
<?php else: ?>

    <?php
    // Calculate watchlist statistics
    $statusCounts = array(
        'Watching' => 0,
        'Completed' => 0,
        'On-Hold' => 0,
        'Dropped' => 0,
        'Plan to Watch' => 0
    );

    $totalEpisodes = 0;
    $watchedEpisodes = 0;
    $ratedAnimeCount = 0;
    $totalRating = 0;

    foreach ($watchlist as $item) {
        // Count by status
        if (isset($statusCounts[$item['status']])) {
            $statusCounts[$item['status']]++;
        }

        // Count episodes
        if (!empty($item['episode_count'])) {
            $totalEpisodes += $item['episode_count'];
        }

        if (!empty($item['episodes_watched'])) {
            $watchedEpisodes += $item['episodes_watched'];
        }

        // Calculate average rating
        if (!empty($item['user_rating'])) {
            $ratedAnimeCount++;
            $totalRating += $item['user_rating'];
        }
    }

    $avgRating = $ratedAnimeCount > 0 ? round($totalRating / $ratedAnimeCount, 1) : 0;
    $completionRate = $totalEpisodes > 0 ? round(($watchedEpisodes / $totalEpisodes) * 100, 1) : 0;
    $totalEntries = count($watchlist);
    ?>

    <!-- Watchlist Statistics -->
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <div class="col">
            <div class="card h-100 border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="bi bi-list-check me-2"></i>Watchlist Summary</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Anime
                            <span class="badge bg-primary rounded-pill"><?= $totalEntries ?></span>
                        </li>
                        <?php foreach ($statusCounts as $status => $count): ?>         <?php if ($count > 0): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $status ?>
                                    <span
                                        class="badge status-<?= str_replace(' ', '-', $status) ?> rounded-pill"><?= $count ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-success">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-play-circle me-2"></i>Episode Progress</h5>
                    <div class="text-center mb-3">
                        <div class="d-inline-block position-relative" style="width: 120px; height: 120px;">
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <h3 class="mb-0"><?= $completionRate ?>%</h3>
                                <small>Complete</small>
                            </div>
                            <svg width="120" height="120" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#e9ecef" stroke-width="12" />
                                <circle cx="60" cy="60" r="54" fill="none" stroke="#198754" stroke-width="12"
                                    stroke-dasharray="339.292"
                                    stroke-dashoffset="<?= 339.292 * (1 - $completionRate / 100) ?>" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="mb-1">Episodes Watched: <strong><?= $watchedEpisodes ?></strong></p>
                        <p class="mb-0">Total Episodes: <strong><?= $totalEpisodes ?></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning"><i class="bi bi-star me-2"></i>Rating Overview</h5>
                    <div class="text-center mb-3">
                        <div class="display-4 fw-bold">
                            <?= $avgRating ?>
                            <small class="text-muted fs-6">/10</small>
                        </div>
                        <p>Average Rating</p>

                        <div class="mb-2">
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar"
                                    style="width: <?= ($avgRating / 10) * 100 ?>%"></div>
                            </div>
                        </div>
                        <small>Based on <?= $ratedAnimeCount ?> rated anime</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Poster</th>
                    <th>Anime</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Progress</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($watchlist as $item): ?>
                    <tr>
                        <td style="width: 80px;">
                            <?php if ($item['poster_path']): ?>
                                <img src="<?= htmlspecialchars($item['poster_path']) ?>" class="img-thumbnail"
                                    alt="<?= htmlspecialchars($item['title']) ?> poster" width="70">
                            <?php else: ?>
                                <div class="bg-secondary text-white text-center" style="width: 70px; height: 100px;">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="anime_detail.php?id=<?= $item['anime_id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                        </td>
                        <td>
                            <span
                                class="badge status-<?= str_replace(' ', '-', $item['status']) ?>"><?= $item['status'] ?></span>
                        </td>
                        <td>
                            <?php if ($item['user_rating']): ?>
                                <span class="rating"><?= $item['user_rating'] ?>/10</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['episode_count']): ?>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?= $item['episodes_watched'] ?? 0 ?>/<?= $item['episode_count'] ?></span>
                                    <div class="progress flex-grow-1" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: <?= ($item['episodes_watched'] / $item['episode_count']) * 100 ?>%;"
                                            aria-valuenow="<?= $item['episodes_watched'] ?? 0 ?>" aria-valuemin="0"
                                            aria-valuemax="<?= $item['episode_count'] ?>"></div>
                                    </div>
                                </div>
                            <?php elseif ($item['episodes_watched']): ?>
                                <?= $item['episodes_watched'] ?> eps watched
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="anime_detail.php?id=<?= $item['anime_id'] ?>#watchlist"
                                    class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="actions/remove_from_watchlist.php" method="POST" class="d-inline">
                                    <input type="hidden" name="anime_id" value="<?= $item['anime_id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger confirm-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>