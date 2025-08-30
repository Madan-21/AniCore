<?php
/**
 * Add Anime Page
 * 
 * Shows anime not in the user's watchlist with options to add them
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

// Require login for this page
require_login();

$userId = $_SESSION['user_id'];

// Get all anime not in the user's watchlist
try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $perPage = 12; // Number of anime per page
    $offset = ($page - 1) * $perPage;

    // Get total count for pagination
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM anime a
        WHERE NOT EXISTS (
            SELECT 1 FROM user_anime_watchlist w
            WHERE w.anime_id = a.id AND w.user_id = :user_id
        )
    ");
    $countStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $countStmt->execute();
    $totalAnime = $countStmt->fetchColumn();
    $totalPages = ceil($totalAnime / $perPage);

    // Get anime with pagination
    $stmt = $pdo->prepare("
        SELECT a.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
        FROM anime a
        LEFT JOIN anime_genres ag ON a.id = ag.anime_id
        LEFT JOIN genres g ON ag.genre_id = g.id
        WHERE NOT EXISTS (
            SELECT 1 FROM user_anime_watchlist w
            WHERE w.anime_id = a.id AND w.user_id = :user_id
        )
        GROUP BY a.id
        ORDER BY a.title
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $animeList = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching anime list: " . $e->getMessage();
    $animeList = [];
}

// Include header
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Add Anime to Watchlist</h1>
    <div>
        <a href="my_watchlist.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to My Watchlist
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
<?php endif; ?>

<?php if (empty($animeList)): ?>
    <div class="alert alert-info">
        <h4 class="alert-heading">No anime available!</h4>
        <p>You've already added all available anime to your watchlist.</p>
        <hr>
        <p><a href="my_watchlist.php" class="alert-link">View your watchlist</a></p>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($animeList as $anime): ?>
            <div class="col">
                <div class="card h-100 anime-card">
                    <?php if ($anime['poster_path']): ?>
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

                        <?php if (!empty($anime['release_year'])): ?>
                            <p class="card-text"><small class="text-muted"><?= $anime['release_year'] ?></small></p>
                        <?php endif; ?>

                        <?php if (!empty($anime['genres'])): ?>
                            <p class="card-text"><small class="text-muted"><?= htmlspecialchars($anime['genres']) ?></small></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-grid gap-2">
                            <a href="anime_detail.php?id=<?= $anime['id'] ?>" class="btn btn-sm btn-outline-primary">View
                                Details</a>
                            <form action="actions/add_to_watchlist.php" method="POST">
                                <input type="hidden" name="anime_id" value="<?= $anime['id'] ?>">
                                <input type="hidden" name="status" value="Plan to Watch">
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">Add to Watchlist</button>
                            </form>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Bootstrap dropdowns directly
        const dropdownTriggerList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownTriggerList.forEach(function (dropdownTriggerEl) {
            new bootstrap.Dropdown(dropdownTriggerEl);
        });

        // Episode counter buttons functionality
        document.querySelectorAll('.episode-btn').forEach(button => {
            button.addEventListener('click', function () {
                const input = this.parentNode.querySelector('.episodes-input');
                const currentVal = parseInt(input.value) || 0;
                const maxVal = parseInt(input.dataset.max) || 9999;

                if (this.dataset.action === 'plus' && currentVal < maxVal) {
                    input.value = currentVal + 1;
                } else if (this.dataset.action === 'minus' && currentVal > 0) {
                    input.value = currentVal - 1;
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>