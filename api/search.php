<?php
/**
 * Search API - TMDB Search
 * IMPORTANTE: SIEMPRE ESPAÑOL (es-MX)
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/api.php';

$query = trim($_GET['q'] ?? '');

if (empty($query) || strlen($query) < 2) {
    http_response_code(400);
    echo json_encode(['error' => 'Query too short']);
    exit;
}

// Buscar en caché primero
$data = getCachedSearch($query);

if ($data) {
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'No results', 'results' => []]);
}
