<?php
/**
 * Get Movies API - Retrieve stored movies by section
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/constants.php';

$section = $_GET['section'] ?? 'trending';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? 20);
$offset = ($page - 1) * $limit;

// Validar sección
$validSections = ['trending', 'anime', 'series'];
if (!in_array($section, $validSections)) {
    $section = 'trending';
}

$db = getDB();

try {
    // Obtener películas de la sección
    $stmt = $db->prepare('
        SELECT id, tmdb_id, title, description, poster_path, backdrop_path, media_type
        FROM movies
        WHERE section = ? OR section = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ');
    $stmt->bindValue(1, $section, PDO::PARAM_STR);
    $stmt->bindValue(2, '', PDO::PARAM_STR);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();

    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener total
    $totalStmt = $db->prepare('SELECT COUNT(*) FROM movies WHERE section = ?');
    $totalStmt->execute([$section]);
    $total = $totalStmt->fetchColumn();

    echo json_encode([
        'results' => $movies,
        'page' => $page,
        'total' => $total,
        'pages' => ceil($total / $limit),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
