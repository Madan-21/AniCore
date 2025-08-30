<?php
/**
 * Edit Anime Page
 * 
 * This page allows administrators to edit anime details
 */

// Suppress notices for this page to avoid undefined array key warnings
error_reporting(E_ALL & ~E_NOTICE);

// Include database connection
require_once 'config/database.php';
// Include functions
require_once 'includes/functions.php';

// Require login to access this page
require_login();

// Check if the user is an admin
$isAdmin = false;
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    $isAdmin = ($user && $user['role'] === 'admin');
} catch (PDOException $e) {
    // In case of error, default to non-admin
}

// If not admin, redirect to homepage with an error message
if (!$isAdmin) {
    header("Location: index.php?message=You do not have permission to access this page&error=1");
    exit;
}

// Check if anime ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?message=Invalid anime ID&error=1");
    exit;
}

$animeId = (int) $_GET['id'];
$anime = null;
$genres = [];
$allGenres = [];
$error = null;
$success = null;

// Get anime details and all available genres
try {
    // Get anime details
    $anime = get_anime_details($pdo, $animeId);

    if (!$anime) {
        header("Location: index.php?message=Anime not found&error=1");
        exit;
    }

    // Get all genres
    $stmt = $pdo->query("SELECT id, name FROM genres ORDER BY name");
    $allGenres = $stmt->fetchAll();

    // Get selected genres for this anime
    $stmt = $pdo->prepare("
        SELECT genre_id FROM anime_genres
        WHERE anime_id = :anime_id
    ");
    $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
    $stmt->execute();
    $selectedGenres = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Error fetching anime details: " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $releaseYear = (int) $_POST['release_year'];
    $episodes = (int) $_POST['episodes']; // This becomes episode_count in database
    $imageUrl = trim($_POST['image_url']);  // This becomes poster_path in database
    $bannerUrl = trim($_POST['banner_url'] ?? ''); // This becomes banner_path in database
    $studio = trim($_POST['studio'] ?? '');
    $director = trim($_POST['director'] ?? '');
    $rating = isset($_POST['rating']) ? (float) $_POST['rating'] : null;
    $selectedGenres = $_POST['genres'] ?? [];

    // Basic validation
    if (empty($title)) {
        $error = "Title is required";
    } elseif ($releaseYear < 1900 || $releaseYear > date('Y') + 1) {
        $error = "Invalid release year";
    } elseif ($episodes < 0) {
        $error = "Episodes cannot be negative";
    } elseif ($rating !== null && ($rating < 0 || $rating > 10)) {
        $error = "Rating must be between 0 and 10";
    } else {
        try {
            // Begin transaction
            $pdo->beginTransaction();            // Update anime details
            $stmt = $pdo->prepare("
                UPDATE anime 
                SET title = :title, description = :description, release_year = :release_year,
                    episode_count = :episodes, poster_path = :image_url, banner_path = :banner_url,
                    studio = :studio, director = :director, rating = :rating
                WHERE id = :id
            ");

            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':release_year', $releaseYear);
            $stmt->bindParam(':episodes', $episodes);
            $stmt->bindParam(':image_url', $imageUrl);
            $stmt->bindParam(':banner_url', $bannerUrl);
            $stmt->bindParam(':studio', $studio);
            $stmt->bindParam(':director', $director);
            $stmt->bindParam(':rating', $rating);
            $stmt->bindParam(':id', $animeId);

            $stmt->execute();

            // Delete all existing genre associations
            $stmt = $pdo->prepare("DELETE FROM anime_genres WHERE anime_id = :anime_id");
            $stmt->bindParam(':anime_id', $animeId);
            $stmt->execute();

            // Insert new genre associations
            if (!empty($selectedGenres)) {
                $insertGenreStmt = $pdo->prepare("
                    INSERT INTO anime_genres (anime_id, genre_id)
                    VALUES (:anime_id, :genre_id)
                ");

                foreach ($selectedGenres as $genreId) {
                    $insertGenreStmt->bindParam(':anime_id', $animeId);
                    $insertGenreStmt->bindParam(':genre_id', $genreId);
                    $insertGenreStmt->execute();
                }
            }

            // Commit transaction
            $pdo->commit();

            // Refresh anime data
            $anime = get_anime_details($pdo, $animeId);

            $success = "Anime updated successfully";
        } catch (PDOException $e) {
            // Rollback on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = "Error updating anime: " . $e->getMessage();
        }
    }
}

// Include page header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Edit Anime</h2>
                <a href="anime_detail.php?id=<?= $animeId ?>" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Back to Details
                </a>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="edit_anime.php?id=<?= $animeId ?>">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title"
                                    value="<?= htmlspecialchars($anime['title'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description"
                                    rows="5"><?= htmlspecialchars($anime['description'] ?? '') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3"> <label for="release_year" class="form-label">Release Year</label>
                                        <input type="number" class="form-control" id="release_year" name="release_year"
                                            value="<?= $anime['release_year'] ?? '' ?>" min="1900"
                                            max="<?= date('Y') + 1 ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="episodes" class="form-label">Episodes</label>
                                        <input type="number" class="form-control" id="episodes" name="episodes"
                                            value="<?= $anime['episode_count'] ?? 0 ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Rating (0-10)</label>
                                        <input type="number" class="form-control" id="rating" name="rating"
                                            value="<?= $anime['rating'] ?? '' ?>" min="0" max="10" step="0.1">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="studio" class="form-label">Studio</label>
                                        <input type="text" class="form-control" id="studio" name="studio"
                                            value="<?= htmlspecialchars($anime['studio'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="director" class="form-label">Director</label>
                                        <input type="text" class="form-control" id="director" name="director"
                                            value="<?= htmlspecialchars($anime['director'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="image_url" class="form-label">Image URL</label>
                                <input type="text" class="form-control" id="image_url" name="image_url"
                                    value="<?= htmlspecialchars($anime['poster_path'] ?? '') ?>">
                                <div class="form-text">URL for the poster image</div>
                            </div>

                            <div class="mb-3">
                                <label for="banner_url" class="form-label">Banner URL</label>
                                <input type="text" class="form-control" id="banner_url" name="banner_url"
                                    value="<?= htmlspecialchars($anime['banner_path'] ?? '') ?>">
                                <div class="form-text">URL for the banner image (optional)</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-header">Preview</div>
                                <img src="<?= !empty($anime['poster_path']) ? htmlspecialchars($anime['poster_path']) : 'images/no-image.jpg' ?>"
                                    class="card-img-top" alt="<?= htmlspecialchars($anime['title'] ?? 'Anime') ?>"
                                    style="height: 300px; object-fit: cover;">
                            </div>

                            <div class="card">
                                <div class="card-header">Genres</div>
                                <div class="card-body">
                                    <p class="card-text">Select all that apply:</p>
                                    <div class="genres-list" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($allGenres as $genre): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="genres[]"
                                                    value="<?= $genre['id'] ?>" id="genre<?= $genre['id'] ?>"
                                                    <?= in_array($genre['id'], $selectedGenres) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="genre<?= $genre['id'] ?>">
                                                    <?= htmlspecialchars($genre['name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="anime_detail.php?id=<?= $animeId ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include page footer
include 'includes/footer.php';
?>