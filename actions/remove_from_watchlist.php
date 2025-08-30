<?php
/**
 * Remove From Watchlist Action
 * 
 * Removes an anime from the user's watchlist
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require login
if (!check_login()) {
    header('Location: ../login.php?message=Please log in to manage your watchlist&error=1');
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

// Remove from watchlist
try {
    $stmt = $pdo->prepare("
        DELETE FROM user_anime_watchlist
        WHERE user_id = :user_id AND anime_id = :anime_id
    ");

    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        header('Location: ../my_watchlist.php?message=Anime not found in your watchlist&error=1');
        exit;
    }

    // Determine where to redirect based on HTTP_REFERER
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($referer, 'anime_detail.php') !== false) {
        header('Location: ../anime_detail.php?id=' . $animeId . '&message=Anime removed from your watchlist');
    } else {
        header('Location: ../my_watchlist.php?message=Anime removed from your watchlist');
    }
    exit;
} catch (PDOException $e) {
    header('Location: ../my_watchlist.php?message=Error removing from watchlist: ' . $e->getMessage() . '&error=1');
    exit;
}
