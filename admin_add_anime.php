<?php
/**
 * Admin Add Anime Page
 * 
 * Allows administrators to add new anime to the database
 */

// Suppress notices for this page to avoid undefined array key warnings
error_reporting(E_ALL & ~E_NOTICE);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require admin access
require_admin();

$message = '';
$error = false;

// Get all available genres for the form
$genres = [];
try {
    $genresStmt = $pdo->query("SELECT id, name FROM genres ORDER BY name");
    $genres = $genresStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching genres: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = trim(htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'));
    $releaseYear = isset($_POST['release_year']) ? (int) $_POST['release_year'] : null;
    $episodeCount = isset($_POST['episode_count']) ? (int) $_POST['episode_count'] : null;
    $selectedGenres = $_POST['genres'] ?? [];

    // Validate required fields
    if (empty($title)) {
        $message = 'Please enter an anime title.';
        $error = true;
    } else {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Insert the anime
            $stmt = $pdo->prepare("
                INSERT INTO anime (title, description, release_year, episode_count)
                VALUES (:title, :description, :release_year, :episode_count)
            ");
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':release_year', $releaseYear, PDO::PARAM_INT);
            $stmt->bindParam(':episode_count', $episodeCount, PDO::PARAM_INT);
            $stmt->execute();

            $animeId = $pdo->lastInsertId();

            // Handle image uploads
            $uploadSuccess = true;
            $posterPath = null;
            $bannerPath = null;

            // Process poster upload if provided
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
                $result = uploadImage('poster', 'posters');
                if ($result['success']) {
                    $posterPath = $result['path'];
                } else {
                    $uploadSuccess = false;
                    $message = $result['message'];
                }
            }

            // Process banner upload if provided
            if ($uploadSuccess && isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
                $result = uploadImage('banner', 'banners');
                if ($result['success']) {
                    $bannerPath = $result['path'];
                } else {
                    $uploadSuccess = false;
                    $message = $result['message'];
                }
            }

            // Update anime with image paths if uploads were successful
            if ($uploadSuccess && ($posterPath || $bannerPath)) {
                $updateFields = [];
                $params = [];

                if ($posterPath) {
                    $updateFields[] = "poster_path = :poster_path";
                    $params[':poster_path'] = $posterPath;
                }

                if ($bannerPath) {
                    $updateFields[] = "banner_path = :banner_path";
                    $params[':banner_path'] = $bannerPath;
                }

                if (!empty($updateFields)) {
                    $updateStmt = $pdo->prepare("UPDATE anime SET " . implode(", ", $updateFields) . " WHERE id = :id");
                    $params[':id'] = $animeId;
                    foreach ($params as $key => $value) {
                        $updateStmt->bindValue($key, $value, PDO::PARAM_STR);
                    }
                    $updateStmt->execute();
                }
            }

            // Add genres if selected
            if (!empty($selectedGenres)) {
                $genreInsert = $pdo->prepare("INSERT INTO anime_genres (anime_id, genre_id) VALUES (:anime_id, :genre_id)");
                foreach ($selectedGenres as $genreId) {
                    $genreInsert->bindValue(':anime_id', $animeId, PDO::PARAM_INT);
                    $genreInsert->bindValue(':genre_id', $genreId, PDO::PARAM_INT);
                    $genreInsert->execute();
                }
            }

            // Commit the transaction
            $pdo->commit();
            $message = "Anime '{$title}' has been added successfully!";
        } catch (PDOException $e) {
            // Roll back the transaction on error
            $pdo->rollBack();
            $message = 'Error adding anime: ' . $e->getMessage();
            $error = true;
            error_log("Error adding anime: " . $e->getMessage());
        }
    }
}

// Function to handle image uploads
function uploadImage($fileField, $folder)
{
    $result = ['success' => false, 'path' => null, 'message' => ''];

    $targetDir = "images/{$folder}/";
    $fileName = basename($_FILES[$fileField]['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Generate a unique filename
    $newFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . time() . '.' . $fileExt;
    $targetPath = $targetDir . $newFileName;

    // Check if file is an actual image
    $check = getimagesize($_FILES[$fileField]['tmp_name']);
    if ($check === false) {
        $result['message'] = "File is not an image.";
        return $result;
    }

    // Check file size (limit to 5MB)
    if ($_FILES[$fileField]['size'] > 5000000) {
        $result['message'] = "File is too large. Maximum size is 5MB.";
        return $result;
    }

    // Allow certain file formats
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExt, $allowedExtensions)) {
        $result['message'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        return $result;
    }

    // Upload file
    if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetPath)) {
        $result['success'] = true;
        $result['path'] = $newFileName;
    } else {
        $result['message'] = "Error uploading file.";
    }

    return $result;
}

// Include header
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3">Add New Anime</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="admin.php">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Anime</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $error ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h2 class="card-title h5 mb-0">Add New Anime</h2>
    </div>
    <div class="card-body">
        <form action="admin_add_anime.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="release_year" class="form-label">Release Year</label>
                    <input type="number" class="form-control" id="release_year" name="release_year" min="1900"
                        max="<?= date('Y') + 1 ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="episode_count" class="form-label">Episode Count</label>
                    <input type="number" class="form-control" id="episode_count" name="episode_count" min="1">
                </div>
            </div>

            <div class="mb-3">
                <label for="genres" class="form-label">Genres</label>
                <select class="form-select" id="genres" name="genres[]" multiple size="5">
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?= $genre['id'] ?>"><?= htmlspecialchars($genre['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Hold Ctrl (Windows) or Command (Mac) to select multiple genres.</div>
            </div>

            <div class="mb-3">
                <label for="poster" class="form-label">Poster Image</label>
                <input type="file" class="form-control" id="poster" name="poster" accept="image/*">
                <div class="form-text">Recommended size: 300x450 pixels</div>
            </div>

            <div class="mb-3">
                <label for="banner" class="form-label">Banner Image</label>
                <input type="file" class="form-control" id="banner" name="banner" accept="image/*">
                <div class="form-text">Recommended size: 1200x400 pixels</div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="admin.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Anime</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>