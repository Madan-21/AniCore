<?php
/**
 * Update Watchlist Item Action
 * 
 * Updates an anime entry in the user's watchlist
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/database.php';
require_once '../includes/functions.php';

// Log request (for audit purposes)
error_log("Update watchlist request received from user ID: " . ($_SESSION['user_id'] ?? 'unknown'));

// Require login
if (!check_login()) {
    header('Location: ../login.php?message=Please log in to update your watchlist&error=1');
    exit;
}

// Check if anime_id is provided via POST
if (!isset($_POST['anime_id'])) {
    header('Location: ../my_watchlist.php?message=Invalid request&error=1');
    exit;
}

$animeId = filter_input(INPUT_POST, 'anime_id', FILTER_VALIDATE_INT);
$userId = $_SESSION['user_id'];

if (!$animeId) {
    header('Location: ../my_watchlist.php?message=Invalid anime ID&error=1');
    exit;
}

// Get form data
$status = filter_input(INPUT_POST, 'status') ?? '';
$userRating = filter_input(INPUT_POST, 'user_rating', FILTER_VALIDATE_INT) ?: null;
$episodesWatched = filter_input(INPUT_POST, 'episodes_watched', FILTER_VALIDATE_INT) ?: null;

// Validate status
$validStatuses = ['Watching', 'Completed', 'On-Hold', 'Dropped', 'Plan to Watch'];
if (!in_array($status, $validStatuses)) {
    header('Location: ../anime_detail.php?id=' . $animeId . '&message=Invalid status&error=1');
    exit;
}

// Validate rating
if ($userRating !== null && ($userRating < 1 || $userRating > 10)) {
    header('Location: ../anime_detail.php?id=' . $animeId . '&message=Rating must be between 1 and 10&error=1');
    exit;
}

// Check if watchlist entry exists
try {
    $stmt = $pdo->prepare("
        SELECT id FROM user_anime_watchlist
        WHERE user_id = :user_id AND anime_id = :anime_id
    ");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        header('Location: ../anime_detail.php?id=' . $animeId . '&message=Anime not found in your watchlist&error=1');
        exit;
    }
} catch (PDOException $e) {
    header('Location: ../anime_detail.php?id=' . $animeId . '&message=Error checking watchlist: ' . $e->getMessage() . '&error=1');
    exit;
}

// Update watchlist entry
try {
    $stmt = $pdo->prepare("
        UPDATE user_anime_watchlist
        SET status = :status, user_rating = :user_rating, episodes_watched = :episodes_watched
        WHERE user_id = :user_id AND anime_id = :anime_id
    ");

    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_rating', $userRating);
    $stmt->bindParam(':episodes_watched', $episodesWatched);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect to anime detail
    header('Location: ../anime_detail.php?id=' . $animeId . '&message=Watchlist entry updated successfully');
    exit;
} catch (PDOException $e) {
    header('Location: ../anime_detail.php?id=' . $animeId . '&message=Error updating watchlist: ' . $e->getMessage() . '&error=1');
    exit;
}
