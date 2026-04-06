<?php
/**
 * API Configuration
 * IMPORTANTE: SIEMPRE ESPAÑOL (es-MX) en todas las consultas a TMDB
 */

// TMDB API Configuration
define('TMDB_API_KEY', '2628b2d65ef5a50b08d992e0a7c2de56');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');

// IMPORTANTE: SIEMPRE FORZAR ESPAÑOL
define('LANGUAGE', 'es-MX');
define('LANGUAGE_FALLBACKS', ['es-MX', 'es-LA', 'es']);

// Cache settings
define('CACHE_DURATION', 3600 * 24); // 24 hours

function fetchTMDB($endpoint, $params = []) {
    // IMPORTANTE: SIEMPRE AÑADIR language=es-MX a los parámetros
    $params['api_key'] = TMDB_API_KEY;
    $params['language'] = LANGUAGE;

    $url = TMDB_BASE_URL . $endpoint . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    return null;
}

function getTrendingMovies() {
    // Obtener tendencias (películas y series) en ESPAÑOL
    return fetchTMDB('/trending/all/week', []);
}

function getAnimeTV() {
    // Obtener anime latino en ESPAÑOL
    return fetchTMDB('/discover/tv', [
        'with_genres' => 16,
        'with_original_language' => 'ja',
    ]);
}

function getSeriesVIP() {
    // Obtener series en ESPAÑOL
    return fetchTMDB('/discover/tv', [
        'sort_by' => 'popularity.desc',
    ]);
}

function searchTMDB($query) {
    // Buscar en TMDB con language=es-MX FORZADO
    return fetchTMDB('/search/multi', [
        'query' => $query,
    ]);
}

function getMovieDetails($tmdbId, $isTV = false) {
    // Obtener detalles con ESPAÑOL forzado
    $endpoint = $isTV ? '/tv/' . $tmdbId : '/movie/' . $tmdbId;
    return fetchTMDB($endpoint, []);
}

function getEpisodes($tvId, $season = 1) {
    // Obtener episodios en ESPAÑOL
    return fetchTMDB('/tv/' . $tvId . '/season/' . $season, []);
}

function getCachedSearch($query) {
    $db = getDB();
    $stmt = $db->prepare('SELECT results FROM search_cache WHERE query = ? AND expires_at > datetime("now")');
    $stmt->execute([$query]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return json_decode($result['results'], true);
    }

    // Si no está en caché, buscar en TMDB
    $data = searchTMDB($query);

    if ($data) {
        // Guardar en caché
        $expire = date('Y-m-d H:i:s', time() + CACHE_DURATION);
        $stmt = $db->prepare('INSERT OR REPLACE INTO search_cache (query, results, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$query, json_encode($data), $expire]);
    }

    return $data;
}

// Requiere config/db.php
require_once __DIR__ . '/db.php';
