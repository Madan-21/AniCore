<?php
/**
 * Helper Functions
 */

/**
 * Check if a user is logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function check_login()
{
    // Check if session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user_id exists and is not empty
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect if user is not logged in
 *
 * @param string $redirect URL to redirect to
 */
function require_login($redirect = 'login.php')
{
    // Make sure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Debug log for authentication issues
    error_log("Checking login. Session data: " . print_r($_SESSION, true));

    // Check if user is logged in (user_id is set AND not empty)
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        error_log("Login required but user is not logged in. Redirecting to $redirect");
        header("Location: $redirect?message=Please log in to access this page&error=1");
        exit;
    }

    error_log("User authenticated successfully: user_id={$_SESSION['user_id']}");
}

/**
 * Check if a user has admin role
 *
 * @return bool True if user is an admin, false otherwise
 */
function check_admin()
{
    // Check if session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in and has admin role
    return isset($_SESSION['user_id']) &&
        !empty($_SESSION['user_id']) &&
        isset($_SESSION['role']) &&
        $_SESSION['role'] === 'admin';
}

/**
 * Redirect if user is not an admin
 *
 * @param string $redirect URL to redirect to
 */
function require_admin($redirect = 'index.php')
{
    // First make sure the user is logged in
    require_login('login.php');

    // Check if user has admin role
    if (!check_admin()) {
        error_log("Admin access required but user is not an admin. Redirecting to $redirect");
        header("Location: $redirect?message=You do not have permission to access this page&error=1");
        exit;
    }

    error_log("User authenticated as admin: user_id={$_SESSION['user_id']}");
}

/**
 * Get anime details by ID
 *
 * @param PDO $pdo Database connection
 * @param int $animeId Anime ID
 * @return array|false Anime details or false if not found
 */
function get_anime_details($pdo, $animeId)
{
    try {
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
            WHERE a.id = :id
            GROUP BY a.id
        ");

        $stmt->bindParam(':id', $animeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get user's watchlist entry for a specific anime
 *
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param int $animeId Anime ID
 * @return array|false Watchlist entry or false if not found
 */
function get_watchlist_entry($pdo, $userId, $animeId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM user_anime_watchlist
            WHERE user_id = :user_id AND anime_id = :anime_id
        ");

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':anime_id', $animeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get all watchlist entries for a user
 *
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param string|null $status Optional status filter
 * @return array User's watchlist
 */
function get_user_watchlist($pdo, $userId, $status = null)
{
    try {
        $query = "
            SELECT w.*, a.title, a.poster_path, a.episode_count
            FROM user_anime_watchlist w
            JOIN anime a ON w.anime_id = a.id
            WHERE w.user_id = :user_id
        ";

        if ($status) {
            $query .= " AND w.status = :status";
        }

        $query .= " ORDER BY w.date_status_updated DESC";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        if ($status) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Send JSON response for AJAX requests
 *
 * @param bool $success Whether the request was successful
 * @param string $message Response message
 * @param array $data Additional data to include in the response
 */
function ajax_response($success, $message, $data = [])
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Log error with context
 *
 * @param string $message Error message
 * @param string $level Error level (error, warning, info)
 * @param array $context Additional context for the error
 */
function log_error($message, $level = 'error', $context = [])
{
    $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
    error_log("[$level] $message$contextStr");
}