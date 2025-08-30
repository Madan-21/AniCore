<?php
/**
 * Search Page
 * 
 * This page allows users to search for anime by title, genre, or release year
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
$searchQuery = '';
$searchType = 'title'; // Default search type
$results = [];
$error = null;

// Process search form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $searchType = isset($_GET['type']) ? $_GET['type'] : 'title';

    // Only search if query is not empty
    if (!empty($searchQuery)) {
        try {
            // Different query based on search type
            switch ($searchType) {
                case 'genre':
                    // Search by genre
                    $stmt = $pdo->prepare("
                        SELECT a.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
                        FROM anime a
                        LEFT JOIN anime_genres ag ON a.id = ag.anime_id
                        LEFT JOIN genres g ON ag.genre_id = g.id
                        WHERE g.name LIKE :query
                        GROUP BY a.id
                        ORDER BY a.title
                    ");
                    $stmt->bindValue(':query', "%{$searchQuery}%");
                    break;

                case 'year':
                    // Search by release year
                    $stmt = $pdo->prepare("
                        SELECT a.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
                        FROM anime a
                        LEFT JOIN anime_genres ag ON a.id = ag.anime_id
                        LEFT JOIN genres g ON ag.genre_id = g.id
                        WHERE a.release_year = :query
                        GROUP BY a.id
                        ORDER BY a.title
                    ");
                    $stmt->bindValue(':query', $searchQuery);
                    break;

                case 'title':
                default:
                    // Search by title
                    $stmt = $pdo->prepare("
                        SELECT a.*, GROUP_CONCAT(g.name SEPARATOR ', ') as genres
                        FROM anime a
                        LEFT JOIN anime_genres ag ON a.id = ag.anime_id
                        LEFT JOIN genres g ON ag.genre_id = g.id
                        WHERE a.title LIKE :query
                        GROUP BY a.id
                        ORDER BY a.title
                    ");
                    $stmt->bindValue(':query', "%{$searchQuery}%");
                    break;
            }

            $stmt->execute();
            $results = $stmt->fetchAll();

        } catch (PDOException $e) {
            $error = "Error executing search: " . $e->getMessage();
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
                <h2 class="h5 mb-0">Search Anime</h2>
            </div>
            <div class="card-body">
                <form action="search.php" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search anime..."
                            value="<?= htmlspecialchars($searchQuery) ?>" required>
                        <select name="type" class="form-select" style="max-width: 150px;">
                            <option value="title" <?= $searchType === 'title' ? 'selected' : '' ?>>Title</option>
                            <option value="genre" <?= $searchType === 'genre' ? 'selected' : '' ?>>Genre</option>
                            <option value="year" <?= $searchType === 'year' ? 'selected' : '' ?>>Year</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && empty($searchQuery)): ?>
                    <div class="alert alert-warning">Please enter a search term</div>
                <?php endif; ?>

                <?php if (!empty($searchQuery) && empty($results)): ?>
                    <div class="alert alert-info">No results found for "<?= htmlspecialchars($searchQuery) ?>"</div>
                <?php endif; ?>

                <?php if (!empty($results)): ?>
                    <h3 class="h6 mb-3">Found <?= count($results) ?> results for "<?= htmlspecialchars($searchQuery) ?>"
                    </h3>

                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php foreach ($results as $anime): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <img src="<?= !empty($anime['image_url']) ? htmlspecialchars($anime['image_url']) : 'images/no-image.jpg' ?>"
                                        class="card-img-top" alt="<?= htmlspecialchars($anime['title']) ?>"
                                        style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($anime['title']) ?></h5>
                                        <p class="card-text small text-muted">
                                            <?= htmlspecialchars($anime['genres'] ?? 'No genres') ?> |
                                            <?= htmlspecialchars($anime['release_year'] ?? 'Unknown year') ?>
                                        </p>
                                        <a href="anime_detail.php?id=<?= $anime['id'] ?>" class="btn btn-sm btn-primary">View
                                            Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include page footer
include 'includes/footer.php';
?>