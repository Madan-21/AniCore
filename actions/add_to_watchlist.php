<?php
/**
 * Add to Watchlist Action
 * 
 * Adds an anime to the user's watchlist
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/database.php';
require_once '../includes/functions.php';

// Log request (for audit purposes)
error_log("Add to watchlist request received from user ID: " . ($_SESSION['user_id'] ?? 'unknown'));

// Require login
if (!check_login()) {
    header('Location: ../login.php?message=Please log in to add anime to your watchlist&error=1');
    exit;
}

// Check for AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Check if anime_id is provided via POST
if (!isset($_POST['anime_id'])) {
    if ($isAjax) {
        ajax_response(false, 'Invalid request: missing anime ID');
    } else {
        header('Location: ../index.php?message=Invalid request&error=1');
        exit;
    }
}

$animeId = filter_input(INPUT_POST, 'anime_id', FILTER_VALIDATE_INT);
$userId = $_SESSION['user_id'];

if (!$animeId) {
    if ($isAjax) {
        ajax_response(false, 'Invalid anime ID');
    } else {
        header('Location: ../index.php?message=Invalid anime ID&error=1');
        exit;
    }
}

// Check if anime exists
try {
    $stmt = $pdo->prepare("SELECT id, title FROM anime WHERE id = :id");
    $stmt->bindParam(':id', $animeId, PDO::PARAM_INT);
    $stmt->execute();
    $animeData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$animeData) {
        if ($isAjax) {
            ajax_response(false, 'Anime not found');
        } else {
            header('Location: ../index.php?message=Anime not found&error=1');
            exit;
        }
    }
} catch (PDOException $e) {
    log_error("Error checking anime: " . $e->getMessage(), 'error', ['anime_id' => $animeId]);

    if ($isAjax) {
        ajax_response(false, 'Error checking anime');
    } else {
        header('Location: ../index.php?message=Error checking anime: ' . $e->getMessage() . '&error=1');
        exit;
    }
}

// Check if anime is already in user's watchlist
try {
    $stmt = $pdo->prepare("
        SELECT id FROM user_anime_watchlist
        WHERE user_id = :user_id AND anime_id = :anime_id
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Already in watchlist
        if ($isAjax) {
            ajax_response(false, 'This anime is already in your watchlist');
        } else {
            header('Location: ../anime_detail.php?id=' . $animeId . '&message=This anime is already in your watchlist&error=1');
            exit;
        }
    }
} catch (PDOException $e) {
    log_error("Error checking watchlist: " . $e->getMessage(), 'error', ['anime_id' => $animeId, 'user_id' => $userId]);

    if ($isAjax) {
        ajax_response(false, 'Error checking watchlist');
    } else {
        header('Location: ../index.php?message=Error checking watchlist: ' . $e->getMessage() . '&error=1');
        exit;
    }
}

// Get status, rating, and episodes watched from form
$status = filter_input(INPUT_POST, 'status') ?: 'Plan to Watch';
$userRating = filter_input(INPUT_POST, 'user_rating', FILTER_VALIDATE_INT) ?: null;
$episodesWatched = filter_input(INPUT_POST, 'episodes_watched', FILTER_VALIDATE_INT) ?: null;

// Validate status
$validStatuses = ['Watching', 'Completed', 'On-Hold', 'Dropped', 'Plan to Watch'];
if (!in_array($status, $validStatuses)) {
    $status = 'Plan to Watch';
}

// Add anime to watchlist
try {
    // Log for debugging
    log_error("Attempting to add anime to watchlist", 'info', [
        'anime_id' => $animeId,
        'user_id' => $userId,
        'status' => $status,
        'rating' => $userRating,
        'episodes_watched' => $episodesWatched
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO user_anime_watchlist (user_id, anime_id, status, user_rating, episodes_watched)
        VALUES (:user_id, :anime_id, :status, :user_rating, :episodes_watched)
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_rating', $userRating);
    $stmt->bindParam(':episodes_watched', $episodesWatched);
    $stmt->execute();    // Log success
    log_error("Successfully added anime to watchlist", 'info', ['anime_id' => $animeId, 'user_id' => $userId]);

    // Prepare success message
    $successMessage = 'Added "' . htmlspecialchars($animeData['title']) . '" to your ' . $status . ' list';

    if ($isAjax) {
        // Return JSON response for AJAX requests
        ajax_response(true, $successMessage, [
            'anime_id' => $animeId,
            'status' => $status,
            'watchlist_id' => $pdo->lastInsertId(),
            // Include any elements that need to be updated via JS
            'updateElement' => '#anime-status-' . $animeId,
            'updateContent' => '<span class="badge bg-success">In Watchlist (' . htmlspecialchars($status) . ')</span>'
        ]);
    } else {
        // Traditional form response
        header('Location: ../anime_detail.php?id=' . $animeId . '&message=' . urlencode($successMessage));
        exit;
    }
} catch (PDOException $e) {
    // Log the error
    log_error("Database error adding anime to watchlist: " . $e->getMessage(), 'error', [
        'anime_id' => $animeId,
        'user_id' => $userId
    ]);

    if ($isAjax) {
        ajax_response(false, 'Error adding anime to watchlist');
    } else {
        header('Location: ../anime_detail.php?id=' . $animeId . '&message=Error adding anime to watchlist: ' . $e->getMessage() . '&error=1');
        exit;
    }
}
